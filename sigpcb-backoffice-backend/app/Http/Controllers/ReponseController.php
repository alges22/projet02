<?php

namespace App\Http\Controllers;

use App\Models\Reponse;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Exception;
use Illuminate\Support\Facades\Validator;

class ReponseController extends  ApiController
{

    /**
     * @OA\Get(
     *     path="/api/anatt-admin/reponses",
     *     operationId="getAllReponses",
     *     tags={"Reponses"},
     *     summary="Récupérer la liste des reponses",
     *     description="Récupère une liste de toutes les reponses enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des reponses récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la reponse",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="un libellé",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="couleur",
     *                      description="code couleur",
     *                      type="string"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $this->hasAnyPermission(["all","read-reponses-possible-management","edit-reponses-possible-management"]);

        try {
            $responses = Reponse::orderBy('name', 'asc')->get();
            return $this->successResponse($responses);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/reponses",
     *      operationId="createReponses",
     *      tags={"Reponses"},
     *      summary="Crée une reponse",
     *      description="Crée une nouvelle reponse enregistré dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="name",
     *                      description="un libellé",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="couleur",
     *                      description="code couleur",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle reponse créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $this->hasAnyPermission(["all","edit-reponses-possible-management"]);

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:reponses,name',
                'couleur' => 'required|string|unique:reponses,couleur',
            ],
        [
                'name.required' => 'Le libellé est obligatoire',
                'name.string' => 'Le libellé doit être un texte',
                'name.unique' => 'Le libellé doit être unique',
                'couleur.required' => 'La couleur est obligatoire',
                'couleur.string' => 'La couleur doit être un texte',
                'couleur.unique' => 'La couleur doit être unique',

        ]);
            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }

            $reponse = Reponse::create($request->all());

            return $this->successResponse($reponse, 'Réponse créée avec succès', 201);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Erreur lors de la création', 500);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/anatt-admin/reponses/{id}",
     *      operationId="updateReponses",
     *      tags={"Reponses"},
     *      summary="Met à jour une reponse existant",
     *      description="Met à jour une reponse existant dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la reponse à mettre à jour",
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
     *                      property="name",
     *                      description="un libellé",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="couleur",
     *                      description="code couleur",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Réponse mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Réponse non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $this->hasAnyPermission(["all","edit-reponses-possible-management"]);

        try {
            $validator = Validator::make($request->all(), [
                'name' => "required|string|unique:reponses,name,$id",
                'couleur' => "required|string|unique:reponses,couleur,$id"
            ],
        [
                'name.required' => 'Le libellé est obligatoire',
                'name.string' => 'Le libellé doit être un texte',
                'name.unique' => 'Le libellé existe déjà',
                'couleur.required' => 'La couleur est obligatoire',
                'couleur.string' => 'La couleur doit être un texte',
                'couleur.unique' => 'La couleur existe déjà'

        ]);
            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }

            $response = Reponse::find($id);
            if (!$response) {
                return $this->errorResponse('Réponse introuvable', statuscode: 422);
            }

            $response->update($request->all());

            return $this->successResponse($response, 'Réponse mise à jour');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour");
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-admin/reponses/{id}",
     *      operationId="getReponsesById",
     *      tags={"Reponses"},
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
     *          description="reponse récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="reponse non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        $this->hasAnyPermission(["all","read-reponses-possible-management","edit-reponses-possible-management"]);

        try {
            $response = Reponse::find($id);
            if (!$response) {
                return $this->errorResponse('Réponse introuvable', null, null, 422);
            }
            return $this->successResponse($response);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/anatt-admin/reponses/{id}",
     *      operationId="deleteReponses",
     *      tags={"Reponses"},
     *      summary="Supprime une reponse",
     *      description="Supprime une reponse de la base de données",
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
     *          description="Réponse supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Réponse non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(["all","edit-reponses-possible-management"]);

        try {
            $response = Reponse::find($id);
            if (!$response) {
                return $this->errorResponse('Réponse introuvable', null, null, 422);
            }

            $response->delete();
            return $this->successResponse(['message' => 'Réponse supprimée avec succès']);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la suppression");
        }
    }
}
