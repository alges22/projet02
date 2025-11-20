<?php

namespace App\Http\Controllers;
use App\Models\CandidatJustificationAbsence;
use App\Models\DossierSession;
use App\Services\GetCandidat;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CandidatJustifController extends ApiController
{
    public function store(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'examen_id' => 'required|integer',
            'piece_justificatve' => 'required|image',
            'fiche_medical' => 'required|image',
        ]);

        // Si la validation échoue, retourner une réponse d'erreur
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first());
        }

        try {
            $user = auth()->user();

            // Vérifier l'existence du candidat
            $candidat = GetCandidat::findOne($user->npi);
            if (!$candidat) {
                return $this->errorResponse("Le numéro NPI fourni est introuvable.", 422);
            }

            // Vérifier si un candidat avec le même NPI et un statut 'init' existe déjà
            $existingJustification = CandidatJustificationAbsence::where('candidat_npi', $user->npi)
                ->where('status', 'init')
                ->first();

            // Si une entrée existe, retourner une réponse d'erreur
            if ($existingJustification) {
                return $this->errorResponse("Un dossier pour ce candidat avec le statut 'init' existe déjà.");
            }

            // Récupérer le dernier dossier session de l'utilisateur
            $dossierSession = DossierSession::where('npi', $user->npi)
                ->orderBy('created_at', 'desc') // Trier par date de création pour récupérer le dernier dossier
                ->first(); // Récupérer le dernier dossier

            if (!$dossierSession) {
                return $this->errorResponse("Aucun dossier trouvé pour ce candidat.", 422);
            }

            // Vérifier si le candidat a été absent pour le code ou la conduite
            $typeExamen = null;
            if ($dossierSession->presence == 'abscent') {
                $typeExamen = 'code-conduite';
            } elseif ($dossierSession->presence_conduite == 'absent') {
                $typeExamen = 'conduite';
            }

            if (!$typeExamen) {
                return $this->errorResponse("Le candidat n'a pas été absent pour l'examen de code ou de conduite.");
            }

            // Gérer les fichiers de justification
            $ficheMedicalFile = null;
            if ($request->hasFile('fiche_medical')) {
                $ficheMedicalFile = $request->file('fiche_medical')->store('fiche_medical', 'public');
            }

            $justifFile = null;
            if ($request->hasFile('piece_justificatve')) {
                $justifFile = $request->file('piece_justificatve')->store('piece_justificatve', 'public');
            }

            // Création d'une nouvelle justification
            $justification = new CandidatJustificationAbsence();
            $justification->examen_id = $request->examen_id;
            $justification->candidat_npi = $user->npi;
            $justification->validated_at = null; // initialisé à null
            $justification->rejeted_at = null; // initialisé à null
            $justification->agent_npi = null; // Si tu veux lier à l'agent actuel
            $justification->dossier_session_id = $dossierSession->id;
            $justification->piece_justificatve = $justifFile;
            $justification->fiche_medical = $ficheMedicalFile;
            $justification->status = 'init'; // statut initial
            $justification->type_examen = $typeExamen; // Enregistrement du type d'examen
            $justification->save();

            // Retourner une réponse de succès
            return $this->succesReponse("La justification d'absence a été ajoutée avec succès.", $justification);
        } catch (\Throwable $e) {
            logger()->error($e);
            // Capture des exceptions et retour d'une réponse d'erreur
            return $this->errorResponse("Une erreur s'est produite : " . $e->getMessage());
        }
    }

}
