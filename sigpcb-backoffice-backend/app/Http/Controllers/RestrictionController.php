<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Reponse;
use App\Models\Restriction;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RestrictionController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-admin/restrictions",
     *     operationId="getAllRestrictions",
     *     tags={"Restrictions"},
     *     summary="Récupérer la liste des restrictions",
     *     description="Récupère une liste de toutes les restrictions enregistrées dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des restrictions récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la restriction",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de la restriction",
     *                      type="string"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $this->hasAnyPermission(["all","read-restrictions-management","edit-restrictions-management"]);

        try {
            $restrictions = Restriction::orderBy('id','desc')->get();
            return $this->successResponse($restrictions);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }


    public function checkIfExists($Name) {
        $existing = Restriction::whereRaw('LOWER(name) LIKE ?', [strtolower($Name)])->first();
        if ($existing) {
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
     *      path="/api/anatt-admin/restrictions",
     *      operationId="createRestrictions",
     *      tags={"Restrictions"},
     *      summary="Crée une nouvelle restriction",
     *      description="Crée une nouvelle restriction enregistrée dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de la restriction",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle restriction créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $this->hasAnyPermission(["all","edit-restrictions-management"]);

        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'unique:restrictions,name'
                ],
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Cette restriction existe déjà.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), null, 422);
            }
            $Name = $request->input('name');
            if ($this->checkIfExists($Name)) {
                $restriction = new Restriction();
                $restriction->name = $Name;
                $restriction->save();
            } else {
            return $this->errorResponse("Cette restriction existe déjà.",'Cette restriction existe déjà.',null, 422);

            }
            return $this->successResponse($restriction, 'Restriction créé avec succès.');
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
     *      path="/api/anatt-admin/restrictions/{id}",
     *      operationId="getRestrictionsById",
     *      tags={"Restrictions"},
     *      summary="Récupère une Restriction par ID",
     *      description="Récupère une Restriction enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la Restriction à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Restriction récupérée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Restriction non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        $this->hasAnyPermission(["all", "read-restrictions-management","edit-restrictions-management"]);

        try {
            try {
                $restriction = Restriction::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La restriction avec n\'a pas été trouvé.', [], null, 422);
            }
            return $this->successResponse($restriction);
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
     *      path="/api/anatt-admin/restrictions/{id}",
     *      operationId="updateRestrictions",
     *      tags={"Restrictions"},
     *      summary="Met à jour une Restriction existante",
     *      description="Met à jour une Restriction existant dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la Restriction à mettre à jour",
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
     *                      description="Nom de la Restrictions",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Restriction mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Restriction non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $this->hasAnyPermission(["all","edit-restrictions-management"]);

        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('restrictions')->ignore($id)
                ],
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Cette restriction existe déjà.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), null, 422);
            }

            $restriction = Restriction::find($id);

            if (!$restriction) {
                return $this->errorResponse("La Restriction spécifiée n'a pas été trouvé.", 'La Restriction spécifiée n\'a pas été trouvé.', null, 422);
            }

            $Name = $request->input('name');

            if ($this->checkIfExists($Name, $id)) {
                $restriction->name = $Name;
                $restriction->save();
            } else {
                return $this->errorResponse("Cette restriction existe déjà.", 'Cette restriction existe déjà.', null, 422);
            }

            return $this->successResponse($restriction, 'Restriction mise à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour de la Restriction.');
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
     *      path="/api/anatt-admin/restrictions/{id}",
     *      operationId="deleteRestrictions",
     *      tags={"Restrictions"},
     *      summary="Supprime une Restriction",
     *      description="Supprime une Restrictions de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la Restrictions à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Restrictions supprimée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Restrictions non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(["all","edit-restrictions-management"]);

        try {
            try {
                $restriction = Restriction::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Cette restriction n\'a pas été trouvé.', [], null, 422);
            }
            $restriction->delete();
            return $this->successResponse($restriction, 'La Restriction a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    private function titresExist(string $restriction_ids)
    {
        $ids = explode(";", $restriction_ids);
        // Si tous les titres exists
        return collect($ids)->every(fn ($id) => Restriction::whereId(intval($id))->exists());
    }
}


