<?php

namespace App\Http\Controllers;

use App\Models\Reponse;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\QuestionLangue;
use App\Models\QuestionReponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\Api;

class QuestionController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-admin/questions",
     *     operationId="getAllQuestions",
     *     tags={"Questions"},
     *     summary="Récupérer la liste des questions",
     *     description="Récupère une liste de tous les questions enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des questions récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la question",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de la question",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="chapitre_id",
     *                      description="ID du chapitre",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="illustration",
     *                      description="une illustration de la question s'il y en a",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="code_illustration",
     *                      description="le code illustratif de l'image de la question s'il y en a",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="texte",
     *                      description="un texte descriptif de la question s'il y en a",
     *                      type="string"
     *                  ),
     *              )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $this->hasAnyPermission(["all","read-qestion-management","edit-qestion-management"]);

        try {
            $query = Question::with(['reponses', 'audiolangues']);

            if ($request->has('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower($searchTerm)]);
                });
            }

            if (request('liste') == 'paginate') {
                $questions = $query->orderByDesc('id')->paginate(10);
            } else {
                $questions = $query->orderByDesc('id')->get();
            }

            if ($questions->isEmpty()) {
                return $this->successResponse([], "Aucun résultat trouvé", 200);
            }

            return $this->successResponse($questions);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/anatt-admin/questions/audios",
     *     operationId="getAllAudioQuestions",
     *     tags={"Questions"},
     *     summary="Récupérer la liste des audios des questions",
     *     description="Récupère une liste de tous les audios des questions enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des audios des questions récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'audio",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="question_id",
     *                      description="l'id de la question",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="langue_id",
     *                      description="ID de la langue",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="audio",
     *                      description="le fichier audio lié a la question",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="time",
     *                      description="le fichier audio lié a la question",
     *                      type="integer"
     *                  ),
     *              )
     *         )
     *     )
     * )
     */
    public function getAudio(Request $request)
    {

        try {
            $this->hasAnyPermission(["all","read-qestion-management","edit-qestion-management"]);
            $query = QuestionLangue::with('question');

            if ($request->has('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('LOWER(langue_id) LIKE ?', [strtolower($searchTerm)]);
                });
            }

            if (request('liste') == 'paginate') {
                $questions = $query->orderByDesc('id')->paginate(10);
            } else {
                $questions = $query->orderByDesc('id')->get();
            }

            if ($questions->isEmpty()) {
                return $this->successResponse([], "Aucun résultat trouvé", 200);
            }

            return $this->successResponse($questions);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/questions/audio",
     *      operationId="createQuestionsLangue",
     *      tags={"Questions"},
     *      summary="Assigné des audios à une question",
     *      description="Assigné des audios à une question enregistré dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="question_id",
     *                      description="L'id de la question",
     *                      type="integer"
     *                  ),
     *                      @OA\Property(
     *                          property="langue_id",
     *                          description="Id de la langue",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="audio",
     *                          description="l'audio de la question selon la langue",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="time",
     *                          description="le temps",
     *                          type="string"
     *                      ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle assignation créé"
     *      )
     * )
     */
    public function createQuestionLangue(Request $request)
    {
        $this->hasAnyPermission(["all","edit-qestion-management"]);

        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'question_id' => 'required|integer',
                    'langue_id' => 'required|integer',
                    'audio' => 'required|file|mimes:audio/mpeg,mpga,mp3,wav,ogg,opus|max:8192',
                    // 'time' => 'required|numeric',
                ],
                [
                    'question_id.required' => 'La question est obligatoire',
                    'langue_id.required' => 'La langue est obligatoire',
                    'langue_id.integer' => 'La langue doit être un entier',
                    'audio.required' => 'L\'audio est obligatoire',
                    'audio.file' => 'L\'audio doit être un fichier',
                    'audio.mimes' => 'L\'audio doit être un fichier mp3',
                    // 'time.required' => 'Le temps est obligatoire',
                    // 'time.numeric' => 'Le temps doit être un nombre',

                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), 422);
            }

            $data = $validator->validated();
            $questionId = $request->input('question_id');
            $langueId = $request->input('langue_id');
            // $time = $request->input('time');
            $question = Question::find($questionId);
            if (!$question) {
                return $this->errorResponse('La question n\'a pas été trouvée', null, null, 422);
            }

            $questionLangue = QuestionLangue::where('question_id', $questionId)
                ->where('langue_id', $langueId)
                ->first();

            if ($questionLangue) {
                // Supprimer l'ancien fichier audio s'il existe
                if ($questionLangue->audio) {
                    Storage::disk('public')->delete($questionLangue->audio);
                }

                // Enregistrer le nouveau fichier audio
                $path = $request->file('audio')->store('audios', 'public');
                $questionLangue->audio = $path;
                $questionLangue->time = '1798';
                // $questionLangue->time = round($time);
                $questionLangue->save();
            } else {
                // Enregistrement d'une nouvelle entrée
                $path = $request->file('audio')->store('audios', 'public');
                $questionLangue = new QuestionLangue();
                $questionLangue->question_id = $questionId;
                $questionLangue->langue_id = $langueId;
                $questionLangue->audio = $path;
                $questionLangue->time = '1798';
                // $questionLangue->time = round($time);
                $questionLangue->save();
            }

            return $this->successResponse($questionLangue, 201);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la création", null, null, 500);
        }
    }



    /**
     * @OA\Post(
     *      path="/api/anatt-admin/questions",
     *      operationId="createQuestions",
     *      tags={"Questions"},
     *      summary="Crée une question",
     *      description="Crée un nouveau questions enregistré dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de la question",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="chapitre_id",
     *                      description="ID du chapitre",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="illustration",
     *                      description="une illustration de la question s'il y en a",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="code_illustration",
     *                      description="le code illustratif de l'image de la question s'il y en a",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="texte",
     *                      description="un texte descriptif de la question s'il y en a",
     *                      type="string"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle question créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $this->hasAnyPermission(["all","edit-qestion-management"]);

        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|unique:base.questions,name',
                    'chapitre_id' => 'required',
                    'illustration' => 'nullable|mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/avi,video/mpeg|max:20480',
                    'code_illustration' => 'nullable|string',
                    'texte' => 'max:2000',
                ],
                [
                    'name.required' => 'Le nom de la question est obligatoire',
                    'name.unique' => 'Cette question existe déjà',
                    'chapitre_id.required' => 'Le chapitre de la question est obligatoire',
                    'illustration.mimetypes' => 'L\'illustration doit être une image ou une vidéo',
                    'code_illustration.string' => 'Le code illustratif doit être une chaîne de caractères',
                    'texte.max' => 'Le texte de la question ne doit pas dépasser 2000 caractères',
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), statuscode: 422);
            }

            $data = $validator->validated();

            // Enregistrer l'image d'illustration
            if ($request->hasFile('illustration')) {
                $file = $request->file('illustration');

                $extension = $file->getClientOriginalExtension();
                $data['illustration_type'] = in_array($extension, ['jpeg', 'jpg', 'png', 'gif']) ? 'image' : 'video';

                $imagePath = $file->store('illustrations', 'public');
                $data['illustration'] = $imagePath;
            }

            $question = Question::create($data);

            return $this->successResponse($question, statuscode: 201);
        } catch (\Throwable $e) {
            return $this->errorResponse("Une erreur s'est produite lors de la création", null, null, 500);
        }
    }



    /**
     * @OA\Post(
     *     path="/api/anatt-admin/questions/reponse",
     *     summary="Créer une réponse pour une question",
     *     description="Créer une nouvelle réponse pour une question spécifiée par son ID",
     *     security={{"api_key":{}}},
     *     tags={"Questions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="question_id", type="integer", description="ID de la question"),
     *             @OA\Property(property="reponse_id", type="integer", description="ID de la réponse"),
     *             @OA\Property(property="is_correct", type="boolean", description="Indicateur de réponse correcte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Réponse créée avec succès",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Question ou réponse introuvable",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur inattendue",
     *     )
     * )
     */
    public function createReponse(Request $request)
    {
        $this->hasAnyPermission(["all","edit-qestion-management"]);

        try {
            // Valider les champs requis
            $this->validate($request, [
                'question_id' => [
                    'required',
                    'integer',
                ],
                'reponse_id' => [
                    'required',
                    'integer',
                    Rule::exists('reponses', 'id'),
                    Rule::unique('question_reponses')->where(function ($query) use ($request) {
                        return $query->where('reponse_id', $request->input('reponse_id'))
                            ->where('question_id', $request->input('question_id'));
                    }),
                ],
                'is_correct' => 'required|boolean'
            ], [
                'question_id.required' => 'Une question est requise',
                'question_id.integer' => 'La question doit être un entier',
                'reponse_id.required' => 'Une réponse est requise',
                'reponse_id.integer' => 'La réponse doit être un entier',
                'reponse_id.exists' => 'La réponse n\'existe pas',
                'reponse_id.unique' => 'La réponse existe déjà',
                'is_correct.required' => 'L\'indicateur de réponse correcte est requis',
                'is_correct.boolean' => 'L\'indicateur de réponse correcte doit être un booléen'
            ]);

            // Vérifier si la question existe
            $questionId = $request->input('question_id');
            $question = Question::find($questionId);
            if (!$question) {
                return $this->errorResponse('Question introuvable', null, null, 422);
            }

            // Vérifier si la réponse existe
            $reponseId = $request->input('reponse_id');
            $reponse = Reponse::find($reponseId);
            if (!$reponse) {
                return $this->errorResponse('Réponse introuvable', null, null, 422);
            }

            // Créer un nouveau modèle de réponse lié à la question
            $question->reponses()->create([
                'reponse_id' => $reponseId,
                'is_correct' => $request->input('is_correct')
            ]);

            return $this->successResponse($reponse, 'Réponse créée avec succès');
        } catch (\Illuminate\Validation\ValidationException $ex) {
            // Capturer l'exception de validation
            return $this->errorResponse($ex->getMessage(), null, null, 422);
        } catch (\Throwable $th) {
            // Capturer toutes les autres exceptions
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }


    /**
     * @OA\Put(
     *     path="/api/anatt-admin/questions/update-reponse/{id}",
     *     summary="Mettre à jour une réponse liée à une question",
     *     security={{"api_key":{}}},
     *     tags={"Questions"},
     *     @OA\Parameter(
     *         name="id",
     *         description="ID de la réponse liée à une question",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="question_id",
     *                 type="integer",
     *                 description="ID de la question"
     *             ),
     *             @OA\Property(
     *                 property="reponse_id",
     *                 type="integer",
     *                 description="ID de la réponse"
     *             ),
     *             @OA\Property(
     *                 property="is_correct",
     *                 type="boolean",
     *                 description="Statut de correction de la réponse"
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Réponse mise à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 description="Message de succès"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 description="Message d'erreur de validation"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enregistrement non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 description="Message d'erreur pour un enregistrement non trouvé"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 description="Message d'erreur générique du serveur"
     *             )
     *         )
     *     ),
     * )
     */
    public function updateReponse(Request $request, $id)
    {
        $this->hasAnyPermission(["all","edit-qestion-management"]);

        try {
            // Valider les champs requis
            $this->validate($request, [
                'question_id' => [
                    'required',
                    'integer',
                    Rule::exists('base.questions', 'id')
                ],
                'reponse_id' => [
                    'required',
                    'integer',
                    Rule::exists('reponses', 'id'),
                    Rule::unique('question_reponses')->where(function ($query) use ($request, $id) {
                        return $query->where('reponse_id', $request->input('reponse_id'))
                            ->where('question_id', $request->input('question_id'))
                            ->where('id', '<>', $id);
                    }),
                ],
                'is_correct' => 'required|boolean'
            ], [
                'question_id.required' => 'La question n\'a pas été trouvée',
                'question_id.integer' => 'La question n\'est pas un entier',
                'reponse_id.required' => 'La réponse n\'a pas été trouvée',
                'reponse_id.integer' => 'La réponse n\'est pas un entier',
                'reponse_id.exists' => 'La réponse n\'existe pas',
                'reponse_id.unique' => 'La réponse existe déjà',
                'is_correct.required' => 'Le champ \'is_correct\' est requis',
                'is_correct.boolean' => 'Le champ \'is_correct\' doit être un booléen'
            ]);

            // Vérifier si la question existe
            $questionId = $request->input('question_id');
            $question = Question::find($questionId);
            if (!$question) {
                return $this->errorResponse('Question introuvable', null, null, 422);
            }

            // Vérifier si la réponse existe
            $reponseId = $request->input('reponse_id');
            $reponse = Reponse::find($reponseId);
            if (!$reponse) {
                return $this->errorResponse('Réponse introuvable', null, null, 422);
            }

            // Récupérer le modèle de la table question_reponses basé sur l'ID
            $questionReponse = QuestionReponse::find($id);

            // Vérifier si l'enregistrement existe
            if (!$questionReponse) {
                return $this->errorResponse('Question Réponse introuvable', null, null, 422);
            }

            // Mettre à jour le modèle de réponse lié à la question
            $questionReponse->update([
                'question_id' => $request->input('question_id'),
                'reponse_id' => $request->input('reponse_id'),
                'is_correct' => $request->input('is_correct')
            ]);

            return $this->successResponse($questionReponse, 'Réponse mise à jour avec succès');
        } catch (\Illuminate\Validation\ValidationException $ex) {
            // Capturer l'exception de validation
            return $this->errorResponse($ex->getMessage(), null, null, 400);
        } catch (\Throwable $th) {
            // Capturer toutes les autres exceptions
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-admin/questions/reponse/{id}",
     *      operationId="getQuestionReponsesById",
     *      tags={"Questions"},
     *      summary="Récupère une reponse par ID",
     *      description="Récupère une reponse enregistré dans la base de données en spécifiant son ID",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la reponse à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="reponse récupérée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="reponses non trouvée"
     *      )
     * )
     */
    public function showQuestionReponse($id)
    {

        try {
            // Récupérer le modèle de la table question_reponses basé sur l'ID
            $questionReponse = QuestionReponse::find($id);

            // Vérifier si l'enregistrement existe
            if (!$questionReponse) {
                return $this->errorResponse('Question Réponse introuvable', null, null, 422);
            }

            // Retourner la réponse avec le modèle de la table question_reponses
            return $this->successResponse($questionReponse, 'Question Réponse récupérée avec succès');
        } catch (\Throwable $th) {
            // Capturer toutes les autres exceptions
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-admin/questions/{id}",
     *      operationId="getQuestionsById",
     *      tags={"Questions"},
     *      summary="Récupère une question par ID",
     *      description="Récupère une question enregistré dans la base de données en spécifiant son ID",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la question à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="question récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="question non trouvé"
     *      )
     * )
     */
    public function show($id)
    {

        try {
            $question = Question::with(['reponses', 'audiolangues'])->find($id);
            if (!$question) {
                return $this->errorResponse('Question introuvable', null, null, 422);
            }
            return $this->successResponse($question);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }


    /**
     * @OA\Put(
     *      path="/api/anatt-admin/questions/{id}",
     *      operationId="updateQuestions",
     *      tags={"Questions"},
     *      summary="Met à jour une question existant",
     *      description="Met à jour une question existant dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la question à mettre à jour",
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
     *                  @OA\Property(
     *                      property="chapitre_id",
     *                      description="ID du chapitre",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="illustration",
     *                      description="une illustration de la question s'il y en a",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="code_illustration",
     *                      description="le code illustratif de l'image de la question s'il y en a",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="texte",
     *                      description="un texte descriptif de la question s'il y en a",
     *                      type="string"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Question mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Question non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $this->hasAnyPermission(["all","edit-qestion-management"]);

        try {
            $question = Question::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:base.questions,name,' . $question->id,
                'chapitre_id' => 'required',
                'illustration' => 'nullable|mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/avi,video/mpeg|max:20480',
                'code_illustration' => 'nullable|string',
                'texte' => 'max:2000',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), statuscode: 422);
            }

            $data = $validator->validated();

            // Mettre à jour l'image d'illustration
            if ($request->hasFile('illustration')) {
                if ($question->illustration) {
                    Storage::disk('public')->delete($question->illustration);
                }

                $file = $request->file('illustration');
                $extension = $file->getClientOriginalExtension();
                $data['illustration_type'] = in_array($extension, ['jpeg', 'jpg', 'png', 'gif']) ? 'image' : 'video';

                $imagePath = $file->store('illustrations', 'public');
                $data['illustration'] = $imagePath;
            }

            $question->update($data);
            return $this->successResponse($question, statuscode: 200);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, null, 500);
        }
    }


    /**
     * @OA\Delete(
     *      path="/api/anatt-admin/questions/{id}",
     *      operationId="deleteQuestions",
     *      tags={"Questions"},
     *      summary="Supprime une question",
     *      description="Supprime une question de la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la question à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Question supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Question non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(["all","edit-qestion-management"]);

        try {
            $question = Question::find($id);
            if (!$question) {
                return $this->errorResponse('Question introuvable', null, null, 422);
            }

            // Supprimez l'audio pour chaque langue de la question
            $question->questionlangue->each(function ($langue) {
                if ($langue->audio) {
                    Storage::disk('public')->delete($langue->audio);
                }
            });

            $question->reponses()->delete(); // Supprimer les réponses associées à la question
            $question->questionlangue()->delete(); // Supprimer les langues associées à la question
            $question->delete(); // Supprimer la question

            return $this->successResponse(['message' => 'Suppression effectuée']);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la suppression");
        }
    }

    /**
     * Activate or deactivate a question.
     *
     * @param int $id
     * @param string $action ('activate' or 'deactivate')
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(Request $request)
    {
        $this->hasAnyPermission(["all", "edit-question-management"]);

        $validator = Validator::make($request->all(), [
            'question_id' => 'required|integer|exists:base.questions,id',
            'action' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation des données échouée', $validator->errors(), null, 422);
        }

        try {
            $question = Question::find($request->question_id);

            if (!$question) {
                return $this->errorResponse('Question introuvable', null, null, 422);
            }

            $status = $request->action === 'active' ? 'active' : 'inactive';
            $question->status = $status;
            $question->save();

            $message = $request->action === 'active' ? 'Question activée avec succès' : 'Question désactivée avec succès';
            return $this->successResponse($question,['message' => $message]);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour du statut");
        }
    }

    public function regenereQuestionCompo(Request $request)
    {
        $response = Api::compo("POST", 'distribute-questions', $request->all());
        # Retrait des informations d'entete
        $message = $response->json("message", "Une erreur est survenue ");
        $data = $response->json('data', null);
        $errors = $response->json('errors', null);
        $statuscode = $response->status();

        # S'il y a une erreur on retourne l'erreur telle quell
        if (!$response->successful()) {
            return $this->errorResponse($message, $errors, $data, $statuscode);
        }

        # On recupère la bonne information
        $data = Api::data($response);

        return $this->successResponse($data, $message, $statuscode);
    }



    /**
     * Supprimer une question réponse.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @OA\Delete(
     *      path="/api/anatt-admin/questions/reponse/{id}",
     *      operationId="deleteQuestionReponse",
     *      tags={"Questions"},
     *      summary="Supprime une reponse d'une question",
     *      description="Supprime une réponse d'une question de la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la reponse à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Question reponse supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Question reponse non trouvé"
     *      )
     * )
     */
    public function deleteReponse($id)
    {
        try {
            $this->hasAnyPermission(["all","edit-qestion-management"]);
            // Récupérer le modèle de la table question_reponses basé sur l'ID
            $questionReponse = QuestionReponse::find($id);

            // Vérifier si l'enregistrement existe
            if (!$questionReponse) {
                return $this->errorResponse('Question Réponse introuvable', null, null, 422);
            }

            // Supprimer la question réponse
            $questionReponse->delete();

            return $this->successResponse(null, 'Question Réponse supprimée avec succès');
        } catch (\Illuminate\Database\QueryException $ex) {
            // Capturer l'exception de requête SQL
            if ($ex->getCode() === '23000') {
                // Vérifier si l'exception est due à une contrainte de clé étrangère
                return $this->errorResponse('Impossible de supprimer cette question réponse car elle est déjà utilisée', null, null, 400);
            } else {
                // Autre erreur de requête SQL
                logger()->error($ex);
                return $this->errorResponse("Une erreur inattendue s'est produite");
            }
        } catch (\Throwable $th) {
            // Capturer toutes les autres exceptions
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/anatt-admin/questions/audio/{id}",
     *      operationId="deleteQuestionsLangue",
     *      tags={"Questions"},
     *      summary="Supprime une assignation question-audio",
     *      description="Supprime une assignation question-audio de la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de question-audio à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Question-audio supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Question-audio non trouvé"
     *      )
     * )
     */
    public function destroyAssignation($id)
    {
        $this->hasAnyPermission(["all","edit-qestion-management"]);
        try {
            $questionLangue = QuestionLangue::find($id);

            if (!$questionLangue) {
                return $this->errorResponse('L\'enregistrement n\'existe pas.', [], 422);
            }

            // Supprimer le fichier audio s'il existe
            if ($questionLangue->audio) {
                Storage::disk('public')->delete($questionLangue->audio);
            }

            $questionLangue->delete();

            return $this->successResponse(null, 'Enregistrement supprimé avec succès.');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la suppression.', null, 500);
        }
    }
}
