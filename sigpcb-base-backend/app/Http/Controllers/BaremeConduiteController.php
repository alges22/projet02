<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\BaremeConduite;
use App\Models\CategoriePermis;
use Illuminate\Support\Facades\Validator;

class BaremeConduiteController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/anatt-base/bareme-conduites",
     *     operationId="getAllBaremeConduites",
     *     tags={"BaremeConduites"},
     *     summary="Récupérer la liste des bareme-conduites",
     *     description="Récupère une liste de tous les bareme-conduites enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des bareme-conduites récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du bareme",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="categorie_permis_id",
     *                      description="ID de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom du bareme",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="poids",
     *                      description="Poids du bareme",
     *                      type="integer"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = CategoriePermis::query()->with('baremes');

            $categorie_permis = $query->orderBy('name', 'asc')->get();
            if ($categorie_permis->isEmpty()) {
                return $this->successResponse([],"Aucun résultat trouvé", 200);
            }

            return $this->successResponse($categorie_permis);
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
     *      path="/api/anatt-base/bareme-conduites",
     *      operationId="createBaremeConduites",
     *      tags={"BaremeConduites"},
     *      summary="Crée un nouveau bareme de conduite",
     *      description="Crée un nouveau bareme de conduite enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="categorie_permis_id",
     *                  description="ID de la catégorie",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="baremes",
     *                  description="Tableau contenant les informations",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                          property="name",
     *                          description="Nom du barème de conduite",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="poids",
     *                          description="Poids du barème de conduite",
     *                          type="integer"
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau bareme de conduite créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => [
                    'required',
                    'exists:categorie_permis,id'
                ],
                'baremes' => [
                    'required',
                    'array',
                    'min:1',
                ],
                'baremes.*.name' => [
                    'required',
                    'string',
                ],
                'baremes.*.poids' => [
                    'required',
                    'numeric',
                ],
            ], [
                'categorie_permis_id.required' => 'La catégorie de permis est obligatoire.',
                'categorie_permis_id.exists' => 'La catégorie de permis sélectionnée n\'existe pas.',
                'baremes.required' => 'Les barèmes de conduite sont obligatoires.',
                'baremes.array' => 'Les barèmes de conduite doivent être un tableau.',
                'baremes.min' => 'Les barèmes de conduite doivent avoir au moins un élément.',
                'baremes.*.name.required' => 'Le champ nom est obligatoire pour tous les barèmes de conduite.',
                'baremes.*.name.string' => 'Le champ nom doit être une chaîne de caractères pour tous les barèmes de conduite.',
                'baremes.*.poids.required' => 'Le champ poids est obligatoire pour tous les barèmes de conduite.',
                'baremes.*.poids.numeric' => 'Le champ poids doit être un nombre pour tous les barèmes de conduite.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),'',422);
            }

            $categorie_permis_id = $request->input('categorie_permis_id');
            $baremes = $request->input('baremes');

            // Vérifier si le même nom a été envoyé plusieurs fois dans le tableau de barèmes
            $existingNames = [];
            $duplicateNames = [];
            foreach ($baremes as $bareme) {
                $name = $bareme['name'];
                if (in_array($name, $existingNames)) {
                    $duplicateNames[] = $name;
                } else {
                    $existingNames[] = $name;
                }
            }

            if (!empty($duplicateNames)) {
                return $this->errorResponse('Le même nom a été envoyé plusieurs fois: ' . implode(', ', $duplicateNames));
            }

            // Boucle sur les valeurs de 'baremes' et crée les nouveaux barèmes de conduite
            $createdBaremes = [];
            foreach ($baremes as $bareme) {
                $name = $bareme['name'];
                $poids = $bareme['poids'];

                // Créer le barème de conduite pour la catégorie de permis spécifiée
                $bareme_conduite = BaremeConduite::create([
                    'name' => $name,
                    'poids' => $poids,
                    'categorie_permis_id' => $categorie_permis_id,
                ]);

                // Ajouter le barème de conduite créé à la liste des barèmes créés
                $createdBaremes[] = $bareme_conduite;
            }

            return $this->successResponse($createdBaremes, 'Barèmes de conduite créés avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur s\'est produite lors de la création des barèmes de conduite.', 500);
        }
    }


    /**
     * @OA\Post(
     *      path="/api/anatt-base/bareme-conduite",
     *      tags={"BaremeConduites"},
     *      summary="Crée un nouveau bareme de conduite",
     *      description="Crée un nouveau bareme de conduite enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="categorie_permis_id",
     *                  description="ID de la catégorie",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="Nom du barème de conduite",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="poids",
     *                  description="Poids du barème de conduite",
     *                  type="number",
     *                  format="float"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau bareme de conduite créé",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Message de succès"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Erreur de validation",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  description="Message d'erreur de validation"
     *              ),
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  description="Erreurs de validation détaillées"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur du serveur",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="error",
     *                  type="string",
     *                  description="Message d'erreur"
     *              )
     *          )
     *      )
     * )
     */
    public function addBareme(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => [
                    'required',
                    'exists:categorie_permis,id'
                ],
                'name' => [
                    'required',
                    'string',
                    Rule::unique('bareme_conduites')->where(function ($query) use ($request) {
                        return $query->where('categorie_permis_id', $request->input('categorie_permis_id'));
                    }),
                ],
                'poids' => [
                    'required',
                    'numeric',
                    function ($attribute, $value, $fail) use ($request) {
                        $categorie_permis_id = $request->input('categorie_permis_id');
                        $currentPoids = $value;
                        $sumPoids = BaremeConduite::where('categorie_permis_id', $categorie_permis_id)->sum('poids');
                        if (($sumPoids + $currentPoids) > 20) {
                            $fail('Le total des points ne doit pas dépasser 20.');
                        }
                    }
                ],
            ], [
                'categorie_permis_id.required' => 'La catégorie de permis est obligatoire.',
                'categorie_permis_id.exists' => 'La catégorie de permis sélectionnée n\'existe pas.',
                'name.required' => 'Le champ nom est obligatoire pour le barème de conduite.',
                'name.string' => 'Le champ nom doit être une chaîne de caractères pour le barème de conduite.',
                'name.unique' => 'Le nom spécifié existe déjà pour cette catégorie de permis.',
                'poids.required' => 'Le champ poids est obligatoire pour le barème de conduite.',
                'poids.numeric' => 'Le champ poids doit être un nombre pour le barème de conduite.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(),'', 422);
            }

            $categorie_permis_id = $request->input('categorie_permis_id');
            $name = $request->input('name');
            $poids = $request->input('poids');

            // Créer le barème de conduite pour la catégorie de permis spécifiée
            $bareme_conduite = BaremeConduite::create([
                'name' => $name,
                'poids' => $poids,
                'categorie_permis_id' => $categorie_permis_id,
            ]);

            return $this->successResponse($bareme_conduite, 'Barème de conduite créé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur s\'est produite lors de la création du barème de conduite.', 500);
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
     *      path="/api/anatt-base/bareme-conduites/{id}",
     *      operationId="getBaremeConduitesById",
     *      tags={"BaremeConduites"},
     *      summary="Récupère un bareme de conduite par ID",
     *      description="Récupère un bareme de conduite enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du bareme à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Bareme de conduite récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Bareme de conduite non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            try {
                $bareme_conduite = BaremeConduite::findOrFail($id);

            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le barème de conduite avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            return $this->successResponse($bareme_conduite);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-base/bareme-conduites/categorie-permis/{categorie_permis_id}",
     *      tags={"BaremeConduites"},
     *      summary="Récupérer les barèmes de conduite par identifiant de catégorie de permis",
     *      description="Récupère les barèmes de conduite correspondant à l'identifiant de la catégorie de permis spécifiée.",
     *      @OA\Parameter(
     *          name="categorie_permis_id",
     *          description="Identifiant de la catégorie de permis",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Bareme de conduite récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Bareme de conduite non trouvé"
     *      )
     * )
     */
    public function getByCategoriePermisId($categorie_permis_id)
    {
        try {
            $categoriePermis = CategoriePermis::find($categorie_permis_id);
            if (!$categoriePermis) {
                return $this->errorResponse("La catégorie de permis de conduite spécifié n'a pas été trouvé.", 404);
            }
            // Récupérer la catégorie de permis par son ID avec ses relations Bareme et SubBareme
            $categorie_permis = CategoriePermis::with(['baremes' => function ($query) {
                $query->orderBy('id');
                // Charger les sous-baremes associés à chaque bareme
            }, 'baremes.subBaremes' => function ($query) {
                $query->orderBy('id');
            }])->find($categorie_permis_id);

            // Vérifier si la catégorie de permis existe
            if (!$categorie_permis) {
                return $this->errorResponse('Catégorie de permis introuvable', null, null, 404);
            }

            // Retourner la catégorie de permis avec ses baremes et sous-baremes triés
            return $this->successResponse($categorie_permis);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
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
     *      path="/api/anatt-base/bareme-conduites/{id}",
     *      operationId="updateBaremeConduites",
     *      tags={"BaremeConduites"},
     *      summary="Met à jour un bareme de conduite existant",
     *      description="Met à jour un bareme de conduite existant dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du bareme à mettre à jour",
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
     *                      property="categorie_permis_id",
     *                      description="ID de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom du bareme",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="poids",
     *                      description="Poids du bareme",
     *                      type="integer"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Bareme de Conduite mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Bareme de Conduite non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => [
                    'required',
                ],
                'name' => [
                    'required',
                    'string',
                    Rule::unique('bareme_conduites')->where(function ($query) use ($request, $id) {
                        return $query->where('categorie_permis_id', $request->input('categorie_permis_id'))
                            ->where('id', '<>', $id);
                    })
                ],
                'poids' => [
                    'required',
                    'numeric',
                    function ($attribute, $value, $fail) use ($request, $id) {
                        $categorie_permis_id = $request->input('categorie_permis_id');
                        $currentPoids = $value;
                        $sumPoids = BaremeConduite::where('categorie_permis_id', $categorie_permis_id)
                            ->where('id', '<>', $id)
                            ->sum('poids');
                        if (($sumPoids + $currentPoids) > 20) {
                            $fail('Le total des points ne doit pas dépasser 20.');
                        }
                    }
                ],
            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.string' => 'Le champ nom doit être une chaîne de caractères.',
                'name.unique' => 'Le nom existe déjà pour cette catégorie de permis.',
                'poids.required' => 'Le champ poids est obligatoire.',
                'poids.numeric' => 'Le champ poids doit être un nombre.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), 422);
            }

            $bareme_conduite = BaremeConduite::find($id);

            if (!$bareme_conduite) {
                return $this->errorResponse("Le barème de conduite spécifié n'a pas été trouvé.", 404);
            }

            $name = $request->input('name');
            $poids = $request->input('poids');

            $bareme_conduite->name = $name;
            $bareme_conduite->poids = $poids;
            $bareme_conduite->save();

            return $this->successResponse($bareme_conduite, 'Barème de conduite mis à jour avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur s\'est produite lors de la mise à jour du barème de conduite.', 500);
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
     *      path="/api/anatt-base/bareme-conduites/{id}",
     *      operationId="deleteBaremeConduites",
     *      tags={"BaremeConduites"},
     *      summary="Supprime un bareme de conduite",
     *      description="Supprime un bareme de conduite de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du bareme à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Bareme de Conduite supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Bareme de Conduite non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            try {
                $bareme_conduite = BaremeConduite::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le barème de conduite avec l\'ID '.$id.' n\'a pas été trouvé.', [], null, 404);
            }
            $bareme_conduite->delete();
            return $this->successResponse($bareme_conduite, 'Le barème de conduite a été supprimé avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
