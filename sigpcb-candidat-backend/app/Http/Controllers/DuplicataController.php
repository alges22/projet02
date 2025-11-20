<?php

namespace App\Http\Controllers;

use App\Models\Duplicata;
use Illuminate\Http\Request;
use App\Models\ParcoursSuivi;
use App\Models\DuplicataRejet;
use App\Models\EservicePayment;
use Illuminate\Support\Facades\DB;
use App\Models\EserviceParcourSuivi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ApiController;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\Validator;


class DuplicataController extends ApiController
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'phone' => 'required',
                'num_permis' => 'required|string',
                'type'=>'required',
                'file'=>'required|image',
                'annexe_id'=>'required|integer',
                'group_sanguin'=>'required|string'
            ]);
            if ($validator->fails()) {
                return $this->errorResponse('Une erreur est survenue', $validator->errors()->toArray());
            }
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }
            $npi = $user->npi;
            $File=null;
            if ($request->hasFile('file')) {
                $File = $request->file('file')->store('file', 'public');
            }

                $duplicata = Duplicata::create([
                    'email' => $request->input('email'),
                    'annexe_id' => $request->input('annexe_id'),
                    'phone' => $request->input('phone'),
                    'npi' => $npi,
                    'file' => $File,
                    'num_permis' => $request->input('num_permis'),
                    'type' => $request->input('type'),
                    'state' => 'payment',
                    'group_sanguin'=>$request->input('group_sanguin'),
                ]);

                $amount = Service::duplicataAmount();
                $transaction = $this->startPaymentProcess([
                    "amount" => $amount,
                ]);
                $transactionId = data_get($transaction, "id");
                $subscription = UserTransaction::create([
                    'uuid'=> Str::uuid(),
                    'service' => 'duplicata',
                    'service_id' => $duplicata->id,
                    'npi' => $npi,
                    'status' => 'init',
                    "amount" => $amount,
                    "transaction_id" => $transactionId,
                    "expired_at" => now()
                ]);

                $tokenUrl = $transaction->generateToken();
                DB::commit();
                return $this->successResponse([
                    "uuid" => $subscription->uuid,
                    "fedapay" => $tokenUrl,
                    "transactionId" => $transactionId
                ],'Demande de duplicata créée avec succès');

        } catch (\Throwable $e) {
            logger()->error($e);
            DB::rollBack();
            return $this->errorResponse('Erreur lors de la création de le demande', $e->getMessage(), '', 500);
        }
    }
    private function startPaymentProcess(array $data)
    {

        $fedaPayEnv = env('FEDAPAY_ENV', 'sandbox');
        if ($fedaPayEnv === 'live') {
            \FedaPay\FedaPay::setEnvironment('live');
            \FedaPay\FedaPay::setApiKey(env('FEDAPAY_LIVE_PRIVATE_KEY'));
        } else {
            \FedaPay\FedaPay::setEnvironment('sandbox');
            \FedaPay\FedaPay::setApiKey(env('FEDAPAY_SANDBOX_PRIVATE_KEY'));
        }

        $user = auth()->user();
        /* Create the transaction */
        return  \FedaPay\Transaction::create(array(
            "description" => "Souscription à " . env('APP_ENV'),
            "amount" => data_get($data, "amount"),
            "currency" => ["iso" => "XOF"],
            "callback_url" => route("payments.dprocced"),
        ));
    }
    public function procced(Request $request)
    {
        $request->validate([
            "id" => "required",
        ]);
        $fedaPayEnv = env('FEDAPAY_ENV', 'sandbox');
        if ($fedaPayEnv === 'live') {
            \FedaPay\FedaPay::setEnvironment('live');
            \FedaPay\FedaPay::setApiKey(env('FEDAPAY_LIVE_PRIVATE_KEY'));
        } else {
            \FedaPay\FedaPay::setEnvironment('sandbox');
            \FedaPay\FedaPay::setApiKey(env('FEDAPAY_SANDBOX_PRIVATE_KEY'));
        }

        $ID = $request->get('id');
        $transaction = \FedaPay\Transaction::retrieve($ID);
        if ($transaction->amount != Service::permisinternationalAmount()) {
            return null;
        }
        $subscription = UserTransaction::where(["transaction_id" => $ID])->first();

        if (!$subscription) {
            return null;
        }
        if ($subscription->status == 'approved') {
            return null;
        }
        if ($transaction->status == "approved") {
            $paid = true;

            $subscription->status = "approved";
            $subscription->expired_at = now()->addMonths(1);
            $subscription->save();

            //la continuité
            $Id = $subscription->service_id;
            $npi = $subscription->npi;
            $amount = $subscription->amount;
            $transactionid = $subscription->transaction_id;
            $demande = Duplicata::find($Id);
            if (!$demande) {
                return null;

            }

            $payment = EservicePayment::create([
                'npi' => $npi,
                'montant' => $amount,
                'status' =>'approved',
                'transactionId' => $transactionid,
                'payment_for' => 'duplicata',
                'phone' => data_get($transaction,'payment_method.number'),
                'operateur' => data_get($transaction,'mode'),
                'date_payment' => now(),
            ]);

            // Si tout se passe bien avec le paiement, mettre à jour le statut de la demande
            $demande->update(['state' => 'init']);

            $candidatEndpoint = env('CANDIDAT');
            $encryptednpi = Crypt::encrypt($npi);
            $urlWithCode = $candidatEndpoint . 'eservice-facture/' . $encryptednpi;
            $url = route('eservice-facture', ['encryptednpi' => $encryptednpi]);
            $user=User::where('npi',$npi)->first();
              // Vérifier si l'utilisateur existe
            if (!$user) {
                return null;

            }
            $message = "Paiement de " . $amount . "F CFA éffectué avec succès pour l'obtention de l'authenticité du permis. Cliquez pour télécharger <a href='" . $urlWithCode . "' target='_blank'>ici</a>";
            //notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-duplicata',
                'service' => 'Duplicata',
                'candidat_id' => $user->id,
                'message' => "Votre demande de duplicata a été soumise avec succès et est en cours de traitement par l'ANaTT.",
                'date_action' => now(),
                'url'=>$url
            ];

            $payment['url'] = $url;

            // Créer le parcours suivi
            $parcoursSuivi = EserviceParcourSuivi::create($parcoursSuiviData);
            return null;
        }

    }
    public function eservicePayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'montant' => 'required|numeric',
                'phone' => 'required|string|max:25',
                'operateur' => 'required|string',
                'payment_for' => 'required|string',
                'date_payment' => 'required|date',
                'transactionId'=> 'required',
                'duplicata_id'=>'required|exists:duplicatas,id',
                'status'=>'required|in:approved'

            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Une erreur est survenue', $validator->errors()->toArray());
            }
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }
            $demandeDuplicata = Duplicata::find($request->input('duplicata_id'));
            if (!$demandeDuplicata) {
                return $this->errorResponse('La demande est introuvable');
            }
            $npi = $user->npi;

            $transactionId = $request->input('transactionId');
            $fedaPayEnv = env('FEDAPAY_ENV', 'sandbox');
            if ($fedaPayEnv === 'live') {
                \FedaPay\FedaPay::setEnvironment('live');
                \FedaPay\FedaPay::setApiKey(env('FEDAPAY_LIVE_PRIVATE_KEY'));
            } else {
                \FedaPay\FedaPay::setEnvironment('sandbox');
                \FedaPay\FedaPay::setApiKey(env('FEDAPAY_SANDBOX_PRIVATE_KEY'));
            }
            $transaction = \FedaPay\Transaction::retrieve($transactionId);
            if ($transaction->status != "approved") {
                return $this->errorResponse('Fedapay indique que le paiement a échoué.');
            }
            if ($transaction->amount != Service::duplicataAmount()) {
                return $this->errorResponse('Le montant de paiement est incorrect');
            }
            $payment = EservicePayment::create([
                'npi' => $npi,
                'montant' => $request->input('montant'),
                'phone' => $request->input('phone'),
                'operateur' => $request->input('operateur'),
                'status' => $request->input('status'),
                'transactionId' => $request->input('transactionId'),
                'payment_for' => 'duplicata',
                'date_payment' => $request->input('date_payment'),
            ]);

            // Si tout se passe bien avec le paiement, mettre à jour le statut de la demande
            $demandeDuplicata->update(['state' => 'init']);
            $montant =  $request->input('montant');
            $candidatEndpoint = env('CANDIDAT');
            $encryptednpi = Crypt::encrypt($npi);
            $urlWithCode = $candidatEndpoint . 'eservice-facture/' . $encryptednpi;
            $url = route('eservice-facture', ['encryptednpi' => $encryptednpi]);
            $message = "Paiement de " . $montant . "F CFA éffectué avec succès pour l'obtention du duplicata. Cliquez pour télécharger <a href='" . $urlWithCode . "' target='_blank'>ici</a>";
            //notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-duplicata',
                'service' => 'Duplicata',
                'candidat_id' => $user->id,
                'message' => "Votre demande de duplicata a été soumise avec succès et est en cours de traitement par l'ANaTT.",
                'date_action' => now(),
                'url'=>$url
            ];

            // Créer le parcours suivi
            $payment['url'] = $url;
            $parcoursSuivi = EserviceParcourSuivi::create($parcoursSuiviData);
            return $this->successResponse($payment,$message);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Erreur lors du paiement', $e->getMessage(), '', 500);
        }
    }

    public function getDuplicata($id)
    {
        try {
            $duplicataRejet = DuplicataRejet::findOrFail($id);

            if (!$duplicataRejet) {
                return $this->errorResponse('La demande est introuvable');
            }

            $duplicataID = $duplicataRejet->duplicata_id;
            $demande = Duplicata::find($duplicataID);

            if (!$demande) {
                return $this->errorResponse('La demande est introuvable');
            }

            return $this->successResponse($demande);
        } catch (\Throwable $e) {
            logger()->error($e);
            $message = 'Une erreur s\'est produite : ' . $e->getMessage();
            return $this->errorResponse("Une erreur est survenue lors de la récupération.");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Vérifier l'existence du rejet de duplicata
            $DuplicataRejet = DuplicataRejet::find($id);

            if (!$DuplicataRejet) {
                return $this->errorResponse('Le rejet est introuvable', null, 404);
            }

            // Vérifier l'existence de la demande de duplicata associée
            $DuplicataID = $DuplicataRejet->duplicata_id;
            $demande = Duplicata::find($DuplicataID);

            if (!$demande) {
                return $this->errorResponse('La demande associée est introuvable', null, 404);
            }

            // Validation des données de la requête
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'phone' => 'required',
                'num_permis' => 'required|string',
                'type'=>'required',
                'file'=>'image',
                'annexe_id'=>'required|integer',
                'group_sanguin'=>'required|string'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors()->toArray());
            }

            // Vérifier et traiter le fichier file
            $file = null;

            try {
                if ($request->hasFile('file')) {
                    $file = $request->file('file')->store('duplicata_files', 'public');
                }
            } catch (\Throwable $fileException) {
                return $this->errorResponse('Erreur lors du téléchargement du fichier', null, 500);
            }

            // Commencer la transaction
            DB::beginTransaction();

            try {
                // Mettre à jour la demande de duplicata
                $demande->update([
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'num_permis' => $request->num_permis,
                    'type' => $request->type,
                    'annexe_id' => $request->annexe_id,
                    'group_sanguin' => $request->group_sanguin,
                    'state' => 'pending',
                ]);

                // Mettre à jour le rejet de duplicata
                $state = 'pending';
                $DuplicataRejet->update([
                    'state' => $state,
                    'date_correction' => now(),
                ]);
                if ($file !== null) {
                    $demande->update(['file' => $file]);
                }

                $user = Auth::user();
                if (!$user) {
                    return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
                }
                $npi = $demande->npi;
                $parcoursSuiviData = [
                    'npi' => $npi,
                    'slug' => 'correction-duplicata',
                    'service' => 'Duplicata',
                    'candidat_id' => $user->id,
                    'message' => "Votre correction a été soumise avec succès et est en cours de traitement par l'ANaTT.",
                    'date_action' => now(),
                ];
                $parcoursSuivi = EserviceParcourSuivi::create($parcoursSuiviData);
                // Valider la transaction
                DB::commit();

                return $this->successResponse($demande, 'Mise à jour effectuée avec succès');
            } catch (\Throwable $e) {
                // Annuler la transaction en cas d'erreur
                DB::rollBack();
                logger()->error($e);
                return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, 500);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, 500);
        }
    }

}
