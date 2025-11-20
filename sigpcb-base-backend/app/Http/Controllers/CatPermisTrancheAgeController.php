<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\CatPermisTrancheAge;
use Illuminate\Support\Facades\Validator;

class CatPermisTrancheAgeController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/anatt-base/cat-permis-tranches",
     *     operationId="getAllCatPermisTrancheAges",
     *     tags={"CatPermisTrancheAges"},
     *     summary="Récupérer la liste des Catégories Permis Tranche Age",
     *     description="Récupère une liste de toutes les Catégories Permis Tranche Age enregistrées dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des Catégories Permis Tranche Age récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la Catégorie Permis Tranche Age",
     *                      type="integer"
     *                  ),
     *                 @OA\Property(
     *                     property="categorie_permis_id",
     *                     description="ID de la catégorie permis à laquelle appartient la nouvelle Catégorie Permis Tranche Age créée",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="tranche_age_id",
     *                     description="ID de tranche d'age à laquelle appartient la nouvelle Catégorie Permis Tranche Age créée",
     *                     type="integer"
     *                 ),
     *                  @OA\Property(
     *                      property="validite",
     *                      description="Validité de la Catégorie Permis Tranche Age (optionnel)",
     *                      type="integer"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $cat_permis_tranche_ages = CatPermisTrancheAge::with(['categoriePermis', 'trancheAge'])->get();
            return $this->successResponse($cat_permis_tranche_ages);
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
     *      path="/api/anatt-base/cat-permis-tranches",
     *      operationId="createCatPermisTrancheAge",
     *      tags={"CatPermisTrancheAges"},
     *      summary="Crée une nouvelle Catégorie Permis Tranche Age",
     *      description="Crée une nouvelle Catégorie Permis Tranche Age enregistrée dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="categorie_permis_id",
     *                  description="ID de la catégorie permis à laquelle appartient la nouvelle Catégorie Permis Tranche Age créée",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="tranche_age_validites",
     *                  description="Tableau contenant les informations sur les tranches d'ages et leurs validités à créer",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                          property="tranche_age_id",
     *                          description="Id de la tranche d'age",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="validite",
     *                          description="Validité de la Catégorie Permis Tranche Age",
     *                          type="integer"
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle Catégorie Permis Tranche Age créée"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Erreur de validation des données d'entrée"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur interne du serveur"
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => 'required|exists:categorie_permis,id',
                'tranche_age_validites' => 'required|array|min:1',
                'tranche_age_validites.*.tranche_age_id' => 'required|exists:tranche_ages,id',
                'tranche_age_validites.*.validite' => 'required|integer|min:1',
            ], [
                'categorie_permis_id.required' => 'La catégorie de permis est obligatoire.',
                'categorie_permis_id.exists' => 'La catégorie sélectionnée n\'existe.',
                'tranche_age_validites.required' => 'Au moins une tranche d\'âge avec une validité doit être spécifiée.',
                'tranche_age_validites.array' => 'La liste des tranches d\'âge et validités doit être un tableau.',
                'tranche_age_validites.min' => 'La liste des tranches d\'âge et validités doit avoir au moins un élément.',
                'tranche_age_validites.*.tranche_age_id.required' => 'La tranche d\'âge est obligatoire.',
                'tranche_age_validites.*.tranche_age_id.exists' => 'La tranche d\'âge sélectionnée n\'existe.',
                'tranche_age_validites.*.validite.required' => 'La validité est obligatoire.',
                'tranche_age_validites.*.validite.integer' => 'La validité doit être un nombre entier.',
                'tranche_age_validites.*.tranche_age_id.unique' => 'La combinaison de la catégorie de permis et de la tranche d\'âge doit être unique.',
            ]);
            $tranche_age_validites = collect($request->input('tranche_age_validites'));
            $duplicates = $tranche_age_validites->duplicates('tranche_age_id');
            if ($duplicates->count() > 0) {
                // Il y a des doublons
                return $this->errorResponse("Vous ne pouvez pas sélectionner deux fois la même tranche_age");
            }

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }

            $data = $validator->validated();
            $cat_permis_tranche_ages = [];
            // Supprimer les anciennes données s'il en existe
            CatPermisTrancheAge::where('categorie_permis_id', $data['categorie_permis_id'])->delete();

            foreach ($data['tranche_age_validites'] as $tranche_age_validite) {
                $cat_permis_tranche_age_data = [
                    'categorie_permis_id' => $data['categorie_permis_id'],
                    'tranche_age_id' => $tranche_age_validite['tranche_age_id'],
                    'validite' => $tranche_age_validite['validite'],
                ];
                $cat_permis_tranche_ages[] = CatPermisTrancheAge::create($cat_permis_tranche_age_data);
            }

            return $this->successResponse($cat_permis_tranche_ages, 'Catégorie Permis Tranche(s) d\'âge créée(s) avec succès.');
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
     *      path="/api/anatt-base/cat-permis-tranches/{id}",
     *      operationId="getCatPermisTrancheAgeById",
     *      tags={"CatPermisTrancheAges"},
     *      summary="Récupère une Catégorie Permis Tranche Age par ID",
     *      description="Récupère une Catégorie Permis Tranche Age enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la Catégorie Permis Tranche Age à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Catégorie Permis Tranche Age récupérée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Catégorie Permis Tranche Age non trouvée"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            try {
                $cat_permis_tranche_age = CatPermisTrancheAge::with(['categoriePermis', 'trancheAge'])->findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Catégorie Permis Tranche d\'âge avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            return $this->successResponse($cat_permis_tranche_age);
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
     *      path="/api/anatt-base/cat-permis-tranches/{id}",
     *      operationId="updateCatPermisTrancheAge",
     *      tags={"CatPermisTrancheAges"},
     *      summary="Met à jour une Catégorie Permis Tranche Age existante",
     *      description="Met à jour une Catégorie Permis Tranche Age existante dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la Catégorie Permis Tranche Age à mettre à jour",
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
     *                 @OA\Property(
     *                     property="categorie_permis_id",
     *                     description="ID de la catégorie permis à laquelle appartient la nouvelle Catégorie Permis Tranche Age créée",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="tranche_age_id",
     *                     description="ID de tranche d'age à laquelle appartient la nouvelle Catégorie Permis Tranche Age créée",
     *                     type="integer"
     *                 ),
     *                  @OA\Property(
     *                      property="validite",
     *                      description="Validité de la Catégorie Permis Tranche Age (optionnel)",
     *                      type="integer"
     *                  )
     *             )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Catégorie Permis Tranche Age mise à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Catégorie Permis Tranche Age non trouvée"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => 'required|exists:categorie_permis,id',
                'tranche_age_id' => [
                    'required',
                    'exists:tranche_ages,id',
                    Rule::unique('cat_permis_tranche_ages')->where(function ($query) use ($request) {
                        return $query->where('categorie_permis_id', $request->input('categorie_permis_id'));
                    })->ignore($id)
                ],
            ], [
                'categorie_permis_id.required' => 'La catégorie de permis est obligatoire.',
                'categorie_permis_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
                'tranche_age_id.required' => 'La tranche d\'âge est obligatoire.',
                'tranche_age_id.exists' => 'La tranche d\'âge sélectionnée n\'existe pas.',
                'tranche_age_id.unique' => 'La tranche d\'âge doit être unique pour chaque combinaison de catégorie permis et tranche d\'âge.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }

            try {
                $cat_permis_tranche_age = CatPermisTrancheAge::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Catégorie Permis Tranche d\'âge avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            $cat_permis_tranche_age->update($request->all());
            return $this->successResponse($cat_permis_tranche_age, 'Catégorie Permis Tranche d\'âge mise à jour avec succès.');
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
     *      path="/api/anatt-base/cat-permis-tranches/{id}",
     *      operationId="deleteCatPermisTrancheAge",
     *      tags={"CatPermisTrancheAges"},
     *      summary="Supprime une Catégorie Permis Tranche Age",
     *      description="Supprime une Catégorie Permis Tranche Age de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la Catégorie Permis Tranche Age à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Catégorie Permis Tranche Age supprimée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Catégorie Permis Tranche Age non trouvée"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            try {
                $cat_permis_tranche_age = CatPermisTrancheAge::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Catégorie Permis Tranche d\'âge avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            $cat_permis_tranche_age->delete();
            return $this->successResponse($cat_permis_tranche_age, 'Catégorie Permis Tranche d\'âge a été supprimée avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
