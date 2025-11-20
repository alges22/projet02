<?php

namespace App\Http\Controllers;

use App\Models\Chapitre;
use Illuminate\Http\Request;
use App\Models\CategoriePermis;
use App\Models\ChapQuestionCount;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ChapitreController extends ApiController
{

    /**
     * @OA\Get(
     *     path="/api/anatt-base/chapitres",
     *     operationId="getAllChapitres",
     *     tags={"Chapitres"},
     *     summary="Récupérer la liste des chapitres",
     *     description="Récupère une liste de tous les chapitres enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des chapitres récupérés avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du chapitre",
     *                      type="integer"
     *                  ),
     *             @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      description="Nom du chapitre"),
     *              ),
     *             @OA\Property(
     *                      property="description",
     *                      type="string",
     *                      description="Une description du chapitre"),
     *              ),
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $query = Chapitre::with('categoriesPermis'); // Charger la relation "categoriesPermis"

            if ($request->has('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower($searchTerm)]);
                });
            }

            if (request('liste') == 'paginate') {
                $chapitres = $query->orderBy('name', 'asc')->paginate(10);
            } else {
                $chapitres = $query->orderBy('name', 'asc')->get();
            }

            if ($chapitres->isEmpty()) {
                return $this->successResponse([], "Aucun résultat trouvé", 200);
            }

            return $this->successResponse($chapitres);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
        }
    }


    private function categoriepermisExist(array $categorie_permis_ids)
    {
        // Si tous les utilisateurs existent
        return collect($categorie_permis_ids)->every(fn ($id) => CategoriePermis::whereId(intval($id))->exists());
    }

    /**
     * @OA\Post(
     *     path="/api/anatt-base/chapitres",
     *     summary="Créer un nouveau chapitre",
     *     tags={"Chapitres"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Nom du chapitre"),
     *             @OA\Property(property="description", type="string", description="Une description du chapitre"),
     *             @OA\Property(property="categorie_permis_ids", type="array", @OA\Items(type="integer"), description="IDs des catégories de permis associées au chapitre")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Réponse de succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Mauvaise requête",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation échouée"),
     *             @OA\Property(property="errors", type="object", example={
     *                 "name": {
     *                     "Le champ name est requis."
     *                 },
     *                 "categorie_permis_ids": {
     *                     "Le champ categorie_permis_ids est requis."
     *                 }
     *             })
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Échec lors de la création")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|unique:chapitres,name',
                    'description' => 'nullable',
                    'categorie_permis_ids' => 'required|array|min:1',
                    'categorie_permis_ids.*' => 'required'
                ],
                [
                    'name.required' => 'Le champ name est requis.',
                    'name.unique' => 'Ce nom de chapitre existe déjà.',
                    'categorie_permis_ids.required' => 'Vous devez sélectionner au moins une catégorie de permis.',
                    'categorie_permis_ids.min' => 'Vous devez sélectionner au moins une catégorie de permis.',

                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), 422);
            }

            $data = $validator->validated();
            $categorie_permis_ids = $data['categorie_permis_ids'];
            unset($data['categorie_permis_ids']);

            // Vérifier si des éléments identiques sont présents dans le tableau
            $unique_categorie_permis_ids = array_unique($categorie_permis_ids);
            if (count($categorie_permis_ids) !== count($unique_categorie_permis_ids)) {
                return $this->errorResponse('Vérifiez que tous les catégories de permis sont uniques', $validator->errors());
            }

            if (!$this->categoriepermisExist($request->input('categorie_permis_ids'))) {
                return $this->errorResponse('Vérifiez que tous les catégories de permis existent', $validator->errors());
            }
            // Créer un nouveau chapitre avec les données de la requête
            $chapitre = Chapitre::create($data);

            // Attacher les relations avec les catégories de permis
            $chapitre->categoriesPermis()->attach($categorie_permis_ids);

            // Recharger le modèle avec les relations pour récupérer les catégories de permis attachées
            $chapitre->load('categoriesPermis');

            return $this->successResponse($chapitre, 'Chapitre créé avec succès', 201);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Échec lors de la création', null, null, 500);
        }
    }



    /**
     * @OA\Put(
     *     path="/api/anatt-base/chapitres/{id}",
     *     summary="Mettre à jour un chapitre",
     *     tags={"Chapitres"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du chapitre à mettre à jour",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Nom du chapitre"),
     *             @OA\Property(property="description", type="string", description="Une description du chapitre"),
     *             @OA\Property(property="categorie_permis_ids", type="array", @OA\Items(type="integer"), description="IDs des catégories de permis associées au chapitre")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Réponse de succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", example={
     *                 "id": 1,
     *                 "name": "Chapitre 1",
     *                 "categorie_permis_ids": {
     *                     1,
     *                     2,
     *                     3
     *                 },
     *                 "created_at": "2023-04-18T12:34:56Z",
     *                 "updated_at": "2023-04-18T12:34:56Z"
     *             })
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Mauvaise requête",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation échouée"),
     *             @OA\Property(property="errors", type="object", example={
     *                 "name": {
     *                     "Le champ name est requis.",
     *                     "Le champ name doit être unique."
     *                 },
     *                 "categorie_permis_ids": {
     *                     "Le champ categorie_permis_ids est requis.",
     *                     "Le champ categorie_permis_ids doit être un tableau.",
     *                     "Le champ categorie_permis_ids doit contenir au moins 1 élément."
     *                 }
     *             })
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Échec lors de la mise à jour")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            try {
                $chapitre = Chapitre::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le chapitre avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|unique:chapitres,name,' . $chapitre->id,
                    'description' => 'nullable',
                    'categorie_permis_ids' => 'required|array|min:1',
                    'categorie_permis_ids.*' => 'required'
                ],
                [
                    'name.required' => 'Le champ nom est requis.',
                    'name.unique' => 'Le champ nom doit être unique.',
                    'categorie_permis_ids.required' => 'Vous devez sélectionner au moins une catégorie de permis.',
                    'categorie_permis_ids.min' => 'Vous devez sélectionner au moins une catégorie de permis.',
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), 422);
            }

            $data = $validator->validated();
            $categorie_permis_ids = $data['categorie_permis_ids'];
            unset($data['categorie_permis_ids']);

            // Vérifier si des éléments identiques sont présents dans le tableau
            $unique_categorie_permis_ids = array_unique($categorie_permis_ids);
            if (count($categorie_permis_ids) !== count($unique_categorie_permis_ids)) {
                return $this->errorResponse('Vérifiez que tous les catégories de permis sont uniques', $validator->errors());
            }

            if (!$this->categoriepermisExist($request->input('categorie_permis_ids'))) {
                return $this->errorResponse('Vérifiez que tous les catégories de permis existent', $validator->errors());
            }

            // Mettre à jour les données du chapitre avec les données de la requête
            $chapitre->update($data);

            // Mettre à jour les relations avec les catégories de permis
            $chapitre->categoriesPermis()->sync($categorie_permis_ids);

            // Recharger le modèle avec les relations pour récupérer les catégories de permis attachées
            $chapitre->load('categoriesPermis');

            return $this->successResponse($chapitre, 'Chapitre mis à jour avec succès', 200);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Échec lors de la mise à jour', null, null, 500);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-base/chapitres/{id}",
     *      tags={"Chapitres"},
     *      summary="Récupère un chapitre par ID",
     *      description="Récupère un chapitre enregistrée dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du chapitre à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Chapitre récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Chapitre non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            try {
                $chapitre = Chapitre::with('categoriesPermis')->findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('Le chapitre avec l\'ID ' . $id . ' n\'a pas été trouvée.', [], null, 404);
            }
            return $this->successResponse($chapitre);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/anatt-base/chapitres/{id}",
     *      tags={"Chapitres"},
     *      summary="Supprime un chapitre",
     *      description="Supprime un chapitre de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du chapitre à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Chapitre supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Chapitre non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        try {
            $chapitre = Chapitre::with('categoriesPermis')->find($id);

            if (!$chapitre) {
                return $this->errorResponse('Chapitre introuvable', [], null, 404);
            }

            // Supprimer les informations de la table associative
            $chapitre->categoriesPermis()->detach();

            // Supprimer le chapitre lui-même
            $chapitre->delete();

            return $this->successResponse(['message' => 'Suppression effectuée avec succès']);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23503') {
                return $this->errorResponse("Impossible de supprimer le chapitre car il est lié à d'autres entités.");
            }

            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la suppression.");
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-base/chapitres/get-many",
     *      tags={"Chapitres"},
     *      summary="Récupérer plusieurs chapitres par ID",
     *      description="Récupère plusieurs chapitres enregistrés dans la base de données en spécifiant leurs IDs",
     *      @OA\Parameter(
     *          name="ids",
     *          description="IDs des chapitres à récupérer (séparés par des virgules)",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Chapitres récupérés avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Chapitre non trouvé"
     *      )
     * )
     */
    public function getMany()
    {
        try {
            $chapitreIds = explode(',', request('ids', ''));
            $chapitres = Chapitre::findMany($chapitreIds);
            return $this->successResponse($chapitres);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue', statuscode: 500);
        }
    }

    public function  chapQuestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "chapitres" => "required|array"
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation échouée', $validator->errors(), 422);
        }

        foreach ($request->chapitres as $key => $chapQuest) {
            ChapQuestionCount::updateOrCreate(
                [
                    "chapitre_id" => $chapQuest['id'],
                ],
                [
                    "chapitre_id" => $chapQuest['id'],
                    "counts" => $chapQuest['counts'] ?? 0,
                ]
            );
        }
        return $this->successResponse(null);
    }
}
