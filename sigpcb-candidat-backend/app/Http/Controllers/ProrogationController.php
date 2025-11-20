<?php

namespace App\Http\Controllers;

use App\Models\Prorogation;
use Illuminate\Http\Request;
use App\Models\EservicePayment;
use App\Models\ProrogationRejet;
use Illuminate\Support\Facades\DB;
use App\Models\EserviceParcourSuivi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ApiController;
use App\Models\Service;
use App\Models\User;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class ProrogationController extends ApiController
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'num_permis' => 'required|string',
                'permis_file' => 'required|image',
                'group_sanguin'=>'required',
                'group_sanguin_file'=>'required|image',
                'fiche_medical_file'=>'required|image',
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
            $groupSanguinFile=null;
            if ($request->hasFile('group_sanguin_file')) {
                $groupSanguinFile = $request->file('group_sanguin_file')->store('group_sanguin_file', 'public');
            }
            $fichMedicalFile=null;
            if ($request->hasFile('fiche_medical_file')) {
                $fichMedicalFile = $request->file('fiche_medical_file')->store('fiche_medical_file', 'public');
            }

                $prorogation = Prorogation::create([
                    'email' => $request->input('email'),
                    'npi' => $npi,
                    'num_permis' => $request->input('num_permis'),
                    'group_sanguin'=> $request->input('group_sanguin'),
                    'permis_file' => $permisFile,
                    'group_sanguin_file' => $groupSanguinFile,
                    'fiche_medical_file' => $fichMedicalFile,
                    'state' => 'payment',
                ]);
                $amount = Service::prorogationAmount();
                $transaction = $this->startPaymentProcess([
                    "amount" => $amount,
                ]);
                $transactionId = data_get($transaction, "id");
                $subscription = UserTransaction::create([
                    'uuid'=> Str::uuid(),
                    'service' => 'prorogation',
                    'service_id' => $prorogation->id,
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
                ],'Demande de prorogation créée avec succès');

        } catch (\Throwable $e) {
            logger()->error($e);
            DB::rollBack();
            return $this->errorResponse('Erreur lors de la création de le demande de prorogation', $e->getMessage(), '', 500);
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
            "callback_url" => route("payments.prprocced"),
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
            $demande = Prorogation::find($Id);
            if (!$demande) {
                return null;

            }

            $payment = EservicePayment::create([
                'npi' => $npi,
                'montant' => $amount,
                'status' =>'approved',
                'transactionId' => $transactionid,
                'payment_for' => 'prorogation',
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
                'slug' => 'demande-prorogation',
                'service' => 'Prorogation',
                'candidat_id' => $user->id,
                'message' => "Votre demande de prorogation a été soumise avec succès et est en cours de traitement par l'ANaTT.",
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
                'prorogation_id'=>'required|exists:prorogations,id',
                'status'=>'required|in:approved'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Une erreur est survenue', $validator->errors()->toArray());
            }
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }
            $demandeProrogation = Prorogation::find($request->input('prorogation_id'));
            if (!$demandeProrogation) {
                return $this->errorResponse('La demande est introuvable');
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
            if ($transaction->amount != Service::prorogationAmount()) {
                return $this->errorResponse('Le montant de paiement est incorrect');
            }
            $npi = $user->npi;

            $payment = EservicePayment::create([
                'npi' => $npi,
                'montant' => $request->input('montant'),
                'phone' => $request->input('phone'),
                'operateur' => $request->input('operateur'),
                'status' => $request->input('status'),
                'transactionId' => $request->input('transactionId'),
                'payment_for' => 'prorogation-permis',
                'date_payment' => $request->input('date_payment'),
            ]);

            // Si tout se passe bien avec le paiement, mettre à jour le statut de la demande
            $demandeProrogation->update(['state' => 'init']);

            $montant =  $request->input('montant');
            $candidatEndpoint = env('CANDIDAT');
            $encryptednpi = Crypt::encrypt($npi);
            $urlWithCode = $candidatEndpoint . 'eservice-facture/' . $encryptednpi;
            $url = route('eservice-facture', ['encryptednpi' => $encryptednpi]);
            $message = "Paiement de " . $montant . "F CFA éffectué avec succès pour l'obtention de votre prorogation de permis. Cliquez pour télécharger <a href='" . $urlWithCode . "' target='_blank'>ici</a>";
            //notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-prorogation',
                'service' => 'Prorogation',
                'candidat_id' => $user->id,
                'message' => "Votre demande de prorogation a été soumise avec succès et est en cours de traitement par l'ANaTT.",
                'date_action' => now(),
                'url'=>$url
            ];

            $payment['url'] = $url;

            // Créer le parcours suivi
            $parcoursSuivi = EserviceParcourSuivi::create($parcoursSuiviData);

            return $this->successResponse($payment,$message);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Erreur lors du paiement', $e->getMessage(), '', 500);
        }
    }


    public function getProrogation($id)
    {
        try {
            $prorogation = ProrogationRejet::findOrFail($id);

            if (!$prorogation) {
                return $this->errorResponse('La demande est introuvable');
            }

            $prorogationID = $prorogation->prorogation_id;
            $demande = Prorogation::find($prorogationID);

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
            $ProrogationRejet = ProrogationRejet::findOrFail($id);

            if (!$ProrogationRejet) {
                return $this->errorResponse('Le rejet d\'authenticité est introuvable', null, 404);
            }

            $prorogationID = $ProrogationRejet->prorogation_id;
            $demande = Prorogation::find($prorogationID);

            if (!$demande) {
                return $this->errorResponse('La demande de prorogation associée est introuvable', null, 404);
            }
            $npi = $demande->npi;
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'num_permis' => 'required|string',
                'permis_file' => 'image',
                'group_sanguin'=>'required',
                'group_sanguin_file'=>'image',
                'fiche_medical_file'=>'image',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors()->toArray());
            }

            $permisFile = null;
            $groupSanguinFile=null;
            $fichMedicalFile=null;
            try {
                if ($request->hasFile('permis_file')) {
                    $permisFile = $request->file('permis_file')->store('permis_file', 'public');
                }
                if ($request->hasFile('group_sanguin_file')) {
                    $groupSanguinFile = $request->file('group_sanguin_file')->store('group_sanguin_file', 'public');
                }
                if ($request->hasFile('fiche_medical_file')) {
                    $fichMedicalFile = $request->file('fiche_medical_file')->store('fiche_medical_file', 'public');
                }
            } catch (\Throwable $fileException) {
                return $this->errorResponse('Erreur lors du téléchargement du fichier', null, 500);
            }

            DB::beginTransaction();

            try {
                // Mettre à jour la demande
                $demande->update([
                    'email' => $request->input('email'),
                    'npi' => $npi,
                    'num_permis' => $request->input('num_permis'),
                    'group_sanguin'=> $request->input('group_sanguin'),
                    'state' => 'pending',
                ]);

                // Mettre à jour le champ permis_file seulement s'il est présent dans la requête
                if ($permisFile !== null) {
                    $demande->update(['permis_file' => $permisFile]);
                }
                if ($groupSanguinFile !== null) {
                    $demande->update(['group_sanguin_file' => $groupSanguinFile]);
                }
                if ($fichMedicalFile !== null) {
                    $demande->update(['fiche_medical_file' => $fichMedicalFile]);
                }

                $state = 'pending';
                $ProrogationRejet->update([
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
                    'slug' => 'correction-prorogation',
                    'service' => 'Prorogation',
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
