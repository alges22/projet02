<?php

namespace App\Http\Controllers;

use App\Models\UniteAdmin;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class UniteAdminController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-admin/unite-admins",
     *     operationId="getAllUniteAdmins",
     *     tags={"UniteAdmins"},
     *     summary="Récupérer la liste des unite-admins",
     *     description="Récupère une liste de tous les unite-admins enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des unite-admins récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'unite-admin",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de l'unite-admin",
     *                      type="string"
     *                  ),
     *                 @OA\Property(
     *                     property="ua_parent_id",
     *                     description="ID de l'unite administrative parent",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="sigle",
     *                     description="Le sigle",
     *                     type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Status de l'unité admini (optionnel)",
     *                      type="boolean"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $this->hasAnyPermission(["all","edit-administrative-units-management","read-administrative-units-management"]);

        try {
            $query = UniteAdmin::query();

            if ($request->has('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('LOWER(name) LIKE ?', [strtolower($searchTerm)])
                        ->orWhereRaw('LOWER(sigle) LIKE ?', [strtolower($searchTerm)]);
                });
            }

            $unite_admins = $query->orderByDesc('id')->get();

            if ($unite_admins->isEmpty()) {
                return $this->successResponse([], "Aucun résultat trouvé", 200);
            }

            return $this->successResponse($unite_admins);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
        }
    }

    /**
     * Store unite admin
     *
     * @param Request $request
     */
    /**
     * @OA\Post(
     *      path="/api/anatt-admin/unite-admins",
     *      operationId="createUniteAdmins",
     *      tags={"UniteAdmins"},
     *      summary="Crée un nouveau unite-admin",
     *      description="Crée un nouveau unite-admin enregistré dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de l'unite-admin",
     *                      type="string"
     *                  ),
     *                 @OA\Property(
     *                     property="ua_parent_id",
     *                     description="ID de l'unite administrative parent",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="sigle",
     *                     description="Le sigle",
     *                     type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Status de l'unité admini (optionnel)",
     *                      type="boolean"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau unite-admin créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $this->hasAnyPermission(["all","edit-administrative-units-management"]);

        $data = [];
        try {
            // Validate data
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255|unique:unite_admins',
                    'sigle' => 'required|string|unique:unite_admins',
                    'status' => 'required|boolean',
                ],
                [
                    'name.required' => 'Le champ nom est obligatoire',
                    'name.string' => 'Le champ nom doit être de type string',
                    'name.max' => 'Le champ nom ne doit pas dépasser 255 caractères',
                    'name.unique' => 'Le nom de l\'unite-admin est déjà utilisé',
                    'sigle.required' => 'Le champ sigle est obligatoire',
                    'sigle.string' => 'Le champ sigle doit être de type string',
                    'sigle.unique' => 'Le sigle de l\'unite-admin est déjà utilisé',
                    'status.required' => 'Le champ status est obligatoire',
                    'status.boolean' => 'Le champ status doit être de type boolean',

                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors());
            }
            //valide data
            $data =  $validator->validated();

            //Si parent id exists
            if ($request->has('ua_parent_id') && intval($request->get('ua_parent_id')) > 0) {
                $parent_id = $request->get('ua_parent_id');

                $parentUniteAdmin = UniteAdmin::find($parent_id);
                //Si parent id existe on l'ajoute
                if ($parentUniteAdmin) {
                    $data['ua_parent_id'] = $parent_id;
                } else {
                    return $this->errorResponse('Unité administrative parent introuvable');
                }
            }

            $unite_admin = UniteAdmin::create($data);

            // Return response
            return $this->successResponse($unite_admin, 'Unité administrative créée avec succès.', 201);
        } catch (\Throwable $e) {
            // Handle error and log it
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la creation de l\'unité administrative');
        }
    }


    /**
     * @OA\Put(
     *      path="/api/anatt-admin/unite-admins/{id}",
     *      operationId="updateUniteAdmins",
     *      tags={"UniteAdmins"},
     *      summary="Met à jour un unite-admin existant",
     *      description="Met à jour un unite-admin existant dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'unite-admin à mettre à jour",
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
     *                      description="Nom de l'unite-admin",
     *                      type="string"
     *                  ),
     *                 @OA\Property(
     *                     property="ua_parent_id",
     *                     description="ID de l'unite administrative parent",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="sigle",
     *                     description="le sigle",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     description="le statut",
     *                     type="boolean"
     *                 )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="UniteAdmin mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="UniteAdmin non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $this->hasAnyPermission(["all","edit-administrative-units-management"]);

        try {
            // Check if UniteAdmin exists
            $unite_admin = UniteAdmin::find($id);

            if (!$unite_admin) {
                return $this->errorResponse('L\'unité administrateur n\'a pas été trouvée.', [], null, 422);
            }

            // Validate data
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255', Rule::unique("unite_admins")->ignore($id)],
                'sigle' => ['required', 'string', Rule::unique("unite_admins")->ignore($id)],
                'status' => ['required'],
                'ua_parent_id' => 'sometimes',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors());
            }

            // Check if parent ID is valid
            if ($request->has('ua_parent_id')) {
                $parent_id = $request->get('ua_parent_id');
                if ($parent_id == $unite_admin->id) {
                    return $this->errorResponse('L\'unité administrateur parent ne peut pas être l\'unité elle-même.');
                }

                $parentUniteAdmin = UniteAdmin::find($parent_id);
                if (!$parentUniteAdmin) {
                    return $this->errorResponse('Unité administrative parent introuvable.');
                }
            }

            $data =  $validator->validated();

            // Update data
            $unite_admin->update($data);

            // Return response
            return $this->successResponse($unite_admin, 'Unité administrative mise à jour avec succès.');
        } catch (\Throwable $e) {
            // Handle error and log it
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour de l'unité administrative.");
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-admin/unite-admins/{id}",
     *      operationId="getUniteAdminsById",
     *      tags={"UniteAdmins"},
     *      summary="Récupère un unite-admin par ID",
     *      description="Récupère un unite-admin enregistré dans la base de données en spécifiant son ID",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'unite-admin à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="UniteAdmin récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="UniteAdmin non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            // Check if resource exists
            $unite_admin = UniteAdmin::find($id);

            if (!$unite_admin) {
                return $this->errorResponse('L\'unité administrateur n\'a pas été trouvée.', [], null, 422);
            }

            // Return response
            return $this->successResponse($unite_admin);
        } catch (\Throwable $e) {
            // Handle error and log it
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la récupération de l'unité administrative");
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/anatt-admin/unite-admins/{id}",
     *      operationId="deleteUniteAdmins",
     *      tags={"UniteAdmins"},
     *      summary="Supprime un unite-admin",
     *      description="Supprime un unite-admin de la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'unite-admin à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="UniteAdmin supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="UniteAdmin non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(["all","edit-administrative-units-management"]);

        try {
            // Check if resource exists
            $unite_admin = UniteAdmin::find($id);

            if (!$unite_admin) {
                return $this->errorResponse('L\'unité administrateur n\'a pas été trouvée.', [], null, 422);
            }

            // Delete resource
            try {
                $unite_admin->delete();
            } catch (\Illuminate\Database\QueryException $e) {
                // Catch foreign key constraint violation exception
                return $this->errorResponse("L'unité administrative ne peut pas être supprimée car elle a des liaisons existantes", [], null, 409);
            }

            // Return success response
            return $this->successResponse(["message" => 'Unité administrative supprimée avec succès']);
        } catch (\Throwable $e) {
            // Handle error and log it
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la suppression de l'unité administrative");
        }
    }


    private function uniteadminExist(string $unite_admin_ids)
    {
        $ids = explode(";", $unite_admin_ids);
        // Si tous les users exists
        return collect($ids)->every(fn ($id) => UniteAdmin::whereId(intval($id))->exists());
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/unite-admins/status",
     *      operationId="createUniteAdminStatus",
     *      tags={"UniteAdmins"},
     *      summary="Désactivation ou activation d'une unité admin",
     *      description="Désactivation ou activation d'une unité admin",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="unite_admin_id",
     *                      description="id de l'unité admin",
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
     *          description="Mise à jour effectué avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="l'unité admin n'a pas été trouvé"
     *      )
     * )
     */
    public function status(Request $request)
    {
        $this->hasAnyPermission(["all","edit-administrative-units-management"]);
        try {
            $validator = Validator::make($request->all(), [
                'unite_admin_id' => 'required',
                'status' => 'required'
            ], [
                'unite_admin_id.required' => 'Aucune unité admin n\'a été sélectionné',
                'status.required' => 'Aucun statut n\'a été envoyé'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), '', 422);
            }

            $unite_admin_id = $request->get('unite_admin_id');
            $status = $request->get('status');
            if (!$this->uniteadminExist($unite_admin_id)) {
                return $this->errorResponse('Vérifiez que l\'unité admin sélectionné existe', $validator->errors());
            }

            UniteAdmin::where('id', $unite_admin_id)->update(['status' => $status]);
            $unite_admin = UniteAdmin::findOrFail($unite_admin_id); // récupérer l'unite_admin mis à jour
            return $this->successResponse(['unite_admin' => $unite_admin, 'message' => 'Mise à jour effectué avec succès']);
        } catch (\Throwable $th) {
            // Gérer l'erreur et la journaliser
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour");
        }
    }
}
