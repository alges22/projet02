<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;

class RoleController extends ApiController
{

    /**
     * @OA\Get(
     *     path="/api/anatt-admin/roles",
     *     operationId="getAllRoles",
     *     tags={"Rôle"},
     *     summary="Récupérer la liste des roles",
     *     description="Récupère une liste de tous les rôles enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des rôles récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du role",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="le nom du role",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="nom_complet",
     *                      description="Une description",
     *                      type="string"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $this->hasAnyPermission(["all", "read-roles","edit-roles"]);
        try {
            $roles = Role::with('permissions')->orderByDesc('id')->get();

            return $this->successResponse($roles);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }


    /**
     * @OA\Post(
     *      path="/api/anatt-admin/roles",
     *      operationId="createRole",
     *      tags={"Rôle"},
     *      summary="Crée un nouveau rôle",
     *      description="Crée un nouveau rôle enregistré dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="nom_complet",
     *                      description="Nom du rôle",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="permissions",
     *                      description="Les permission associés",
     *                      type="string"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau rôle créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $this->hasAnyPermission(["all","edit-roles"]);

        try {
            // Valider les données de la requête
            $validator = Validator::make(
                $request->all(),
                [
                    'nom_complet' => 'required|string|max:255|unique:roles',
                    'permissions' => 'required|array|min:1'
                ],
                [
                    'nom_complet.required' => 'Le nom du rôle est obligatoire',
                    'nom_complet.string' => 'Le nom du rôle doit être de type string',
                    'nom_complet.max' => 'Le nom du rôle ne doit pas dépasser 255 caractères',
                    'nom_complet.unique' => 'Le nom du rôle doit être unique',
                    'permissions.required' => 'Les permissions sont obligatoires',
                    'permissions.array' => 'Les permissions doivent être de type array',
                    'permissions.min' => 'Les permissions doivent être composé d\'au moins 1 permission'
                ]
            );

            // Si la validation échoue, retourner une réponse d'erreur
            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            // Créer un nouveau rôle
            $name = Str::slug($request->input('nom_complet'));
            $permission = $request->input('permissions');
            $role = Role::create(['name' => $name, 'guard_name' => 'web', 'nom_complet' => $request->input('nom_complet')]);
            $role->syncPermissions($permission);
            // Retourner une réponse réussie avec le rôle créé
            return $this->successResponse($role, "Le rôle a été créé avec succès.");
        } catch (\Throwable $th) {
            // Enregistrer l'erreur dans les logs
            logger()->error($th);
            // Retourner une réponse d'erreur générique
            return $this->errorResponse("Une erreur inattendue s'est produite.", 500);
        }
    }

    /**
     * @OA\Put(
     *      path="/api/anatt-admin/roles/{id}",
     *      operationId="updateRôles",
     *      tags={"Rôle"},
     *      summary="Met à jour un rôle existant",
     *      description="Met à jour un rôle existant dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du role à mettre à jour",
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
     *                      property="nom_complet",
     *                      description="Nom du rôle",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="permissions",
     *                      description="Les permissions associés",
     *                      type="string"
     *                  ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Role mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Rôle non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)

    {
        $this->hasAnyPermission(["all","edit-roles"]);
        try {
            // Trouver le rôle à mettre à jour
            $role = Role::find($id);
            if (!$role) {
                return $this->errorResponse("Le rôle n'existe pas.");
            }

            // Valider les données de la requête
            $validator = Validator::make($request->all(), [
                'nom_complet' => 'required|string|max:255|unique:roles,name,' . $id,
                'permissions' => 'required|array|min:1'
            ]);

            // Si la validation échoue, retourner une réponse d'erreur
            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            // Mettre à jour le nom du rôle
            $name = Str::slug($request->input('nom_complet'));
            $role->update(['name' => $name, 'guard_name' => 'web', 'nom_complet' => $request->input('nom_complet')]);

            // Mettre à jour les permissions du rôle
            $permission = $request->input('permissions');
            $role->syncPermissions($permission);

            // Retourner une réponse réussie avec le rôle mis à jour
            return $this->successResponse($role, "Le rôle a été mis à jour avec succès.");
        } catch (\Throwable $th) {
            // Enregistrer l'erreur dans les logs
            logger()->error($th);

            // Retourner une réponse d'erreur générique
            return $this->errorResponse("Une erreur inattendue s'est produite.", 500);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-admin/roles/{id}",
     *      operationId="getRoleById",
     *      tags={"Rôle"},
     *      summary="Récupère un role par ID",
     *      description="Récupère un role enregistré dans la base de données en spécifiant son ID",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du role à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="role récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="role non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            $role = Role::with('permissions')->find($id);
            if (!$role) {
                return $this->errorResponse('Rôle introuvable', null, null, 422);
            }
            return $this->successResponse($role);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }


    /**
     * @OA\Delete(
     *      path="/api/anatt-admin/roles/{id}",
     *      operationId="deleteRoles",
     *      tags={"Rôle"},
     *      summary="Supprime un role",
     *      description="Supprime un role de la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du role à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Role supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Role non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(["all","edit-roles"]);

        try {
            // Trouver le rôle à supprimer
            $role = Role::findOrFail($id);

            // Supprimer toutes les permissions associées
            $role->permissions()->detach();

            // Supprimer le rôle
            $role->delete();

            // Retourner une réponse réussie
            return $this->successResponse(null, "Le rôle a été supprimé avec succès.");
        } catch (\Throwable $th) {
            // Enregistrer l'erreur dans les logs
            logger()->error($th);

            // Retourner une réponse d'erreur générique
            return $this->errorResponse("Une erreur inattendue s'est produite.", 500);
        }
    }
}
