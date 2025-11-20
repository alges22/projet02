<?php

namespace App\Http\Controllers;

use App\Models\Langue;
use App\Models\Admin\Examen;
use Illuminate\Http\Request;
use App\Models\QuestionLangue;
use Illuminate\Validation\Rule;
use App\Http\Controllers\ApiController;
use App\Models\Candidat\DossierSession;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LangueController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/anatt-base/langues",
     *     operationId="getAllLangues",
     *     tags={"Langues"},
     *     summary="Récupérer la liste des langues",
     *     description="Récupère une liste de tous les langues enregistrées dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des langues récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la langue",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de la langue",
     *                      type="string",
     *                      example="Ain"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut de la langue (optionnel)",
     *                      type="boolean"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $langues = Langue::orderByDesc('id')->get();
            return $this->successResponse($langues);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }


    public function checkIfLangueExists($langueName) {
        $existingLangue = Langue::whereRaw('LOWER(name) LIKE ?', [strtolower($langueName)])->first();
        if ($existingLangue) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Post(
     *      path="/api/anatt-base/langues",
     *      operationId="createLangue",
     *      tags={"Langues"},
     *      summary="Crée une nouvelle langue",
     *      description="Crée une nouvelle langue enregistrée dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(
     *                  property="name",
     *                  type="string",
     *                  description="Nom de la langue (obligatoire)"
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  type="boolean",
     *                  description="Statut de la langue (optionnel)"
     *                 )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle langue créée"
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'unique:langues,name'
                ],
                'status' => [
                    'required',
                ]
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom de la langue existe déjà.',
                'status.required' => 'Le champ status est obligatoire.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }
            $langueName = $request->input('name');
            $languestatus = $request->input('status');
            if ($this->checkIfLangueExists($langueName)) {
                $newLangue = new Langue();
                $newLangue->name = $langueName;
                $newLangue->status = $languestatus;
                $newLangue->save();
                // return 'La langue a été ajoutée avec succès.';
            return $this->successResponse($newLangue, 'Langue créée avec succès.', 200);

            } else {
                // return 'Une langue portant ce nom existe déjà.';
            return $this->errorResponse('Une langue portant ce nom existe déjà.');

            }

            // $langue = Langue::create($request->all());
            // return $this->successResponse($langue, 'Langue créée avec succès.', 200);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/api/anatt-base/langues/{id}",
     *      operationId="getLangueById",
     *      tags={"Langues"},
     *      summary="Récupère une langue par ID",
     *      description="Récupère une langue enregistrée dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la langue à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Langue récupérée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Langue non trouvée"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            try {
                $langue = Langue::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La langue avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($langue);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Put(
     *      path="/api/anatt-base/langues/{id}",
     *      operationId="updateLangue",
     *      tags={"Langues"},
     *      summary="Met à jour une langue existante",
     *      description="Met à jour une langue existante dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la langue à mettre à jour",
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
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom de la langue",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  type="boolean",
     *                  description="Statut de la langue (optionnel)"
     *               )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Langue mise à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Langue non trouvée"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('langues')->ignore($id)
                ],
                'status' => [
                    'required',
                ]
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom de la langue existe déjà.',
                'status.required' => 'Le champ statut est obligatoire.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),'', 422);
            }

            $langue = Langue::find($id);

            if (!$langue) {
                return $this->errorResponse("La langue spécifiée n'a pas été trouvée.", 'La langue spécifiée n\'a pas été trouvée.', null, 404);
            }

            $langueName = $request->input('name');
            $langueStatus = $request->input('status');

            if ($this->checkIfLangueExists($langueName)) {
                $langue->name = $langueName;
                $langue->status = $langueStatus;
                $langue->save();
            } else {
                return $this->errorResponse('Une langue portant ce nom existe déjà.', 'Une langue portant ce nom existe déjà.', null, 422);
            }

            return $this->successResponse($langue, 'Langue mise à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour de la langue.');
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Delete(
     *      path="/api/anatt-base/langues/{id}",
     *      operationId="deleteLangue",
     *      tags={"Langues"},
     *      summary="Supprime une langue",
     *      description="Supprime une langue de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la langue à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Langue supprimée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Langue non trouvée"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            $langue = Langue::findOrFail($id);

            // Vérifier si la langue est associée à des enregistrements dans la table question_langue
            $hasQuestions = QuestionLangue::where('langue_id', $id)->exists();
            if ($hasQuestions) {
                return $this->errorResponse('Impossible de supprimer cette langue car elle est déjà utilisée ailleurs.', [], null, 422);
            }

            // Supprimer la langue si elle n'est pas associée à des enregistrements dans la table question_langue
            $langue->delete();

            return $this->successResponse($langue, 'La langue a été supprimée avec succès.');
        } catch (ModelNotFoundException $exception) {
            return $this->errorResponse('La langue avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }


    private function languesExist(string $langue_ids)
    {
        $ids = explode(";", $langue_ids);
        // Si tous les users exists
        return collect($ids)->every(fn ($id) => Langue::whereId(intval($id))->exists());
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/langues/status",
     *      operationId="createLangueStatus",
     *      tags={"Langues"},
     *      summary="Désactivation ou activation d'une langue",
     *      description="Désactivation ou activation d'une langue",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="langue_id",
     *                      description="id de la langue",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="le statut a modifier",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Mise à jour éffectué avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="la langue n'a pas été trouvé"
     *      )
     * )
     */
    public function status(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'langue_id' => 'required',
                'status' => 'required'
            ], [
                'langue_id.required' => 'Aucune langue n\'a été sélectionnée',
                'status.required' => 'Aucun statut n\'a été envoyé'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), '', 419);
            }
            if($request->get('status') == false){

                $examens = Examen::where('closed', false)->get();
                // Par défaut, on permet la désactivation de la langue
                $canDeactivate = true;

                foreach ($examens as $examen) {
                    $examenId = $examen->id;
                    $dossierSession = DossierSession::where('examen_id', $examenId)
                        ->where('closed', false)
                        ->where('abandoned', false)
                        ->where('langue_id', $request->get('langue_id'))
                        ->first();

                    // Si un dossier de session utilise cette langue, on ne peut pas désactiver la langue
                    if ($dossierSession) {
                        $canDeactivate = false;
                        break; // Sortir de la boucle dès qu'on trouve un dossier de session
                    }
                }

                // Si aucun dossier de session n'utilise cette langue, on peut la désactiver
                if ($canDeactivate) {
                    return $this->updateLangue($request);
                } else {
                    return $this->errorResponse('La langue ne peut pas être désactivée car des examens sont en cours pour cette langue');
                }
            }
            return $this->updateLangue($request);

        } catch (\Throwable $th) {
            // Gérer l'erreur et la journaliser
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour");
        }
    }

    private function updateLangue(Request $request)
    {
        $langue_id = $request->input('langue_id');
        $status = $request->input('status');

        if (!$this->languesExist($langue_id)) {
            return $this->errorResponse('Vérifiez que la langue sélectionnée existe');
        }

        Langue::where('id', $langue_id)->update(['status' => $status]);

        $langue = Langue::findOrFail($langue_id); // récupérer la langue mis à jour
        return $this->successResponse(['langue' => $langue, 'message' => 'Mise à jour effectuée avec succès']);
    }

}
