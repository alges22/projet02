<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Admin\Examen;
use Illuminate\Http\Request;
use App\Models\ParcoursSuivi;
use App\Models\DossierSession;
use App\Models\DossierCandidat;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class ReconduitController extends ApiController
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'auto_ecole_id' => 'required|integer|min:1',
                'annexe_anatt_id' => 'required',
                'examen_type' => 'required|in:code-conduite,conduite',
                'dossier_candidat_id' => 'required|exists:dossier_candidats,id',
            ], [
                "dossier_candidat_id.required" => 'Le dossier est obligatoire',
                'auto_ecole_id.required' => 'L\'identification de l\'auto-école est obligatoire.',
                'annexe_anatt_id.required' => 'L\'identification de l\'annexe est obligatoire.',
                'examen_type.required' => 'Le type d\'examen est obligatoire.',
                'examen_type.string' => 'Le type d\'examen doit être une chaîne de caractères.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }

            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $candidat_id = $user->id; // Récupérer l'ID de l'utilisateur connecté
            $dossier_candidat_id = $request->input('dossier_candidat_id');
            //Verifier si le candidat n'as pas deja un autre dossier encours
            $existDossierCandidat = DossierCandidat::where('candidat_id', $candidat_id)->where('state','pending')
            ->orderByDesc('created_at')
            ->exists();

            if ($existDossierCandidat) {
                return $this->errorResponse('Vous avez un dossier ouvert pour un autre permis, veuillez terminer ce dossier pour poursuivre.', null, null, 422);
            }
            // Récupérer la dernière insertion de DossierSession pour le candidat connecté
            $lastDossierSession = DossierSession::where('dossier_candidat_id', $dossier_candidat_id)
                ->orderByDesc('created_at')
                ->first();

            if (!$lastDossierSession) {
                return $this->errorResponse('Aucun DossierSession trouvé pour ce candidat.', null, null, 422);
            }
            $permisName = $request->input('nom_permis');

            // Récupérer les valeurs nécessaires depuis la dernière DossierSession
            $restriction_medical = $lastDossierSession->restriction_medical;
            $fiche_medical = $lastDossierSession->fiche_medical;
            $permis_extension_id = $lastDossierSession->permis_extension_id;
            $langue_id = $lastDossierSession->langue_id;
            $is_militaire = $lastDossierSession->is_militaire;
            $montant_paiement = $lastDossierSession->montant_paiement;
            $npi = $lastDossierSession->npi;
            $permis_prealable_id = $lastDossierSession->permis_prealable_id;
            $permis_prealable_dure = $lastDossierSession->permis_prealable_dure;
            $categorie_permis_id = $lastDossierSession->categorie_permis_id;
            $presence = $lastDossierSession->presence;
            $resultat_code = $lastDossierSession->resultat_code;
            $examen_id = $lastDossierSession->examen_id;

            $lastExamen = Examen::find($examen_id);
            if (!$lastExamen) {
                return $this->errorResponse("Cette session est introuvable");
            }

            // Obtenir la date de conduite
            $dateConduite = Carbon::parse($lastExamen->date_conduite);

            // Ajouter 6 mois à la date de conduite
            $dateFinPeriode = $dateConduite->addMonths(6);

            // Obtenir la date actuelle
            $dateActuelle = Carbon::now();

            // Vérifier si la date actuelle est comprise entre la date de conduite et 6 mois plus tard
            if ($dateActuelle->greaterThan($dateFinPeriode)) {
                // Envoyer un message à l'utilisateur
                return $this->errorResponse("Vous ne pouvez plus composer cet examen en tant que reconduit car la période de six mois est écoulée, veuillez vous inscrire a nouveau.");
            }
            // Enregistrez les valeurs récupérées dans la base de données
            $newSession = DossierSession::create([
                'auto_ecole_id' => $request->input('auto_ecole_id'),
                'dossier_candidat_id' => $dossier_candidat_id,
                'annexe_id' => $request->input('annexe_anatt_id'),
                'type_examen' => $request->input('examen_type'),
                'restriction_medical' => $restriction_medical,
                'presence' => $presence,
                'fiche_medical' => $fiche_medical,
                'permis_extension_id' => $permis_extension_id,
                'langue_id' => $langue_id,
                'is_militaire' => $is_militaire,
                'montant_paiement' => $montant_paiement,
                'npi' => $npi,
                'permis_prealable_id' => $permis_prealable_id,
                'permis_prealable_dure' => $permis_prealable_dure,
                'categorie_permis_id' => $categorie_permis_id,
                'resultat_code' => $resultat_code,
                'state' => 'init',
                'date_inscription' => $lastDossierSession->date_inscription,
            ]);

            // Enregistrement dans le modèle ParcoursSuivi
            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $npi;
            $parcoursSuivi->slug = 'reconduit-' . $request->input('examen_type');
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $candidat_id;
            $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
            $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
            $parcoursSuivi->date_action = now();
            $parcoursSuivi->dossier_session_id = $newSession->id;

            if ($request->input('examen_type') === 'code-conduite') {
                $message = 'Votre demande pour passer à l\'examen du Permis de conduire catégorie ' . $request->input('nom_permis') . ' pour le code a été envoyée avec succès.';
            } elseif ($request->input('examen_type') === 'conduite') {
                $message = 'Votre demande pour passer à l\'examen du Permis de conduire catégorie ' . $request->input('nom_permis') . ' pour la conduite a été envoyée avec succès.';
            } else {
                $message = 'Votre demande pour passer à l\'examen du Permis de conduire catégorie ' . $request->input('nom_permis') . ' a été envoyée avec succès.';
            }

            $parcoursSuivi->message = $message;
            $parcoursSuivi->save();

            $dc = DossierCandidat::findOrFail($dossier_candidat_id);
            if (!$dc) {
                return $this->errorResponse("La dossier est introuvable");
            }

            $state = 'pending';
            $dc->update([
                'is_deleted' => false,
                'state' => $state,
            ]);
            // Retourner une réponse de succès avec les données enregistrées
            return $this->successResponse($newSession, 'Données enregistrées avec succès', 201);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la création des données");
        }
    }
}
