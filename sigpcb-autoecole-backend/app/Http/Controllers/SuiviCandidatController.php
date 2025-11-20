<?php

namespace App\Http\Controllers;

use App\Services\Api;
use Illuminate\Http\Request;
use App\Models\SuiviCandidat;
use App\Models\DossierSession;
use App\Models\DossierCandidat;
use App\Models\MoniteurSuiviCandidat;
use App\Services\Help;
use App\Services\Mail\Messager;
use App\Services\Mail\EmailNotifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Candidat\ParcoursSuivi;
use App\Services\Sms;


class SuiviCandidatController extends ApiController
{

    /**
     * @OA\Get(
     *     path="/api/anatt-autoecole/suivi-candidats",
     *     summary="Obtenir la liste des dossiers de suivi des candidats",
     *     description="Récupère la liste des dossiers de suivi des candidats enregistrés",
     *     operationId="getAllSuiviCandidat",
     *     tags={"SuiviCandidat"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des dossiers de suivi des candidats",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du suivi",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="auto_ecole_id",
     *                      description="ID de l'auto ecole",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="npi",
     *                      description="npi du candidat",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="categorie_permis_id",
     *                      description="ID de la catégorie de permis",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="langue_id",
     *                      description="ID de la langue de composition",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier candidat",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="chapitres_id",
     *                      description="ID des chapitres que le candidat a suivi",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="le statut ",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="is_valid",
     *                      description="le statut de validation du dossier ",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="certification",
     *                      description="La case certification a cocher par l'auto école",
     *                      type="string"
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *     )
     * )
     */
    public function index()
    {
        $wheres = array_merge(request()->all(), [
            'auto_ecole_id' => Help::authAutoEcole()->id,
            "scope" => "autoEcole"
        ]); // on ajoute maintenant

        return $this->exprotFromBase("suivi-candidats", $wheres);
    }


