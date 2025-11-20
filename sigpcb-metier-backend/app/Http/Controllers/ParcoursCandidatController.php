<?php

namespace App\Http\Controllers;

use App\Services\Api;
use Illuminate\Http\Request;
use App\Models\ParcoursSuivi;
use App\Models\DossierSession;
use App\Models\DossierCandidat;
use App\Models\ParcoursCandidat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ParcoursCandidatController extends ApiController
{

        /**
     * @OA\Get(
     *      path="/api/anatt-candidat/candidat-parcours",
     *      operationId="getCandidatParcours",
     *      tags={"CandidatParcours"},
     *      summary="Obtient la liste des parcours des candidats",
     *      description="Obtient la liste des parcours des candidats enregistrés dans la base de données",
     *      @OA\Response(
     *          response=200,
     *          description="Liste des parcours des candidats",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="candidat_id",
     *                      description="ID du candidat",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="auto_ecole_id",
     *                      description="ID de l'auto école choisi",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier du candidat",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="categorie_permis_id",
     *                      description="ID du catégorie de permis",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="examen_id",
     *                      description="ID de l'examen",
     *                      type="integer",
     *                  ),
    *                  @OA\Property(
     *                      property="annexe_anatt_id",
     *                      description="ID de l'annexe anatt choisi",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="npi",
     *                      description="npi du candidat",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="examen_type",
     *                      description="Le type de l'examen du candidat (ecrit/conduite)",
     *                      type="string",
     *                      enum={"ecrit", "conduite"},
     *                  ),
     *                  @OA\Property(
     *                      property="candidat_ecrit_note",
     *                      description="la note écrite du candidat",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="candidat_conduite_note",
     *                      description="la note de conduite du candidat",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="candidat_presence",
     *                      description="la présence du candidat",
     *                      type="boolean",
     *                  ),
     *                  @OA\Property(
     *                      property="is_close",
     *                      description="la présence du candidat",
     *                      type="boolean",
     *                  ),
     *              )
     *          )
     *      )
     * )
     */   
    public function index()
    {
        try {
            $parcours = ParcoursCandidat::orderByDesc('id')->get();

            return $this->successResponse($parcours, 'Liste des parcours de candidats récupérée avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération de la liste des parcours de candidats.');
        }
    }

     /**
     * @OA\Post(
     *      path="/api/anatt-candidat/candidat-parcours",
     *      operationId="storeCandidatParcours",
     *      tags={"CandidatParcours"},
     *      summary="Enregistre le parcours d'un candidat",
     *      description="Enregistre le parcours d'un candidat dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Données du parcours du candidat",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="auto_ecole_id",
     *                  description="ID de l'auto école choisie",
     *                  type="integer",
     *              ),
     *              @OA\Property(
     *                  property="dossier_candidat_id",
     *                  description="ID du dossier du candidat",
     *                  type="integer",
     *              ),
     *              @OA\Property(
     *                  property="categorie_permis_id",
     *                  description="ID de la catégorie de permis",
     *                  type="integer",
     *              ),
     *              @OA\Property(
     *                  property="examen_id",
     *                  description="ID de l'examen",
     *                  type="integer",
     *              ),
     *              @OA\Property(
     *                  property="annexe_anatt_id",
     *                  description="ID de l'annexe anatt choisie",
     *                  type="integer",
     *              ),
     *              @OA\Property(
     *                  property="examen_type",
     *                  description="Le type de l'examen du candidat (ecrit/conduite)",
     *                  type="string",
     *                  enum={"ecrit", "conduite"},
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Parcours du candidat enregistré avec succès",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  description="Message de succès",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  description="Données du parcours enregistré",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du parcours enregistré",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="auto_ecole_id",
     *                      description="ID de l'auto école choisie",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier du candidat",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="annexe_anatt_id",
     *                      description="ID de l'annexe anatt choisie",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="examen_type",
     *                      description="Le type de l'examen du candidat (ecrit/conduite)",
     *                      type="string",
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation échouée",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  description="Message d'erreur",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="errors",
     *                  description="Liste des erreurs de validation",
     *                  type="object",
     *              )
     *          )
     *      )
     * )
     */
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
                'annexe_anatt_id.exists' => 'L\'annexe sélectionnée n\'existe pas.',
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
    
            // Enregistrez les valeurs récupérées dans la base de données
            $newSession = DossierSession::create([
                'auto_ecole_id' => $request->input('auto_ecole_id'),
                'dossier_candidat_id' => $dossier_candidat_id,
                'annexe_id' => $request->input('annexe_anatt_id'),
                'type_examen' => $request->input('examen_type'),
                'restriction_medical' => $restriction_medical,
                'fiche_medical' => $fiche_medical,
                'permis_extension_id' => $permis_extension_id,
                'langue_id' => $langue_id,
                'is_militaire' => $is_militaire,
                'montant_paiement' => $montant_paiement,
                'npi' => $npi,
                'permis_prealable_id' => $permis_prealable_id,
                'permis_prealable_dure' => $permis_prealable_dure,
                'categorie_permis_id' => $categorie_permis_id,
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
    
            // Retourner une réponse de succès avec les données enregistrées
            return $this->successResponse($newSession, 'Données enregistrées avec succès', 201);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la création des données");
        }
    }
    

    public function stdore(Request $request)
    {
        try {
            // Valider les données de la requête
            $validator = Validator::make($request->all(), [
                'auto_ecole_id' => 'required|integer',
                'dossier_candidat_id' => 'required|exists:dossier_candidats,id',
                'categorie_permis_id' => 'required|integer',
                'annexe_anatt_id' => 'required|integer',
                'examen_type' => 'required|string',
            ],[
                // Messages d'erreur personnalisés
                "dossier_candidat_id.required" => 'Le dossier est obligatoire',
                "categorie_permis_id.required" => 'La catégorie de permis est obligatoire',
                "annexe_anatt_id.required" => 'L\'Annexe anatt est obligatoire',
                "examen_type.required" => 'Type de l\'examen est requis',
                "auto_ecole_id.required" =>'Veuillez sélectionner une auto-école valide.',
            ]);

            // En cas d'échec de validation, renvoyer une réponse d'erreur avec les erreurs de validation
            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }

            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
            }

            // Récupérer le NPI du candidat depuis le dossier candidat
            $dossier_candidat = DossierCandidat::findOrFail($request->dossier_candidat_id);
            $categorie_permis_id = $dossier_candidat->categorie_permis_id ?? null;

            // L'utilisateur est authentifié, vous pouvez récupérer son ID
            $candidat_id = $user->id; // Récupérer l'ID de l'utilisateur connecté
            $requestData = $request->all();
            $requestData['candidat_id'] = $candidat_id;

            // Vérifier si le candidat a déjà un parcours en cours
            $lastParcours = ParcoursCandidat::where('candidat_id', $candidat_id)
                ->latest('created_at')
                ->first();

            if ($lastParcours && !$lastParcours->is_closed) {
                return $this->errorResponse('Vous avez déjà un parcours en cours.', null, null, 422);
            }

            // Faire la requête pour récupérer le NPI du candidat à partir de l'API externe
            $response = Api::candidat('GET', "dossier-candidats/{$candidat_id}");
            if ($response === -1 || !$response->successful()) {
                return $this->errorResponse('Impossible de récupérer le NPI pour le candidat.', null, null, 422);
            }

            $npi = $response->json()['data']['npi'] ?? null;

            // Vérifier si le NPI a été récupéré
            if (!$npi) {
                return $this->errorResponse("NPI introuvable pour le candidat.", null, null, 404);
            }

            $requestData['npi'] = $npi;
            $date_soumission = now();
            $permisName = $request->input('nom_permis');

            // Créer le parcours du candidat dans la base de données
            $parcoursCandidat = ParcoursCandidat::create($requestData);

            // Enregistrement dans le modèle ParcoursSuivi
            $parcoursSuivi = new ParcoursSuivi();
            $parcoursSuivi->npi = $npi;
            $parcoursSuivi->slug = 'reconduit-' .$request->input('examen_type');
            $parcoursSuivi->service = 'Permis';
            $parcoursSuivi->candidat_id = $candidat_id;
            $parcoursSuivi->dossier_candidat_id = $request->input('dossier_candidat_id');
            $parcoursSuivi->categorie_permis_id = $categorie_permis_id;
            $parcoursSuivi->message = 'Votre demande pour passer à l\'examen du Permis de conduire pour le/la '.$request->input('examen_type').' catégorie ' . $permisName . ' entant que reconduit a été effectuée avec succès';                        
            $parcoursSuivi->date_action = $date_soumission;
            $parcoursSuivi->save(); 

            // Retourner une réponse de succès avec les données du parcours enregistré
            return $this->successResponse($parcoursCandidat, 'Parcours du candidat enregistré avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            // Retourner une réponse d'erreur en cas d'exception
            return $this->errorResponse('Une erreur est survenue lors de l\'enregistrement du parcours du candidat.', null, null, 500);
        }
    }
    

}
