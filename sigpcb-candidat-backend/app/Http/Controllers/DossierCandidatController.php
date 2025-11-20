<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Services\Api;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ParcoursSuivi;
use App\Models\DossierSession;
use App\Models\CandidatPayment;
use App\Models\DossierCandidat;
use App\Services\Mail\Messager;
use App\Models\PermisNumPayment;
use Illuminate\Http\UploadedFile;
use App\Models\EserviceParcourSuivi;
use App\Services\Mail\EmailNotifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Services\DossierCandidat\FullDossierDetails;
use App\Services\DossierCandidat\CreateCandidatDossier;
use App\Models\Admin\Restriction;
use App\Models\Examen;


class DossierCandidatController extends ApiController
{

    public function index()
    {

        try {
            $dossiers = DossierCandidat::all()->map(function (DossierCandidat $dossier) {
                return $dossier->load('lastDossierSession');
            });
            return $this->successResponse($dossiers);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse($e->getMessage());
        }
    }


    /**
     * @OA\Post(
     *      path="/api/anatt-candidat/dossier-candidats",
     *      operationId="createDossierCandidats",
     *      tags={"DossierCandidats"},
     *      summary="Crée un nouveau dossier-candidats",
     *      description="Crée un nouveau dossier-candidat enregistré dans la base de donnée",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="candidat_id",
     *                      description="ID du candidat",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="npi",
     *                      description="npi du candidat",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="auto_ecole_id",
     *                      description="ID de l'auto ecole",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="categorie_permis_id",
     *                      description="ID de la catégorie du permis",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="annexe_id",
     *                      description="ID de l'annexe",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="langue_id",
     *                      description="ID de la langue de composition",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="group_sanguin",
     *                      description="Le groupe sanguin du candidat",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="code_autoecole",
     *                      description="Le code délivré par l'auto école",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="fiche_medical",
     *                      description="le fichier de la fiche médical",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="groupage_test",
     *                      description="le fichier du test de groupage",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="restriction_medical",
     *                      description="restriction médical du candidat",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="candidat_type",
     *                      description="Pour militaire ou civil",
     *                      type="string"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau dossier-candidat créé"
     *      )
     * )
     */
    public function store(Request $request)
    {

        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'candidat_id' => ['required', 'exists:candidats,id'],
            'categorie_permis_id' => 'required',
            'langue_id' => 'required',
            'code_autoecole' => 'required',
            'auto_ecole_id' => 'required',
            'annexe_id' => 'required',
            'fiche_medical' => 'nullable|image',
            'groupage_test' => 'nullable|image',
            'permis_prealable_id' => 'nullable|integer',
            'permis_prealable_dure' => 'nullable|integer',
            'num_permis' => 'nullable',
            'num_matricule' => 'nullable',
            'fichier_permis_prealable' => 'nullable|file',
            'npi' => 'required',
            'permis_extension_id' => 'nullable|integer',
            'restriction_medical' => 'nullable',
            'candidat_type' => 'required'
        ], [
            'candidat_id.required' => 'Le champ candidat est requis.',
            'candidat_id.exists' => 'Le candidat sélectionné n\'existe pas.',
            'categorie_permis_id.required' => 'Le champ catégorie de permis est requis.',
            'langue_id.required' => 'Le champ langue est requis.',
            'code_autoecole.required' => 'Le champ code de l\'auto-école est requis.',
            'auto_ecole_id.required' => 'Le champ auto-école est requis.',
            'fiche_medical.image' => 'Le champ fiche médicale doit être un fichier image.',
            'groupage_test.image' => 'Le champ test de groupage doit être un fichier image.',
            'permis_prealable_id.nullable' => 'Le champ permis préalable doit être nullable.',
            'num_permis.nullable' => 'Le champ numéro de permis doit être nullable.',
            'num_matricule.nullable' => 'Le champ numéro de matricule doit être nullable.',
            'fichier_permis_prealable.nullable' => 'Le champ fichier permis préalable doit être nullable.',
            'fichier_permis_prealable.file' => 'Le champ fichier permis préalable doit être un fichier.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
        }
        $user = Auth::user();
        if (!$user) {
            return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
        }
        $npi = $user->npi;
        $categoriePermisId = $request->input('categorie_permis_id');
        $candidatType = $request->input('candidat_type');

