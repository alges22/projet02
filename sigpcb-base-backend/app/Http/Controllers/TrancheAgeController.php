<?php

namespace App\Http\Controllers;

use App\Models\CategoriePermis;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\TrancheAge;
use Illuminate\Support\Facades\Validator;

class TrancheAgeController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/anatt-base/tranche-ages",
     *     operationId="getAllTrancheAges",
     *     tags={"TrancheAges"},
     *     summary="Récupérer la liste des tranche-ages",
     *     description="Récupère une liste de tous les tranche-ages enregistrées dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des tranche-ages récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la tranche d'age",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="categorie_permis_id",
     *                      description="ID de la catégorie de permis",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="validite",
     *                      description="La validité du permis",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut de la tranche d'age (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="age_min",
     *                      description="Age minimal de la tranche d'age",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="age_max",
     *                      description="Age maximal de la tranche d'age",
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
            $tranche_ages = TrancheAge::orderBy('id', 'DESC')->get();
            return $this->successResponse($tranche_ages);
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
     *      path="/api/anatt-base/tranche-ages",
     *      operationId="createTrancheAges",
     *      tags={"TrancheAges"},
     *      summary="Crée une nouvelle tranche d'age",
     *      description="Crée une nouvelle tranche d'age enregistrée dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="categorie_permis_id",
     *                      description="ID de la catégorie de permis",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="validite",
     *                      description="La validité du permis",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="age_min",
     *                      description="Age minimal de la tranche d'age",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="age_max",
     *                      description="Age maximal de la tranche d'age",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut de la tranche d'age (optionnel)",
     *                      type="boolean"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle tranche d'age créée"
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            Validator::extend('champ1_inferieur_champ2', function($attribute, $value, $parameters, $validator) {
                $champ1 = $value;
                $champ2 = $validator->getData()[$parameters[0]];
                return $champ1 < $champ2;
            });

            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => 'required|exists:categorie_permis,id',
                'validite' => 'required|integer',
                'age_min' => [
                    'required',
                    'integer',
                    'champ1_inferieur_champ2:age_max',
                    Rule::unique('tranche_ages')->where(function ($query) use ($request) {
                        return $query->where('age_max', $request->input('age_max'));
                    })->ignore($request->input('id'), 'id')
                ],
                'age_max' => 'required|integer',
                'status' => 'boolean',
            ], [
                'categorie_permis_id.required' => 'L\'ID de la catégorie de permis est obligatoire.',
                'categorie_permis_id.exists' => 'L\'ID de la catégorie de permis spécifié n\'existe pas.',
                'validite.required' => 'La validité du permis est obligatoire.',
                'age_min.required' => 'L\'âge minimal est obligatoire.',
                'age_min.integer' => 'L\'âge minimal doit être un entier.',
                'age_max.required' => 'L\'âge maximal est obligatoire.',
                'age_max.integer' => 'L\'âge maximal doit être un entier.',
                'champ1_inferieur_champ2' => 'L\'âge minimal doit être inférieur à l\'âge maximal.',
                'age_min.unique' => 'L\'âge minimal doit être unique pour chaque combinaison d\'âge minimal et d\'âge maximal.',
                'status.boolean' => 'Le statut doit être une valeur booléenne.',
            ]);
            $categorie_permis_id = $request->input('categorie_permis_id');

            $existingAgeMin = CategoriePermis::where('id', $categorie_permis_id)->value('age_min');

            if ($request->input('age_min') < $existingAgeMin) {
                return $this->errorResponse("La tranche d'âge [{$request->input('age_min')}, {$request->input('age_max')}] est invalide. L'âge minimal ne doit pas être inférieur à l'âge minimal du permis.", null, 422);
            }


            if ($validator->fails()) {
                return $this->errorResponse('La validation a échoué.', $validator->errors(),422);
            }
            $data = $validator->validated();
            $existingTrancheAge = TrancheAge::where([
                'categorie_permis_id' => $data['categorie_permis_id'],
                'validite' => $data['validite'],
                'age_min' => $data['age_min'],
                'age_max' => $data['age_max'],
            ])->first();

            if ($existingTrancheAge) {
                return $this->errorResponse('Une tranche d\'âge identique existe déjà.', [], 409);
            }
            $tranche_age = TrancheAge::create($data);
            return $this->successResponse($tranche_age, 'Tranche d\'âge créée avec succès.');
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
     *      path="/api/anatt-base/tranche-ages/{id}",
     *      operationId="getTrancheAgesById",
     *      tags={"TrancheAges"},
     *      summary="Récupère une tranche d'age par ID",
     *      description="Récupère une tranche d'age enregistrée dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la tranche d'age à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="tranche d'age récupérée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="tranche d'age non trouvée"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            try {
                $tranche_ages = TrancheAge::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La tranche d\âge avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($tranche_ages);
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
    public function update(Request $request, $id)
    {
        try {

            Validator::extend('champ1_inferieur_champ2', function($attribute, $value, $parameters, $validator) {
                $champ1 = $value;
                $champ2 = $validator->getData()[$parameters[0]];
                return $champ1 < $champ2;
            });

            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => 'required|exists:categorie_permis,id',
                'age_min' => [
                    'nullable',
                    'integer',
                    'champ1_inferieur_champ2:age_max',
                    Rule::unique('tranche_ages')->where(function ($query) use ($request) {
                        return $query->where('age_max', $request->input('age_max'));
                    })->ignore($id)
                ],
                'age_max' => 'nullable|integer'
            ], [
                'age_min.required' => 'L\'âge minimal est obligatoire.',
                'age_max.required' => 'L\'âge maximal est obligatoire.',
                'champ1_inferieur_champ2' => 'L\'âge minimal doit être plus petit que l\'âge maximal.',
                'age_min.unique' => 'L\'âge minimal doit être unique pour chaque combinaison d\'âge maximal et d\'âge minimal.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }
            $categorie_permis_id = $request->input('categorie_permis_id');

            $existingAgeMin = CategoriePermis::where('id', $categorie_permis_id)->value('age_min');

            if ($request->input('age_min') < $existingAgeMin) {
                return $this->errorResponse("La tranche d'âge [{$request->input('age_min')}, {$request->input('age_max')}] est invalide. L'âge minimal ne doit pas être inférieur à l'âge minimal du permis.", null, 422);
            }
            try {
                $tranche_age = TrancheAge::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La tranche d\'âge avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            $tranche_age->update($request->all());
            return $this->successResponse($tranche_age, 'Tranche d\'âge mise à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    /**
     * @OA\Put(
     *      path="/api/anatt-base/tranche-ages/{id}",
     *      operationId="updateTrancheAges",
     *      tags={"TrancheAges"},
     *      summary="Met à jour une catégorie de permis existante",
     *      description="Met à jour une catégorie de permis existante dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la catégorie à mettre à jour",
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
     *                      property="age_min",
     *                      description="L'age minimum pour obtenir ce permis",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="categorie_permis_id",
     *                      description="L'id de la catégorie de permis concerné",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="validite",
     *                      description="La validité en fonction de  l'age",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="age_max",
     *                      description="L'age maxi pour obtenir ce permis",
     *                      type="string"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Tranche age mise à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Tranche age non trouvée"
     *      )
     * )
     */
    public function updateTrancheAge(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'age_min' => 'required|integer|min:1',
                'validite' => 'required|min:1',
                'age_max' => 'required|integer|min:1',
                'categorie_permis_id' => 'required|exists:categorie_permis,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),422);
            }

            try {
                $tranche_age = TrancheAge::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La tranche age avec l\'ID '.$id.' n\'a pas été trouvée.', [], null, 404);
            }

            // Vérifier si les nouvelles valeurs existent déjà pour cette catégorie de permis
            if ($request->has('age_min') && $request->has('validite') && $request->has('age_max') && $request->has('categorie_permis_id')) {
                $existingTrancheAge = TrancheAge::where('age_min', $request->input('age_min'))
                    ->where('validite', $request->input('validite'))
                    ->where('age_max', $request->input('age_max'))
                    ->where('categorie_permis_id', $request->input('categorie_permis_id'))
                    ->first();

                if ($existingTrancheAge && $existingTrancheAge->id != $id) {
                    return $this->errorResponse('Une tranche d\'âge avec les mêmes valeurs existe déjà pour cette catégorie de permis.', [], null, 422);
                }
            }

            $tranche_age->update($request->all());
            return $this->successResponse($tranche_age, 'Tranche age mise à jour avec succès.');
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
     *      path="/api/anatt-base/tranche-ages/{id}",
     *      operationId="deleteTrancheAges",
     *      tags={"TrancheAges"},
     *      summary="Supprime une tranche d'age",
     *      description="Supprime une tranche d'age de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la tranche d'age à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="tranche d'age supprimée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="tranche d'age non trouvée"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            try {
                $tranche_age = TrancheAge::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La tranche d\'âge avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            $tranche_age->delete();
            return $this->successResponse($tranche_age, 'La tranche d\'âge a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    private function tranchageExist(string $tranche_age_ids)
    {
        $ids = explode(";", $tranche_age_ids);
        //
        return collect($ids)->every(fn ($id) => TrancheAge::whereId(intval($id))->exists());
    }

        /**
     * @OA\Post(
     *      path="/api/anatt-admin/tranche-ages/status",
     *      operationId="createTrancheAgesStatus",
     *      tags={"TrancheAges"},
     *      summary="Désactivation ou activation d'une tranche d'age",
     *      description="Désactivation ou activation d'une tranche d'age",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="tranche_age_id",
     *                      description="id de la tranche d'age",
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
     *          description="la tranche d'age n'a pas été trouvé"
     *      )
     * )
     */
    public function status(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tranche_age_id' => 'required',
                'status' => 'required'
            ], [
                'tranche_age_id.required' => 'Aucune tranche d\'age n\'a été sélectionné',
                'status.required' => 'Aucun statut n\'a été envoyé'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), 422);
            }

            $tranche_age_id = $request->get('tranche_age_id');
            $status = $request->get('status');
            if (!$this->tranchageExist($tranche_age_id)) {
                return $this->errorResponse('Vérifiez que la tranche d\'age sélectionné existe', $validator->errors(),422);
            }

            $tranche_age = TrancheAge::where('id', $tranche_age_id)->first();

            TrancheAge::where('id', $tranche_age_id)->update(['status' => $status]);
            $tranche_age = TrancheAge::findOrFail($tranche_age_id); // récupérer la langue mis à jour
            return $this->successResponse(['tranche_age' => $tranche_age, 'message' => 'Mise à jour effectué avec succès']);
        } catch (\Throwable $th) {
            // Gérer l'erreur et la journaliser
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour");
        }
    }
}
