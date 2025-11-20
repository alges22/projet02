<?php

namespace App\Http\Controllers;

use App\Models\Base\CandidatExamenSalle;
use App\Models\Base\DispensePaiement;
use App\Models\Base\JuryCandidat;
use App\Models\Candidat\Candidat;
use App\Models\Candidat\DossierCandidat;
use App\Models\Candidat\DossierSession;
use App\Models\Candidat\ParcoursSuivi;
use App\Models\Examen;
use App\Models\User;
use App\Services\Api;
use App\Services\GetCandidat;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class UpdateDossierSessionController extends ApiController
{
    public function updateCandidatSession(Request $request)
    {
        try {
            // Validation des données d'entrée
            $validator = Validator::make($request->all(), [
                'npi' => 'required',
                'examen_id' => 'required'
            ], [
                'npi.required' => 'Le numéro NPI est requis.',
                'examen_id.required' => 'Veuillez sélectionner un examen.',
            ]);

            // Si la validation échoue, retourner les erreurs
            if ($validator->fails()) {
                return $this->errorResponse("La validation des données a échoué.", $validator->errors());
            }

            // Vérifier l'existence du candidat
            $candidat = GetCandidat::findOne($request->npi);
            if (!$candidat) {
                return $this->errorResponse("Le numéro NPI fourni est introuvable.", 422);
            }

            // Vérification de la session du dossier
            $d_session = DossierSession::where('npi', $request->npi)->latest()->first();
            if (!$d_session || $d_session->abandoned || $d_session->closed) {
                return $this->errorResponse("Aucune session active trouvée pour ce candidat, ou la session a été clôturée/abandonnée.");
            }
            if( $d_session->state != "pending"){
                return $this->errorResponse("Action impossible : Ce candidat n'est pas a l'étape de paiement/son auto école n'a peut être pas encore valider sa formation");
            }
            // Vérification si l'examen est toujours ouvert
            $examenId = $request->examen_id;
            $annexeId = $d_session->annexe_id;
            // Récupérer les informations de l'examen pour ajouter à la session
            $examen = Examen::find($examenId);
            if (!$examen) {
                return $this->errorResponse('Information de session non disponible.');
            }
            // Vérifier si l'examen est déjà utilisé dans ExamenSalle ou Juricandidat
            $isUsedInExamenSalle = CandidatExamenSalle::where('annexe_id', $annexeId)
                ->where('examen_id', $examenId)
                ->exists();

            $isUsedInJuricandidat = JuryCandidat::where('annexe_id', $annexeId)
                ->where('examen_id', $examenId)
                ->exists();

            if ($isUsedInExamenSalle || $isUsedInJuricandidat) {
                return $this->errorResponse("La session sélectionnée n'est plus disponible.");
            }

            // Vérification du candidat dans la table Candidat
            $candidat = Candidat::where('npi', $request->npi)->first();
            if (!$candidat) {
                return $this->errorResponse('Le candidat n\'a pas été trouvé.', 404);
            }

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
            $parcoursSuivi->message = "Votre processus d'inscription a été finalisé. Vous pouvez poursuivre sans paiement. Préparez-vous pour la session : $sessionLong.";
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

    public function createDispensePaiement(Request $request)
    {
        $this->hasAnyPermission(["all","edit-dispense-paiement"]);

        try {
            // Validation des données d'entrée
            $validator = Validator::make($request->all(), [
                'npi' => 'required',
            ], [
                'npi.required' => 'Le numéro NPI est requis.',
            ]);

            // Si la validation échoue, retourner les erreurs
            if ($validator->fails()) {
                return $this->errorResponse("La validation des données a échoué.", $validator->errors());
            }
           // Vérifier l'existence du candidat
           $candidat = GetCandidat::findOne($request->npi);
           if (!$candidat) {
               return $this->errorResponse("Le numéro NPI fourni est introuvable.", 422);
           }

            // Vérifier si une entrée existe déjà pour ce candidat avec le statut "init" ou "validated"
            $existingDispense = DispensePaiement::where('candidat_npi', $request->npi)
                ->whereIn('status', ['init', 'validated'])
                ->first();

            // Si une entrée existe déjà, refuser la création
            if ($existingDispense) {
                return $this->errorResponse("Une dispense de paiement a déjà été enregistrée pour ce candidat avec le statut 'init' ou 'validated'.", 422);
            }

            // Créer une nouvelle entrée dans la table dispense_paiements
            $dispensePaiement = new DispensePaiement();
            $dispensePaiement->candidat_npi = $request->npi;
            $dispensePaiement->status = 'init';  // Initialiser avec le statut 'init'
            $dispensePaiement->created_by = auth()->id();  // Utiliser l'ID de l'utilisateur connecté
            $dispensePaiement->save();

            // Retourner une réponse de succès
            return $this->successResponse($dispensePaiement, 'Dispense de paiement créée avec succès.');

        } catch (\Throwable $th) {
            // Log de l'erreur
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue est survenue. Veuillez réessayer plus tard.", null, null, 500);
        }
    }

    public function index(Request $request)
    {
        $this->hasAnyPermission(["all","read-dispense-paiement","edit-dispense-paiement","manage-dispense-paiement"]);

        // Création de la requête pour récupérer toutes les dispenses avec les relations 'examen' et 'dossierSession'
        $query = DispensePaiement::with(['examen', 'dossierSession']); // Chargement des relations

        // Appliquer les filtres
        $query->orderByDesc('id');  // Tri par id en ordre décroissant
        $query = $this->applyFilters($query);

        // Pagination des résultats
        $dispenses = $query->paginate(10);

        // Initialisation d'une collection vide pour stocker les npi
        $npiCollection = collect();

        // Itérer sur chaque dispense pour récupérer les NPIs des candidats et d'autres informations
        foreach ($dispenses as $dispense) {
            // Ajouter le npi du candidat
            if (!is_null($dispense->candidat_npi)) {
                $npiCollection->push($dispense->candidat_npi);
            }

            // Ajouter le npi du validator, si disponible
            if (!is_null($dispense->validator_npi)) {
                $npiCollection->push($dispense->validator_npi);
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
        foreach ($dispenses as $dispense) {
            // Associer les informations du candidat
            $candidat = $candidats->where('npi', $dispense->candidat_npi)->first();
            $dispense->candidat_info = $candidat;

            // Associer les informations du validateur, si disponible
            if ($dispense->validator_npi) {
                $validator = $validateurs->get($dispense->validator_npi);
                $dispense->validator_info = $validator; // Ajoute les informations du validateur
            }
        }

        return $this->successResponse($dispenses);
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

    public function validateOrRejectDispense(Request $request, $dispenseId)
    {
        $this->hasAnyPermission(["all","manage-dispense-paiement"]);

        try {
            // Validation des paramètres de la requête
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:validate,reject',
            ], [
                'action.required' => 'L\'action (validation ou rejet) est requise.',
                'action.in' => 'L\'action doit être soit "validate" soit "reject".',
            ]);

            // Si la validation échoue, retourner les erreurs
            if ($validator->fails()) {
                return $this->errorResponse("La validation des données a échoué.", $validator->errors());
            }

            // Récupérer la dispense par son ID
            $dispense = DispensePaiement::find($dispenseId);

            if (!$dispense) {
                return $this->errorResponse("La demande de dispense n'a pas été trouvée.", null, null, 404);
            }

            // Vérifier si la dispense a déjà été validée ou rejetée
            if (in_array($dispense->status, ['validated', 'rejected'])) {
                return $this->errorResponse("Cette demande a déjà été " . $dispense->status . ".", null, null, 400);
            }

            // Récupérer l'utilisateur connecté
            $user = auth()->user();
            $userId = $user->id;  // ID de l'utilisateur
            $userNpi = $user->npi;  // NPI de l'utilisateur

            // Vérification de l'action : validation ou rejet
            if ($request->action == 'validate') {
                // Mise à jour pour la validation
                $dispense->status = 'validated';  // Changer le statut en "validated"
                $dispense->validated_at = now();  // Date de validation
                $dispense->validator_id = $userId;  // ID de la personne qui valide
                $dispense->validator_npi = $userNpi;  // NPI de la personne qui valide
            } elseif ($request->action == 'reject') {
                // Mise à jour pour le rejet
                $dispense->status = 'rejected';  // Changer le statut en "rejected"
                $dispense->rejeted_at = now();  // Date de rejet
                $dispense->validator_id = $userId;  // ID de la personne qui rejette
                $dispense->validator_npi = $userNpi;  // NPI de la personne qui rejette
            }

            // Sauvegarder les modifications dans la base de données
            $dispense->save();

            // Retourner une réponse de succès
            return $this->successResponse($dispense, 'La demande de dispense a été traitée avec succès.');

        } catch (\Throwable $th) {
            // Log de l'erreur
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue est survenue. Veuillez réessayer plus tard.", null, null, 500);
        }
    }

}
