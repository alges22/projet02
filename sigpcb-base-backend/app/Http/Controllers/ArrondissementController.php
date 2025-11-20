<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\Arrondissement;
use Illuminate\Support\Facades\Validator;

class ArrondissementController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/api/anatt-base/arrondissements",
     *      operationId="getArrondissements",
     *      tags={"Arrondissements"},
     *      summary="Obtient la liste des arrondissements",
     *      description="Obtient la liste des arrondissements enregistrés dans la base de données",
     *      @OA\Response(
     *          response=200,
     *          description="Liste des arrondissements",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'arrondissement",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de l'arrondissement",
     *                      type="string",
     *                      example="1er arrondissement"
     *                  ),
     *                  @OA\Property(
     *                      property="commune_id",
     *                      description="ID de la commune à laquelle appartient l'arrondissement",
     *                      type="integer",
     *                      example=1
     *                  )
     *              )
     *          )
     *      )
     * )
     */     
    public function index(Request $request)
    {
        try {
            $query = Arrondissement::query()->with('commune');
            
            if ($request->has('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'ILIKE', $searchTerm)
                        ->orWhereHas('commune', function ($query) use ($searchTerm) {
                            $query->where('name', 'ILIKE', $searchTerm);
                        });
                });
            }

            // Ajout du GROUP BY sur departement_id
            $query->orderBy('commune_id', 'asc')->groupBy(['commune_id', 'id']);
            
            if (request('liste') == 'paginate') {
                $arrondissements = $query->orderBy('name', 'asc')->paginate(10);
            } else {
                $arrondissements = $query->orderBy('name', 'asc')->get();
            }
    
            if ($arrondissements->isEmpty()) {
                return $this->successResponse([],"Aucun résultat trouvé", 200);
            }
            
            return $this->successResponse($arrondissements);
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
     *      path="/api/anatt-base/arrondissements",
     *      operationId="createArrondissement",
     *      tags={"Arrondissements"},
     *      summary="Crée un nouvel arrondissement",
     *      description="Crée un nouvel arrondissement enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom de l'arrondissement",
     *                  type="string",
     *                  example="1er arrondissement"
     *              ),
     *              @OA\Property(
     *                  property="commune_id",
     *                  description="ID de la commune à laquelle appartient l'arrondissement",
     *                  type="integer",
     *                  example=1
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvel arrondissement créé",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID du nouvel arrondissement créé",
     *                  type="integer",
     *                  example=2
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom du nouvel arrondissement créé",
     *                  type="string",
     *                  example="2ème arrondissement"
     *              ),
     *              @OA\Property(
     *                  property="commune_id",
     *                  description="ID de la commune à laquelle appartient le nouvel arrondissement créé",
     *                  type="integer",
     *                  example=1
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
                    Rule::unique('arrondissements')->where(function ($query) use ($request) {
                        return $query->where('commune_id', $request->commune_id);
                    })
                ],
                'commune_id' => 'required|exists:communes,id'
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom de l\'arrondissement existe déjà pour cette commune.',
                'commune_id.required' => 'Le champ commune est obligatoire.',
                'commune_id.exists' => 'La commune sélectionnée n\'existe pas.'
            ]);
    
            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors());
            }
    
            $arrondissement = Arrondissement::create($request->all());
            return $this->successResponse($arrondissement, 'Arrondissement créé avec succès.');
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
     *      path="/api/anatt-base/arrondissements/{id}",
     *      operationId="getArrondissementById",
     *      tags={"Arrondissements"},
     *      summary="Affiche les détails d'un arrondissement",
     *      description="Affiche les détails d'un arrondissement enregistré dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'arrondissement à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Détails de l'arrondissement récupéré",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID de l'arrondissement",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom de l'arrondissement",
     *                  type="string",
     *                  example="Arrondissement 1"
     *              ),
     *              @OA\Property(
     *                  property="commune_id",
     *                  description="ID de la commune à laquelle appartient l'arrondissement",
     *                  type="integer",
     *                  example=2
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Arrondissement non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            try {
                $arrondissement = Arrondissement::with(['commune'])->findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'arrondissement avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($arrondissement);
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
     *      path="/api/anatt-base/arrondissements/{id}",
     *      operationId="updateArrondissement",
     *      tags={"Arrondissements"},
     *      summary="Mettre à jour un arrondissement",
     *      description="Met à jour un arrondissement enregistré dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'arrondissement",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom de l'arrondissement",
     *                  type="string",
     *                  example="Villejuif"
     *              ),
     *              @OA\Property(
     *                  property="commune_id",
     *                  description="ID de la commune associée à l'arrondissement",
     *                  type="integer",
     *                  example=12
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Arrondissement mis à jour",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="id",
     *                  description="ID de l'arrondissement mis à jour",
     *                  type="integer",
     *                  example=2
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom de l'arrondissement mis à jour",
     *                  type="string",
     *                  example="Villejuif"
     *              ),
     *              @OA\Property(
     *                  property="commune_id",
     *                  description="ID de la commune associée à l'arrondissement mis à jour",
     *                  type="integer",
     *                  example=12
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Arrondissement non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('arrondissements')->ignore(intval($id)),
                ],
                'commune_id' => 'required|exists:communes,id'
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom de l\'arrondissement existe déjà.',
                'commune_id.required' => 'Le champ commune est obligatoire.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors());
            }


            try {
                $arrondissement = Arrondissement::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'arrondissement avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 404);
            }
            $arrondissement->update($request->all());
            return $this->successResponse($arrondissement, 'Arrondissement mise à jour avec succès.');
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
     *      path="/api/anatt-base/arrondissements/{id}",
     *      operationId="deleteArrondissement",
     *      tags={"Arrondissements"},
     *      summary="Supprime un arrondissement existant",
     *      description="Supprime l'arrondissement spécifié de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'arrondissement à supprimer",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Arrondissement supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Arrondissement non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            try {
                $arrondissement = Arrondissement::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'arrondissement avec l\'ID ' . $id . ' n\'a pas été trouvé.', [], null, 404);
            }
            $arrondissement->delete();
            return $this->successResponse($arrondissement, 'L\'arrondissement a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
