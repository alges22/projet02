<?php

namespace App\Http\Controllers;

use App\Models\Authenticite;
use Illuminate\Http\Request;
use App\Models\ParcoursSuivi;
use App\Models\EservicePayment;
use Illuminate\Support\Facades\DB;
use App\Models\PermisInternational;
use App\Models\EserviceParcourSuivi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ApiController;
use App\Models\PermisInternationalRejet;
use App\Models\Service;
use App\Models\User;
use App\Models\UserTransaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PermisInternationalController extends ApiController
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'num_permis' => 'required|string',
                'permis_file' => 'required|image',
                'categorie_permis_ids' => 'required',

            ]);
            if ($validator->fails()) {
                return $this->errorResponse('Une erreur est survenue', $validator->errors()->toArray());
            }
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }
            $npi = $user->npi;
            $permisFile=null;
            if ($request->hasFile('permis_file')) {
                $permisFile = $request->file('permis_file')->store('permis_file', 'public');
            }

                $permisInternational = PermisInternational::create([
                    'email' => $request->input('email'),
                    'npi' => $npi,
                    'num_permis' => $request->input('num_permis'),
                    'permis_file' => $permisFile,
                    'categorie_permis_ids' => json_encode($request->input('categorie_permis_ids')),
                    'state' => 'payment',
                ]);

                $amount = Service::permisinternationalAmount();
                $transaction = $this->startPaymentProcess([
                    "amount" => $amount,
                ]);
                $transactionId = data_get($transaction, "id");
                $subscription = UserTransaction::create([
                    'uuid'=> Str::uuid(),
                    'service' => 'permis-international',
                    'service_id' => $permisInternational->id,
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
                ],'Demande de permis international créée avec succès');

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
            "callback_url" => route("payments.piprocced"),
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
            $demande = PermisInternational::find($Id);
            if (!$demande) {
                return null;

            }

            $payment = EservicePayment::create([
                'npi' => $npi,
                'montant' => $amount,
                'status' =>'approved',
                'transactionId' => $transactionid,
                'payment_for' => 'permis-international',
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
                'slug' => 'demande-permis-international',
                'service' => 'Permis International',
                'candidat_id' => $user->id,
                'message' => "Votre demande de permis international a été soumise avec succès et est en cours de traitement par l'ANaTT.",
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
                'date_payment' => 'required|date',
                'transactionId'=> 'required',
                'permis_international_id'=>'required|exists:permis_internationals,id',
                'status'=>'required|in:approved'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Une erreur est survenue', $validator->errors()->toArray());
            }
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

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
            if ($transaction->amount != Service::permisinternationalAmount()) {
                return $this->errorResponse('Le montant de paiement est incorrect');
            }

            $npi = $user->npi;
            $demandePI = PermisInternational::find($request->input('permis_international_id'));
            if (!$demandePI) {
                return $this->errorResponse('La demande est introuvable');
            }
            $payment = EservicePayment::create([
                'npi' => $npi,
                'montant' => $request->input('montant'),
                'phone' => $request->input('phone'),
                'operateur' => $request->input('operateur'),
                'status' => $request->input('status'),
                'transactionId' => $request->input('transactionId'),
                'payment_for' => 'permis-international',
                'date_payment' => $request->input('date_payment'),
            ]);

            // Si tout se passe bien avec le paiement, mettre à jour le statut de la demande
            $demandePI->update(['state' => 'init']);

            $montant =  $request->input('montant');
            $candidatEndpoint = env('CANDIDAT');
            $encryptednpi = Crypt::encrypt($npi);
            $urlWithCode = $candidatEndpoint . 'eservice-facture/' . $encryptednpi;
            $url = route('eservice-facture', ['encryptednpi' => $encryptednpi]);
            $message = "Paiement de " . $montant . "F CFA éffectué avec succès pour l'obtention du permis international. Cliquez pour télécharger <a href='" . $urlWithCode . "' target='_blank'>ici</a>";
            //notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-permis-international',
                'service' => 'Permis International',
                'candidat_id' => $user->id,
                'message' => "Votre demande de permis international a été soumise avec succès et est en cours de traitement par l'ANaTT.",
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

    public function getPermisI($id)
    {
        try {
            $permisInternationalRejet = PermisInternationalRejet::findOrFail($id);

            if (!$permisInternationalRejet) {
                return $this->errorResponse('La demande est introuvable');
            }

            $permisInternationalID = $permisInternationalRejet->permis_international_id;
            $demande = PermisInternational::find($permisInternationalID);

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
            $PermisInternationalRejet = PermisInternationalRejet::findOrFail($id);

            if (!$PermisInternationalRejet) {
                return $this->errorResponse('Le rejet d\'authenticité est introuvable', null, 404);
            }

            $permisID = $PermisInternationalRejet->permis_international_id;
            $demande = PermisInternational::find($permisID);

            if (!$demande) {
                return $this->errorResponse('La demande d\'authenticité associée est introuvable', null, 404);
            }

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'num_permis' => 'required|string',
                'permis_file' => 'image',
                'categorie_permis_ids' => 'required',

            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors()->toArray());
            }

            $permisFile = null;

            try {
                if ($request->hasFile('permis_file')) {
                    $permisFile = $request->file('permis_file')->store('permis_file', 'public');
                }
            } catch (\Throwable $fileException) {
                return $this->errorResponse('Erreur lors du téléchargement du fichier', null, 500);
            }

            DB::beginTransaction();

            try {
                // Mettre à jour la demande
                $demande->update([
                    'email' => $request->email,
                    'num_permis' => $request->num_permis,
                    'state' => 'pending',
                    'categorie_permis_ids' => json_encode($request->input('categorie_permis_ids')),

                ]);

                // Mettre à jour le champ permis_file seulement s'il est présent dans la requête
                if ($permisFile !== null) {
                    $demande->update(['permis_file' => $permisFile]);
                }

                $state = 'pending';
                $PermisInternationalRejet->update([
                    'state' => $state,
                    'date_correction' => now(),
                ]);

                $user = Auth::user();
                if (!$user) {
                    return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
                }
                $npi = $demande->npi;
                $parcoursSuiviData = [
                    'npi' => $npi,
                    'slug' => 'correction-permis-international',
                    'service' => 'Permis International',
                    'candidat_id' => $user->id,
                    'message' => "Votre correction a été soumise avec succès et est en cours de traitement par l'ANaTT.",
                    'date_action' => now(),
                ];
                $parcoursSuivi = EserviceParcourSuivi::create($parcoursSuiviData);
                DB::commit();

                return $this->successResponse($demande, 'Mise à jour effectuée avec succès');
            } catch (\Throwable $e) {
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
