<?php

namespace App\Http\Controllers;
use Exception;
use App\Services\Api;
use Illuminate\Http\Request;
use App\Models\ParcoursSuivi;
use App\Models\DossierSession;
use App\Models\CandidatPayment;
use App\Models\DossierCandidat;
use App\Models\ExamenSalle;
use App\Models\Juricandidat;
use App\Models\Service;
use App\Models\User;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class FindPaymentController extends ApiController
{

    public function procced(Request $request)
    {
        $request->validate([
            "transaction_id" => "required"
        ]);
        $user = Auth::user();
        if (!$user) {
            return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
        }
        $npi = $user->npi;

        $ID = $request->get('transaction_id');
        $subscription = UserTransaction::where(["transaction_id" => $ID])->first();
        if (!$subscription) {
            return $this->errorResponse('Ce paiement n\'existe pas');
        }
        if ($subscription->status == 'approved') {
            return $this->errorResponse('Votre souscription a été déjà vérifiée');
        }

        //dans le cas d'un paiement pour un examen de conduire code-conduite
        if ($subscription->service === 'ds-code-conduite'){

            $dossierSessionId = $subscription->service_id;
            $demande  = DossierSession::find($dossierSessionId)->get();
            if (!$demande) {
                return $this->errorResponse('Ce dossier n\'existe pas');
            }
            //verifions si pour cet dossier le candidat a deja fait des actions
            if(($demande->abandoned === true) || ($demande->presence != null) || ($demande->closed === true)){
                return $this->errorResponse("Nous ne pouvons pas traiter votre demande concernant votre dossier actuel, car il a été soit abandonné, soit clôturé, ou vous avez déjà effectué une composition.");
            }
            //verifions si l'examen est toujours ouverte
            $examenId=$demande->examen_id;
            $annexeId=$demande->annexe_id;
            // Vérifier si l'examen est utilisé dans ExamenSalle pour l'annexe donnée
            $isUsedInExamenSalle = ExamenSalle::where('annexe_id', $annexeId)
            ->where('examen_id', $examenId)
            ->exists();

            // Vérifier si l'examen est utilisé dans Juricandidat pour l'annexe donnée
            $isUsedInJuricandidat = Juricandidat::where('annexe_id', $annexeId)
                ->where('examen_id', $examenId)
                ->exists();

            // Si l'examen est utilisé dans l'une ou l'autre des tables, on le considère comme utilisé
            if ($isUsedInExamenSalle || $isUsedInJuricandidat) {
                // Examen est utilisé, ne rien retourner
                return $this->errorResponse("Nous ne pouvons pas traiter votre demande concernant votre dossier actuel, car la session n'est plus disponible");
            }

            $fedaPayEnv = env('FEDAPAY_ENV', 'sandbox');
            if ($fedaPayEnv === 'live') {
                \FedaPay\FedaPay::setEnvironment('live');
                \FedaPay\FedaPay::setApiKey(env('FEDAPAY_LIVE_PRIVATE_KEY'));
            } else {
                \FedaPay\FedaPay::setEnvironment('sandbox');
                \FedaPay\FedaPay::setApiKey(env('FEDAPAY_SANDBOX_PRIVATE_KEY'));
            }

            $transaction = \FedaPay\Transaction::retrieve($ID);
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
                $user  = User::where('npi', $npi)->first();

                // Récupérer l'ID de l'utilisateur connecté (candidat_id)
                $candidatId = $user->id;
                $dateSoumission = now();
                // Ajouter l'ID de l'utilisateur connecté dans la requête
                $request->merge(['candidat_id' => $candidatId]);
                $montant = $amount;
                $session = $demande->examen_id;
                $dossier_candidat_id = $demande->dossier_candidat_id;

                $dossier = DossierCandidat::findOrFail($dossier_candidat_id);
                $d_session = DossierSession::where('dossier_candidat_id', $dossier->id)->latest()->first();
                $transactionId = $ID;
                if ($d_session) {
                    // Mettre à jour le champ "state" du dossier avec la valeur de la variable $state
                    $d_session->bouton_paiement = -1;
                    $d_session->examen_id = $session;
                    $d_session->state = "payment";
                    $d_session->date_payment = now();
                    $d_session->save();
                    $dossier_session_id =  $d_session->id;
                }

                // Récupérer la liste des permis depuis l'endpoint
                $path = "categorie-permis";
                $response = Api::base('GET', $path);
                if ($response->successful()) {
                    $permis = $response->json()['data'];
                    $categoriePermisId = $dossier->categorie_permis_id;
                    $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;
                }

                $suivi = ParcoursSuivi::where('dossier_candidat_id', $dossier_candidat_id)
                    ->where('slug', 'monitoring')
                    ->orderByDesc('created_at')
                    ->first();

                if (!$suivi) {
                    return $this->errorResponse('Parcours suivi non trouvé');
                }
                $suivi->bouton = '{"bouton":"Paiement","status":"-1"}';
                $suivi->save();
                $dossierId = $d_session->id;
                $encryptedDossierId = Crypt::encrypt($dossierId);
                $url = route('generate-facture', ['encryptedDossierId' => $encryptedDossierId]);
                // Enregistrement dans le modèle ParcoursSuivi
                $parcoursSuivi = new ParcoursSuivi();
                $parcoursSuivi->npi = $dossier->npi;
                $parcoursSuivi->slug = 'inscription';
                $parcoursSuivi->service = 'Permis';
                $parcoursSuivi->candidat_id = $candidatId;
                $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
                $parcoursSuivi->dossier_session_id = $dossier_session_id;
                $parcoursSuivi->categorie_permis_id = $dossier->categorie_permis_id;
                $parcoursSuivi->message = "Paiement de " . $montant . "F CFA éffectué avec succès pour l'inscription à la catégorie de permis de conduire " . $nomPermis;
                $parcoursSuivi->url = $url;
                $parcoursSuivi->date_action = $dateSoumission;
                $parcoursSuivi->save();

                $description = "Paiement à l'ANaTT du Service" . " " . $suivi->service . " categorie " . $nomPermis;
                $request->merge(['description' => $description]);
                $request->merge(['dossier_session_id' => $dossier_session_id]);


                     // Préparation des données de paiement
                $paymentData = [
                    'candidat_id' => $candidatId,
                    'auto_ecole_id' => $demande->auto_ecole_id,
                    'agregateur' => 'fedaPay',
                    'description' => $description,
                    'transaction_id' => $transaction->id,
                    'reference' => data_get($transaction,'mode'),
                    'mode' => data_get($transaction,'mode'),
                    'operation' => 'payment',
                    'transaction_key' => data_get($transaction,'mode'),
                    'montant' => $montant,
                    'ref_operateur' => data_get($transaction,'mode'),
                    'numero_recu' => data_get($transaction,'mode'),
                    'moyen_payment' => 'momo',
                    'status' => 'approved',
                    'date_payment' => now(),
                    'dossier_candidat_id' => $dossier_candidat_id,
                    'dossier_session_id' => $dossier_session_id,
                    'examen_id' => $demande->examen_id,
                ];

                // Création de l'enregistrement de paiement
                $candidat_payment = CandidatPayment::create($paymentData);
                $candidat_payment['url']=$url;
                $this->openMonitoring($dossier_session_id);
                return $this->successResponse($candidat_payment, 'Paiement ajouté avec succès');
            }
        }
    }

    private function openMonitoring($dossier_session_id)
    {
        $response = Api::base("POST", "dossier-sessions/suivi-candidat/state", [
            'state' => "pending",
            'dossier_session_id' => $dossier_session_id
        ]);

        $data  = Api::data($response);
    }
}
