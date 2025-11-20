<?php

namespace App\Services\DossierCandidat;

use App\Models\User;
use App\Services\Api;
use App\Models\AncienPermis;
use Illuminate\Http\Request;
use App\Models\ParcoursSuivi;
use App\Models\DossierSession;
use App\Models\DossierCandidat;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class CreateCandidatDossier extends ApiController
{
    /**
     * Crée un nouveau dossier candidat avec les informations fournies.
     *
     * @param Request $request Les données de la requête HTTP.
     * @return \Illuminate\Http\JsonResponse La réponse JSON avec les résultats de l'opération.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
        }
        $candidatId = $user->id;
        $categoriePermisId = $request->input('categorie_permis_id');
        $candidatType = $request->input('candidat_type');
        // Récupérer le nom de la catégorie de permis depuis le request

        // Appel à l'API externe pour récupérer le montant de la catégorie de permis
        $categoryPermis = Api::data(Api::base('GET', "categorie-permis/{$categoriePermisId}"));

        $permisName = $categoryPermis['name'];
        $montantKey = $candidatType === 'civil' ? 'montant' : 'montant_militaire';
        $montant = $categoryPermis[$montantKey] ?? null;
        $numPermis = $request->input('num_permis');

        if (!$montant) {
            return $this->errorResponse("Montant introuvable pour la catégorie de permis {$permisName}.", null, null, 500);
        }
        // Vérifier s'il s'agit d'une réinscription
        $isReinscription = $request->input('has_dossier_permis') === 'true';

        $groupSanguin = null;
        $groupageTest = null;

        // Si c'est une réinscription, récupérer les informations du dernier dossier réinscrit
        if ($isReinscription) {
            $lastDossier = $this->storeReinscription($candidatId, $categoriePermisId, $request);
            $groupSanguin = $lastDossier->group_sanguin;
            $groupageTest = $lastDossier->groupage_test;
        } else {
            // Vérifier si un dossier existe déjà pour le candidat avec le même categorie_permis_id
            $existingDossier = DossierCandidat::where('candidat_id', $candidatId)
                ->where('categorie_permis_id', $categoriePermisId)
                ->exists();

            if ($existingDossier) {
                $existingDossierSameType = DossierCandidat::where('candidat_id', $candidatId)
                    ->where('categorie_permis_id', $categoriePermisId)
                    ->where('is_militaire', $request->input('candidat_type'))
                    ->exists();

                if ($existingDossierSameType) {
                    return $this->errorResponse('Un dossier existe déjà pour ce candidat avec la même catégorie de permis', null, null, 422);
                }
            }

            // Si ce n'est pas une réinscription, vérifier si les champs groupage_test et group_sanguin ont été fournis
            if ($request->hasFile('groupage_test')) {
                $groupageTest = $request->file('groupage_test')->store('groupage_test', 'public');
            }

            if ($request->has('group_sanguin')) {
                $groupSanguin = $request->input('group_sanguin');
            }
        }

        $ficheMedical = null;
        if ($request->hasFile('fiche_medical')) {
            $ficheMedical = $request->file('fiche_medical')->store('fiche_medical', 'public');
        }

        $permisPrealableId = $request->input('permis_prealable_id');;


        // Vérifier si les valeurs permis_prealable_id et permis_prealable_dure sont présentes dans la requête


        $ancienDossier = DossierCandidat::where('npi', $request->get('npi'))->latest()->first();

        $hasOldDossier = $ancienDossier !== null;


        if ($hasOldDossier) {
            $groupageTest = $ancienDossier->groupage_test;
            $groupSanguin = $ancienDossier->group_sanguin;

            //Si un permis préalable existait et la date est bonne
            //Si un permis préalable existait et c'est la date qui n'est pas bonne, $hasValideDate, prend false

            if (!$this->hasValidPermisPrealable($request, $candidatId, $remainingMonths, $hasValideDate)) {
                if (!$hasValideDate) {
                    return $this->successResponse(null, 'Vous n\'avez pas validé le temps requis pour continuer. Il vous reste ' . $remainingMonths . ' mois.', 400);
                }
            }
            $dossierCandidat = $this->createDossierWithSession(
                $request,
                $categoriePermisId,
                $groupSanguin,
                $groupageTest,
                $ficheMedical,
                $candidatType,
                $montant
            );
            // Mettre à jour le champ has_dossier_permi de l'utilisateur
            $user = User::find($candidatId);
            $user->has_dossier_permi = true;
            $user->save();

            $this->createParcoursSuivi($dossierCandidat, $permisName);
            return $this->successResponse([

                'dossierCandidat' => $dossierCandidat,
                'user' => $user
            ], 'Données enregistrées avec succès');
        } else {
            //Les informations de groupe sanguin
            $v = Validator::make($request->all(), [
                'group_sanguin' => "required",
                'groupage_test' => "required|file",
            ]);

            if ($v->fails()) {
                return $this->errorResponse("La validation é échoué", $v->errors());
            }

            //I n'y a pas de  numéro de permis
            if ($numPermis !== null) {
                return  $this->storeWithAncien(
                    $request,
                    $categoriePermisId,
                    $groupSanguin,
                    $groupageTest,
                    $permisPrealableId,
                    $permisName,
                    $ficheMedical,
                    $candidatType,
                    $montant
                );
            }
            $dossierCandidat = $this->createDossierWithSession(
                $request,
                $categoriePermisId,
                $groupSanguin,
                $groupageTest,
                $ficheMedical,
                $candidatType,
                $montant
            );
            // Mettre à jour le champ has_dossier_permi de l'utilisateur
            $user = User::find($candidatId);
            $user->has_dossier_permi = true;
            $user->save();

            $this->createParcoursSuivi($dossierCandidat, $permisName);
            return $this->successResponse([

                'dossierCandidat' => $dossierCandidat,
                'user' => $user
            ], 'Données enregistrées avec succès');
        }
    }

    /**
     * Crée un nouveau dossier candidat avec une session associée.
     *
     * @param int $candidatId L'identifiant du candidat.
     * @param Request $request Les données de la requête HTTP.
     * @param int $categoriePermisId L'identifiant de la catégorie de permis.
     * @param string|null $groupSanguin Le groupe sanguin du candidat (optionnel).
     * @param string|null $groupageTest Le résultat du test de groupage (optionnel).
     * @param string|null $ficheMedical Le chemin vers le fichier de la fiche médicale (optionnel).
     * @param string $candidatType Le type de candidat (civil ou militaire).
     * @param float|null $montant Le montant du permis (optionnel).
     * @return DossierCandidat Le nouveau dossier candidat créé.
     */
    private function createDossierWithSession($request, $categoriePermisId, $groupSanguin, $groupageTest, $ficheMedical, $candidatType, $montant)
    {
        $dossierCandidat = DossierCandidat::create([
            'candidat_id' => $request->input('candidat_id'),
            'is_militaire' => $candidatType,
            'categorie_permis_id' => $categoriePermisId,
            'npi' => $request->input('npi'),
            'group_sanguin' => $groupSanguin,
            'groupage_test' => $groupageTest,
        ]);

        $inputRestrictions = $request->input('restriction_medical');

        // Convertir les données en tableau PHP
        $restrictionMedical = explode(',', $inputRestrictions);
        
        // Convertir le tableau en format JSON pour l'insertion dans la base de données
        $restrictionMedicalJson = json_encode($restrictionMedical);

        DossierSession::create([
            'is_militaire' => $candidatType,
            'restriction_medical' => $restrictionMedicalJson,
            'fiche_medical' => $ficheMedical,
            'type_examen' => $request->input('type_examen', 'code-conduite'),
            'permis_extension_id' => $request->input('permis_extension_id'),
            'langue_id' => $request->input('langue_id'),
            'auto_ecole_id' => $request->input('auto_ecole_id'),
            'annexe_id' => $request->input('annexe_id'),
            'montant_paiement' => $montant,
            "npi" => $request->input('npi'),
            'dossier_candidat_id' => $dossierCandidat->id,
            'categorie_permis_id' => $categoriePermisId,
            'permis_prealable_id' => $request->input('permis_prealable_id'),
            'permis_prealable_dure' => $request->input('permis_prealable_dure')
        ]);

        return $dossierCandidat;
    }

    /**
     * Crée un dossier candidat à partir d'un ancien permis.
     *
     * @param int $candidatId L'identifiant du candidat.
     * @param Request $request Les données de la requête HTTP.
     * @param int $categoriePermisId L'identifiant de la catégorie de permis.
     * @param string|null $groupSanguin Le groupe sanguin du candidat (optionnel).
     * @param string|null $groupageTest Le résultat du test de groupage (optionnel).
     * @param int|null $permisPrealableId L'identifiant du permis préalable (optionnel).
     * @param string $permisName Le nom de la catégorie de permis.
     * @param string|null $ficheMedical Le chemin vers le fichier de la fiche médicale (optionnel).
     * @param string $candidatType Le type de candidat (civil ou militaire).
     * @param float|null $montant Le montant du permis (optionnel).
     * @return \Illuminate\Http\JsonResponse La réponse JSON avec les résultats de l'opération.
     */
    private function storeWithAncien(
        $request,
        $categoriePermisId,
        $groupSanguin,
        $groupageTest,
        $permisPrealableId,
        $permisName,
        $ficheMedical,
        $candidatType,
        $montant
    ) {
        $fichierPermisPrealable = $request->file('fichier_permis_prealable')->store('fichier_permis_prealable', 'public');
        /**
         * @var DossierCandidat $dossierCandidat
         */
        $dossierCandidat = $this->createDossierWithSession(
            $request,
            $categoriePermisId,
            $groupSanguin,
            $groupageTest,
            $ficheMedical,
            $candidatType,
            $montant
        );

        AncienPermis::create([
            'num_matricule' =>
            $request->input('num_matricule'),
            'num_permis' =>  $request->input('num_permis'),
            'categorie_permis_id' => $permisPrealableId,
            'candidat_id' => $request->input('candidat_id'),
            'dossier_candidat_id' =>  $dossierCandidat->id,
            'fichier_permis_prealable' => $fichierPermisPrealable,

        ]);


        // Mettre à jour le champ has_dossier_permi de l'utilisateur
        $user = User::find($request->input('candidat_id'));
        $user->has_dossier_permi = true;
        $user->save();

        $this->createParcoursSuivi($dossierCandidat, $permisName);
        return $this->successResponse([
            'dossierCandidat' => $dossierCandidat,
            'user' => $user
        ], 'Données enregistrées avec succès');
    }

    private function storeReinscription($candidatId, $categoriePermisId, Request $request)
    {
        $lastDossier = DossierCandidat::where('candidat_id', $candidatId)
            ->orderByDesc('created_at')
            ->first();

        if (!$lastDossier) {
            return $this->errorResponse('Aucun dossier trouvé pour la réinscription', null, null, 422);
        }
        // Vérifier si un dossier existe déjà pour le candidat avec le même categorie_permis_id
        $existingDossier = DossierCandidat::where('candidat_id', $candidatId)
            ->where('categorie_permis_id', $categoriePermisId)
            ->exists();

        if ($existingDossier) {
            $existingDossierSameType = DossierCandidat::where('candidat_id', $candidatId)
                ->where('categorie_permis_id', $categoriePermisId)
                ->where('is_militaire', $request->input('candidat_type'))
                ->exists();

            if ($existingDossierSameType) {
                return $this->errorResponse('Un dossier existe déjà pour ce candidat avec la même catégorie de permis', null, null, 422);
            }
        }
        return $lastDossier;
    }

    /**
     * Crée un suivi de parcours associé à un dossier candidat et enregistre l'ID de DossierSession.
     *
     * @param DossierCandidat $dossierCandidat Le dossier candidat associé.
     * @param string $permisName Le nom de la catégorie de permis.
     * @return ParcoursSuivi Le suivi de parcours créé.
     */
    private function createParcoursSuivi(DossierCandidat $dossierCandidat, $permisName)
    {
        $parcoursSuiviData = [
            'npi' => $dossierCandidat->npi,
            'slug' => 'preinscription',
            'service' => 'Permis',
            'candidat_id' => $dossierCandidat->candidat_id,
            'dossier_candidat_id' => $dossierCandidat->id,
            'categorie_permis_id' => $dossierCandidat->categorie_permis_id,
            'message' => 'Votre demande de préinscription à l\'examen du Permis de conduire catégorie ' . $permisName . ' a été effectuée avec succès',
            'date_action' => now(),
        ];

        // Créer le parcours suivi
        $parcoursSuivi = ParcoursSuivi::create($parcoursSuiviData);

        // Récupérer l'ID de DossierSession associée et la mettre à jour dans le parcours suivi
        $dossierSession = DossierSession::where('dossier_candidat_id', $dossierCandidat->id)->first();
        if ($dossierSession) {
            $parcoursSuivi->dossier_session_id = $dossierSession->id;
            $parcoursSuivi->save();
        }

        return $parcoursSuivi;
    }

    private function hasValidPermisPrealable($request, $candidatId, &$remainingMonths, &$hasValidDay = true)
    {
        $permisPrealableId = $request->input('permis_prealable_id');
        $permisPrealableDure = $request->input('permis_prealable_dure');
        $hasPermisPrealable = $permisPrealableId && $permisPrealableDure;

        if ($hasPermisPrealable) {
            $permisPrealable = Api::data(Api::admin('GET', 'candidat-permis/' . $candidatId . '/' . $permisPrealableId));

            $dateDelivrance = $permisPrealable['0']['date_delivrance'];

            // Calcul du nombre total de mois écoulés depuis la date de délivrance du permis

            $diffTime = now()->diffInMonths($dateDelivrance);
            $hasValidDay =  $diffTime >= $permisPrealableDure;

            if (!$hasValidDay) {
                $remainingMonths = $permisPrealableDure - $diffTime;
                return false;
            } else {

                return true;
            }
        }

        return !$hasPermisPrealable;
    }
}
