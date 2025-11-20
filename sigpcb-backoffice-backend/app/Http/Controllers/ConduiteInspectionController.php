<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ConduiteInspection;

class ConduiteInspectionController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-admin/conduite-inspections",
     *     operationId="getAllConduiteInspections",
     *     tags={"ConduiteInspections"},
     *     summary="Récupérer la liste des conduite-inspections",
     *     description="Récupère une liste de tous les conduite-inspections enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des conduite-inspections récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la conduite-inspection",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Status de l'conduite-inspection (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="observations",
     *                      description="Nom de la conduite-inspection",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="inspecteur_id",
     *                      description="ID de l'inspecteur",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="vague_id",
     *                      description="ID de la vague",
     *                      type="integer"
     *                  ),
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $inspections = ConduiteInspection::all();
            return $this->successResponse($inspections);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/conduite-inspections",
     *      operationId="createConduiteInspections",
     *      tags={"ConduiteInspections"},
     *      summary="Crée un conduite-inspections",
     *      description="Crée un nouveau conduite-inspections enregistré dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="status",
     *                      description="Status de l'conduite-inspection (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="observations",
     *                      description="Nom de la conduite-inspection",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="inspecteur_id",
     *                      description="ID de l'inspecteur",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="vague_id",
     *                      description="ID de la vague",
     *                      type="integer"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau conduite-inspections créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required',
                'observations' => 'required',
                'inspecteur_id' => 'required',
                'vague_id' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }

            $inspection = ConduiteInspection::create($validator->validated());

            return $this->successResponse($inspection, statuscode: 201);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la création", null, null, 500);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/anatt-admin/conduite-inspections/{id}",
     *      operationId="updateConduiteInspections",
     *      tags={"ConduiteInspections"},
     *      summary="Met à jour un conduite-inspection existant",
     *      description="Met à jour un conduite-inspection existant dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la conduite-inspection à mettre à jour",
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
     *                      property="status",
     *                      description="Status de l'conduite-inspection (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="observations",
     *                      description="Nom de la conduite-inspection",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="inspecteur_id",
     *                      description="ID de l'inspecteur",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="vague_id",
     *                      description="ID de la vague",
     *                      type="integer"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Conduite-inspection mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Conduite-inspection non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required',
                'observations' => 'required',
                'inspecteur_id' => 'required',
                'vague_id' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }

            $inspection = ConduiteInspection::findOrFail($id);
            $inspection->update($validator->validated());

            return $this->successResponse($inspection, statuscode: 201);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour", null, null, 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-admin/conduite-inspections/{id}",
     *      operationId="getConduiteInspectionsById",
     *      tags={"ConduiteInspections"},
     *      summary="Récupère un conduite-inspection par ID",
     *      description="Récupère un conduite-inspection enregistré dans la base de données en spécifiant son ID",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la conduite-inspection à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="conduite-inspection récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="conduite-inspection non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            $inspection = ConduiteInspection::find($id);
            if (!$inspection) {
                return $this->errorResponse('Conduite introuvable', null, null, 422);
            }
            return $this->successResponse($inspection);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/anatt-admin/conduite-inspections/{id}",
     *      operationId="deleteConduiteInspections",
     *      tags={"ConduiteInspections"},
     *      summary="Supprime un conduite-inspection",
     *      description="Supprime un conduite-inspection de la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la conduite-inspection à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Conduite-inspection supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Conduite-inspection non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            $inspection = ConduiteInspection::find($id);
            if (!$inspection) {
                return $this->errorResponse('Conduite introuvable', null, null, 422);
            }

            $inspection->delete();
            return $this->successResponse(true, 'Suppression effectuée');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la suppression");
        }
    }
}
