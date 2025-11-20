<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\CentreExamen;
use Illuminate\Support\Facades\Validator;

class CentreExamenController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/anatt-base/centre-examens",
     *     operationId="getAllCentreExamens",
     *     tags={"CentreExamens"},
     *     summary="Récupérer la liste des centres d'examen",
     *     description="Récupère une liste de tous les centres d'examen enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des centres d'examen récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du centre",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom du centre",
     *                      type="string"
     *                  ),
     *                 @OA\Property(
     *                     property="commune_id",
     *                     description="ID de la commune à laquelle appartient le nouveau centre créé",
     *                     type="integer"
     *                 ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut du centre (optionnel)",
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
            $centre_examens = CentreExamen::with(['commune'])->get();
            return $this->successResponse($centre_examens);
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
     *      path="/api/anatt-base/centre-examens",
     *      operationId="createCentreExamen",
     *      tags={"CentreExamens"},
     *      summary="Crée un nouveau centre d'examen",
     *      description="Crée un nouveau centre d'examen enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom du centre d'examen",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="commune_id",
     *                  description="ID de la commune à laquelle appartient le nouveau centre créé",
     *                  type="integer"
     *                 ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut du centre (optionnel)",
     *                      type="boolean"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau centre d'examen créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'unique:centre_examens,name'
                ],
                'commune_id' => 'required|exists:communes,id',
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom du centre d\'examen existe déjà.',
                'commune_id.required' => 'Le champ commune est obligatoire.',
                'commune_id.exists' => 'La commune sélectionnée n\'existe.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }

            $centre_examen = CentreExamen::create($request->all());
            return $this->successResponse($centre_examen, 'Centre d\'examen créé avec succès.');
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
     *      path="/api/anatt-base/centre-examens/{id}",
     *      operationId="getCentreExamenById",
     *      tags={"CentreExamens"},
     *      summary="Récupère un centre par ID",
     *      description="Récupère un centre enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du centre à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Centre d'examen récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Centre d'examen non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            try {
                $centre_examen = CentreExamen::with(['commune'])->findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le centre d\'examen avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($centre_examen);
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
     *      path="/api/anatt-base/centre-examens/{id}",
     *      operationId="updateCentreExamen",
     *      tags={"CentreExamens"},
     *      summary="Met à jour un centre existant",
     *      description="Met à jour un centre existant dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du centre à mettre à jour",
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
     *                  description="Nom du centre d'examen",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="commune_id",
     *                  description="ID de la commune à laquelle appartient le nouveau centre créé",
     *                  type="integer"
     *                 ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut du centre (optionnel)",
     *                      type="boolean"
     *                  )
     *             )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Centre d'examen mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Centre d'examen non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('centre_examens')->ignore(intval($id)),
                ],
                'commune_id' => 'required|exists:communes,id'
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom du centre d\'examen existe déjà.',
                'commune_id.required' => 'Le champ commune est obligatoire.',
                'commune_id.exists' => 'La commune sélectionnée n\'existe.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }

            try {
                $centre_examen = CentreExamen::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le centre d\'examen avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            $centre_examen->update($request->all());
            return $this->successResponse($centre_examen, 'Centre d\'examen mis à jour avec succès.');
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
     *      path="/api/anatt-base/centre-examens/{id}",
     *      operationId="deleteCentreExamen",
     *      tags={"CentreExamens"},
     *      summary="Supprime un centre",
     *      description="Supprime un centre de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du centre à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Centre d'examen supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Centre d'examen non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            try {
                $centre_examen = CentreExamen::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le centre d\'examen avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            $centre_examen->delete();
            return $this->successResponse($centre_examen, 'Le centre d\'examen a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