        try {

            $lastDossier = DossierCandidat::where('npi', $npi)
                ->orderByDesc('created_at')
                ->first();

            if ($lastDossier && $lastDossier->state === 'pending') {
                return $this->errorResponse('Vous devez finaliser votre dernier dossier en cours avant de créer un nouveau dossier.', null, null, 422);
            }
            $existingDossier = DossierCandidat::where('npi', $npi)
                ->where('categorie_permis_id', $categoriePermisId)
                ->where('is_militaire', $candidatType)
                ->first();
            if ($existingDossier) {
                $dossierState = $existingDossier->state;
                switch ($dossierState) {

                    case 'success':
                        return $this->errorResponse('Vous avez déjà obtenu la catégorie de permis choisie, veuillez réessayer avec une autre.', null, null, 422);
                    case 'pending':
                        return $this->errorResponse('Un dossier en attente existe déjà pour ce candidat avec la même catégorie de permis et type de candidat.', null, null, 422);
                    case 'closed':
                    case 'failed':
                        $state = 'pending';
                        $existingPendingDossier = DossierCandidat::where('npi', $npi)
                            ->where('state', $state)
                            ->first();
                        if ($existingPendingDossier) {
                            return $this->errorResponse('Veuillez finaliser le dossier en cours avant de tenter l\'obtention d\'une autre catégorie de permis.', null, null, 422);
                        } else {
                            $candidatId = $user->id;
                            $categoriePermisId = $request->input('categorie_permis_id');
                            $candidatType = $request->input('candidat_type');
                            // Appel à l'API externe pour récupérer le montant de la catégorie de permis
                            $categoryPermis = Api::data(Api::base('GET', "categorie-permis/{$categoriePermisId}"));

                            $permisName = $categoryPermis['name'];
                            $montantKey = $candidatType === 'civil' ? 'montant' : 'montant_militaire';
                            $montant = $categoryPermis[$montantKey] ?? null;
                            $numPermis = $request->input('num_permis');

                            if (!$montant) {
                                return $this->errorResponse("Montant introuvable pour la catégorie de permis {$permisName}.", null, null, 500);
                            }

                            if($request->has('permis_extension_id')){
                                $categoryPermisEx = Api::data(Api::base('GET', "categorie-permis/{$request->has('permis_extension_id')}"));
                                $permisExName = $categoryPermisEx['name'];
                                $montantEx = $categoryPermisEx['montant_extension'];
                                if (!$montantEx) {
                                    return $this->errorResponse("Montant introuvable pour la catégorie de permis {$permisExName}.", null, null, 500);
                                }
                                $montant = $montant + $montantEx;
                            }
                            $oldDossierSession = DossierSession::where('dossier_candidat_id', $existingDossier->id)->latest()->first();
                            if ($oldDossierSession) {

                                $restrictionMedicalJson = null;

                                if (($request->has('restriction_medical')) && (strlen($request->input('restriction_medical')) > 0)) {
                                    $inputRestrictions = $request->input('restriction_medical');
                                    // Convertir les données en tableau PHP
                                    $restrictionMedical = explode(',', $inputRestrictions);

                                    // Initialiser le tableau des restrictions valides
                                    $validRestrictions = [];

                                    // Vérifier chaque restriction
                                    foreach ($restrictionMedical as $restrictionId) {
                                        if ($restrictionId == "0") {
                                            // Si la restriction est "0", ajouter sans vérifier
                                            $validRestrictions[] = $restrictionId;
                                        } else {
                                            // Vérifier l'existence de la restriction dans le modèle Restriction
                                            $exists = Restriction::find($restrictionId);
                                            if ($exists) {
                                                $validRestrictions[] = $restrictionId;
                                            } else {
                                                // Si la restriction n'est pas trouvée, renvoyer un message d'erreur
                                                return $this->errorResponse('Restriction médicale non trouvée', null, null, 422);
                                            }
                                        }
                                    }

                                    // Convertir le tableau des restrictions valides en format JSON pour l'insertion dans la base de données
                                    $restrictionMedicalJson = json_encode($validRestrictions);
                                }
                                $ficheMedical = null;
                                if ($request->hasFile('fiche_medical')) {
                                    $ficheMedical = $request->file('fiche_medical')->store('fiche_medical', 'public');
                                }else {
                                    $ficheMedical = $oldDossierSession->fiche_medical;
                                }

                                $newDossierSession = new DossierSession;
                                $newDossierSession->categorie_permis_id = $categoriePermisId;
                                $newDossierSession->fiche_medical =$ficheMedical;
                                $newDossierSession->is_militaire = $oldDossierSession->is_militaire;
                                $newDossierSession->permis_prealable_id = $oldDossierSession->permis_prealable_id;
                                $newDossierSession->permis_prealable_dure = $oldDossierSession->permis_prealable_dure;
                                $newDossierSession->dossier_candidat_id = $existingDossier->id;
                                $newDossierSession->npi = $npi;
                                $newDossierSession->restriction_medical = $restrictionMedicalJson;
                                $newDossierSession->permis_extension_id = $request->input('permis_extension_id');
                                $newDossierSession->examen_id = null;
                                $newDossierSession->state = 'init';
                                $newDossierSession->closed = false;
                                $newDossierSession->presence = null;
                                $newDossierSession->presence_conduite = null;
                                $newDossierSession->montant_paiement = $montant;
                                $newDossierSession->auto_ecole_id = $request->input('auto_ecole_id');
                                $newDossierSession->annexe_id = $request->input('annexe_id');
                                $newDossierSession->resultat_conduite = null;
                                $newDossierSession->resultat_code = null;
                                $newDossierSession->langue_id = $request->input('langue_id');
                                $newDossierSession->date_inscription = now();
                                $newDossierSession->save();
                                $existingDossier->update(['state' => 'pending']);

                                $parcoursSuiviData = [
                                    'npi' => $existingDossier->npi,
                                    'slug' => 'new-ds',
                                    'service' => 'Permis',
                                    'candidat_id' => $existingDossier->candidat_id,
                                    'dossier_candidat_id' => $existingDossier->id,
                                    'categorie_permis_id' => $existingDossier->categorie_permis_id,
                                    'message' => 'Une nouvelle session vous a été créée avec succès',
                                    'date_action' => now(),
                                ];

                                // Créer le parcours suivi
                                $parcoursSuivi = ParcoursSuivi::create($parcoursSuiviData);
                                DB::commit();
                                return $this->successResponse([
                                    'dossierSession' => $newDossierSession,
                                    'user' => $user
                                ], 'Une nouvelle session a été créée avec succès ');
                            }
                        }
                    default:
                        return $this->errorResponse('Un dossier existe déjà pour ce candidat avec la même catégorie de permis et type de candidat.', null, null, 422);
                }
            }
            $succes = (new CreateCandidatDossier())->store($request);
            DB::commit();
            return $succes;
        } catch (\Throwable $th) {
            logger()->error($th);
            DB::rollBack();
            return $this->errorResponse("Une erreur est survenue sur le serveur");
        }
    }

    public function storeExternalReconduit(Request $request)
    {

        DB::beginTransaction();
        $validator = Validator::make($request->all(), [
            'candidat_id' => ['required', 'exists:candidats,id'],
            'categorie_permis_id' => 'required',
            'langue_id' => 'required',
            'code_autoecole' => 'required',
            'auto_ecole_id' => 'required',
            'annexe_id' => 'required',
            'fiche_medical' => 'nullable|image',
            'groupage_test' => 'nullable|image',
            'permis_prealable_id' => 'nullable|integer',
            'permis_prealable_dure' => 'nullable|integer',
            'num_permis' => 'nullable',
            'num_matricule' => 'nullable',
            'fichier_permis_prealable' => 'nullable|file',
            'npi' => 'required',
            'permis_extension_id' => 'nullable|integer',
            'restriction_medical' => 'nullable',
            'candidat_type' => 'required'
        ], [
            'candidat_id.required' => 'Le champ candidat est requis.',
            'candidat_id.exists' => 'Le candidat sélectionné n\'existe pas.',
            'categorie_permis_id.required' => 'Le champ catégorie de permis est requis.',
            'langue_id.required' => 'Le champ langue est requis.',
            'code_autoecole.required' => 'Le champ code de l\'auto-école est requis.',
            'auto_ecole_id.required' => 'Le champ auto-école est requis.',
            'fiche_medical.image' => 'Le champ fiche médicale doit être un fichier image.',
            'groupage_test.image' => 'Le champ test de groupage doit être un fichier image.',
            'permis_prealable_id.nullable' => 'Le champ permis préalable doit être nullable.',
            'num_permis.nullable' => 'Le champ numéro de permis doit être nullable.',
            'num_matricule.nullable' => 'Le champ numéro de matricule doit être nullable.',
            'fichier_permis_prealable.nullable' => 'Le champ fichier permis préalable doit être nullable.',
            'fichier_permis_prealable.file' => 'Le champ fichier permis préalable doit être un fichier.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
        }
        $user = Auth::user();
        if (!$user) {
            return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
        }
        $npi = $user->npi;
        $categoriePermisId = $request->input('categorie_permis_id');
        $candidatType = $request->input('candidat_type');

        try {

            $lastDossier = DossierCandidat::where('npi', $npi)
                ->orderByDesc('created_at')
                ->first();

            if ($lastDossier && $lastDossier->state === 'pending') {
                return $this->errorResponse('Vous devez finaliser votre dernier dossier en cours avant de créer un nouveau dossier.', null, null, 422);
            }
            $existingDossier = DossierCandidat::where('npi', $npi)
                ->where('categorie_permis_id', $categoriePermisId)
                ->where('is_militaire', $candidatType)
                ->first();
            if ($existingDossier) {
                $dossierState = $existingDossier->state;
                switch ($dossierState) {

                    case 'success':
                        return $this->errorResponse('Vous avez déjà obtenu la catégorie de permis choisie, veuillez réessayer avec une autre.', null, null, 422);
                    case 'pending':
                        return $this->errorResponse('Un dossier en attente existe déjà pour ce candidat avec la même catégorie de permis et type de candidat.', null, null, 422);
                    case 'closed':
                    case 'failed':
                        $state = 'pending';
                        $existingPendingDossier = DossierCandidat::where('npi', $npi)
                            ->where('state', $state)
                            ->first();
                        if ($existingPendingDossier) {
                            return $this->errorResponse('Veuillez finaliser le dossier en cours avant de tenter l\'obtention d\'une autre catégorie de permis.', null, null, 422);
                        } else {
                            $candidatId = $user->id;
                            $categoriePermisId = $request->input('categorie_permis_id');
                            $candidatType = $request->input('candidat_type');
                            // Appel à l'API externe pour récupérer le montant de la catégorie de permis
                            $categoryPermis = Api::data(Api::base('GET', "categorie-permis/{$categoriePermisId}"));

                            $permisName = $categoryPermis['name'];
                            $montantKey = $candidatType === 'civil' ? 'montant' : 'montant_militaire';
                            $montant = $categoryPermis[$montantKey] ?? null;
                            $numPermis = $request->input('num_permis');

                            if (!$montant) {
                                return $this->errorResponse("Montant introuvable pour la catégorie de permis {$permisName}.", null, null, 500);
                            }

                            if($request->has('permis_extension_id')){
                                $categoryPermisEx = Api::data(Api::base('GET', "categorie-permis/{$request->has('permis_extension_id')}"));
                                $permisExName = $categoryPermisEx['name'];
                                $montantEx = $categoryPermisEx['montant_extension'];
                                if (!$montantEx) {
                                    return $this->errorResponse("Montant introuvable pour la catégorie de permis {$permisExName}.", null, null, 500);
                                }
                                $montant = $montant + $montantEx;
                            }
                            $oldDossierSession = DossierSession::where('dossier_candidat_id', $existingDossier->id)->latest()->first();
                            if ($oldDossierSession) {

                                $restrictionMedicalJson = null;

                                if (($request->has('restriction_medical')) && (strlen($request->input('restriction_medical')) > 0)) {
                                    $inputRestrictions = $request->input('restriction_medical');
                                    // Convertir les données en tableau PHP
                                    $restrictionMedical = explode(',', $inputRestrictions);

                                    // Initialiser le tableau des restrictions valides
                                    $validRestrictions = [];

                                    // Vérifier chaque restriction
                                    foreach ($restrictionMedical as $restrictionId) {
                                        if ($restrictionId == "0") {
                                            // Si la restriction est "0", ajouter sans vérifier
                                            $validRestrictions[] = $restrictionId;
                                        } else {
                                            // Vérifier l'existence de la restriction dans le modèle Restriction
                                            $exists = Restriction::find($restrictionId);
                                            if ($exists) {
                                                $validRestrictions[] = $restrictionId;
                                            } else {
                                                // Si la restriction n'est pas trouvée, renvoyer un message d'erreur
                                                return $this->errorResponse('Restriction médicale non trouvée', null, null, 422);
                                            }
                                        }
                                    }

                                    // Convertir le tableau des restrictions valides en format JSON pour l'insertion dans la base de données
                                    $restrictionMedicalJson = json_encode($validRestrictions);
                                }

                                $newDossierSession = new DossierSession;
                                $newDossierSession->categorie_permis_id = $categoriePermisId;
                                $newDossierSession->fiche_medical = $oldDossierSession->fiche_medical;
                                $newDossierSession->is_militaire = $oldDossierSession->is_militaire;
                                $newDossierSession->permis_prealable_id = $oldDossierSession->permis_prealable_id;
                                $newDossierSession->permis_prealable_dure = $oldDossierSession->permis_prealable_dure;
                                $newDossierSession->dossier_candidat_id = $existingDossier->id;
                                $newDossierSession->npi = $npi;
                                $newDossierSession->type_examen = 'conduite';
                                $newDossierSession->restriction_medical = $restrictionMedicalJson;
                                $newDossierSession->permis_extension_id = $request->input('permis_extension_id');
                                $newDossierSession->examen_id = null;
                                $newDossierSession->state = 'init';
                                $newDossierSession->closed = false;
                                $newDossierSession->presence = 'present';
                                $newDossierSession->presence_conduite = null;
                                $newDossierSession->montant_paiement = $montant;
                                $newDossierSession->auto_ecole_id = $request->input('auto_ecole_id');
                                $newDossierSession->annexe_id = $request->input('annexe_id');
                                $newDossierSession->resultat_conduite = null;
                                $newDossierSession->langue_id = $request->input('langue_id');
                                $newDossierSession->date_inscription = now();
                                $newDossierSession->resultat_code = 'success';
                                $newDossierSession->is_external = true;
                                $newDossierSession->save();
                                $existingDossier->update(['state' => 'pending']);

                                $parcoursSuiviData = [
                                    'npi' => $existingDossier->npi,
                                    'slug' => 'new-ds',
                                    'service' => 'Permis',
                                    'candidat_id' => $existingDossier->candidat_id,
                                    'dossier_candidat_id' => $existingDossier->id,
                                    'categorie_permis_id' => $existingDossier->categorie_permis_id,
                                    'message' => 'Une nouvelle session vous a été créée avec succès',
                                    'date_action' => now(),
                                ];

                                // Créer le parcours suivi
                                $parcoursSuivi = ParcoursSuivi::create($parcoursSuiviData);
                                DB::commit();
                                return $this->successResponse([
                                    'dossierSession' => $newDossierSession,
                                    'user' => $user
                                ], 'Une nouvelle session a été créée avec succès ');
                            }
                        }
                    default:
                        return $this->errorResponse('Un dossier existe déjà pour ce candidat avec la même catégorie de permis et type de candidat.', null, null, 422);
                }
            }
            $succes = (new CreateCandidatDossier())->store($request);
            DB::commit();
            return $succes;
        } catch (\Throwable $th) {
            logger()->error($th);
            DB::rollBack();
            return $this->errorResponse("Une erreur est survenue sur le serveur");
        }
    }

    /**
     * @OA\Post(
     *     path="/api/anatt-candidat/dossier-candidats/close",
     *     summary="Fermer un nouveau dossier",
     *      operationId="closeDossierSession",
     *     tags={"DossierCandidats"},
     *     @OA\RequestBody(
     *         required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier du candidat",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_session_id",
     *                      description="ID du dossier session",
     *                      type="integer",
     *                      example=1
     *                  ),
     *      ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Dossier fermé avec succès",
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation échouée",
     *     ),
     * )
     */
    public function closeDossier(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $npi = $user->npi;
            $candidatId = $user->id;
            $state = 'closed';

            // Récupérer le dernier dossier
            $lastDossier = $this->getLastDossier($npi);

            if ($lastDossier) {
                $this->updateDossierState($lastDossier, $state);

                // Récupérer la dernière session de dossier
                $lastDossierSession = $this->getLastDossierSession($lastDossier);

                if ($lastDossierSession) {
                    $this->closeDossierSession($lastDossierSession);
                    $this->updateSuiviButton($lastDossierSession);
                }
            }
            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $lastDossierSession->npi;
            $parcoursSuivi->slug = 'notification-fermeture';
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $candidatId;
            $parcoursSuivi->dossier_candidat_id = $lastDossierSession->dossier_candidat_id;
            $parcoursSuivi->dossier_session_id = $lastDossierSession->id;
            $parcoursSuivi->categorie_permis_id = $lastDossierSession->categorie_permis_id;
            $parcoursSuivi->bouton = json_encode(['bouton' => 'Reinscription', 'status' => '1']);
            $parcoursSuivi->message = "Ce dossier a été fermé avec succès";
            $parcoursSuivi->date_action = now();
            $parcoursSuivi->save();
            return $this->successResponse($lastDossierSession, 'Le statut du dossier candidat a été mis à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur est survenue sur le serveur");
        }
    }

    private function getLastDossier($npi)
    {
        return DossierCandidat::where('npi', $npi)
            ->orderByDesc('id')
            ->first();
    }

    private function updateDossierState($dossier, $state)
    {
        $dossier->state = $state;
        $dossier->save();
    }

    private function getLastDossierSession($dossier)
    {
        return $dossier->lastDossierSession;
    }

    private function closeDossierSession($dossierSession)
    {
        $dossierSession->closed = true;
        $dossierSession->abandoned = true;
        $dossierSession->save();
    }
    private function openDossierSession($dossierSession)
    {
        $dossierSession->closed = false;
        $dossierSession->abandoned = false;
        $dossierSession->save();
    }
    /**
     * @OA\Post(
     *     path="/api/anatt-candidat/dossier-candidats/open",
     *     summary="Fermer un nouveau dossier",
     *      operationId="openDossierSession",
     *     tags={"DossierCandidats"},
     *     @OA\RequestBody(
     *         required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier du candidat",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_session_id",
     *                      description="ID du dossier session",
     *                      type="integer",
     *                      example=1
     *                  ),
     *      ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Dossier fermé avec succès",
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation échouée",
     *     ),
     * )
     */
    public function openDossier(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $npi = $user->npi;
            $candidatId = $user->id;
            $state = 'pending';

            // Récupérer le dernier dossier
            $lastDossier = $this->getLastDossier($npi);

            if ($lastDossier) {
                $this->updateDossierState($lastDossier, $state);

                // Récupérer la dernière session de dossier
                $lastDossierSession = $this->getLastDossierSession($lastDossier);

                if ($lastDossierSession) {
                    $this->openDossierSession($lastDossierSession);
                }
            }
            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $lastDossierSession->npi;
            $parcoursSuivi->slug = 'notification-ouverture';
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $candidatId;
            $parcoursSuivi->dossier_candidat_id = $lastDossierSession->dossier_candidat_id;
            $parcoursSuivi->dossier_session_id = $lastDossierSession->id;
            $parcoursSuivi->categorie_permis_id = $lastDossierSession->categorie_permis_id;
            $parcoursSuivi->bouton = json_encode(['bouton' => 'Reinscription', 'status' => '1']);
            $parcoursSuivi->message = "Ce dossier a été ouvert avec succès";
            $parcoursSuivi->date_action = now();
            $parcoursSuivi->save();
            return $this->successResponse($lastDossierSession, 'Le statut du dossier candidat a été mis à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur est survenue sur le serveur");
        }
    }
    /**
     * @OA\Post(
     *      path="/api/anatt-candidat/dossier-candidats/justification-paiement",
     *      operationId="createJustifCandidatPayment",
     *      tags={"DossierCandidats"},
     *      summary="Enrégistrer un nouveau paiement du candidat",
     *      description="Crée un nouveau paiement du candidat enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="agregateur",
     *                      description="Nom de l'agrégateur",
     *                      type="string",
     *                      example="Nom de l'agrégateur"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      description="Description du paiement",
     *                      type="string",
     *                      example="Description du paiement"
     *                  ),
     *                  @OA\Property(
     *                      property="transaction_id",
     *                      description="ID de transaction",
     *                      type="string",
     *                      example="123456789"
     *                  ),
     *                  @OA\Property(
     *                      property="reference",
     *                      description="Référence du paiement",
     *                      type="string",
     *                      example="REF789456"
     *                  ),
     *                  @OA\Property(
     *                      property="mode",
     *                      description="Mode de paiement",
     *                      type="string",
     *                      example="Mode de paiement"
     *                  ),
     *                  @OA\Property(
     *                      property="operation",
     *                      description="Opération de paiement",
     *                      type="string",
     *                      example="Opération de paiement"
     *                  ),
     *                  @OA\Property(
     *                      property="transaction_key",
     *                      description="Clé de transaction",
     *                      type="string",
     *                      example="TransKey123"
     *                  ),
     *                  @OA\Property(
     *                      property="montant",
     *                      description="Le montant payé",
     *                      type="string",
     *                      example="50.00"
     *                  ),
     *                  @OA\Property(
     *                      property="phone_payment",
     *                      description="Numéro de téléphone utilisé pour le paiement",
     *                      type="string",
     *                      example="+1234567890"
     *                  ),
     *                  @OA\Property(
     *                      property="ref_operateur",
     *                      description="La référence de l'opérateur",
     *                      type="string",
     *                      example="OP123456"
     *                  ),
     *                  @OA\Property(
     *                      property="numero_recu",
     *                      description="Le numéro du reçu",
     *                      type="string",
     *                      example="REC789456"
     *                  ),
     *                  @OA\Property(
     *                      property="moyen_payment",
     *                      description="Le moyen de paiement (momo ou portefeuille)",
     *                      type="string",
     *                      example="momo"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut du paiement (pending, approved, declined ou canceled)",
     *                      type="string",
     *                      example="approved"
     *                  ),
     *                  @OA\Property(
     *                      property="num_transaction",
     *                      description="Numéro de transaction délivré par l'agrégateur",
     *                      type="string",
     *                      example="123456789"
     *                  ),
     *                  @OA\Property(
     *                      property="date_payment",
     *                      description="Date de paiement",
     *                      type="string",
     *                      format="date",
     *                      example="2023-07-19"
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier du candidat",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_session_id",
     *                      description="ID du dossier session",
     *                      type="integer",
     *                      example=1
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau paiement du candidat créé",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du nouveau paiement du candidat créé",
     *                  type="integer",
     *              ),
     *      )
     * )
     * )
     */
    public function closeAndCreateDossierSession(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'agregateur' => 'required|string',
                    'description' => 'required|string',
                    'transaction_id' => 'required|integer',
                    'reference' => 'required|string',
                    'mode' => 'required|string',
                    'operation' => 'required|string',
                    'transaction_key' => 'required|string',
                    'montant' => 'required|numeric|min:0',
                    'phone_payment' => 'required|string|min:8|max:25',
                    'ref_operateur' => 'nullable|string',
                    'numero_recu' => 'nullable|string|max:100|unique:auto_ecole_payments,numero_recu,NULL,id,agregateur_id,' . $request->agregateur,
                    'moyen_payment' => 'required|in:momo,portefeuille',
                    'status' => 'required|in:pending,approved,declined,canceled',
                    'num_transaction' => 'nullable|string|max:100|unique:auto_ecole_payments,num_transaction,NULL,id,agregateur_id,' . $request->agregateur,
                    'date_payment' => 'nullable|date',
                    'dossier_candidat_id' => 'required|exists:dossier_candidats,id',
                    'dossier_session_id' => 'required|exists:dossier_sessions,id',
                    'session_id' => 'required|integer',
                ],
                [
                    "transaction_id.required"   => "Le champ transaction id est obligatoire.",
                    "reference.required"    => "Le champ reference est obligatoire.",
                    "mode.required"         => "Le champ mode est obligatoire.",
                    "operation.required"     => "Le champ operation est obligatoire.",
                    "transaction_key.required"        => "Le champ transaction key est obligatoire.",
                    "montant.required"       => "Le champ montant est obligatoire.",
                    "montant.numeric"           => "Le champ montant doit etre un nombre.",
                    "montant.min"               => "Le champ montant ne peut pas avoir une valeur inférieure à :min .",
                    "phone_payment.required"            => "Le champ phone payment est obligatoire.",
                    "phone_payment.min"                 => "Le champ phone payment doit contenir au moins :min caractères.",
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Une erreur est survenue lors de la création du paiement', $validator->errors()->toArray());
            }

            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $candidatId = $user->id; // Récupérer l'ID de l'utilisateur connecté

            // Ajouter l'ID de l'utilisateur connecté dans la requête
            $request->merge(['candidat_id' => $candidatId]);
            // Récupérer la DossierSession en fonction des IDs fournis
            $dossierCandidatId = $request->input('dossier_candidat_id');
            $dossierSessionId = $request->input('dossier_session_id');

            $dossierSession = DossierSession::find($dossierSessionId);

            if (!$dossierSession) {
                return $this->errorResponse("Ce dossier session n'existe pas.", null, null, 422);
            }


            $transactionId = $request->input('transaction_id');
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
            $montantPayment = $dossierSession->montant_paiement;
            if ($transaction->amount != $montantPayment) {
                return $this->errorResponse('Le montant de paiement est incorrect');
            }

            // Créer une nouvelle DossierSession en dupliquant les informations, à l'exception de certains champs
            $newDossierSession = $this->createNewDossierSession($dossierSession, $request);
            // Fermer la DossierSession en mettant 'closed' à true
            $dossierSession->closed = true;
            $dossierSession->save();

            // Utiliser les informations de la nouvelle DossierSession pour créer un paiement
            $this->updateSuiviButton($dossierSession);
            $dossierId = $newDossierSession->id;
            $encryptedDossierId = Crypt::encrypt($dossierId);
            $url = route('generate-facture', ['encryptedDossierId' => $encryptedDossierId]);
            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $dossierSession->npi;
            $parcoursSuivi->slug = 'inscription';
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $candidatId;
            $parcoursSuivi->dossier_candidat_id = $dossierCandidatId;
            $parcoursSuivi->dossier_session_id = $newDossierSession->id;
            $parcoursSuivi->categorie_permis_id = $newDossierSession->categorie_permis_id;
            $parcoursSuivi->message = "Paiement de " . $newDossierSession->montant . "F CFA éffectué avec succès, soyez en attente de votre convocation";
            $parcoursSuivi->url = $url;
            $parcoursSuivi->date_action = now();
            $parcoursSuivi->save();

            $dossier = DossierCandidat::findOrFail($dossierCandidatId);
            $path = "categorie-permis";
            $response = Api::base('GET', $path);

            if ($response->successful()) {
                $permis = $response->json()['data'];

                $categoriePermisId = $dossier->categorie_permis_id;
                $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;
            }


            $description = $request->input('description') . " " . $parcoursSuivi->service . " categorie " . $nomPermis;
            $request->merge(['description' => $description]);
            $payment = $this->createCandidatPayment($newDossierSession, $request, $candidatId);
            $payment['url'] = $url;
            return $this->successResponse($payment, 'Le paiement a été créé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur est survenue sur le serveur");
        }
    }

    private function createNewDossierSession($oldDossierSession, $request)
    {
        // Dupliquer les informations du vieux DossierSession, à l'exception de certains champs
        $newDossierSessionData = $oldDossierSession->toArray();
        $newDossierSessionData['old_ds_justif_id'] = $oldDossierSession['id']; //ajout de l'ancien id
        $newDossierSessionData['date_inscription'] = $oldDossierSession['date_inscription'];
        $newDossierSessionData['examen_id'] = null; //ajout de l'ancien id
        $newDossierSessionData['state'] = 'validate';
        $newDossierSessionData['closed'] = false;
        $newDossierSessionData['examen_id'] = $request->input('session_id');
        unset($newDossierSessionData['id']); //Supprime l'ID pour qu'il soit crée dynamiquement
        unset($newDossierSessionData['created_at']); //Supprime le created_at pour qu'il soit crée dynamiquement
        unset($newDossierSessionData['updated_at']); //Supprime le updated_at pour qu'il soit crée dynamiquement

        // Créer le nouveau DossierSession
        $newDossierSession = DossierSession::create($newDossierSessionData);

        return $newDossierSession;
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-candidat/dossier-candidats/expire-paiement",
     *      operationId="createRejetExpirePayment",
     *      tags={"DossierCandidats"},
     *      summary="Enrégistrer un nouveau paiement du candidat apres une expiration de la date de session",
     *      description="Crée un nouveau paiement du candidat enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="agregateur",
     *                      description="Nom de l'agrégateur",
     *                      type="string",
     *                      example="Nom de l'agrégateur"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      description="Description du paiement",
     *                      type="string",
     *                      example="Description du paiement"
     *                  ),
     *                  @OA\Property(
     *                      property="transaction_id",
     *                      description="ID de transaction",
     *                      type="string",
     *                      example="123456789"
     *                  ),
     *                  @OA\Property(
     *                      property="reference",
     *                      description="Référence du paiement",
     *                      type="string",
     *                      example="REF789456"
     *                  ),
     *                  @OA\Property(
     *                      property="mode",
     *                      description="Mode de paiement",
     *                      type="string",
     *                      example="Mode de paiement"
     *                  ),
     *                  @OA\Property(
     *                      property="operation",
     *                      description="Opération de paiement",
     *                      type="string",
     *                      example="Opération de paiement"
     *                  ),
     *                  @OA\Property(
     *                      property="transaction_key",
     *                      description="Clé de transaction",
     *                      type="string",
     *                      example="TransKey123"
     *                  ),
     *                  @OA\Property(
     *                      property="montant",
     *                      description="Le montant payé",
     *                      type="string",
     *                      example="50.00"
     *                  ),
     *                  @OA\Property(
     *                      property="phone_payment",
     *                      description="Numéro de téléphone utilisé pour le paiement",
     *                      type="string",
     *                      example="+1234567890"
     *                  ),
     *                  @OA\Property(
     *                      property="ref_operateur",
     *                      description="La référence de l'opérateur",
     *                      type="string",
     *                      example="OP123456"
     *                  ),
     *                  @OA\Property(
     *                      property="numero_recu",
     *                      description="Le numéro du reçu",
     *                      type="string",
     *                      example="REC789456"
     *                  ),
     *                  @OA\Property(
     *                      property="moyen_payment",
     *                      description="Le moyen de paiement (momo ou portefeuille)",
     *                      type="string",
     *                      example="momo"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut du paiement (pending, approved, declined ou canceled)",
     *                      type="string",
     *                      example="approved"
     *                  ),
     *                  @OA\Property(
     *                      property="num_transaction",
     *                      description="Numéro de transaction délivré par l'agrégateur",
     *                      type="string",
     *                      example="123456789"
     *                  ),
     *                  @OA\Property(
     *                      property="date_payment",
     *                      description="Date de paiement",
     *                      type="string",
     *                      format="date",
     *                      example="2023-07-19"
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier du candidat",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_session_id",
     *                      description="ID du dossier session",
     *                      type="integer",
     *                      example=1
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau paiement du candidat créé",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du nouveau paiement du candidat créé",
     *                  type="integer",
     *              ),
     *      )
     * )
     * )
     */
    public function createRejetExpirePayment(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'agregateur' => 'required|string',
                    'description' => 'required|string',
                    'transaction_id' => 'required|integer',
                    'reference' => 'required|string',
                    'mode' => 'required|string',
                    'operation' => 'required|string',
                    'transaction_key' => 'required|string',
                    'montant' => 'required|numeric|min:0',
                    'phone_payment' => 'required|string|min:8|max:25',
                    'ref_operateur' => 'nullable|string',
                    'numero_recu' => 'nullable|string|max:100|unique:auto_ecole_payments,numero_recu,NULL,id,agregateur_id,' . $request->agregateur,
                    'moyen_payment' => 'required|in:momo,portefeuille',
                    'status' => 'required|in:pending,approved,declined,canceled',
                    'num_transaction' => 'nullable|string|max:100|unique:auto_ecole_payments,num_transaction,NULL,id,agregateur_id,' . $request->agregateur,
                    'date_payment' => 'nullable|date',
                    'dossier_candidat_id' => 'required|exists:dossier_candidats,id',
                    'dossier_session_id' => 'required|exists:dossier_sessions,id',
                    'session_id' => 'required|integer',
                ],
                [
                    "transaction_id.required"   => "Le champ transaction id est obligatoire.",
                    "reference.required"    => "Le champ reference est obligatoire.",
                    "mode.required"         => "Le champ mode est obligatoire.",
                    "operation.required"     => "Le champ operation est obligatoire.",
                    "transaction_key.required"        => "Le champ transaction key est obligatoire.",
                    "montant.required"       => "Le champ montant est obligatoire.",
                    "montant.numeric"           => "Le champ montant doit etre un nombre.",
                    "montant.min"               => "Le champ montant ne peut pas avoir une valeur inférieure à :min .",
                    "phone_payment.required"            => "Le champ phone payment est obligatoire.",
                    "phone_payment.min"                 => "Le champ phone payment doit contenir au moins :min caractères.",
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Une erreur est survenue lors de la création du paiement', $validator->errors()->toArray());
            }

            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $candidatId = $user->id; // Récupérer l'ID de l'utilisateur connecté

            // Ajouter l'ID de l'utilisateur connecté dans la requête
            $request->merge(['candidat_id' => $candidatId]);
            // Récupérer la DossierSession en fonction des IDs fournis
            $dossierCandidatId = $request->input('dossier_candidat_id');
            $dossierSessionId = $request->input('dossier_session_id');


            $lastDossierSession = DossierSession::find($dossierSessionId);
            $old_ds_rejet_id = $lastDossierSession->old_ds_rejet_id;

            $dossierSession = DossierSession::find($old_ds_rejet_id);

            $transactionId = $request->input('transaction_id');
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

            if (!$dossierSession) {
                return $this->errorResponse("Ce dossier session n'existe pas.", null, null, 422);
            }
            $montantPayment = $lastDossierSession->montant_paiement;
            if ($transaction->amount != $montantPayment) {
                return $this->errorResponse('Le montant de paiement est incorrect');
            }
            // Fermer le DossierSession en mettant 'closed' à true
            $dossierSession->closed = true;
            $dossierSession->save();

            $lastDossierSession->examen_id = $request->input('session_id');
            $lastDossierSession->state = 'payment';
            $lastDossierSession->save();
            // Utiliser les informations de la nouvelle DossierSession pour créer un paiement
            $this->updateSuiviExpireButton($lastDossierSession);
            $dossier = DossierCandidat::findOrFail($dossierCandidatId);

            $path = "categorie-permis";
            $response = Api::base('GET', $path);

            if ($response->successful()) {
                $permis = $response->json()['data'];

                $categoriePermisId = $dossier->categorie_permis_id;
                $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;
            }

            $dossierId = $lastDossierSession->id;
            $encryptedDossierId = Crypt::encrypt($dossierId);
            $url = route('generate-facture', ['encryptedDossierId' => $encryptedDossierId]);

            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $lastDossierSession->npi;
            $parcoursSuivi->slug = 'inscription';
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $candidatId;
            $parcoursSuivi->dossier_candidat_id = $dossierCandidatId;
            $parcoursSuivi->dossier_session_id = $lastDossierSession->id;
            $parcoursSuivi->categorie_permis_id = $lastDossierSession->categorie_permis_id;
            $parcoursSuivi->message = "Paiement de " . $lastDossierSession->montant_paiement . "F CFA éffectué avec succès pour l'inscription à la catégorie de permis de conduire " . $nomPermis;
            $parcoursSuivi->url = $url;
            $parcoursSuivi->date_action = now();
            $parcoursSuivi->save();

            $description = $request->input('description') . " " . $parcoursSuivi->service . " categorie " . $nomPermis;
            $request->merge(['description' => $description]);
            $payment = $this->createCandidatPayment($lastDossierSession, $request, $candidatId);

            $payment['url'] = $url;

            return $this->successResponse($payment, 'Le paiement a été créé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur est survenue sur le serveur");
        }
    }

    public function createRejetExpire(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'dossier_candidat_id' => 'required|exists:dossier_candidats,id',
                    'dossier_session_id' => 'required|exists:dossier_sessions,id',
                    'session_id' => 'required|integer',
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Une erreur est survenue lors de la création du paiement', $validator->errors()->toArray());
            }

            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $candidatId = $user->id;

            // Ajouter l'ID de l'utilisateur connecté dans la requête
            $request->merge(['candidat_id' => $candidatId]);
            // Récupérer le DossierSession en fonction des IDs fournis
            $dossierCandidatId = $request->input('dossier_candidat_id');
            $dossierSessionId = $request->input('dossier_session_id');

            $lastDossierSession = DossierSession::find($dossierSessionId);
            $old_ds_rejet_id = $lastDossierSession->old_ds_rejet_id;

            $dossierSession = DossierSession::find($old_ds_rejet_id);


            if (!$dossierSession) {
                return $this->errorResponse("Ce dossier session n'existe pas.", null, null, 422);
            }
            // Fermer le DossierSession en mettant 'closed' à true
            $dossierSession->closed = true;
            $dossierSession->save();

            $lastDossierSession->examen_id = $request->input('session_id');
            $lastDossierSession->state = 'payment';
            $lastDossierSession->save();
            // Utiliser les informations de la nouvelle DossierSession pour créer un paiement
            $this->updateSuiviExpireButton($lastDossierSession);
            $dossier = DossierCandidat::findOrFail($dossierCandidatId);

            $path = "categorie-permis";
            $response = Api::base('GET', $path);

            if ($response->successful()) {
                $permis = $response->json()['data'];

                $categoriePermisId = $dossier->categorie_permis_id;
                $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;
            }

            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $lastDossierSession->npi;
            $parcoursSuivi->slug = 'inscription';
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $candidatId;
            $parcoursSuivi->dossier_candidat_id = $dossierCandidatId;
            $parcoursSuivi->dossier_session_id = $lastDossierSession->id;
            $parcoursSuivi->categorie_permis_id = $lastDossierSession->categorie_permis_id;
            $parcoursSuivi->message = "Nouvelle session choisi avec succès";
            $parcoursSuivi->date_action = now();
            $parcoursSuivi->save();

            $dossierId = $lastDossierSession->id;
            $encryptedDossierId = Crypt::encrypt($dossierId);

            return $this->successResponse($lastDossierSession, 'Nouvelle session choisi avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur est survenue sur le serveur");
        }
    }

    private function createCandidatPayment($dossierSession, $request, $candidatId)
    {
        // Créer un paiement dans la table payments en utilisant les informations de la DossierSession et les champs fournis dans la requête
        $paymentData = [
            'auto_ecole_id' => $dossierSession->auto_ecole_id,
            'candidat_id' => $candidatId,
            'agregateur' => $request->input('agregateur'),
            'description' => $request->input('description'),
            'transaction_id' => $request->input('transaction_id'),
            'reference' => $request->input('reference'),
            'mode' => $request->input('mode'),
            'operation' => $request->input('operation'),
            'transaction_key' => $request->input('transaction_key'),
            'montant' => $request->input('montant'),
            'phone_payment' => $request->input('phone_payment'),
            'ref_operateur' => $request->input('ref_operateur'),
            'numero_recu' => $request->input('numero_recu'),
            'moyen_payment' => $request->input('moyen_payment'),
            'status' => $request->input('status'),
            'num_transaction' => $request->input('num_transaction'),
            'date_payment' => $request->input('date_payment'),
            'dossier_candidat_id' => $dossierSession->dossier_candidat_id,
            'dossier_session_id' => $dossierSession->id,
            'examen_id' => $request->input('session_id'),
        ];

        $payment = CandidatPayment::create($paymentData);

        return $payment;
    }
    private function updateSuiviButton($dossierSession)
    {
        $suivi = ParcoursSuivi::where('dossier_session_id', $dossierSession->id)
            ->where('slug', 'validation-justif-failed')
            ->orderByDesc('created_at')
            ->first();

        if ($suivi) {
            $suivi->bouton = '{"bouton":"Rejet","status":"-1"}';
            $suivi->save();
        }
    }
    private function updateSuiviExpireButton($lastDossierSession)
    {
        $suivi = ParcoursSuivi::where('dossier_session_id', $lastDossierSession->id)
            ->where('slug', 'correction-rejet')
            ->orderByDesc('created_at')
            ->first();

        if ($suivi) {
            $suivi->bouton = '{"bouton":"Reinscription","status":"-1"}';
            $suivi->save();
        }
    }


    public function updateState(Request $request)
    {
        try {

            // Vérifier si le dossier candidat existe avec l'ID donné

            $validator = Validator::make(
                $request->all(),
                [
                    'state' => "required|in:init,pending,validate,rejet,payment",
                    "id" => "required|exists:dossier_sessions,id"
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('La validation a échoué', $validator->errors(), statuscode: 422);
            }
            $id = $request->input('id');
            $session = DossierSession::findOrFail($id);

            // Récupérer le champ "state" du dossier
            $state = $request->input('state');


            // Mettre à jour le champ "state" du dossier avec la valeur de la variable $state
            $session->state = $state;

            $candidat = Api::data(Api::base('GET', "candidats/$session->npi"));

            $stateMessage = "";
            // Mettre à jour le champ "bouton_paiement" en fonction du state
            if ($state === 'pending') {
                $session->bouton_paiement = 1;
                $stateMessage = "Félicitations votre suivi a été effectué, vous pouvez passer au paiement";
            } elseif ($state === 'init') {
                $session->bouton_paiement = 0;
                $stateMessage = "Mise en attente";
            } elseif ($state === 'validate') {
                $session->bouton_paiement = -1;
                $state = 'validate';
            } else {
                // Si le state ne correspond à aucun des cas ci-dessus, vous pouvez gérer une erreur ou une valeur par défaut ici.
                // Par exemple, retourner une réponse d'erreur ou attribuer une valeur par défaut.
                // Dans cet exemple, je vais simplement attribuer la valeur 0.
                $session->bouton_paiement = 0;
                $session->state = $state; //rejet ou payment
            }


            $success =  $session->save();

            if ($success) {
                $messageBuilder = (new Messager())
                    ->subject('Statut de validation de suivi')
                    ->greeting("Bonjour {$candidat['prenoms']}")
                    ->headline("Statut de votre dossier")
                    ->introParagraph($stateMessage)
                    ->setAction('Me connecter', env('FRONTEND_URL') . '/connexion')
                    ->lastParagraph("En cas d'erreur vous pouvez rapprocher de votre auto-école")
                    ->goodbye('Merci et bonne chance !')
                    ->footer();

                (new EmailNotifier($messageBuilder, $candidat))->procced();
            }
            $session['candidat'] = $candidat;
            return $this->successResponse($session, 'Le statut du dossier candidat a été mis à jour avec succès.');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour du dossier candidat.', null, null, 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/anatt-candidat/updat-dossier-state",
     *     operationId="updateDossierStateCed",
     *     tags={"DossierCandidats"},
     *     summary="Mettre à jour le statut du dossier candidat par ced",
     *     description="Mettre à jour le statut du dossier candidat avec la nouvelle valeur de statut",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données du dossier candidat à mettre à jour",
     *         @OA\JsonContent(
     *             required={"id", "state"},
     *             @OA\Property(property="id", type="integer", example="1", description="ID du dossier candidat à mettre à jour"),
     *             @OA\Property(property="state", type="string", example="init", description="Nouveau statut du dossier candidat")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Le statut du dossier candidat a été mis à jour avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", description="ID du dossier candidat"),
     *             @OA\Property(property="candidat_id", type="integer", description="ID du candidat"),
     *             @OA\Property(property="npi", type="string", description="NPI du candidat"),
     *             @OA\Property(property="langue_id", type="integer", description="ID de la langue de composition"),
     *             @OA\Property(property="auto_ecole_id", type="integer", description="ID de l'auto-école"),
     *             @OA\Property(property="phone", type="string", description="Le téléphone du candidat"),
     *             @OA\Property(property="code_autoecole", type="string", description="Le code délivré par l'auto-école"),
     *             @OA\Property(property="fiche_medical", type="string", description="Le fichier de la fiche médicale"),
     *             @OA\Property(property="restriction_medical", type="string", description="Restriction médicale du candidat"),
     *             @OA\Property(property="groupage_test", type="string", description="Le fichier du test de groupage"),
     *             @OA\Property(property="candidat_type", type="string", description="Pour militaire ou civil"),
     *             @OA\Property(property="adresse", type="string", description="L'adresse du candidat"),
     *             @OA\Property(property="arrondissement_id", type="integer", description="ID de l'arrondissement"),
     *             @OA\Property(property="annexe_id", type="integer", description="ID de l'annexe"),
     *             @OA\Property(property="examen_id", type="integer", description="ID de l'examen"),
     *             @OA\Property(property="group_sanguin", type="string", description="Le groupe sanguin du candidat"),
     *             @OA\Property(property="medecin1_name", type="string", description="Le nom du medecin 1 du candidat"),
     *             @OA\Property(property="medecin2_name", type="string", description="Le nom du medecin 2 du candidat"),
     *             @OA\Property(property="hopital", type="string", description="Le nom de l'hôpital"),
     *             @OA\Property(property="medecin1_contact", type="string", description="Le contact du medecin 1 du candidat"),
     *             @OA\Property(property="medecin2_contact", type="string", description="Le contact du medecin 2 du candidat"),
     *             @OA\Property(property="type_piece_id", type="integer", description="ID de la pièce d'identité"),
     *             @OA\Property(property="num_piece_identite", type="string", description="Numéro de la pièce d'identité"),
     *             @OA\Property(property="fichier_piece_identite", type="string", description="Le fichier de la pièce d'identité"),
     *             @OA\Property(property="fichier_acte_nais", type="string", description="Le fichier de l'acte de naissance"),
     *             @OA\Property(property="fichier_visite_med", type="string", description="Le fichier de la visite médicale"),
     *             @OA\Property(property="photo", type="string", description="La photo du candidat"),
     *             @OA\Property(property="is_paid", type="boolean", description="Si le candidat a payé (optionnel)"),
     *             @OA\Property(property="is_valid", type="boolean", description="Si le dossier est validé (optionnel)"),
     *             @OA\Property(property="is_close", type="boolean", description="Si le dossier est bouclé (optionnel)"),
     *             @OA\Property(property="date_soumission", type="string", description="La date de soumission du dossier"),
     *             @OA\Property(property="categorie_permis_id", type="integer", description="ID de la catégorie du permis"),
     *             @OA\Property(property="date_payment", type="string", description="La date de paiement"),
     *             @OA\Property(property="date_validation", type="string", description="La date de validation du dossier"),
     *             @OA\Property(property="resultat_conduite", type="string", description="Résultat de la conduite", enum={"success", "failed"}),
     *             @OA\Property(property="is_deleted", type="boolean", description="Si le dossier est supprimé (optionnel)"),
     *             @OA\Property(property="state", type="boolean", description="Le statut du dossier, validation Anatt (optionnel)")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Dossier candidat non trouvé avec l'ID spécifié",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Une erreur est survenue lors de la mise à jour du dossier candidat",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Message d'erreur")
     *         )
     *     )
     * )
     */
    public function updateStateCed(Request $request)
    {
        $v = Validator::make($request->all(), [
            'dossier_session_id' => 'required|exists:dossier_sessions,id',
            'dossier_candidat_id' => 'required|exists:dossier_candidats,id',
            'state' => 'required|in:validate,rejet'
        ]);
        if ($v->fails()) {
            return  $this->errorResponse("La validation a échoué", $v->errors());
        }
        try {
            $dossier_session_id = $request->input('dossier_session_id');
            $dossier_candidat_id = $request->input('dossier_candidat_id');
            $agent_id = $request->input('agent_id');
            // Vérifier si le dossier candidat existe avec l'ID donné
            $dossier = DossierCandidat::findOrFail($dossier_candidat_id);
            $dossiersession = DossierSession::findOrFail($dossier_session_id);

            $npi = $dossier->npi;
            $candidat_id = $dossier->candidat_id;
            $categorie_permis_id = $dossier->categorie_permis_id;
            $date_soumission = now();
            // Mettre à jour le champ "state" du dossier avec la valeur de la variable $state
            $state = $request->input('state');

            // Vérifier si le dossier a été rejeté (state = 'rejet')
            if ($state === 'rejet') {
                $motif_rejet = $request->input('motif');
                $consignes = $request->input('consignes');
                $dossiersession->state = $state;
                $dossiersession->save();

                $parcoursSuivi = new ParcoursSuivi();
                $parcoursSuivi->npi = $npi;
                $parcoursSuivi->slug = "validation-anatt-failed";
                $parcoursSuivi->service = 'Permis';
                $parcoursSuivi->candidat_id = $candidat_id;
                $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
                $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
                $parcoursSuivi->message = "Votre dossier a été rejeté. Motif : " . $motif_rejet . " Consignes à suivre : " . $consignes;
                $parcoursSuivi->agent_id = $agent_id;
                $parcoursSuivi->bouton = json_encode(['bouton' => 'Rejet', 'status' => '1']);
                $parcoursSuivi->dossier_session_id = $dossier_session_id;
                $parcoursSuivi->date_action = $date_soumission;
                $parcoursSuivi->save();
            } else {
                // Bloc pour la validation réussie (convocation)
                $dossiersession->state = $state;
                $dossiersession->save();

                $parcoursSuivi = new ParcoursSuivi();
                $parcoursSuivi->npi = $npi;
                $parcoursSuivi->slug = "validation-anatt-success";
                $parcoursSuivi->service = 'Permis';
                $parcoursSuivi->candidat_id = $candidat_id;
                $parcoursSuivi->dossier_candidat_id = $dossier_candidat_id;
                $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
                $parcoursSuivi->message = "Votre dossier a été validé par l'ANaTT, votre convocation pour la composition de l'épreuve du code vous sera envoyée dans les prochains jours";
                $parcoursSuivi->agent_id = $agent_id;
                $parcoursSuivi->bouton = json_encode(['bouton' => 'Convocation', 'status' => '0']);
                $parcoursSuivi->dossier_session_id = $dossier_session_id;
                $parcoursSuivi->date_action = $date_soumission;
                $parcoursSuivi->save();
            }

            return $this->successResponse($dossier, 'Le statut du dossier candidat a été mis à jour avec succès.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            logger()->error($e);
            return $this->errorResponse('Dossier candidat non trouvé.', null, null, 422);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour du dossier candidat.', null, null, 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/anatt-candidat/dossier-candidat/{dossier_id}/full",
     *     summary="Obtenir les informations complètes d'un dossier candidat",
     *     description="Récupère les informations complètes d'un dossier candidat en incluant les informations de l'auto-école et de la catégorie de permis",
     *     operationId="fullDossier",
     *     tags={"DossierCandidats"},
     *     @OA\Parameter(
     *         name="dossier_id",
     *         in="path",
     *         description="ID du dossier candidat",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informations complètes du dossier candidat",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 description="ID du dossier candidat",
     *                 type="integer"
     *             ),
     *             @OA\Property(
     *                 property="candidat_id",
     *                 description="ID du candidat",
     *                 type="integer"
     *             ),
     *             @OA\Property(
     *                 property="auto_ecole_id",
     *                 description="ID de l'auto-école",
     *                 type="integer"
     *             ),
     *             @OA\Property(
     *                 property="num_permis",
     *                 description="Numéro de permis du candidat",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="npi",
     *                 description="Numéro de permis international du candidat",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="montant_paiement",
     *                 description="Montant du paiement du candidat",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="code_autoecole",
     *                 description="Code de l'auto-école",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="restriction_medical",
     *                 description="Indicateur de restriction médicale",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="fiche_medical",
     *                 description="Nom du fichier de la fiche médicale",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="groupage_test",
     *                 description="Nom du fichier du groupage sanguin",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="is_militaire",
     *                 description="Statut militaire du candidat",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="annexe_id",
     *                 description="ID de l'annexe",
     *                 type="integer",
     *             ),
     *             @OA\Property(
     *                 property="group_sanguin",
     *                 description="Groupe sanguin du candidat",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="date_soumission",
     *                 description="Date de soumission du dossier",
     *                 type="string",
     *                 format="date",
     *             ),
     *             @OA\Property(
     *                 property="categorie_permis_id",
     *                 description="ID de la catégorie de permis",
     *                 type="integer",
     *             ),
     *             @OA\Property(
     *                 property="date_payment",
     *                 description="Date de paiement du candidat",
     *                 type="string",
     *                 format="date",
     *             ),
     *             @OA\Property(
     *                 property="date_validation",
     *                 description="Date de validation du dossier",
     *                 type="string",
     *                 format="date",
     *             ),
     *             @OA\Property(
     *                 property="resultat_conduite",
     *                 description="Résultat de l'examen de conduite du candidat",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="bouton_paiement",
     *                 description="Statut du bouton de paiement",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="state",
     *                 description="Statut du dossier",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="parcours_state",
     *                 description="Statut du parcours",
     *                 type="string",
     *             ),
     *             @OA\Property(
     *                 property="is_deleted",
     *                 description="Indicateur de suppression du dossier",
     *                 type="boolean",
     *             ),
     *             @OA\Property(
     *                 property="auto_ecole",
     *                 description="Informations de l'auto-école",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     description="ID de l'auto-école",
     *                     type="integer"
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="categorie_permis",
     *                 description="Informations de la catégorie de permis",
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     description="ID de la catégorie de permis",
     *                     type="integer"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucun résultat trouvé",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *     )
     * )
     */
    public function fullDossier($dossier_id)
    {
        try {
            // Utiliser la méthode "find" au lieu de "findOrFail"
            $dossier = DossierCandidat::find($dossier_id);

            if (!$dossier) {
                return $this->errorResponse('Aucun résultat trouvé', statuscode: 404);
            }
            $resultat = (new FullDossierDetails($dossier))->get();
            return $this->successResponse($resultat);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération du dossier du candidat.', statuscode: 500);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/anatt-candidat/dossier-session/{dossier_session_id}",
     *      operationId="updateDossierSession",
     *      tags={"DossierCandidats"},
     *      summary="Mise à jour d'une DossierSession par son ID",
     *      description="Met à jour les informations d'une DossierSession enregistrée dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="dossier_session_id",
     *          description="ID de la DossierSession",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="montant_paiement", type="number", example="120", description="Le nouveau montant du paiement"),
     *              @OA\Property(property="is_militaire", type="string", enum={"civil", "militaire"}, description="Le nouveau statut militaire"),
     *              @OA\Property(property="restriction_medical", type="string", nullable=true, description="La nouvelle restriction médicale"),
     *              @OA\Property(property="state", type="string", nullable=true, description="Le nouveau statut"),
     *              @OA\Property(property="presence", type="string", nullable=true, description="La nouvelle présence"),
     *              @OA\Property(property="presence_conduite", type="string", nullable=true, description="La nouvelle présence"),
     *              @OA\Property(property="examen_id", type="integer", nullable=true, description="Le nouvel ID de l'examen"),
     *              @OA\Property(property="resultat_conduite", type="string", nullable=true, description="Le resultat de la conduite"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="DossierSession mise à jour avec succès",
     *          @OA\JsonContent(
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Dossier Session non trouvé"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Échec de la validation"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Une erreur est survenue lors de la mise à jour"
     *      )
     * )
     */
    public function updateDossierSession(Request $request, $id)
    {
        try {
            $dossierSession = DossierSession::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'montant_paiement' => 'numeric',
                'is_militaire' => 'in:civil,militaire',
                'restriction_medical' => 'nullable|string',
                'state' => 'nullable|string',
                'presence' => 'nullable|string',
                'presence_conduite' => 'nullable|string',
                'examen_id' => 'nullable',
                'resultat_conduite' => 'nullable|in:success,failed',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 'Validation échouée', null, 422);
            }

            // Mettez à jour les champs du dossier session avec les nouvelles valeurs du formulaire
            $dossierSession->montant_paiement = $request->input('montant_paiement', $dossierSession->montant_paiement);
            $dossierSession->is_militaire = $request->input('is_militaire', $dossierSession->is_militaire);
            $dossierSession->restriction_medical = $request->input('restriction_medical', $dossierSession->restriction_medical);
            $dossierSession->state = $request->input('state', $dossierSession->state);
            $dossierSession->presence = $request->input('presence', $dossierSession->presence);
            $dossierSession->presence_conduite = $request->input('presence_conduite', $dossierSession->presence_conduite);
            $dossierSession->examen_id = $request->input('examen_id', $dossierSession->examen_id);
            $dossierSession->resultat_conduite = $request->input('resultat_conduite', $dossierSession->resultat_conduite);

            $dossierSession->save();

            return $this->successResponse($dossierSession, 'Dossier session mis à jour avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la mise à jour du dossier session');
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/dossier-session/{dossier_session_id}",
     *      operationId="getDossierSessionInformation",
     *      tags={"DossierCandidats"},
     *      summary="Récupère les informations d'une DossierSession par son ID",
     *      description="Récupère les informations d'une DossierSession enregistrée dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="dossier_session_id",
     *          description="ID de la DossierSession",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Informations de la DossierSession récupérées avec succès",
     *          @OA\JsonContent(
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Dossier Session non trouvé"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Une erreur est survenue lors de la récupération des informations"
     *      )
     * )
     */
    public function getDossierSessionInformation($dossier_session_id)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }
            $Usernpi = $user->npi;
            // Chercher le DossierSession avec l'ID donné
            $dossierSession = DossierSession::find($dossier_session_id);
            if (!$dossierSession) {
                return $this->errorResponse('Dossier Session non trouvé.', null, null, 422);
            }
            $dsNpi = $dossierSession->npi;
            if ($user->npi != $dossierSession->npi) {
                return $this->errorResponse('Vous n\'êtes pas autorisé a accéder à cette ressource', null, null, 422);
            }
            // Récupérer dossier_candidat_id depuis la DossierSession
            $dossier_candidat_id = $dossierSession->dossier_candidat_id;

            // Chercher le DossierCandidat associé
            $dossierCandidat = DossierCandidat::find($dossier_candidat_id);

            if (!$dossierCandidat) {
                return $this->errorResponse('Dossier Candidat non trouvé.', null, null, 422);
            }

            // Récupérer le dernier ancien permis du candidat
            $lastAncienPermis = $dossierCandidat->lastAncienPermis;

            // Préparer les données pour la vue
            $data = [
                'dossier_session' => $dossierSession,
                'dossier_candidat' => $dossierCandidat,
            ];

            return $this->successResponse($data);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des informations.', null, null, 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/anatt-candidat/check-permis-prealable",
     *     summary="Vérifie le permis préalable d'un candidat",
     *     tags={"DossierCandidats"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="candidatId", type="integer", description="ID du candidat"),
     *             @OA\Property(property="permisPrealableId", type="integer", description="ID du permis préalable"),
     *             @OA\Property(property="permisPrealableDure", type="integer", description="Durée requise pour le permis préalable en jours")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Le temps requis pour continuer est validé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Le temps requis pour continuer est validé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Vous n'avez pas validé le temps requis pour continuer",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Vous n'avez pas validé le temps requis pour continuer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Une erreur s'est produite lors de la vérification du permis préalable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Une erreur s'est produite lors de la vérification du permis préalable")
     *         )
     *     )
     * )
     */
    public function checkPermisPrealable(Request $request)
    {
        try {
            $candidatId = $request->candidatId;
            $permisPrealableId = $request->permisPrealableId;
            $permisPrealableDure = $request->permisPrealableDure;

            // Appel de l'API pour vérifier le permis préalable
            $response = Api::base('GET', "candidat-permis/$candidatId/$permisPrealableId");

            // Vérifier si la requête a réussi
            if ($response !== -1 && $response->successful()) {
                $result = $response->json();
                $dateDelivrance = $result['data'][0]['date_delivrance'];

                // Calcul du nombre total de mois écoulés depuis la date de délivrance du permis
                $now = now();
                $diffTime = $now->diffInMonths($dateDelivrance);

                if ($diffTime >= $permisPrealableDure) {
                    return $this->successResponse(null, 'Le temps requis pour continuer est validé', 200);
                } else {
                    $remainingMonths = $permisPrealableDure - $diffTime;
                    return $this->successResponse(null, 'Vous n\'avez pas validé le temps requis pour continuer. Il vous reste ' . $remainingMonths . ' mois.', 422);
                }
            } else {
                return $this->successResponse(null, 'Aucun résultat trouvé', 404);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la vérification du permis préalable', null, null, 500);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/dossier-candidats/{id}",
     *      operationId="getDossierCandidatsById",
     *      tags={"DossierCandidats"},
     *      summary="Récupère un dossier-candidats par ID",
     *      description="Récupère un dossier-candidat enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du dossier-candidat à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="dossier-candidat récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="dossier-candidat non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            $dossier = DossierCandidat::findOrFail($id);
            //On prend la dernière session
            return $this->successResponse($dossier);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/dossier-candidats-souscriptions/{id}",
     *      operationId="getDossierCandidatsSouscriptionsById",
     *      tags={"DossierCandidats"},
     *      summary="Récupère les dossiers d'un candidat par son ID",
     *      description="Récupère les dossiers d'un candidat enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du candidat",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="les dossiers d'un candidat ont été récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="les dossiers d'un candidat n'ont pas été trouvé"
     *      )
     * )
     */
    public function getDossiersByCandidatId($id)
    {
        try {
            // Obtenir la liste des dossiers du candidat
            $dossiers = DossierCandidat::where('candidat_id', $id)
                ->orderByDesc('id')
                ->get();

            if ($dossiers->isEmpty()) {
                return $this->successResponse(null, 'Aucun dossier trouvé pour ce candidat', 200);
            }

            // Récupérer la liste des permis depuis l'endpoint
            $response = Api::base('GET', "categorie-permis");

            if ($response !== -1 && $response->successful()) {
                $permis = $response->json()['data'];

                $result = [];

                foreach ($dossiers as $dossier) {
                    $categoriePermisId = $dossier->categorie_permis_id;

                    $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;

                    $result[] = [
                        'dossier' => $dossier,
                        'nom_permis' => $nomPermis,
                    ];
                }

                return $this->successResponse($result);
            } else {
                return $this->errorResponse('Impossible de récupérer la liste des permis', null, null, 422);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des informations.', null, null, 500);
        }
    }



    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/dossier-candidats-byautoecole/{id}",
     *      operationId="getDossierCandidatsAutoecoleById",
     *      tags={"DossierCandidats"},
     *      summary="Récupère les dossiers d'une auto école par son ID",
     *      description="Récupère les dossiers d'une auto école enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'auto école",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="les dossiers ont été récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="les dossiers n'ont pas été trouvé"
     *      )
     * )
     */
    public function getInitDossiersByAutoEcoleId($id)
    {
        try {

            // Obtenir la liste des dossiers du candidat
            $dossier_sessions = DossierSession::where(
                'auto_ecole_id',
                $id
            )->where('state', 'init')
                ->orderByDesc('created_at')
                ->paginate(10);

            //On ajoute le dossier et le condidat
            $dossier_sessions->map(function ($d) {
                $d->dossier = DossierCandidat::find($d->dossier_candidat_id);
                $candidat = Api::base('GET', "candidats/{$d->npi}");
                $d->setAttribute('candidat', Api::data($candidat));
                return $d;
            });
            // Récupérer la liste des permis depuis l'endpoint
            $permis = Api::data(Api::base('GET', "categorie-permis"));
            $result =   [];
            foreach ($dossier_sessions as $ds) {
                $dossier = $ds->dossier;
                $categoriePermisId = $dossier->categorie_permis_id;

                $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;

                $ds['nom_permis'] = $nomPermis;
                $result[] = $ds;
            }

            return $this->successResponse($result);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Impossible de récupérer la liste des permis");
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/init-dossier-candidats-byautoecole/{id}",
     *      operationId="getInitDossierCandidatsAutoecoleById",
     *      tags={"DossierCandidats"},
     *      summary="Récupère les nouveaux dossiers d'une auto école par son ID",
     *      description="Récupère les nouveaux dossiers d'une auto école enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'auto école",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="les dossiers ont été récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="les dossiers n'ont pas été trouvé"
     *      )
     * )
     */
    public function getDossiersByAutoEcoleId($id)
    {
        try {
            // Obtenir la liste des dossiers du candidat
            $dossiers = DossierCandidat::where('auto_ecole_id', $id)
                ->orderByDesc('id')
                ->get();

            if ($dossiers->isEmpty()) {
                return $this->successResponse([], 'Aucun dossier trouvé pour cette auto école', 200);
            }

            // Récupérer la liste des permis depuis l'endpoint
            $response = Api::base('GET', "categorie-permis");

            if ($response !== -1 && $response->successful()) {
                $permis = $response->json()['data'];

                $result = [];

                foreach ($dossiers as $dossier) {
                    $categoriePermisId = $dossier->categorie_permis_id;

                    $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;

                    $result[] = [
                        'dossier' => $dossier,
                        'nom_permis' => $nomPermis,
                    ];
                }

                return $this->successResponse($result, 'succes', 200);
            } else {
                return $this->errorResponse('Impossible de récupérer la liste des permis');
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des informations.', null, null, 500);
        }
    }



    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/pending-dossier-candidats-byautoecole/{id}",
     *      operationId="getPendingDossierCandidatsAutoecoleById",
     *      tags={"DossierCandidats"},
     *      summary="Récupère les dossiers en attente d'une auto école par son ID",
     *      description="Récupère les dossiers en attente d'une auto école enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'auto école",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="les dossiers ont été récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="les dossiers n'ont pas été trouvé"
     *      )
     * )
     */
    public function getPendingDossiersByAutoEcoleId($id)
    {
        try {
            // Obtenir la liste des dossiers du candidat en attente
            $dossiers = DossierCandidat::where('auto_ecole_id', $id)
                ->where('state', 'pending')
                ->orderByDesc('id')
                ->get();

            if ($dossiers->isEmpty()) {
                return $this->successResponse([], 'Aucun dossier en attente trouvé pour cette auto école', 200);
            }

            // Récupérer la liste des permis depuis l'endpoint
            $response = Api::base('GET', "categorie-permis");

            if ($response !== -1 && $response->successful()) {
                $permis = $response->json()['data'];

                $result = [];

                foreach ($dossiers as $dossier) {
                    $categoriePermisId = $dossier->categorie_permis_id;

                    $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;

                    $result[] = [
                        'dossier' => $dossier,
                        'nom_permis' => $nomPermis,
                    ];
                }

                return $this->successResponse($result, 'succes', 200);
            } else {
                return $this->errorResponse('Impossible de récupérer la liste des permis');
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des informations.', null, null, 500);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/validate-dossier-candidats-byautoecole/{id}",
     *      operationId="getValidateDossierCandidatsAutoecoleById",
     *      tags={"DossierCandidats"},
     *      summary="Récupère les dossiers validés d'une auto école par son ID",
     *      description="Récupère les dossiers validés d'une auto école enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'auto école",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="les dossiers ont été récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="les dossiers n'ont pas été trouvé"
     *      )
     * )
     */
    public function getValidateDossiersByAutoEcoleId($id)
    {
        try {
            // Obtenir la liste des dossiers du candidat
            $dossiers = DossierCandidat::where('auto_ecole_id', $id)
                ->where('state', 'validate')
                ->orderByDesc('id')
                ->get();

            if ($dossiers->isEmpty()) {
                return $this->successResponse([], 'Aucun dossier trouvé pour cet auto école', 200);
            }


            // Récupérer la liste des permis depuis l'endpoint
            $response = Api::base('GET', "categorie-permis");

            if ($response !== -1 && $response->successful()) {
                $permis = $response->json()['data'];

                $result = [];

                foreach ($dossiers as $dossier) {
                    $categoriePermisId = $dossier->categorie_permis_id;

                    $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;

                    $result[] = [
                        'dossier' => $dossier,
                        'nom_permis' => $nomPermis,
                    ];
                }

                return $this->successResponse($result, 'succes', 200);
            } else {
                return $this->errorResponse('Impossible de récupérer la liste des permis', null, null, 422);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/rejet-dossier-candidats-byautoecole/{id}",
     *      operationId="getRejetDossierCandidatsAutoecoleById",
     *      tags={"DossierCandidats"},
     *      summary="Récupère les dossiers rejetés d'une auto école par son ID",
     *      description="Récupère les dossiers rejetés d'une auto école enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'auto école",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="les dossiers ont été récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="les dossiers n'ont pas été trouvé"
     *      )
     * )
     */
    public function getRejetDossiersByAutoEcoleId($id)
    {
        try {
            // Obtenir la liste des dossiers du candidat
            $dossiers = DossierCandidat::where('auto_ecole_id', $id)
                ->where('state', 'rejet')
                ->orderByDesc('id')
                ->get();

            if ($dossiers->isEmpty()) {
                return $this->successResponse([], 'Aucun dossier trouvé pour cet auto école', 200);
            }


            // Récupérer la liste des permis depuis l'endpoint
            $response = Api::base('GET', "categorie-permis");

            if ($response !== -1 && $response->successful()) {
                $permis = $response->json()['data'];

                $result = [];

                foreach ($dossiers as $dossier) {
                    $categoriePermisId = $dossier->categorie_permis_id;

                    $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;

                    $result[] = [
                        'dossier' => $dossier,
                        'nom_permis' => $nomPermis,
                    ];
                }

                return $this->successResponse($result, 'succes', 200);
            } else {
                return $this->errorResponse('Impossible de récupérer la liste des permis', null, null, 422);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/dossier-candidats-souscription",
     *      operationId="getOneDossierCandidatSouscriptionById",
     *      tags={"DossierCandidats"},
     *      summary="Récupère le dernier dossier d'un candidat par son ID",
     *      description="Récupère le dossier d'un candidat enregistré dans la base de données en spécifiant son ID",
     *      @OA\Response(
     *          response=200,
     *          description="le dossier du candidat a été récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="le dossier du candidat n'a pas été trouvé"
     *      )
     * )
     */
    public function getOneDossiersByCandidatId()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $id = $user->id; // Récupérer l'ID de l'utilisateur connecté

            // Obtenir le dernier dossier du candidat
            $dossier = DossierCandidat::where('candidat_id', $id)
                ->latest('id')
                ->first();
            $dossierSession = $dossier->dossierSessions->first();


            if (!$dossier) {
                return $this->successResponse([], 'Aucun dossier trouvé pour ce candidat', 200);
            }

            // Récupérer la liste des permis depuis l'endpoint
            $response = Api::base('GET', "categorie-permis");

            if ($response !== -1 && $response->successful()) {
                $permis = $response->json()['data'];

                $categoriePermisId = $dossier->categorie_permis_id;
                $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;

                $result = [
                    'dossier' => $dossier,
                    'nom_permis' => $nomPermis,
                    'dossier_session' => $dossierSession,
                ];

                return $this->successResponse($result, 'succes', 200);
            } else {
                return $this->errorResponse('Impossible de récupérer la liste des permis', null, null, 422);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/anatt-candidat/candidat-session",
     *     summary="Mettre à jour la session de dossier choisie par le candidat",
     *     tags={"DossierCandidats"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="dossier_candidat_id", type="integer", description="ID du dossier du candidat"),
     *             @OA\Property(property="dossier_session_id", type="integer", description="ID de la session de dossier"),
     *             @OA\Property(property="examen_id", type="integer", description="ID de l'examen choisi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Session de dossier mise à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Félicitations, votre choix de session a été enregistré avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreurs de validation ou de correspondance de sessions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Les sessions ne correspondent pas."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Une erreur est survenue lors de la mise à jour de la session de dossier",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue lors de la mise à jour de la session de dossier.")
     *         )
     *     )
     * )
     */

    public function updateSession(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'dossier_candidat_id' => 'required|exists:dossier_candidats,id',
                'dossier_session_id' => 'required|exists:dossier_sessions,id',
                'examen_id' => 'required|integer'
            ], [
                'dossier_candidat_id.required' => 'Le dossier du candidat est obligatoire.',
                'dossier_candidat_id.exists' => 'Le dossier du candidat sélectionné n\'existe pas.'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }

            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $candidatId = $user->id;
            $npi = $user->npi;

            $lastDossierSession = DossierCandidat::findOrFail($request->input('dossier_candidat_id'))->lastDossierSession;

            if (!$lastDossierSession) {
                return $this->errorResponse('Aucune dernière session trouvée.', null, null, 422);
            }
            $annexeId = $lastDossierSession->annexe_id;
            $examenId = $request->input('examen_id');
            if (!Examen::notUsed($annexeId)->where('id', $examenId)->exists()) {
                return $this->errorResponse("Impossible de continuer");
            }
            $categorie_permis_id = $lastDossierSession->categorie_permis_id;
            if ($lastDossierSession->old_ds_justif_id !== $request->input('dossier_session_id')) {
                return $this->errorResponse('Les sessions ne correspondent pas.', null, null, 422);
            }
            $lastDossierSession->examen_id = $request->input('examen_id');
            $lastDossierSession->closed = false;
            $lastDossierSession->save();

            // Mettre à jour le champ "bouton" dans ParcoursSuivi associé au dossier_candidat_id
            $latestParcoursSuivi = ParcoursSuivi::where('npi', $npi)
                ->orderByDesc('created_at')
                ->first();

            if ($latestParcoursSuivi) {
                $latestParcoursSuivi->bouton = json_encode(['bouton' => 'Session', 'status' => '-1']);
                $latestParcoursSuivi->save();
            }

            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $npi;
            $parcoursSuivi->slug = "choix-session";
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $candidatId;
            $parcoursSuivi->dossier_candidat_id = $request->input('dossier_candidat_id');
            $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
            $parcoursSuivi->message = "Félicitations, votre choix de session a été enregistré avec succès.";
            $parcoursSuivi->dossier_session_id = $request->input('dossier_session_id');
            $parcoursSuivi->date_action = now();
            $parcoursSuivi->save();


            return $this->successResponse($lastDossierSession, 'Session de dossier mise à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour de la session de dossier.', null, null, 500);
        }
    }

    public function updatePassedSession(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'dossier_candidat_id' => 'required|exists:dossier_candidats,id',
                'dossier_session_id' => 'required|exists:dossier_sessions,id',
                'examen_id' => 'required|integer'
            ], [
                'dossier_candidat_id.required' => 'Le dossier du candidat est obligatoire.',
                'dossier_candidat_id.exists' => 'Le dossier du candidat sélectionné n\'existe pas.'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }

            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $candidatId = $user->id;
            $npi = $user->npi;

            $lastDossierSession = DossierSession::find($request->input('dossier_session_id'));

            if (!$lastDossierSession) {
                return $this->errorResponse('Aucune dernière session trouvée.', null, null, 422);
            }
            $annexeId = $lastDossierSession->annexe_id;
            $examenId = $request->input('examen_id');
            if (!Examen::notUsed($annexeId)->where('id', $examenId)->exists()) {
                return $this->errorResponse("Impossible de continuer");
            }

            if($annexeId != '8'){
                // Vérifier si le nombre d'inscriptions non closes pour cette session d'examen dépasse 20
                $nombreInscriptions = DossierSession::where('examen_id', $examenId)
                                                    ->where('closed', false)
                                                    ->count();
                if ($nombreInscriptions == 30) {
                    return $this->errorResponse('Cette session est complète. Veuillez sélectionner une autre session d\'examen.', null, null, 422);
                }
            }

            $categorie_permis_id = $lastDossierSession->categorie_permis_id;
            $examen_id = $lastDossierSession->examen_id;
            if ($lastDossierSession->examen_id === $request->input('examen_id')) {
                return $this->errorResponse('Vous ne pouvez pas sélectionner la même session.', null, null, 422);
            }
            $lastDossierSession->examen_id = $request->input('examen_id');
            // $lastDossierSession->closed = false;
            $lastDossierSession->state = 'payment';
            $lastDossierSession->save();

            // Mettre à jour le champ "bouton" dans ParcoursSuivi associé au dossier_candidat_id
            $latestParcoursSuivi = ParcoursSuivi::where('npi', $npi)
                ->orderByDesc('created_at')
                ->first();

            if ($latestParcoursSuivi) {
                $latestParcoursSuivi->bouton = json_encode(['bouton' => 'Session', 'status' => '-1']);
                $latestParcoursSuivi->save();
            }

            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $npi;
            $parcoursSuivi->slug = "choix-session";
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $candidatId;
            $parcoursSuivi->dossier_candidat_id = $request->input('dossier_candidat_id');
            $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
            $parcoursSuivi->message = "Félicitations, votre choix de session a été enregistré avec succès.";
            $parcoursSuivi->dossier_session_id = $request->input('dossier_session_id');
            $parcoursSuivi->date_action = now();
            $parcoursSuivi->save();

            return $this->successResponse($lastDossierSession, 'Session de dossier mise à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour de la session de dossier.', null, null, 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/dossier-candidats-parcours",
     *      operationId="getDossierCandidatParcours",
     *      tags={"DossierCandidats"},
     *      summary="Récupère le dossier d'un candidat et son parcours pour la personne connectée",
     *      description="Récupère le dossier d'un candidat enregistré dans la base de données pour la personne connectée",
     *      @OA\Response(
     *          response=200,
     *          description="Le dossier du candidat a été récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Le dossier du candidat n'a pas été trouvé"
     *      )
     * )
     */
    public function getDossiersWithRelationsByCandidatId()
    {
        try {
            // Obtenir l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $id = $user->id;

            // Obtenir tous les dossiers du candidat avec les informations de la relation "parcourssuivi"
            $dossiers = DossierCandidat::with(['parcourssuivi' => function ($query) {
                $query->orderByDesc('id');
            }])
                ->where('candidat_id', $id)
                ->orderByDesc('created_at')
                ->get();

            if ($dossiers->isEmpty()) {
                return $this->successResponse([], 'Aucun dossier trouvé pour ce candidat', 200);
            }

            // Ajouter les données de PermisNumPayment associées au candidat
            $permisnums = PermisNumPayment::where('candidat_id', $id)
                ->orderByDesc('created_at')
                ->get();

            // Récupérer la liste des permis depuis l'endpoint
            $response = Api::base('GET', "categorie-permis");

            if ($response !== -1 && $response->successful()) {
                $permis = $response->json()['data'];

                // Créer un tableau final pour stocker les données
                $finalData = [];

                // Pour chaque dossier, récupérer le nom du permis depuis la liste des permis
                foreach ($dossiers as $dossier) {
                    $ds  = DossierSession::where('dossier_candidat_id', $dossier->id)->latest()->first();
                    $categoriePermisId = $dossier->categorie_permis_id;
                    $nomPermis = collect($permis)->firstWhere('id', $categoriePermisId)['name'] ?? null;

                    // Trouver les demandes de permis numérique associées à ce dossier
                    $categorie_permis_id = $dossier->categorie_permis_id;
                    // Récupérer la dernière insertion de ParcoursSuivi
                    $dossier['bouton_paiement'] = $ds !== null ? $ds->bouton_paiement : null;
                    $dossier['montant_paiement'] = $ds !== null ? $ds->montant_paiement : null;
                    $dossier['slug'] = 'dossier';
                    $dossier['nom_permis'] = $nomPermis;

                    $finalData[] =  $dossier;
                    // Vérifier si un PermisNumPayment a été trouvé pour ce dossier
                    foreach ($permisnums as $key => $permisnum) {
                        $nomPermisNum = collect($permis)->firstWhere('id', $categorie_permis_id)['name'] ?? null;
                        $permisnum['nom_permis'] = $nomPermisNum;

                        // Récupérer les parcours associés à ce PermisNumPayment
                        $parcoursPermisNum = ParcoursSuivi::where('slug', 'permis-numerique')
                            ->where('categorie_permis_id', $categorie_permis_id)
                            ->where('permis_num_payment_id', $permisnum->id)
                            ->orderByDesc('created_at')
                            ->get();

                        $permisnum['parcourssuivi'] = $parcoursPermisNum;
                        $permisnum['slug'] = 'permisnum';
                        $finalData[] = $permisnum;
                    }
                }
                # Trie par created_at
                $finalData = collect($finalData)->sortByDesc(function ($item) {
                    return Carbon::parse($item->created_at)->timestamp;
                })->values();

                return $this->successResponse($finalData);
            } else {
                return $this->errorResponse('Impossible de récupérer la liste des permis', null, null, 422);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des informations.', null, null, 500);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/candidats-eservices-parcours",
     *      operationId="getCandidatEservicesParcours",
     *      tags={"DossierCandidats"},
     *      summary="Récupère les eservices d'un candidat et son parcours pour la personne connectée",
     *      description="Récupère les eservices d'un candidat enregistré dans la base de données pour la personne connectée",
     *      @OA\Response(
     *          response=200,
     *          description="Le dossier du candidat a été récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Le dossier du candidat n'a pas été trouvé"
     *      )
     * )
     */
    public function getEserviceByCandidatId()
    {
        try {
            // Obtenir l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            $id = $user->id;

            // Obtenir les informations groupées par service depuis la table EserviceParcourSuivi
            $eserviceData = EserviceParcourSuivi::where('candidat_id', $id)
                ->orderByDesc('created_at')
                ->get()->map(function ($item) {
                    $eserviceInfo = json_decode($item->eservice, true);
                    // Récupérer les informations du modèle en fonction du champ eservice
                    $modelName = $eserviceInfo['Model'] ?? null;
                    $modelId = $eserviceInfo['id'] ?? null;

                    if ($modelName && $modelId) {
                        $modelData = app("App\\Models\\$modelName")->find($modelId);

                        if ($modelData) {
                            $item->model_info = $modelData;
                        }
                    }

                    return $item->makeHidden('eservice');
                })
                ->groupBy('service');

            if ($eserviceData->isEmpty()) {
                return $this->successResponse([], 'Aucune information trouvée pour cet utilisateur', 200);
            }

            return $this->successResponse($eserviceData->values());
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des informations.', null, null, 500);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/anatt-candidat/dossier-candidats/{id}",
     *      operationId="updateDossierCandidats",
     *      tags={"DossierCandidats"},
     *      summary="Met à jour un dossier-candidat existant",
     *      description="Met à jour un dossier-candidat existant dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du dossier-candidat à mettre à jour",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="dossier-candidat mis à jour avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="dossier-candidat non trouvé",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  example="Dossier candidat non trouvé"
     *              )
     *          )
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $user = auth()->user(); // Récupérer l'utilisateur connecté
            // Retrouver le dossier de session en utilisant l'ID fourni
            $oldDossierSession = DossierSession::findOrFail($id);
            // Mettre à jour les champs simples du dossier candidat
            $examen_id = $oldDossierSession->examen_id;
            $data = Api::data(Api::base('GET', "examens/{$examen_id}"));
            $fin_gestion_rejet_at = $data['fin_gestion_rejet_at'];
            // Convertir les dates en objets Carbon pour faciliter la comparaison
            $now = now(); // Date et heure actuelles
            $finGestionRejet = Carbon::parse($fin_gestion_rejet_at); // Date de fin de gestion rejet
            $state = $oldDossierSession->state;
            if ($state != 'rejet') {
                return $this->errorResponse('Action impossible');
            }
            // Comparer les dates en incluant les heures, minutes et secondes
            if ($finGestionRejet->isPast()) {
                $oldDossierSession->update([
                    'closed' => true,
                ]);

                // Retrouver le dossier candidat correspondant au dossier de session
                $dossier_candidat_id = $oldDossierSession->dossier_candidat_id;
                $dossierCandidat = DossierCandidat::findOrFail($dossier_candidat_id);
                $categoriePermisId = $dossierCandidat->categorie_permis_id;

                $categoryPermis = Api::data(Api::base('GET', "categorie-permis/{$categoriePermisId}"));

                $permisName = $categoryPermis['name'];
                $restrictionMedicalJson = null;

                if (($request->has('restriction_medical')) && (strlen($request->input('restriction_medical')) > 0)) {
                    $inputRestrictions = $request->input('restriction_medical');
                    // Convertir les données en tableau PHP
                    $restrictionMedical = explode(',', $inputRestrictions);

                    // Initialiser le tableau des restrictions valides
                    $validRestrictions = [];

                    // Vérifier chaque restriction
                    foreach ($restrictionMedical as $restrictionId) {
                        if ($restrictionId == "0") {
                            // Si la restriction est "0", ajouter sans vérifier
                            $validRestrictions[] = $restrictionId;
                        } else {
                            // Vérifier l'existence de la restriction dans le modèle Restriction
                            $exists = Restriction::find($restrictionId);
                            if ($exists) {
                                $validRestrictions[] = $restrictionId;
                            } else {
                                // Si la restriction n'est pas trouvée, renvoyer un message d'erreur
                                return $this->errorResponse('Restriction médicale non trouvée', null, null, 422);
                            }
                        }
                    }

                    // Convertir le tableau des restrictions valides en format JSON pour l'insertion dans la base de données
                    $restrictionMedicalJson = json_encode($validRestrictions);
                }
                DossierSession::where('dossier_candidat_id', $dossier_candidat_id)
                ->where('closed', false)
                ->update(['closed' => true]);
                // Créer un nouveau dossier de session
                $newDossierSession = DossierSession::create([
                    'dossier_candidat_id' => $dossierCandidat->id,
                    'is_militaire' => $request->input('candidat_type', $oldDossierSession->is_militaire),
                    'npi' => $request->input('npi', $oldDossierSession->npi),
                    'restriction_medical' => $restrictionMedicalJson,
                    'type_examen' => $request->input('type_examen', $oldDossierSession->type_examen),
                    'examen_id' => $request->input('examen_id', $oldDossierSession->examen_id),
                    'langue_id' => $request->input('langue_id', $oldDossierSession->langue_id),
                    'auto_ecole_id' => $request->input('auto_ecole_id', $oldDossierSession->auto_ecole_id),
                    'annexe_id' => $request->input('annexe_id', $oldDossierSession->annexe_id),
                    'old_ds_rejet_id' => $oldDossierSession->id,
                    'montant_paiement' => $request->input('montant_paiement', $oldDossierSession->montant_paiement),
                    'bouton_paiement' => $oldDossierSession->bouton_paiement,
                    'date_inscription' => $oldDossierSession->date_inscription,
                    'state' => 'pending',
                    'is_paid' => $oldDossierSession->is_paid,
                    'categorie_permis_id' => $oldDossierSession->categorie_permis_id,
                    'permis_prealable_id' => $oldDossierSession->permis_prealable_id,
                    'permis_prealable_dure' => $oldDossierSession->permis_prealable_dure,
                    'resultat_code' => $oldDossierSession->resultat_code,
                    'presence' => $oldDossierSession->presence,
                    'is_external' => $oldDossierSession->is_external,
                ]);
                // Mettre à jour les champs simples du dossier candidat
                $dossierCandidat->update([
                    'candidat_id' => $user->id, // Utiliser l'ID de l'utilisateur connecté
                    'group_sanguin' => $request->input('group_sanguin', $dossierCandidat->group_sanguin),
                ]);

                if ($request->hasFile('groupage_test')) {
                    $fichegroupageTest = $request->file('groupage_test');
                    $groupageTest = $fichegroupageTest->store('groupage_test', 'public');
                    $dossierCandidat->update(['groupage_test' => $groupageTest]);
                }
                if ($request->hasFile('fiche_medical')) {
                    $fichierficheMedical = $request->file('fiche_medical');
                    $ficheMedical = $fichierficheMedical->store('fiche_medical', 'public');
                    $newDossierSession->update(['fiche_medical' => $ficheMedical]);
                }else{
                    $newDossierSession->update(['fiche_medical' => $oldDossierSession->fiche_medical]);
                }
                $suivi = ParcoursSuivi::where('dossier_candidat_id', $dossier_candidat_id)
                    ->where('slug', 'validation-anatt-failed')
                    ->orderByDesc('created_at')
                    ->first();

                if (!$suivi) {
                    return $this->errorResponse('Parcours suivi non trouvé');
                }
                $suivi->bouton = '{"bouton":"Rejet","status":"0"}';
                $suivi->save();

                // Créer le parcours suivi
                $parcoursSuivi = new ParcoursSuivi();
                $parcoursSuivi->npi = $dossierCandidat->npi;
                $parcoursSuivi->slug = "correction-rejet";
                $parcoursSuivi->service = 'Permis';
                $parcoursSuivi->candidat_id = $dossierCandidat->candidat_id;
                $parcoursSuivi->dossier_candidat_id = $dossierCandidat->id;
                $parcoursSuivi->categorie_permis_id = $dossierCandidat->categorie_permis_id;
                $parcoursSuivi->dossier_session_id = $newDossierSession->id;
                $parcoursSuivi->message = "Vous avez corrigé et soumis votre dossier avec succès. Votre demande de préinscription à l'examen du Permis de conduire catégorie " . $permisName . " a été effectuée avec succès, Le délai ayant été dépassé pour la session précédente, veuillez sélectionner à nouveau votre session pour continuer";
                $parcoursSuivi->bouton = json_encode(['bouton' => 'Reinscription', 'status' => '1']);
                $parcoursSuivi->date_action = now();
                $parcoursSuivi->save();

                // Retourner la réponse JSON appropriée
                return $this->successResponse([
                    'dossierCandidat' => $dossierCandidat,
                    'newDossierSession' => $newDossierSession,
                ], 'Données mises à jour avec succès', 201);
            } else {
                $oldDossierSession->update([
                    'closed' => true,
                ]);

                // Retrouver le dossier candidat correspondant au dossier de session
                $dossier_candidat_id = $oldDossierSession->dossier_candidat_id;
                $dossierCandidat = DossierCandidat::findOrFail($dossier_candidat_id);
                $categoriePermisId = $dossierCandidat->categorie_permis_id;

                $categoryPermis = Api::data(Api::base('GET', "categorie-permis/{$categoriePermisId}"));

                $permisName = $categoryPermis['name'];

                $inputRestrictions = $request->input('restriction_medical');

                // Convertir les données en tableau PHP
                $restrictionMedical = explode(',', $inputRestrictions);

                // Convertir le tableau en format JSON pour l'insertion dans la base de données
                $restrictionMedicalJson = json_encode($restrictionMedical);
                DossierSession::where('dossier_candidat_id', $dossier_candidat_id)
                ->where('closed', false)
                ->update(['closed' => true]);
                // Créer un nouveau dossier de session
                $newDossierSession = DossierSession::create([
                    'dossier_candidat_id' => $dossierCandidat->id,
                    'is_militaire' => $request->input('candidat_type', $oldDossierSession->is_militaire),
                    'npi' => $request->input('npi', $oldDossierSession->npi),
                    'restriction_medical' => $restrictionMedicalJson,
                    'type_examen' => $request->input('type_examen', $oldDossierSession->type_examen),
                    'langue_id' => $request->input('langue_id', $oldDossierSession->langue_id),
                    'auto_ecole_id' => $request->input('auto_ecole_id', $oldDossierSession->auto_ecole_id),
                    'annexe_id' => $request->input('annexe_id', $oldDossierSession->annexe_id),
                    'old_ds_rejet_id' => $oldDossierSession->id,
                    'montant_paiement' => $request->input('montant_paiement', $oldDossierSession->montant_paiement),
                    'examen_id' => $request->input('examen_id', $oldDossierSession->examen_id),
                    'bouton_paiement' => $oldDossierSession->bouton_paiement,
                    'date_inscription' => $oldDossierSession->date_inscription,
                    'state' => 'payment',
                    'is_paid' => $oldDossierSession->is_paid,
                    'categorie_permis_id' => $oldDossierSession->categorie_permis_id,
                    'permis_prealable_id' => $oldDossierSession->permis_prealable_id,
                    'permis_prealable_dure' => $oldDossierSession->permis_prealable_dure,
                    'resultat_code' => $oldDossierSession->resultat_code,
                    'presence' => $oldDossierSession->presence,
                    'is_external' => $oldDossierSession->is_external,
                ]);
                // Mettre à jour les champs simples du dossier candidat
                $dossierCandidat->update([
                    'candidat_id' => $user->id, // Utiliser l'ID de l'utilisateur connecté
                    'group_sanguin' => $request->input('group_sanguin', $dossierCandidat->group_sanguin),
                ]);
                if ($request->hasFile('groupage_test')) {
                    $fichegroupageTest = $request->file('groupage_test');
                    $groupageTest = $fichegroupageTest->store('groupage_test', 'public');
                    $dossierCandidat->update(['groupage_test' => $groupageTest]);
                }
                if ($request->hasFile('fiche_medical')) {
                    $fichierficheMedical = $request->file('fiche_medical');
                    $ficheMedical = $fichierficheMedical->store('fiche_medical', 'public');
                    $newDossierSession->update(['fiche_medical' => $ficheMedical]);
                }else{
                    $newDossierSession->update(['fiche_medical' => $oldDossierSession->fiche_medical]);
                }
                $suivi = ParcoursSuivi::where('dossier_candidat_id', $dossier_candidat_id)
                    ->where('slug', 'validation-anatt-failed')
                    ->orderByDesc('created_at')
                    ->first();

                if (!$suivi) {
                    return $this->errorResponse('Parcours suivi non trouvé');
                }
                $suivi->bouton = '{"bouton":"Rejet","status":"0"}';
                $suivi->save();

                $parcoursSuiviData = [
                    'npi' => $dossierCandidat->npi,
                    'slug' => 'correction-rejet-succes',
                    'service' => 'Permis',
                    'candidat_id' => $dossierCandidat->candidat_id,
                    'dossier_candidat_id' => $dossierCandidat->id,
                    'categorie_permis_id' => $dossierCandidat->categorie_permis_id,
                    'dossier_session_id' => $newDossierSession->id,
                    'message' => 'Vous avez corrigé et soumis votre dossier avec succès. Votre demande de préinscription à l\'examen du Permis de conduire catégorie ' . $permisName . ' a été effectuée avec succès',
                    'date_action' => now(),
                ];

                // Créer le parcours suivi
                $parcoursSuivi = ParcoursSuivi::create($parcoursSuiviData);
                // Retourner la réponse JSON appropriée
                return $this->successResponse([
                    'dossierCandidat' => $dossierCandidat,
                    'newDossierSession' => $newDossierSession,
                ], 'Données mises à jour avec succès', 200);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour.', null, null, 500);
        }
    }

    public function updateCandidatDossier(Request $request, $id)
    {
        try {
            $user = auth()->user(); // Récupérer l'utilisateur connecté

            // Validation des données entrantes
            $validator = Validator::make($request->all(), [
                'restriction_medical' => 'required|string',
                'langue_id' => 'required|exists:langues,id',
                'group_sanguin' => 'nullable|string',
                'groupage_test' => 'nullable|file',
                'fiche_medical' => 'nullable|file',
            ]);

            // Vérifier si la validation a échoué
            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }

            // Retrouver le dossier de session en utilisant l'ID fourni
            $oldDossierSession = DossierSession::find($id);

            // Retrouver le dossier candidat correspondant au dossier de session
            $dossierCandidat = DossierCandidat::find($oldDossierSession->dossier_candidat_id);

            // Convertir les données en tableau PHP
            $restrictionMedical = explode(',', $request->input('restriction_medical'));

            // Convertir le tableau en format JSON pour l'insertion dans la base de données
            $restrictionMedicalJson = json_encode($restrictionMedical);

            // Vérifier si le dossier de session et le dossier de candidat correspondent
            if ($oldDossierSession->dossier_candidat_id !== $dossierCandidat->id) {
                return $this->errorResponse('Le dossier de session et le dossier de candidat ne correspondent pas.', null, null, 422);
            }

            if ($oldDossierSession->state !== 'init') {
                return $this->errorResponse('Vous ne pouvez plus modifier vos informations', null, null, 422);
            }

            // Mettre à jour les champs du dossier de session
            $oldDossierSession->update([
                'restriction_medical' => $restrictionMedicalJson,
                'langue_id' => $request->input('langue_id'),
            ]);

            // Mettre à jour le champ fiche_medical si un fichier est joint
            if ($request->hasFile('fiche_medical')) {
                $ficheMedical = $request->file('fiche_medical')->store('fiche_medical', 'public');
                $oldDossierSession->update(['fiche_medical' => $ficheMedical]);
            }

            if ($request->has('group_sanguin')) {
                $groupSanguin = $request->input('group_sanguin');
                $dossierCandidat->update(['group_sanguin' => $groupSanguin]);
            }

            if ($request->hasFile('groupage_test')) {
                $groupageTest = $request->file('groupage_test')->store('groupage_test', 'public');
                $dossierCandidat->update(['groupage_test' => $groupageTest]);
            }

            return $this->successResponse([
                'dossierCandidat' => $dossierCandidat,
                'oldDossierSession' => $oldDossierSession,
            ], 'Données mises à jour avec succès', 200);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour.', null, null, 500);
        }
    }


    /**
     * @OA\Delete(
     *      path="/api/anatt-candidat/dossier-candidats/{id}",
     *      operationId="deleteDossierCandidats",
     *      tags={"DossierCandidats"},
     *      summary="Supprime un dossier-candidat",
     *      description="Supprime un dossier-candidat de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du dossier-candidat à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="dossier-candidat supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="dossier-candidat non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            $dossier = DossierCandidat::findOrFail($id);
            $dossier->delete();

            return $this->successResponse(null, 'Dossier supprimé avec succès.');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse($e->getMessage());
        }
    }
}
