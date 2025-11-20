<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\Departement;
use Illuminate\Support\Facades\Validator;

class DepartementController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/api/anatt-base/departements",
     *      operationId="getAllDepartments",
     *      tags={"Départements"},
     *      summary="Récupère tous les départements",
     *      description="Récupère une liste de tous les départements enregistrés dans la base de données",
     *      @OA\Response(
     *          response=200,
     *          description="Liste de tous les départements",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du département",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom du département",
     *                      type="string",
     *                      example="Ain"
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = Departement::query();
    
            if ($request->has('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'ILIKE', $searchTerm);
                });
            }
    
            $departements = $query->orderBy('name', 'asc')->get();
    
            if ($departements->isEmpty()) {
                return $this->successResponse([],"Aucun résultat trouvé", 200);

            }
    
            return $this->successResponse($departements);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
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
     *      path="/api/anatt-base/departements",
     *      operationId="createDepartment",
     *      tags={"Départements"},
     *      summary="Crée un nouveau département",
     *      description="Crée un nouveau département enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom du département",
     *                  type="string",
     *                  example="Ain"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau département créé",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du nouveau département créé",
     *                  type="integer",
     *                  example=2
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom du nouveau département créé",
     *                  type="string",
     *                  example="Aisne"
     *              )
     *          )
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'unique:departements,name'
                ],
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom du département existe déjà.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors());
            }

            $departement = Departement::create($request->all());
            return $this->successResponse($departement, 'Département créé avec succès.');
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
     *      path="/api/anatt-base/departements/{id}",
     *      operationId="getDepartmentById",
     *      tags={"Départements"},
     *      summary="Récupère un département par ID",
     *      description="Récupère un département enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du département à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Département récupéré avec succès",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du département",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom du département",
     *                  type="string",
     *                  example="Ain"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Département non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            try {
                $departement = Departement::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le département avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($departement);
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
     *      path="/api/anatt-base/departements/{id}",
     *      operationId="updateDepartment",
     *      tags={"Départements"},
     *      summary="Met à jour un département existant",
     *      description="Met à jour un département existant dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du département à mettre à jour",
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
     *                  description="Nom du département",
     *                  type="string",
     *                  example="Ain"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Département mis à jour avec succès",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du département mis à jour",
     *                  type="integer",
     *                  example=2
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom du département mis à jour",
     *                  type="string",
     *                  example="Aisne"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Département non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('departements')->ignore(intval($id)),
                ],
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom du département existe déjà.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors());
            }
            try {
                $departement = Departement::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le département avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            $departement->update($request->all());
            return $this->successResponse($departement, 'Département mis à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
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
     *      path="/api/anatt-base/departements/{id}",
     *      operationId="deleteDepartment",
     *      tags={"Départements"},
     *      summary="Supprime un département",
     *      description="Supprime un département de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du département à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Département supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Département non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            try {
                $departement = Departement::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le département avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            $departement->delete();
            return $this->successResponse($departement, 'Le département a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
