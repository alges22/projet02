<?php

namespace App\Http\Controllers;

use Exception;
use App\Services\Api;
use App\Models\AnnexeAnatt;
use Illuminate\Http\Request;
use App\Models\AnnexeAnattDepartement;
use Illuminate\Support\Facades\Validator;

class AnnexeAnattController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-admin/annexe-annats",
     *     operationId="getAllAnnexeAnatts",
     *     tags={"AnnexeAnatts"},
     *     summary="Récupérer la liste des annexe-annats",
     *     description="Récupère une liste de tous les annexe-annats enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des annexe-annats récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'annexe anatt",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="le nom de l'annexe",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="adresse_annexe",
     *                      description="adresse de l'annexe anatt",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="téléphone de l'annexe anatt",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="conduite_lieu_adresse",
     *                      description="le lieu de la conduite",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="commune_id",
     *                      description="ID de la commune",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Status de l'annexe anatt (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="departement_ids",
     *                      description="ID des départements couverts",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="departement_id",
     *                      description="ID du département lié a la commune",
     *                      type="string"
     *                  ),
     *              )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $this->hasAnyPermission(['all', 'edit-annex-management','read-annex-management']);

        try {
            $query = AnnexeAnatt::query()->with('annexeAnattDepartements');

            // Recherche
            if ($request->has('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('name ILIKE ?', ['%' . $searchTerm . '%']);
                });
            }

            if (request('liste') == 'paginate') {
                $annexe_anatts = $query->orderByDesc('id')->paginate(10);
            } else {
                $annexe_anatts = $query->orderByDesc('id')->get();
            }

            if ($annexe_anatts->isEmpty()) {
                return $this->successResponse([], "Aucun résultat trouvé", 200);
            }

            return $this->successResponse($annexe_anatts);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/annexe-annats/departements-couverts",
     *      operationId="createAnnexeAnattsDepartement",
     *      tags={"AnnexeAnatts"},
     *      summary="Assigné a une annexe anatt ses départements couvert",
     *      description="Assigné a une annexe anatt ses départements couvert enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="annexe_anatt_id",
     *                      description="Id de l'annexe anatt",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="departement_ids",
     *                      description="ID du ou des départements associés",
     *                      type="string"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle assignation annexe anatt créé"
     *      )
     * )
     */
    public function addDepartement(Request $request)
    {
        $this->hasAnyPermission(['all', 'edit-annex-management']);
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'annexe_anatt_id' => 'required|integer|exists:annexe_anatts,id',
                    'departement_ids' => 'required|array|min:1',
                    'departement_ids.*' => 'required'
                ],
                [
                    'annexe_anatt_id.required' => 'L\'annexe anatt est obligatoire',
                    'annexe_anatt_id.integer' => 'L\'annexe anatt doit être un entier',
                    'annexe_anatt_id.exists' => 'L\'annexe anatt n\'existe pas',
                    'departement_ids.required' => 'Les département sont obligatoires',
                    'departement_ids.array' => 'Les département doivent être un tableau',
                    'departement_ids.min' => 'Les département doivent être au moins 1',
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }
            $data = $validator->validated();

            $annexe_anatt_departements = [];
            foreach ($validator->validated()['departement_ids'] as $departement_id) {
                // Vérifier si le département_id est déjà associé à un autre annexe_anatt_id
                $existingRecord = AnnexeAnattDepartement::where('departement_id', $departement_id)->first();
                if ($existingRecord) {
                    return $this->errorResponse('Le département est déjà associé à un autre annexe_anatt.', null, null, 422);
                }

                $annexe_anatt_departements[] = [
                    'annexe_anatt_id' => $data['annexe_anatt_id'],
                    'departement_id' => $departement_id
                ];
            }

            AnnexeAnattDepartement::insert($annexe_anatt_departements);

            $result = AnnexeAnatt::with('annexeAnattDepartements')->findOrFail($data['annexe_anatt_id']);

            return $this->successResponse($result, 'Assignation créée avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Échec lors de la création', null, null, 500);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-admin/annexeanatt-salle-compos/{annexe_anatt_id}",
     *      operationId="getAnnexeAnattSalleCompoById",
     *      tags={"getAllAnnexeAnatts"},
     *      summary="Récupère les salles d'une annexe anatt",
     *      description="Récupère les salles d'une annexe anatt enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="annexe_anatt_id",
     *          description="ID de l'annexe anatt dont on veut récuperer la salle",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Salle de composition récupérée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Salle de composition non trouvée"
     *      )
     * )
     */
    public function getAnnexeAnattSalles($annexe_anatt_id)
    {
        $this->hasAnyPermission(['all', 'edit-annex-management']);
        try {
            $path = "annexeanatt-salle-compos/" . $annexe_anatt_id;
            $response =  Api::base('GET', $path);
            // $response = Http::withOptions(['verify' => false])->get('https://4jwr.l.time4vps.cloud:8083/api/anatt-base/agregateurs/'.$id);

            if ($response->ok()) {
                $responseData = $response->json();
                return $this->successResponseclient($responseData, 'Success', 200);
            } else {
                $errorData = $response->json();
                $errorMessages = $errorData['message'] ?? 'Une erreur s\'est produite lors de la récupération des salles.';
                $errors = $errorData['errors'] ?? null;
                return $this->errorResponseclient($errorMessages, $errors, null, $response->status());
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    public function storeMultiple(Request $request)
    {
        $this->hasAnyPermission(["all", "edit-annex-management"]);

        return $this->postToBase("salle-compo/multiple", $request->all());
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/annexe-annats",
     *      operationId="createAnnexeAnatts",
     *      tags={"AnnexeAnatts"},
     *      summary="Crée un annexe anatt",
     *      description="Crée une nouvelle annexe anatt enregistré dans la base de données",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="name",
     *                      description="le nom de l'annexe",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="adresse_annexe",
     *                      description="adresse de l'annexe anatt",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="téléphone de l'annexe anatt",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="conduite_lieu_adresse",
     *                      description="le lieu de la conduite",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="commune_id",
     *                      description="ID de la commune",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Status de l'annexe anatt (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="departement_id",
     *                      description="ID du département lié a la commune",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="departement_ids",
     *                      description="ID du ou des départements associés",
     *                      type="string"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouvelle annexe anatt créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $this->hasAnyPermission(['all', 'edit-annex-management']);
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|unique:annexe_anatts,name',
                    'adresse_annexe' => 'sometimes|max:255',
                    'email' => 'required|email|unique:annexe_anatts,email',
                    'phone' => 'sometimes|max:255|unique:annexe_anatts,phone',
                    'conduite_lieu_adresse' => 'sometimes|max:255',
                    'commune_id' => 'sometimes',
                    'departement_id' => 'required',
                    'status' => 'required|boolean',
                    'departement_ids' => 'required|array|min:1',
                    'departement_ids.*' => 'required',
                ],
                [
                    'name.required' => 'Le nom de l\'annexe est obligatoire',
                    'name.unique' => 'Cette annexe existe déjà',
                    'adresse_annexe.max' => 'La taille de l\'adresse de l\'annexe est trop grande',
                    'phone.max' => 'La taille du téléphone de l\'annexe est trop grande',
                    'phone.unique' => 'Ce téléphone existe déjà',
                    'conduite_lieu_adresse.max' => 'La taille de la conduite de lieu d\'adresse est trop grande',
                    'commune_id.required' => 'La commune de l\'annexe est obligatoire',
                    'departement_id.required' => 'Le département de l\'annexe est obligatoire',
                    'status.required' => 'Le status de l\'annexe est obligatoire',
                    'status.boolean' => 'Le status de l\'annexe doit être un booléen',
                    'departement_ids.required' => 'Le département de l\'annexe est obligatoire',
                    'departement_ids.array' => 'Le département de l\'annexe doit être un tableau',
                    'departement_ids.min' => 'Le département de l\'annexe doit être supérieur à 0',
                    'departement_ids.*.required' => 'Le département de l\'annexe doit être supérieur à 0',
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }
            $data = $validator->validated();
            unset($data['departement_ids']);
            foreach ($validator->validated()['departement_ids'] as $departement_id) {
                // Vérifier si le département_id est déjà associé à un autre annexe_anatt_id
                $existingRecord = AnnexeAnattDepartement::where('departement_id', $departement_id)->first();
                if ($existingRecord) {
                    return $this->errorResponse('Le département est déjà associé à un autre Annexe ANaTT.', null, null, 422);
                }
            }
            $annexe_anatt = AnnexeAnatt::create($data);
            $annexe_anatt_departements = [];
            foreach ($validator->validated()['departement_ids'] as $departement_id) {

                $annexe_anatt_departements[] = [
                    'annexe_anatt_id' => $annexe_anatt->id,
                    'departement_id' => $departement_id
                ];
            }
            AnnexeAnattDepartement::insert($annexe_anatt_departements);
            $result = AnnexeAnatt::with('annexeAnattDepartements')->findOrFail($annexe_anatt->id);

            return $this->successResponse($result, 'Annexe ANaTT créée avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Échec lors de la création', null, null, 500);
        }
    }


    /**
     * @OA\Put(
     *      path="/api/anatt-admin/annexe-annats/{id}",
     *      operationId="updateAnnexeAnatts",
     *      tags={"AnnexeAnatts"},
     *      summary="Met à jour une annexe anatt existant",
     *      description="Met à jour une annexe anatt existant dans la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'annexe anatt à mettre à jour",
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
     *                      description="le nom de l'annexe",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="adresse_annexe",
     *                      description="adresse de l'annexe anatt",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="téléphone de l'annexe anatt",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="conduite_lieu_adresse",
     *                      description="le lieu de la conduite",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="commune_id",
     *                      description="ID de la commune",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Status de l'annexe anatt (optionnel)",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="departement_ids",
     *                      description="ID du ou des départements associés",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="departement_id",
     *                      description="ID du département lié a la commune",
     *                      type="string"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Annexe ANaTT mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Annexe ANaTT non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $this->hasAnyPermission(['all', 'edit-annex-management']);
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => "required|unique:annexe_anatts,name,$id",
                    'email' => "required|unique:annexe_anatts,email,$id",
                    'adresse_annexe' => 'sometimes|max:255',
                    'phone' => "sometimes|max:25|unique:annexe_anatts,phone,$id",
                    'conduite_lieu_adresse' => 'sometimes|max:255',
                    'commune_id' => 'sometimes',
                    'departement_id' => 'required',
                    'status' => 'sometimes|boolean',
                    'departement_ids' => 'sometimes|array|min:1',
                    'departement_ids.*' => 'sometimes',
                ],
                [
                    'name.required' => 'Le nom de l\'annexe est obligatoire',
                    'name.unique' => 'Le nom de l\'annexe doit être unique',
                    'adresse_annexe.max' => 'La taille de l\'adresse de l\'annexe ne doit pas dépasser 255 caractères',
                    'phone.max' => 'La taille du téléphone de l\'annexe ne doit pas dépasser 25 caractères',
                    'phone.unique' => 'Le téléphone de l\'annexe doit être unique',
                    'conduite_lieu_adresse.max' => 'La taille de la conduite de lieu d\'adresse de l\'annexe ne doit pas dépasser 255 caractères',
                    'commune_id.required' => 'La commune de l\'annexe est obligatoire',
                    'departement_id.required' => 'Le département de l\'annexe est obligatoire',
                    'status.boolean' => 'Le status de l\'annexe doit être un booléen',
                    'departement_ids.array' => 'Le département de l\'annexe doit être un tableau',
                    'departement_ids.min' => 'Le département de l\'annexe doit être un tableau',


                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }

            $annexe_anatt = AnnexeAnatt::findOrFail($id);
            $data = $validator->validated();
            unset($data['departement_ids']);
            $annexe_anatt->fill($data)->save();

            // $annexe_anatt = AnnexeAnatt::findOrFail($id);
            // $annexe_anatt->fill($validator->validated())->save();

            if ($request->has('departement_ids')) {
                $departement_ids = $request->input('departement_ids');
                $annexe_anatt_departements = collect($departement_ids)->map(function ($departement_id) {
                    return ['departement_id' => $departement_id];
                })->toArray();
                $annexe_anatt->annexeAnattDepartements()->delete();
                $annexe_anatt->annexeAnattDepartements()->createMany($annexe_anatt_departements);
            }

            $result = AnnexeAnatt::with('annexeAnattDepartements')->findOrFail($annexe_anatt->id);

            return $this->successResponse($result, 'Annexe ANaTT mise à jour avec succès');

            if ($request->has('departement_ids')) {
                $annexe_anatt->annexeAnattDepartements()->sync($request->input('departement_ids'));
            }

            $result = AnnexeAnatt::with('annexeAnattDepartements')->findOrFail($annexe_anatt->id);

            return $this->successResponse($result, 'Annexe ANaTT mise à jour avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Échec lors de la mise à jour', null, null, 500);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-admin/annexe-annats/{id}",
     *      operationId="getAnnexeAnattsById",
     *      tags={"AnnexeAnatts"},
     *      summary="Récupère une annexe anatt par ID",
     *      description="Récupère une annexe anatt enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'annexe anatt à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Annexe ANaTT récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Annexe ANaTT non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            $annexe = AnnexeAnatt::with('annexeAnattDepartements')->find($id);
            if (!$annexe) {
                return $this->errorResponse('Annexe introuvable');
            }
            return $this->successResponse($annexe);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }


    /**
     * @OA\Delete(
     *      path="/api/anatt-admin/annexe-annats/{id}",
     *      operationId="deleteAnnexeAnatts",
     *      tags={"AnnexeAnatts"},
     *      summary="Supprime une annexe anatt",
     *      description="Supprime une annexe anatt de la base de données",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'anatt à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Annexe ANaTT supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Annexe ANaTT non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(['all', 'edit-annex-management']);
        try {
            $data = AnnexeAnatt::findOrFail($id);
            AnnexeAnattDepartement::where('annexe_anatt_id', $id)->delete();
            $data->delete();
            return $this->successResponse(null, 'Suppression effectuée avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Échec lors de la suppression', null, null, 500);
        }
    }

    private function annexeanattExist(string $annexe_anatt_ids)
    {
        $ids = explode(";", $annexe_anatt_ids);
        // Si toutes les annexes exists
        return collect($ids)->every(fn ($id) => AnnexeAnatt::whereId(intval($id))->exists());
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/annexe-annats/status",
     *      operationId="createAnnexeAnattsStatus",
     *      tags={"AnnexeAnatts"},
     *      summary="Désactivation ou activation d'une annexe",
     *      description="Désactivation ou activation d'une annexe",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="annexe_anatt_id",
     *                      description="id de l'annexe",
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
     *          description="l'annexe n'a pas été trouvé"
     *      )
     * )
     */
    public function status(Request $request)
    {
        $this->hasAnyPermission(['all', 'edit-annex-management']);
        try {
            $validator = Validator::make($request->all(), [
                'annexe_anatt_id' => 'required',
                'status' => 'required'
            ], [
                'annexe_anatt_id.required' => 'Aucune annexe n\'a été sélectionnée',
                'status.required' => 'Aucun statut n\'a été envoyé'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), '', 419);
            }

            $annexe_anatt_id = $request->get('annexe_anatt_id');
            $status = $request->get('status');
            if (!$this->annexeanattExist($annexe_anatt_id)) {
                return $this->errorResponse('Vérifiez que l\'annexe sélectionné existe', $validator->errors());
            }

            AnnexeAnatt::where('id', $annexe_anatt_id)->update(['status' => $status]);
            $annexe_anatt = AnnexeAnatt::findOrFail($annexe_anatt_id); // récupérer l'utilisateur mis à jour
            return $this->successResponse(['annexe_anatt' => $annexe_anatt, 'message' => 'Mise à jour effectué avec succès']);
        } catch (\Throwable $th) {
            // Gérer l'erreur et la journaliser
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour");
        }
    }
}
