<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\Commune;
use Illuminate\Support\Facades\Validator;

class CommuneController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/api/anatt-base/communes",
     *      operationId="getCommunes",
     *      tags={"Communes"},
     *      summary="Obtient la liste de toutes les communes",
     *      description="Obtient la liste de toutes les communes enregistrées dans la base de données",
     *      @OA\Response(
     *          response=200,
     *          description="Liste des communes récupérée avec succès",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la commune",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de la commune",
     *                      type="string",
     *                      example="Paris"
     *                  ),
     *                  @OA\Property(
     *                      property="departement_id",
     *                      description="ID du département de la commune",
     *                      type="integer",
     *                      example=75
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = Commune::query()->with('departement');
            
            if ($request->has('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'ILIKE', $searchTerm)
                        ->orWhereHas('departement', function ($query) use ($searchTerm) {
                            $query->where('name', 'ILIKE', $searchTerm);
                        });
                });
            }
    
            // Ajout du GROUP BY sur departement_id et id
            $query->orderBy('departement_id', 'asc')->groupBy(['departement_id', 'id']);
    
            if (request('liste') == 'paginate') {
                $communes = $query->orderBy('name', 'asc')->paginate(10);
            } else {
                $communes = $query->orderBy('name', 'asc')->get();
            }
    
            if ($communes->isEmpty()) {
                return $this->successResponse([],"Aucun résultat trouvé", 200);
            }
            
            return $this->successResponse($communes);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
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
     *      path="/api/anatt-base/communes",
     *      operationId="createCommune",
     *      tags={"Communes"},
     *      summary="Crée une nouvelle commune",
     *      description="Crée une nouvelle commune enregistrée dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom de la commune",
     *                  type="string",
     *                  example="Lyon"
     *              ),
     *              @OA\Property(
     *                  property="departement_id",
     *                  description="ID du département auquel appartient la commune",
     *                  type="integer",
     *                  example=69
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle commune créée",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID de la nouvelle commune créée",
     *                  type="integer",
     *                  example=123
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom de la nouvelle commune créée",
     *                  type="string",
     *                  example="Lyon"
     *              ),
     *              @OA\Property(
     *                  property="departement_id",
     *                  description="ID du département auquel appartient la nouvelle commune créée",
     *                  type="integer",
     *                  example=69
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
                    Rule::unique('communes')->where(function ($query) use ($request) {
                        return $query->where('departement_id', $request->departement_id);
                    }),
                ],
                'departement_id' => [
                    'required',
                    'exists:departements,id'
                ]
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom de la commune existe déjà pour ce département.',
                'departement_id.required' => 'Le champ département est obligatoire.',
                'departement_id.exists' => 'Le département sélectionné n\'existe.',
            ]);            

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors());
            }

            $commune = Commune::create($request->all());
            return $this->successResponse($commune, 'Commune créée avec succès.');
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
     *      path="/api/anatt-base/communes/{id}",
     *      operationId="getCommuneById",
     *      tags={"Communes"},
     *      summary="Récupère une commune par son ID",
     *      description="Récupère les informations d'une commune enregistrée dans la base de données par son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la commune à récupérer",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Informations de la commune",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID de la commune",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom de la commune",
     *                  type="string",
     *                  example="Paris"
     *              ),
     *              @OA\Property(
     *                  property="departement_id",
     *                  description="ID du département de la commune",
     *                  type="integer",
     *                  example=75
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Commune non trouvée"
     *      )
     * )
     */

    public function show($id)
    {
        try {
            try {
                $commune = Commune::with(['departement'])->findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La commune avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($commune);
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
     *      path="/api/anatt-base/communes/{id}",
     *      operationId="updateCommune",
     *      tags={"Communes"},
     *      summary="Modifie une commune existante",
     *      description="Modifie une commune existante enregistrée dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la commune à modifier",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              example=1
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom de la commune",
     *                  type="string",
     *                  example="Lyon"
     *              ),
     *              @OA\Property(
     *                  property="departement_id",
     *                  description="ID du département auquel appartient la commune",
     *                  type="integer",
     *                  example=69
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Commune modifiée avec succès",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID de la commune modifiée",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom de la commune modifiée",
     *                  type="string",
     *                  example="Lyon"
     *              ),
     *              @OA\Property(
     *                  property="departement_id",
     *                  description="ID du département auquel appartient la commune modifiée",
     *                  type="integer",
     *                  example=69
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Commune non trouvée",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="message",
     *                  description="Message d'erreur",
     *                  type="string",
     *                  example="La commune spécifiée n'a pas été trouvée."
     *              )
     *          )
     *      )
     * )
     */

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('communes')->ignore(intval($id)),
                ],
                'departement_id' => 'required|exists:departements,id'
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom de la commune existe déjà.',
                'departement_id.required' => 'Le champ département est obligatoire.',
                'departement_id.exists' => 'Le département sélectionné n\'existe.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors());
            }

            try {
                $commune = Commune::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La commune avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            $commune->update($request->all());
            return $this->successResponse($commune, 'Commune mise à jour avec succès.');
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
     *      path="/api/anatt-base/communes/{id}",
     *      operationId="deleteCommune",
     *      tags={"Communes"},
     *      summary="Supprime une commune",
     *      description="Supprime une commune enregistrée dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la commune à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *              example=1
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Commune supprimée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Commune non trouvée"
     *      )
     * )
     */

    public function destroy($id)
    {
        try {
            try {
                $commune = Commune::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La commune avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            $commune->delete();
            return $this->successResponse($commune, 'La commune a été supprimée avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
