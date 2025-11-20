<?php

namespace App\Http\Controllers;

use App\Models\Admin\Examen;
use App\Models\Base\DispensePaiement;
use App\Models\DossierCandidat;
use App\Models\DossierSession;
use App\Models\ParcoursSuivi;
use App\Models\User;
use App\Services\Api;
use App\Services\GetCandidat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DispensePaiementController extends ApiController
{

    public function checkValidatedDispense(Request $request)
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = auth()->user();

            // Vérifier si l'utilisateur a un NPI
            if (!$user || !$user->npi) {
                return $this->errorResponse('NPI introuvable pour cet utilisateur.', 404);
            }

            // Rechercher une entrée dans la table dispense_paiements avec le statut 'validated'
            $dispense = DispensePaiement::where('candidat_npi', $user->npi)
                                        ->where('status', 'validated')
                                        ->first();

            // Vérifier si une dispense avec le statut 'validated' existe
            if (!$dispense) {
                return $this->successResponse([], 'Aucune dispense avec le statut "validated" trouvée pour cet utilisateur.');
            }

            return $this->successResponse($dispense, 'Une dispense avec le statut "validated" existe pour cet utilisateur.');

        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue lors de la vérification de la dispense.', 500);
        }
    }

    public function updateCandidatSession(Request $request)
    {
        try {
            // Validation des données d'entrée
            $validator = Validator::make($request->all(), [
                'examen_id' => 'required'
            ], [
                'examen_id.required' => 'Veuillez sélectionner un examen.',
            ]);

            // Si la validation échoue, retourner les erreurs
            if ($validator->fails()) {
                return $this->errorResponse("La validation des données a échoué.", $validator->errors());
            }
            $user = auth()->user();
            // Vérifier l'existence du candidat
            $candidat = GetCandidat::findOne($user->npi);
            if (!$candidat) {
                return $this->errorResponse("Le numéro NPI fourni est introuvable.", 422);
            }

            // Vérification de la session du dossier
            $d_session = DossierSession::where('npi', $user->npi)->latest()->first();
            if (!$d_session || $d_session->abandoned || $d_session->closed) {
                return $this->errorResponse("Aucune session active trouvée pour ce candidat, ou la session a été clôturée/abandonnée.");
            }
            // Vérification si l'examen est toujours ouvert
            $examenId = $request->examen_id;
            $annexeId = $d_session->annexe_id;
            // Récupérer les informations de l'examen pour ajouter à la session
            $examen = Examen::find($examenId);
            if (!$examen) {
                return $this->errorResponse('Information de session non disponible.');
            }

            // Vérification du candidat dans la table Candidat
            $candidat = User::where('npi', $user->npi)->first();
            if (!$candidat) {
                return $this->errorResponse('Le candidat n\'a pas été trouvé.', 404);
            }
            // Rechercher une entrée dans la table dispense_paiements avec le statut 'validated'
            $dispense = DispensePaiement::where('candidat_npi', $user->npi)
                                        ->where('status', 'validated')
                                        ->first();

            // Vérifier si une dispense avec le statut 'validated' existe
            if (!$dispense) {
                return $this->errorResponse('Aucune dispense avec le statut "validated" trouvée pour cet utilisateur.');
            }

            $dispense->status = 'used';
            $dispense->used_at = now();
            $dispense->examen_id = $examenId;
            $dispense->dossier_session_id = $d_session->id;
            $dispense->save();

            // Mise à jour du dossier session
            $dossier_candidat_id = $d_session->dossier_candidat_id;
            $dossier = DossierCandidat::findOrFail($dossier_candidat_id);

            // Mise à jour de la session
            $d_session->bouton_paiement = -1;
            $d_session->examen_id = $examenId;
            $d_session->state = "payment";
            $d_session->paiement_by = auth()->id();
            $d_session->date_payment = now();
            $d_session->save();


            $sessionLong = $examen->session_long;
            // Mise à jour du suivi du parcours
            $suivi = ParcoursSuivi::where('dossier_candidat_id', $dossier_candidat_id)
                ->where('slug', 'monitoring')
                ->orderByDesc('created_at')
                ->first();

            if (!$suivi) {
                return $this->errorResponse('Le suivi de parcours du candidat n\'a pas été trouvé.');
            }

            // Mise à jour du bouton de suivi
            $suivi->bouton = '{"bouton":"Paiement","status":"-1"}';
            $suivi->save();

            // Enregistrement d'un nouveau suivi
            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $dossier->npi;
            $parcoursSuivi->slug = 'inscription';
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $candidat->id;
            $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
            $parcoursSuivi->dossier_session_id = $d_session->id;
            $parcoursSuivi->categorie_permis_id = $dossier->categorie_permis_id;

            // Message actualisé : indiquer que l'utilisateur peut continuer sans paiement immédiat
            $parcoursSuivi->message = "Vous avez sélectionné l'examen avec succès. Étant bénéficiaire d’un laisser-passer, aucun paiement n’a été nécessaire.";
            $parcoursSuivi->date_action = now();
            $parcoursSuivi->save();

            // Ouverture du suivi dans un autre système si nécessaire
            $this->openMonitoring($d_session->id);

            // Retourner une réponse de succès
            return $this->successResponse($parcoursSuivi, 'Opération effectuée avec succès.');

        } catch (\Throwable $th) {
            // Log de l'erreur
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue est survenue. Veuillez réessayer plus tard.", null, null, 500);
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
