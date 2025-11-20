<?php

namespace App\Http\Controllers;

use App\Models\Echange;
use App\Models\EchangeRejet;
use Illuminate\Http\Request;
use App\Models\ParcoursSuivi;
use Illuminate\Support\Str;
use App\Models\EservicePayment;
use Illuminate\Support\Facades\DB;
use App\Models\EserviceParcourSuivi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ApiController;
use App\Models\Service;
use App\Models\User;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\Validator;

class EchangeController extends ApiController
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'num_permis' => 'required|string',
                'delivrance_ville' => 'required|string',
                'group_sanguin' => 'required|string',
                'categorie_permis_ids' => 'required',
                'delivrance_date' => 'required|string',
                'structure_email' => 'required|email',
                'group_sanguin_file' => 'required|image',
                'authenticite_file' => 'required|image',
                'permis_file' => 'required|image',
            ]);
            if ($validator->fails()) {
                return $this->errorResponse('Une erreur est survenue', $validator->errors()->toArray());
            }
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }
            $npi = $user->npi;
            $groupsanguinFile=null;
            if ($request->hasFile('group_sanguin_file')) {
                $groupsanguinFile = $request->file('group_sanguin_file')->store('group_sanguin_file', 'public');
            }

            $permisFile=null;
            if ($request->hasFile('permis_file')) {
                $permisFile = $request->file('permis_file')->store('permis_file', 'public');
            }
            $authenticiteFile=null;
            if ($request->hasFile('authenticite_file')) {
                $authenticiteFile = $request->file('authenticite_file')->store('authenticite_file', 'public');
            }

                $echange = Echange::create([
                    'email' => $request->input('email'),
                    'npi' => $npi,
                    'num_permis' => $request->input('num_permis'),
                    'group_sanguin_file' => $groupsanguinFile,
                    'state' => 'payment',
                    'structure_email' =>$request->input('structure_email'),
                    'delivrance_date' =>$request->input('delivrance_date'),
                    'delivrance_ville' =>$request->input('delivrance_ville'),
                    'group_sanguin' =>$request->input('group_sanguin'),
                    'categorie_permis_ids' => json_encode($request->input('categorie_permis_ids')),
                    'permis_file' => $permisFile,
                    'authenticite_file' => $authenticiteFile,
                ]);

                $amount = Service::echangeAmount();
                $transaction = $this->startPaymentProcess([
                    "amount" => $amount,
                ]);
                $transactionId = data_get($transaction, "id");
                $subscription = UserTransaction::create([
                    'uuid'=> Str::uuid(),
                    'service' => 'echange',
                    'service_id' => $echange->id,
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
                ],'Demande d\'échange de permis créée avec succès');

        } catch (\Throwable $e) {
            logger()->error($e);
            DB::rollBack();
            return $this->errorResponse('Erreur lors de la création de le demande d\'échange', $e->getMessage(), '', 500);
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
            "callback_url" => route("payments.eprocced"),
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
            $demande = Echange::find($Id);
            if (!$demande) {
                return null;

            }

            $payment = EservicePayment::create([
                'npi' => $npi,
                'montant' => $amount,
                'status' =>'approved',
                'transactionId' => $transactionid,
                'payment_for' => 'echange',
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
                'slug' => 'demande-echange',
                'service' => 'Echange',
                'candidat_id' => $user->id,
                'message' => "Votre demande d'échange de permis a été soumise avec succès et est en cours de traitement par l'ANaTT.",
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
                'echange_id'=>'required|exists:echanges,id',
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
            if ($transaction->amount != Service::echangeAmount()) {
                return $this->errorResponse('Le montant de paiement est incorrect');
            }

            $demandeEchange = Echange::find($request->input('echange_id'));
            if (!$demandeEchange) {
                return $this->errorResponse('La demande est introuvable');
            }
            $npi = $user->npi;

            $payment = EservicePayment::create([
                'npi' => $npi,
                'montant' => $request->input('montant'),
                'phone' => $request->input('phone'),
                'operateur' => $request->input('operateur'),
                'status' => $request->input('status'),
                'transactionId' => $request->input('transactionId'),
                'payment_for' => 'echange-permis',
                'date_payment' => $request->input('date_payment'),
            ]);

            // Si tout se passe bien avec le paiement, mettre à jour le statut de la demande
            $demandeEchange->update(['state' => 'init']);

            $montant =  $request->input('montant');
            $candidatEndpoint = env('CANDIDAT');
            $encryptednpi = Crypt::encrypt($npi);
            $urlWithCode = $candidatEndpoint . 'eservice-facture/' . $encryptednpi;
            $url = route('eservice-facture', ['encryptednpi' => $encryptednpi]);
            $message = "Paiement de " . $montant . "F CFA éffectué avec succès pour l'échange de votre permis. Cliquez pour télécharger <a href='" . $urlWithCode . "' target='_blank'>ici</a>";
            //notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-echange',
                'service' => 'Echange',
                'candidat_id' => $user->id,
                'message' => "Votre demande d'échange de permis a été soumise avec succès et est en cours de traitement par l'ANaTT.",
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

    public function getEchange($id)
    {
        try {
            $echangeRejet = EchangeRejet::findOrFail($id);

            if (!$echangeRejet) {
                return $this->errorResponse('La demande est introuvable');
            }

            $echangeID = $echangeRejet->echange_id;
            $demande = Echange::find($echangeID);

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
            // Vérifier l'existence du rejet d'échange
            $EchangeRejet = EchangeRejet::find($id);

            if (!$EchangeRejet) {
                return $this->errorResponse('Le rejet est introuvable', null, 404);
            }
            // Vérifier l'existence de la demande d'échange associée
            $EchangeID = $EchangeRejet->echange_id;
            $demande = Echange::find($EchangeID);

            if (!$demande) {
                return $this->errorResponse('La demande associée est introuvable', null, 404);
            }

            // Validation des données de la requête
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'num_permis' => 'required|string',
                'delivrance_ville' => 'required|string',
                'group_sanguin' => 'required|string',
                'categorie_permis_ids' => 'required',
                'delivrance_date' => 'required|string',
                'structure_email' => 'required|email',
                'group_sanguin_file' => 'image',
                'permis_file' => 'image',
                'authenticite_file' => 'image',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors()->toArray());
            }

            $groupSanguinFile = null;

            try {
                if ($request->hasFile('group_sanguin_file')) {
                    $groupSanguinFile = $request->file('group_sanguin_file')->store('group_sanguin_file', 'public');
                }
            } catch (\Throwable $fileException) {
                return $this->errorResponse('Erreur lors du téléchargement du fichier group_sanguin_file', null, 500);
            }

            $permisFile = null;

            try {
                if ($request->hasFile('permis_file')) {
                    $permisFile = $request->file('permis_file')->store('permis_file', 'public');
                }
            } catch (\Throwable $fileException) {
                return $this->errorResponse('Erreur lors du téléchargement du fichier permis_file', null, 500);
            }

            $authenticiteFile = null;

            try {
                if ($request->hasFile('authenticite_file')) {
                    $authenticiteFile = $request->file('authenticite_file')->store('authenticite_file', 'public');
                }
            } catch (\Throwable $fileException) {
                return $this->errorResponse('Erreur lors du téléchargement du fichier authenticite_file', null, 500);
            }
            // Commencer la transaction
            DB::beginTransaction();

            try {
                // Mettre à jour la demande
                $demande->update([
                    'email' => $request->email,
                    'num_permis' => $request->num_permis,
                    'delivrance_ville' => $request->delivrance_ville,
                    'group_sanguin' => $request->group_sanguin,
                    'categorie_permis_ids' => json_encode($request->input('categorie_permis_ids')),
                    'delivrance_date' => $request->delivrance_date,
                    'structure_email' => $request->structure_email,
                    'state' => 'pending',
                ]);

                // Mettre à jour le rejet d'échange
                $state = 'pending';
                $EchangeRejet->update([
                    'state' => $state,
                    'date_correction' => now(),
                ]);

                if ($permisFile !== null) {
                    $demande->update(['permis_file' => $permisFile]);
                }
                if ($groupSanguinFile !== null) {
                    $demande->update(['group_sanguin_file' => $groupSanguinFile]);
                }
                if ($authenticiteFile !== null) {
                    $demande->update(['authenticite_file' => $authenticiteFile]);
                }

                $user = Auth::user();
                if (!$user) {
                    return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
                }
                $npi = $demande->npi;
                $parcoursSuiviData = [
                    'npi' => $npi,
                    'slug' => 'correction-echange',
                    'service' => 'Echange',
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
