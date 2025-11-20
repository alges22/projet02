<?php
namespace App\Http\Controllers;
use Spatie\Permission\Models\Permission;

class PermissionController extends ApiController
{

    /**
     * @OA\Get(
     *     path="/api/anatt-admin/permissions",
     *     operationId="getAllPermissions",
     *     tags={"Permissions"},
     *     summary="Récupérer la liste des permissions",
     *     description="Récupère une liste des permissions enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des permissions récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la permission",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="le nom de la permission",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="nom_complet",
     *                      description="Le nom a afficher",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      description="la description de la permission",
     *                      type="string"
     *                  ),
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            // Récupérer toutes les permissions groupées par onglet
            $permissions = Permission::all()->groupBy('onglet');

            // Retourner une réponse réussie avec les permissions groupées
            return $this->successResponse($permissions);
        } catch (\Throwable $th) {
            // Enregistrer l'erreur dans les logs
            logger()->error($th);

            // Retourner une réponse d'erreur générique
            return $this->errorResponse("Une erreur inattendue s'est produite.");
        }
    }

}
