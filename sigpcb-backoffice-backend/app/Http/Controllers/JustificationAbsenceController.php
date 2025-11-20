<?php

namespace App\Http\Controllers;

use App\Models\Candidat\CandidatJustificationAbsence;
use App\Models\Candidat\DossierCandidat;
use App\Models\Candidat\DossierSession;
use App\Models\Candidat\ParcoursSuivi;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Services\GetCandidat;
use App\Models\Candidat\Candidat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JustificationAbsenceController extends ApiController
{
    public function index(Request $request)
    {
        $this->hasAnyPermission(["all",'read-dossier-validation','edit-dossier-validation']);

        $query = CandidatJustificationAbsence::with(['examen', 'dossierSession']);

        // Appliquer les filtres
        $query->orderByDesc('id');  // Tri par id en ordre décroissant
        $query = $this->applyFilters($query);

        // Pagination des résultats
        $justifs = $query->paginate(10);

        // Initialisation d'une collection vide pour stocker les npi
        $npiCollection = collect();

        foreach ($justifs as $justif) {
            // Ajouter le npi du candidat
            if (!is_null($justif->candidat_npi)) {
                $npiCollection->push($justif->candidat_npi);
            }

            // Ajouter le npi du validator, si disponible
            if (!is_null($justif->agent_npi)) {
                $npiCollection->push($justif->agent_npi);
            }
        }

        // Retirer les valeurs nulles ou vides de la collection
        $npiCollection = $npiCollection->filter(function ($npi) {
            return !is_null($npi) && $npi !== '';
        });

        // Retirer les doublons de la collection
        $npiCollection = $npiCollection->unique();

        // Récupérer les informations des candidats en fonction des NPIs
        $candidats = collect(GetCandidat::get($npiCollection->all()));

        // Récupérer les utilisateurs (validateurs) en fonction des NPIs des validateurs
        $validateurs = User::whereIn('npi', $npiCollection->all())->get()->keyBy('npi');

        // Associer les informations des candidats et des validateurs aux dispenses
        foreach ($justifs as $justif) {
            // Associer les informations du candidat
            $candidat = $candidats->where('npi', $justif->candidat_npi)->first();
            $justif->candidat_info = $candidat;

            // Associer les informations du validateur, si disponible
            if ($justif->agent_npi) {
                $validator = $validateurs->get($justif->agent_npi);
                $justif->validator_info = $validator; // Ajoute les informations du validateur
            }
        }

        return $this->successResponse($justifs);
    }

    public function applyFilters($query)
    {
        // Filtre par examen_id
        if (request()->has('examen_id')) {
            $query = $query->where('examen_id', request('examen_id'));
        }

        // Filtre par statut
        if (request()->has('status')) {
            $statuses = explode(',', request()->get('status'));
            $query = $query->whereIn('status', $statuses);
        }

        // Filtre par date
        if (request()->has('created_at')) {
            $query = $query->whereDate('created_at', request('created_at'));
        }

        // Filtre par recherche (par NPI, IFU, ou autre)
        if (request()->has('search')) {
            $searchTerm = request('search');
            $query = $query->where(function ($query) use ($searchTerm) {
                $query->where('candidat_npi', 'LIKE', "%$searchTerm%")
                        ->orWhere('note', 'LIKE', "%$searchTerm%");
            });
        }

        return $query;
    }

    public function validateOrRejectJustif(Request $request, $id)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:validated,rejected', // Le statut doit être 'validated' ou 'rejected'
        ]);

        if ($validator->fails()) {
            return $this->errorReponse($validator->errors()->first());
        }

        try {
            // Trouver la justification d'absence par ID
            $justification = CandidatJustificationAbsence::findOrFail($id);

            // Vérifier si un dossier session existe
            $dossierSession = DossierSession::find($justification->dossier_session_id);

            if (!$dossierSession) {
                return $this->errorReponse("Aucun dossier session associé n'a été trouvé.");
            }

            // Récupérer le dossier candidat
            $dossierCandidat = DossierCandidat::find($dossierSession->dossier_candidat_id);

            if (!$dossierCandidat) {
                return $this->errorReponse("Aucun dossier candidat associé n'a été trouvé.");
            }

            // Récupérer l'utilisateur authentifié
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            // $candidatId = $user->id;
            $npi = $user->npi;
            // Vérification du candidat dans la table Candidat
            $candidat = Candidat::where('npi', $dossierSession->npi)->first();
            if (!$candidat) {
                return $this->errorResponse('Le candidat n\'a pas été trouvé.', 404);
            }
            // Mettre à jour le statut de la justification
            $justification->status = $request->status;
            if ($request->status == 'validated') {
                $justification->validated_at = now(); // Enregistrer la date de validation
                $justification->agent_npi = $npi;
            } elseif ($request->status == 'rejected') {
                $justification->rejeted_at = now();
                $justification->agent_npi = $npi;
            }
            $justification->save();

            // Créer une nouvelle ligne dans dossier_sessions seulement si le statut est 'validated'
            if ($request->status == 'validated') {
                // Mettre à jour le state du dossier candidat à 'pending'
                $dossierCandidat->state = 'pending';
                $dossierCandidat->save();
                $newDossierSession = new DossierSession();

                // Copier les champs nécessaires du dossier session existant
                $newDossierSession->npi = $dossierSession->npi;
                $newDossierSession->montant_paiement = $dossierSession->montant_paiement;
                $newDossierSession->is_militaire = $dossierSession->is_militaire;
                $newDossierSession->restriction_medical = $dossierSession->restriction_medical;
                $newDossierSession->date_payment = $dossierSession->date_payment;
                $newDossierSession->date_inscription = $dossierSession->date_inscription;
                $newDossierSession->date_validation = $dossierSession->date_validation;
                $newDossierSession->resultat_conduite = $dossierSession->resultat_conduite;
                $newDossierSession->resultat_code = $dossierSession->resultat_code;
                $newDossierSession->bouton_paiement = $dossierSession->bouton_paiement;
                // $newDossierSession->closed = $dossierSession->closed;
                $newDossierSession->is_paid = $dossierSession->is_paid;
                $newDossierSession->fiche_medical = $dossierSession->fiche_medical;
                $newDossierSession->type_examen = $justification->type_examen;
                $newDossierSession->presence = $dossierSession->presence;
                $newDossierSession->presence_conduite = $dossierSession->presence_conduite;
                $newDossierSession->langue_id = $dossierSession->langue_id;
                $newDossierSession->auto_ecole_id = $dossierSession->auto_ecole_id;
                $newDossierSession->annexe_id = $dossierSession->annexe_id;
                $newDossierSession->examen_id = $justification->examen_id;
                $newDossierSession->categorie_permis_id = $dossierSession->categorie_permis_id;
                $newDossierSession->permis_prealable_dure = $dossierSession->permis_prealable_dure;
                $newDossierSession->permis_prealable_id = $dossierSession->permis_prealable_id;
                $newDossierSession->permis_extension_id = $dossierSession->permis_extension_id;
                $newDossierSession->dossier_candidat_id = $dossierSession->dossier_candidat_id;
                $newDossierSession->old_ds_rejet_id = $dossierSession->old_ds_rejet_id;
                $newDossierSession->old_ds_justif_id = $dossierSession->old_ds_justif_id;

                // Mettre à jour l'état du nouveau dossier session
                $newDossierSession->state = 'validate';

                // Sauvegarder le nouveau dossier session
                $newDossierSession->save();

                            // Enregistrement d'un nouveau suivi
            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $dossierSession->npi;
            $parcoursSuivi->slug = 'inscription';
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $candidat->id;
            $parcoursSuivi->dossier_candidat_id = $dossierCandidat->id;
            $parcoursSuivi->dossier_session_id = $dossierSession->id;
            $parcoursSuivi->categorie_permis_id = $dossierSession->categorie_permis_id;

            // Message actualisé : indiquer que l'utilisateur peut continuer sans paiement immédiat
            $parcoursSuivi->message = "L'ANaTT vient de valider votre justification d'absence, vous recevrez sous peu votre convocation";
            $parcoursSuivi->date_action = now();
            $parcoursSuivi->save();

            }

            // Retourner une réponse de succès
            return $this->succesReponse("La dispense a été " . $request->status . " avec succès.", $justification);
        } catch (\Exception $e) {
            // Capture des exceptions et retour d'une réponse d'erreur
            return $this->errorReponse("Une erreur s'est produite : " . $e->getMessage());
        }
    }


}
