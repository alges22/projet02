<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;
use App\Models\CategoriePermis;
use App\Models\CategoriePermisExtensible;
use App\Models\TrancheAge;
use Illuminate\Support\Facades\Validator;

class CategoriePermisController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/anatt-base/categorie-permis",
     *     operationId="getAllCategoriePermis",
     *     tags={"CategoriePermis"},
     *     summary="Récupérer la liste des categorie-permis",
     *     description="Récupère une liste de tous les categorie-permis enregistrées dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des categorie-permis récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de la catégorie",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      description="Description de la catégorie",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="permis_prealable",
     *                      description="Le permis préalable de la catégorie",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="permis_prealable_dure",
     *                      description="La duré du permis préalable de la catégorie",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="is_extension",
     *                      description="Si cette catégorie peut etre extensible ou pas",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut de la catégorie (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="validite",
     *                      description="Validité de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="age_min",
     *                      description="Age minimale de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="is_valid_age",
     *                      description="L'age est un age valide de la catégorie (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="montant_militaire",
     *                      description="Montant de la catégorie pour les militaires",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="montant_etranger",
     *                      description="Montant de la catégorie pour les étrangers",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="montant",
     *                      description="Montant de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="note_min",
     *                      description="Note minimale de la catégorie",
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
            $categorie_permis = CategoriePermis::with(['trancheage', 'extensions', 'permisPrealable'])
                ->orderBy('name', 'asc')
                ->get();

            return $this->successResponse($categorie_permis);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/anatt-base/categorie-permis/extensions",
     *     operationId="getAllCategoriePermisExtension",
     *     tags={"CategoriePermis"},
     *     summary="Récupérer la liste des categorie-permis représentant des extensions",
     *     description="Récupère une liste de tous les categorie-permis représentant des extensions enregistrées dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des categorie-permis représentant des extensions récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de la catégorie",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      description="Description de la catégorie",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="permis_prealable",
     *                      description="Le permis préalable de la catégorie",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="permis_prealable_dure",
     *                      description="La duré du permis préalable de la catégorie",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="is_extension",
     *                      description="Si cette catégorie peut etre extensible ou pas",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut de la catégorie (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="validite",
     *                      description="Validité de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="age_min",
     *                      description="Age minimale de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="is_valid_age",
     *                      description="L'age est un age valide de la catégorie (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="montant_militaire",
     *                      description="Montant de la catégorie pour les militaires",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="montant_etranger",
     *                      description="Montant de la catégorie pour les étrangers",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="montant",
     *                      description="Montant de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="note_min",
     *                      description="Note minimale de la catégorie",
     *                      type="integer"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function getExtension()
    {
        try {
            // Utiliser la méthode where pour filtrer les données où le champ is_extension est égal à true
            $categorie_permis = CategoriePermis::with(['trancheage', 'permisPrealable'])->where('is_extension', true)->orderBy('name', 'asc')->get();
            return $this->successResponse($categorie_permis);
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
     *      path="/api/anatt-base/categorie-permis",
     *      operationId="createCategoriePermis",
     *      tags={"CategoriePermis"},
     *      summary="Crée une nouvelle catégorie de permis",
     *      description="Crée une nouvelle catégorie de permis enregistrée dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de la catégorie",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      description="Description de la catégorie",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="permis_prealable",
     *                      description="Le permis préalable de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="permis_prealable_dure",
     *                      description="La duré du permis préalable de la catégorie",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="is_extension",
     *                      description="Si cette catégorie peut etre extensible ou pas",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut de la catégorie (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="age_min",
     *                      description="Age minimale de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="is_valid_age",
     *                      description="L'age est un age valide de la catégorie (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="montant",
     *                      description="Montant de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="note_min",
     *                      description="Note minimale de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="montant_militaire",
     *                      description="Montant de la catégorie pour les militaires",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="montant_etranger",
     *                      description="Montant de la catégorie pour les étrangers",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                  property="tranche_age_groupe",
     *                  description="Tableau contenant les informations sur les tranches ages",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                          property="age_min",
     *                          description="L'age minimum",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="age_max",
     *                          description="L'age maximum",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="validite",
     *                          description="La validité du permis",
     *                          type="string"
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle catégorie de permis créée"
     *      )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'unique:categorie_permis,name'
                ],
                'tranche_age_groupe' => 'required|array|min:1',
                'tranche_age_groupe.*.age_min' => [
                    'nullable',
                    'integer',
                ],
                'tranche_age_groupe.*.age_max' => [
                    'nullable',
                    'integer',
                ],
                'tranche_age_groupe.*.validite' => 'required|integer',
                'description' => 'nullable|string|max:255',
                'status' => 'boolean',
                'age_min' => 'required|integer',
                'is_valid_age' => 'boolean',
                'montant_militaire' => 'required|integer',
                'montant_etranger' => 'required|integer',
                'montant' => 'required|integer',
                'note_min' => 'required|integer',
                'permis_prealable' => 'nullable|exists:categorie_permis,id',
                'permis_prealable_dure' => 'nullable|integer|max:255',
                'is_extension' => ['boolean'],
                "montant_extension" => [Rule::when(boolval($request->is_extension), [
                    "required", "numeric"
                ])]

            ], [
                'name.required' => 'Le champ nom est obligatoire.',
                'montant_militaire.required' => 'Le champ montant pour les militaires est obligatoire.',
                'name.unique' => 'Le nom de la catégorie existe déjà.',
                'description.string' => 'La description doit être une chaîne de caractères.',
                'description.max' => 'La description ne doit pas dépasser :max caractères.',
                'status.boolean' => 'Le statut doit être un booléen.',
                'validite.min' => 'La validité doit être d\'au moins :min.',
                'age_min.required' => 'L\'âge minimal est obligatoire.',
                'age_min.integer' => 'L\'âge minimal doit être un nombre entier.',
                'montant_militaire.integer' => 'Le montant militaire doit être un nombre entier.',
                'is_valid_age.boolean' => 'Le statut de validité de l\'âge doit être un booléen.',
                'montant.required' => 'Le montant est obligatoire.',
                'montant.integer' => 'Le montant doit être un nombre entier.',
                'note_min.required' => 'La note minimale est obligatoire.',
                'note_min.integer' => 'La note minimale doit être un nombre entier.',
                'permis_prealable.string' => 'Le permis préalable doit être une chaîne de caractères.',
                'permis_prealable.max' => 'Le permis préalable ne doit pas dépasser :max caractères.',
                'permis_prealable_dure.string' => 'La durée du permis préalable doit être une chaîne de caractères.',
                'permis_prealable_dure.max' => 'La durée du permis préalable ne doit pas dépasser :max caractères.',
                'is_extension.boolean' => 'Le statut d\'extensibilité doit être un booléen.',
                // 'permis_prealable.same' => 'Le permis préalable ne peut pas être le même que le nom du permis principal.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), 422);
            }
            // Récupération de l'âge minimal en dehors du tableau tranche_age_groupe
            $ageMinPrincipal = $request->input('age_min');
            // Vérification des doublons dans le tableau tranche_age_groupe
            $tranche_age_groupe = $request->input('tranche_age_groupe');
            $tranche_age_groupe_unique = array_unique($tranche_age_groupe, SORT_REGULAR);
            if (count($tranche_age_groupe) != count($tranche_age_groupe_unique)) {
                return $this->errorResponse("Le tableau tranche_age_groupe contient des doublons.", null, 422);
            }
            // Vérification des âges minimaux dans le tableau tranche_age_groupe
            foreach ($tranche_age_groupe as $tranche) {
                if (isset($tranche['age_min'])) {
                    if ($tranche['age_min'] < $ageMinPrincipal) {
                        return $this->errorResponse("La tranche d'âge [{$tranche['age_min']}, {$tranche['age_max']}] est invalide ! L'âge minimal ne doit pas être inférieur à l'âge minimal du permis.", null, 422);
                    }
                }
            }
            // Vérification des âges minimaux et maximaux
            foreach ($tranche_age_groupe as $tranche) {
                if (isset($tranche['age_min']) && isset($tranche['age_max'])) {
                    if ($tranche['age_min'] >= $tranche['age_max']) {
                        return $this->errorResponse("La tranche d'âge [{$tranche['age_min']}, {$tranche['age_max']}] est invalide.", null, 422);
                    }
                }
            }

            // Création de la catégorie de permis principale
            $categorie_permis = CategoriePermis::with(['trancheage', 'permisPrealable'])->create($request->all());
            foreach ($tranche_age_groupe as $tranche) {
                $tranche_age = new TrancheAge($tranche);
                $categorie_permis->trancheage()->save($tranche_age);
            }
            $categorie_permis->load('trancheage');
            // Enregistrement des extensions de permis si elles sont présentes
            if ($request->has('extensions') && is_array($request->get('extensions'))) {
                $extensions = $request->get('extensions');

                // Enregistrement des extensions de permis associées à la catégorie de permis principale
                foreach ($extensions as $extensionId) {
                    $extension = new CategoriePermisExtensible();
                    $extension->categorie_permis_id = $categorie_permis->id;
                    $extension->categorie_permis_extensible_id = $extensionId;
                    $extension->save();
                }
            }
            $categorie_permis->load(['extensions', 'permisPrealable']);
            return $this->successResponse("La catégorie de permis a été créée avec succès.", $categorie_permis, 201);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur est survenue lors de la création de la catégorie de permis.", null, 500);
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
     *      path="/api/anatt-base/categorie-permis/{id}",
     *      operationId="getCategoriePermisById",
     *      tags={"CategoriePermis"},
     *      summary="Récupère une catégorie de permis par ID",
     *      description="Récupère une catégorie de permis enregistrée dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la catégorie à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Categorie de permis récupérée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Categorie de permis non trouvée"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            try {
                $categorie_permis = CategoriePermis::with(['trancheage', 'extensions', 'permisPrealable'])->findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La catégorie de permis avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            return $this->successResponse($categorie_permis);
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
     *      path="/api/anatt-base/categorie-permis/{id}",
     *      operationId="updateCategoriePermis",
     *      tags={"CategoriePermis"},
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
     *                      property="name",
     *                      description="Nom de la catégorie",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      description="Description de la catégorie",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="is_extension",
     *                      description="Si cette catégorie peut etre extensible ou pas",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut de la catégorie (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="age_min",
     *                      description="Age minimale de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="is_valid_age",
     *                      description="L'age est un age valide de la catégorie (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="permis_prealable",
     *                      description="Le permis préalable de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="permis_prealable_dure",
     *                      description="La duré du permis préalable de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="montant",
     *                      description="Montant de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="note_min",
     *                      description="Note minimale de la catégorie",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="montant_militaire",
     *                      description="Montant de la catégorie pour les militaires",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="montant_etranger",
     *                      description="Montant de la catégorie pour les étrangers",
     *                      type="integer"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Catégorie de permis mise à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Catégorie de permis non trouvée"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    Rule::unique('categorie_permis')->ignore(intval($id)),
                ],

                'description' => 'nullable|string|max:255',
                'status' => 'boolean',
                'age_min' => 'required|integer',
                'is_valid_age' => 'boolean',
                'for_all' => 'boolean',
                'montant' => 'required|integer',
                'montant_militaire' => 'required|integer',
                'montant_etranger' => 'required|integer',
                'note_min' => 'required|integer',
                'permis_prealable' => 'nullable|exists:categorie_permis,id',
                'permis_prealable_dure' => 'nullable|integer|max:255',
                'is_extension' => 'boolean'
            ],  [
                'name.required' => 'Le champ nom est obligatoire.',
                'name.unique' => 'Le nom de la catégorie existe déjà.',
                'description.string' => 'La description doit être une chaîne de caractères.',
                'description.max' => 'La description ne doit pas dépasser :max caractères.',
                'status.boolean' => 'Le statut doit être un booléen.',
                'validite.min' => 'La validité doit être d\'au moins :min.',
                'age_min.required' => 'L\'âge minimal est obligatoire.',
                'age_min.integer' => 'L\'âge minimal doit être un nombre entier.',
                'is_valid_age.boolean' => 'Le statut de validité de l\'âge doit être un booléen.',
                'montant.required' => 'Le montant est obligatoire.',
                'montant.integer' => 'Le montant doit être un nombre entier.',
                'montant_militaire.required' => 'Le montant pour les militaires est obligatoire.',
                'montant_militaire.integer' => 'Le montant pour les militaires doit être un nombre entier.',
                'note_min.required' => 'La note minimale est obligatoire.',
                'note_min.integer' => 'La note minimale doit être un nombre entier.',
                'permis_prealable.string' => 'Le permis préalable doit être une chaîne de caractères.',
                'permis_prealable.max' => 'Le permis préalable ne doit pas dépasser :max caractères.',
                'permis_prealable_dure.string' => 'La durée du permis préalable doit être une chaîne de caractères.',
                'permis_prealable_dure.max' => 'La durée du permis préalable ne doit pas dépasser :max caractères.',
                'is_extension.boolean' => 'Le statut d\'extensibilité doit être un booléen.',
                // 'permis_prealable.same' => 'Le permis préalable ne peut pas être le même que le nom du permis principal.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), 422);
            }
            if ($request->has('permis_prealable')) {
                $permis_prealable = $request->get('permis_prealable');
                if ($permis_prealable == $id) {
                    return $this->errorResponse('Vous ne pouvez pas définir le même  permis comme étant son propre permis préalable', [], null, 404);
                }
            }
            $ageMinPrincipal = $request->input('age_min');

            try {
                $categorie_permis = CategoriePermis::with('trancheage')->findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La catégorie de permis avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            $categorie_permis->update($request->all());
            return $this->successResponse($categorie_permis, 'Catégorie de permis mise à jour avec succès.');
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
     *      path="/api/anatt-base/categorie-permis/{id}",
     *      operationId="deleteCategoriePermis",
     *      tags={"CategoriePermis"},
     *      summary="Supprime une catégorie de permis",
     *      description="Supprime une catégorie de permis de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la catégorie à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Catégorie de permis supprimée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Catégorie de permis non trouvée"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            try {
                $categorie_permis = CategoriePermis::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('La catégorie de permis avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            // Supprimer les informations de la relation "extensions"
            $categorie_permis->extensions()->delete();
            // Delete related "trancheage"
            $categorie_permis->chapitres()->delete();
            $categorie_permis->trancheage()->delete();
            $categorie_permis->baremes()->delete();

            // Delete the "CategoriePermis" record
            $categorie_permis->delete();

            return $this->successResponse($categorie_permis, 'La catégorie de permis a été supprimée avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-base/categorie-permis/extension",
     *      operationId="createCategoriePermisExtensible",
     *      tags={"CategoriePermis"},
     *      summary="Crée une nouvelle extension",
     *      description="Crée une nouvelle extension enregistrée dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="categorie_permis_id",
     *                  description="L'id de la catégorie principale",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="categorie_permis_extensible_id",
     *                  description="L'id de la categorie de permis a utilisé comme extension",
     *                  type="integer"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle extension créée"
     *      )
     * )
     */
    public function storeExtension(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'categorie_permis_id' => 'required|exists:categorie_permis,id',
                'categorie_permis_extensible_id' => 'required|exists:categorie_permis,id',
            ], [
                'categorie_permis_id.required' => 'La catégorie de permis est obligatoire.',
                'categorie_permis_extensible_id.required' => 'L\extension est obligatoire.',
                'categorie_permis_id.exists' => 'La catégorie de permis sélectionnée n\'existe pas.',
                'categorie_permis_extensible_id.exists' => 'L\'extension sélectionnée n\'existe pas.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), 422);
            }

            $categorie_permis_id = $request->input('categorie_permis_id');
            $categorie_permis_extensible_id = $request->input('categorie_permis_extensible_id');

            // Vérifier si l'insertion existe déjà
            $existingExtension = CategoriePermisExtensible::where('categorie_permis_id', $categorie_permis_id)
                ->where('categorie_permis_extensible_id', $categorie_permis_extensible_id)
                ->first();

            if ($existingExtension) {
                return $this->errorResponse('Cette extension existe déjà dans la base de données.');
            }

            $extension = CategoriePermisExtensible::create($request->all());
            return $this->successResponse($extension, 'Extension créée avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }


    /**
     * @OA\Delete(
     *      path="/api/anatt-base/categorie-permis/extension/{id}",
     *      operationId="deleteCategoriePermisExtensible",
     *      tags={"CategoriePermis"},
     *      summary="Supprime une extension d'une catégorie de permis",
     *      description="Supprime une extension d'une catégorie de permis de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'extension de la catégorie à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Extension supprimée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Extension non trouvée"
     *      )
     * )
     */
    public function destroyExtension($id)
    {
        try {
            try {
                $extension = CategoriePermisExtensible::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'extension avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            $extension->delete();
            return $this->successResponse($extension, 'L\'extension de la catégorie de permis a été supprimée avec succès.');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
}