    /**
     * @OA\Get(
     *     path="/api/anatt-autoecole/auth/suivi-candidat/{suivi_id}/chapitres",
     *     summary="Obtenir la liste des chapitres suivi pour un candidat",
     *     description="Récupère la liste des chapitres suivi pour un candidat",
     *     operationId="candidatChapitre",
     *     tags={"SuiviCandidat"},
     *     @OA\Parameter(
     *         name="suivi_id",
     *         in="path",
     *         description="ID du suivi",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des chapitres de suivi des candidats",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du suivi",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="auto_ecole_id",
     *                      description="ID de l'auto ecole",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="npi",
     *                      description="npi du candidat",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="categorie_permis_id",
     *                      description="ID de la catégorie de permis",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="langue_id",
     *                      description="ID de la langue de composition",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="dossier_candidat_id",
     *                      description="ID du dossier candidat",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="chapitres_id",
     *                      description="ID des chapitres que le candidat a suivi",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="le statut",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="is_valid",
     *                      description="le statut de validation du dossier",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="certification",
     *                      description="La case certification a cocher par l'auto école",
     *                      type="string"
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
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

    public function candidatChapitre($id)
    {
        try {
            $suivi = SuiviCandidat::find($id);
            if (!$suivi) {
                return $this->errorResponse('Aucun résultat trouvé', statuscode: 404);
            }
            $chapitreIds = explode(',', $suivi->chapitres_id);
            $chapitres = [];
            foreach ($chapitreIds as $chapitreId) {
                $path = "chapitres/" . $chapitreId;
                $response =  Api::base('GET', $path);

                $data = Api::data($response);
                //Le moins indique qu'il y a une erreur sur le serveur distant
                if ($data === -1) {
                    return $this->errorResponse('Aucun résultat trouvé pour le chapitre id ' . $chapitreId, statuscode: 404);
                }
                $chapitres[] = $data;
            }
            return $this->successResponse($chapitres);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des dossiers de suivi des candidats.', statuscode: 500);
        }
    }

    private function sendToExternalAPI(array $data)
    {
        try {
            // Validation des données
            $validator = Validator::make($data, [
                'service' => 'required|string',
                'telephone' => 'required',
                'candidat_id' => 'required|exists:base.candidats,id',
                'auto_ecole_id' => 'nullable',
                'agent_id' => 'nullable',
                'categorie_permis_id' => 'required',
                'npi' => 'nullable|string',
                'slug' => 'nullable|string',
                'message' => 'nullable|string',
                'bouton' => 'nullable|string',
                'action' => 'nullable|string',
                'url' => 'nullable|string',
                'dossier_candidat_id' => 'nullable|exists:base.dossier_candidats,id',
                'dossier_session_id' => 'nullable|exists:base.dossier_sessions,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }

            $dateAction = now();

            // Créer l'objet et enregistrer dans la base de données
            $model = new ParcoursSuivi();
            $model->service = $data['service'];
            $model->candidat_id = $data['candidat_id'];
            $model->auto_ecole_id = $data['auto_ecole_id'];
            $model->agent_id = $data['agent_id'];
            $model->categorie_permis_id = $data['categorie_permis_id'];
            $model->npi = $data['npi'];
            $model->slug = $data['slug'];
            $model->message = $data['message'];
            $model->bouton = $data['bouton'];
            $model->action = $data['action'];
            $model->url = $data['url'];
            $model->date_action = $dateAction;
            $model->dossier_candidat_id = $data['dossier_candidat_id'];
            $model->dossier_session_id = $data['dossier_session_id'];
            $model->save();

            // Envoi du SMS
            $country_code = '229';
            $num = $data['telephone'];
            $text = 'Votre auto-ecole vient de valider votre formation, veuillez vous connecter pour choisir votre session';
            Sms::sendSMS($country_code, $num, $text);

            // Retourner une réponse de succès avec les données créées
            return $this->successResponse($model, 'Données enregistrées avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            // Retourner une réponse d'erreur en cas d'exception
            return $this->errorResponse('Une erreur s\'est produite lors de l\'enregistrement des informations.', 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-autoecole/suivi-candidats",
     *      operationId="storeSuiviCandidat",
     *      tags={"SuiviCandidat"},
     *      summary="Enregistrer les dossiers de suivi des candidats",
     *      description="Enregistre les dossiers de suivi des candidats pour une auto-école donnée",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="categorie_permis_id", type="integer", description="ID de la catégorie de permis", example="2"),
     *              @OA\Property(property="langue_id", type="integer", description="ID de la langue", example="1"),
     *              @OA\Property(property="dossier_candidat_id", type="array", description="Tableau des IDs des dossiers candidats", @OA\Items(type="integer", example="3")),
     *              @OA\Property(property="chapitres_id", type="array", description="Tableau des IDs des chapitres", @OA\Items(type="integer", example="4")),
     *              @OA\Property(property="certification", type="boolean", description="Certification du suivi", example=false),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Dossiers enregistrés avec succès"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation échouée"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur lors de l'enregistrement"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dossier_session_id' => 'required|array|min:1',
            'dossier_session_id.*' => 'required',
            'chapitres_id' => 'required|array|min:1',
            'chapitres_id.*' => 'required',
            'certification' => 'boolean',
        ], [
            'dossier_session_id.required' => 'Les dossiers sont requis',
            'dossier_session_id.array' => 'Le champ dossiers du candidat doit être un tableau',
            'chapitres_id.required' => 'Veuillez sélectionner au moins un chapitre.',
            'chapitres_id.array' => 'Le champ chapitre doit être un tableau',
            'certification.boolean' => 'Certification incorrecte (true/false)',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation échouée', $validator->errors(), statuscode: 422);
        }
        DB::beginTransaction();
        try {

            $moniteur = Help::moniteurAuth();

            // L'utilisateur est authentifié, vous pouvez récupérer son ID

            $auto_ecole = Help::authAutoEcole();
            $auto_ecole_id = $auto_ecole->id; //


            $dossier_session_ids = $request->input('dossier_session_id');
            $chapitres_ids = implode(',', $request->input('chapitres_id'));
            $certification = $request->input('certification') ?? false;

            $enregistrements = [];
            // Enregistrement des dossiers pour chaque candidat avec les NPIs correspondants
            foreach ($dossier_session_ids as $session_id) {

                $session = DossierSession::find($session_id);
                $dossier = DossierCandidat::find($session->dossier_candidat_id);
                $npi = $session['npi'];
                $categorie_permis_id = $session->categorie_permis_id;
                $langue_id = $session->langue_id;
                $date = now();
                if ($session->closed === true || $session->abandoned === true || $session->state != 'init') {
                    return $this->errorResponse('Impossible de poursuivre avec le candidat au numéro NPI ' . $npi . ' : la session est soit fermée, abandonnée ou dans un état non initialisé.', null, null, 500);
                }


                $suivi = SuiviCandidat::create([
                    'auto_ecole_id' => $auto_ecole_id,
                    'npi' => $npi,
                    'categorie_permis_id' => $categorie_permis_id,
                    'langue_id' => $langue_id,
                    'dossier_candidat_id' => $session['dossier_candidat_id'],
                    'annexe_id' => $session['annexe_id'],
                    'examen_id' => $session['examen_id'] ?? null,
                    'dossier_session_id' => $session['id'],
                    'chapitres_id' => $chapitres_ids,
                    'certification' => $certification,
                ]);

                $enregistrements[] = $suivi;


                // Mets à jour le dossier session
                $this->updateStateDossierSession($session, 'pending', $candidat);

                // Créer les données à envoyer à l'API externe
                $data = [
                    'service' => 'Monitoring',
                    'candidat_id' => $dossier['candidat_id'],
                    'auto_ecole_id' => $auto_ecole_id,
                    'agent_id' => null,
                    'categorie_permis_id' => $categorie_permis_id,
                    'npi' => $npi,
                    'slug' => 'monitoring',
                    'message' => "L'auto école " . $auto_ecole->name . " vient d'enrégistrer votre formation vous pouvez procéder au paiement de vos frais d'inscription",
                    'bouton' => '{"bouton":"Paiement","status":"1"}',
                    'action' => null,
                    'url' => null,
                    'date_action' => $date,
                    'dossier_candidat_id' => $session['dossier_candidat_id'],
                    'dossier_session_id' => $session['id'],
                    'telephone' => $candidat['telephone']
                ];

                if ($moniteur) {
                    MoniteurSuiviCandidat::create([
                        "suivi_candidat_id" => $suivi->id,
                        'moniteur_id' => $moniteur->id
                    ]);
                } else {
                    MoniteurSuiviCandidat::create([
                        "suivi_candidat_id" => $suivi->id,
                        'moniteur_id' => 1, //promoteur id
                        'user' => "promoteur"
                    ]);
                }

                // Appeler la fonction pour envoyer les données à l'API externe
                $this->sendToExternalAPI($data);
            }

            Help::historique(
                "Monitoring",
                "Monitoring des candidats effectué avec succès",
                "Monitoring-init",
                "Votre monitoring des candidats ce {$date} a effectué avec succès",
                $auto_ecole->promoteur
            );
            DB::commit();
            return $this->successResponse($enregistrements, 'Les données ont été enregistrées avec succès.');
        } catch (\Throwable $e) {
            logger()->error($e);
            DB::rollBack();
            return $this->errorResponse('Une erreur est survenue lors de l\'enregistrement.', null, null, 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/anatt-autoecole/update-dossier-state",
     *     summary="Mettre à jour le statut du suivi candidat et du dossier candidat",
     *     description="Met à jour le statut du suivi candidat ainsi que le statut du dossier candidat associé",
     *     operationId="updateState",
     *     tags={"SuiviCandidat"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 description="ID du suivi candidat à mettre à jour",
     *                 type="integer",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Le statut du suivi candidat a été mis à jour avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="id",
     *                 description="ID du suivi candidat",
     *                 type="integer"
     *             ),
     *             @OA\Property(
     *                 property="state",
     *                 description="Nouveau statut du suivi candidat",
     *                 type="string"
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Monitoring candidat non trouvé",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *     )
     * )
     */
    public function updateState(Request $request)
    {
        try {
            $id = $request->input('id');
            $agent_id = $request->input('agent_id');

            // Vérifier si le suivi existe avec l'ID donné
            $suivi = SuiviCandidat::findOrFail($id);

            // Récupérer le dossier candidat associé au suivi
            $dossierCandidatId = $suivi->dossier_candidat_id;

            // Mettre à jour le champ "state" du suivi avec la valeur "validate"
            $suivi->state = 'validate';
            $suivi->save();

            // Mettre à jour le champ "state" du dossier candidat via l'endpoint update-dossier-state sur l'instance candidat
            $response = Api::base('POST', "updat-dossier-state", ['id' => $dossierCandidatId, 'state' => 'validate']);
            $data = Api::data($response);

            if ($data === -1) {
                return $this->errorResponse('Une erreur est survenue lors de la mise à jour du statut du dossier candidat.', null, null, 500);
            }

            return $this->successResponse($suivi, 'Le statut du monitoring candidat a été mis à jour avec succès.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Monitoring candidat non trouvé.', statuscode: 422);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour du monitoring candidat.', null, null, 500);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-autoecole/suivi-candidats/{id}",
     *      operationId="showSuiviCandidat",
     *      tags={"SuiviCandidat"},
     *      summary="Obtenir les informations d'un dossier de suivi de candidat",
     *      description="Récupère les informations d'un dossier de suivi de candidat en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du dossier de suivi de candidat",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Informations du dossier de suivi de candidat récupérées avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Dossier de suivi de candidat non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            $suiviCandidat = SuiviCandidat::findOrFail($id);
            return $this->successResponse($suiviCandidat);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Dossier de suivi de candidat introuvable.',);
        }
    }

    public function updateStateDossierSession(DossierSession $session, $state, &$candidat)
    {
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
                ->lastParagraph("En cas d'erreur vous pouvez rapprocher de votre auto-école")
                ->goodbye('Merci et bonne chance !')
                ->footer();

            (new EmailNotifier($messageBuilder, $candidat))->procced();
        }
    }
}
