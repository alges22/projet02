<?php

namespace App\Http\Controllers;

use App\Models\Authenticite;
use Illuminate\Http\Request;
use App\Models\ParcoursSuivi;
use App\Models\EservicePayment;
use App\Models\AuthenticiteRejet;
use Illuminate\Support\Facades\DB;
use App\Models\EserviceParcourSuivi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ApiController;
use App\Models\Service;
use Illuminate\Support\Str;
use App\Exceptions\GlobalException;
use App\Models\User;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\Validator;

class AuthenticiteController extends ApiController
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'num_permis' => 'required|string',
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
            $permisFile=null;
            if ($request->hasFile('permis_file')) {
                $permisFile = $request->file('permis_file')->store('permis_file', 'public');
            }

                $authenticite = Authenticite::create([
                    'email' => $request->input('email'),
                    'npi' => $npi,
                    'num_permis' => $request->input('num_permis'),
                    'permis_file' => $permisFile,
                    'state' => 'payment',
                ]);

                $amount = Service::authenticiteAmount();
                $transaction = $this->startPaymentProcess([
                    "amount" => $amount,
                ]);
                $transactionId = data_get($transaction, "id");
                $subscription = UserTransaction::create([
                    'uuid'=> Str::uuid(),
                    'service' => 'authenticite',
                    'service_id' => $authenticite->id,
                    'npi' => $npi,
                    'status' => 'init',
                    "amount" => $amount,
                    "transaction_id" => $transactionId,
                    "expired_at" => now()
                ]);

                $tokenUrl = $transaction->generateToken();
                DB::commit();
                return $this->successResponse([
                    // "authenticite" => $authenticite,
                    "uuid" => $subscription->uuid,
                    "fedapay" => $tokenUrl,
                    "transactionId" => $transactionId
                ],'Demande d\'authenticité créée avec succès');

        } catch (\Throwable $e) {
            logger()->error($e);
            DB::rollBack();
            return $this->errorResponse('Erreur lors de la création de le demande d\'authenticité', $e->getMessage(), '', 500);
        }
    }

    public function checkTransactionUuid($uuid) {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $npi = $user->npi;
            $transaction = UserTransaction::where('uuid', $uuid)->first();

            if (!$transaction) {
                return $this->errorResponse('Cette transaction n\'existe pas', null, null, 422);
            }
            if ($transaction->service === "ds-code-conduite") {
                $user = Auth::user();
                if (!$user) {
                    return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
                }

                $npi = $user->npi;
                $transacNpi = $transaction->npi;
                if ($npi != $transacNpi) {
                    return $this->errorResponse('Vous n\'avez pas le droit de consulter cette transaction', null, null, 422);
                }
                $amount=$transaction->amount;
                $candidatEndpoint = env('CANDIDAT');
                // $encryptednpi = Crypt::encrypt($npi);
                // $url = route('generate-facture', ['encryptednpi' => $encryptednpi]);
                $dossierId = $transaction->service_id;
                $encryptedDossierId = Crypt::encrypt($dossierId);
                $url = route('generate-facture', ['encryptedDossierId' => $encryptedDossierId]);
                $urlWithCode = $candidatEndpoint . 'generate-facture/' . $encryptedDossierId;
                // Collecte des données pour la réponse
                $data = [
                    'uuid' => $transaction->uuid,
                    'service' => $transaction->service,
                    'npi' => $transaction->npi,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'url' => $url,
                    'date_payment' => $transaction->created_at,
                ];
                $message = "Paiement de " . $amount . "F CFA éffectué avec succès. Cliquez pour télécharger <a href='" . $urlWithCode . "' target='_blank'>ici</a>";
                return $this->successResponse($data,$message);
            }
            $transacNpi = $transaction->npi;
            if ($npi != $transacNpi) {
                return $this->errorResponse('Vous n\'avez pas le droit de consulter cette transaction', null, null, 422);
            }
            $amount=$transaction->amount;
            $candidatEndpoint = env('CANDIDAT');
            $encryptednpi = Crypt::encrypt($npi);
            $urlWithCode = $candidatEndpoint . 'eservice-facture/' . $encryptednpi;
            $url = route('eservice-facture', ['encryptednpi' => $encryptednpi]);
            // Collecte des données pour la réponse
            $data = [
                'uuid' => $transaction->uuid,
                'service' => $transaction->service,
                'npi' => $transaction->npi,
                'status' => $transaction->status,
                'amount' => $transaction->amount,
                'url' => $url,
                'date_payment' => $transaction->created_at,
            ];
            $message = "Paiement de " . $amount . "F CFA éffectué avec succès. Cliquez pour télécharger <a href='" . $urlWithCode . "' target='_blank'>ici</a>";
            return $this->successResponse($data,$message);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération. ' . $e->getMessage());
        }
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
        if ($transaction->amount != Service::authenticiteAmount()) {
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
            $authenticiteId = $subscription->service_id;
            $npi = $subscription->npi;
            $amount = $subscription->amount;
            $transactionid = $subscription->transaction_id;
            $demandeAuthenticite = Authenticite::find($authenticiteId);
            if (!$demandeAuthenticite) {
                return null;

            }

            $payment = EservicePayment::create([
                'npi' => $npi,
                'montant' => $amount,
                'status' =>'approved',
                'transactionId' => $transactionid,
                'payment_for' => 'authenticite',
                'phone' => data_get($transaction,'payment_method.number'),
                'operateur' => data_get($transaction,'mode'),
                'date_payment' => now(),
            ]);

            // Si tout se passe bien avec le paiement, mettre à jour le statut de la demande
            $demandeAuthenticite->update(['state' => 'init']);

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
                'slug' => 'demande-authenticite',
                'service' => 'Authenticite',
                'candidat_id' => $user->id,
                'message' => "Votre demande d'authenticité a été soumise avec succès et est en cours de traitement par l'ANaTT.",
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
                'authenticite_id'=>'required|exists:authenticites,id',
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
            if ($transaction->amount != Service::authenticiteAmount()) {
                return $this->errorResponse('Le montant de paiement est incorrect');
            }

            $demandeAuthenticite = Authenticite::find($request->input('authenticite_id'));
            if (!$demandeAuthenticite) {
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
                'payment_for' => 'authenticite',
                'date_payment' => $request->input('date_payment'),
            ]);

            // Si tout se passe bien avec le paiement, mettre à jour le statut de la demande
            $demandeAuthenticite->update(['state' => 'init']);

            $montant =  $request->input('montant');
            $candidatEndpoint = env('CANDIDAT');
            $encryptednpi = Crypt::encrypt($npi);
            $urlWithCode = $candidatEndpoint . 'eservice-facture/' . $encryptednpi;
            $url = route('eservice-facture', ['encryptednpi' => $encryptednpi]);
            $message = "Paiement de " . $montant . "F CFA éffectué avec succès pour l'obtention de l'authenticité du permis. Cliquez pour télécharger <a href='" . $urlWithCode . "' target='_blank'>ici</a>";
            //notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-authenticite',
                'service' => 'Authenticite',
                'candidat_id' => $user->id,
                'message' => "Votre demande d'authenticité a été soumise avec succès et est en cours de traitement par l'ANaTT.",
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
            "callback_url" => route("payments.procced"),
        ));
    }

    public function getAuthenticite($id)
    {
        try {
            $authen = AuthenticiteRejet::findOrFail($id);

            if (!$authen) {
                return $this->errorResponse('La demande est introuvable');
            }

            $authenticiteID = $authen->authenticite_id;
            $demande = Authenticite::find($authenticiteID);

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
            $AuthenticiteRejet = AuthenticiteRejet::findOrFail($id);

            if (!$AuthenticiteRejet) {
                return $this->errorResponse('Le rejet d\'authenticité est introuvable', null, 404);
            }

            $authenticiteID = $AuthenticiteRejet->authenticite_id;
            $demande = Authenticite::find($authenticiteID);

            if (!$demande) {
                return $this->errorResponse('La demande d\'authenticité associée est introuvable', null, 404);
            }
            $npi = $demande->npi;
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'num_permis' => 'required|string',
                'permis_file' => 'image',
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
                ]);

                // Mettre à jour le champ permis_file seulement s'il est présent dans la requête
                if ($permisFile !== null) {
                    $demande->update(['permis_file' => $permisFile]);
                }

                $state = 'pending';
                $AuthenticiteRejet->update([
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
                    'slug' => 'correction-authenticite',
                    'service' => 'Authenticite',
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

