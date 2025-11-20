<?php

namespace App\Http\Controllers;

use App\Services\Api;

class AdminController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-autoecole/restrictions-admin",
     *     operationId="getAllRestrictions",
     *     tags={"Admin"},
     *     summary="Récupérer la liste des restrictions",
     *     description="Récupère une liste de toutes les restrictions enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des chapitres récupérés avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la restriction",
     *                      type="integer"
     *                  ),
     *             @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      description="Nom de la restriction"),
     *              ),
     *         )
     *     )
     * )
     */
    // public function index()
    // {
    //     try {
    //         $path = "restrictions";
    //         $response = Api::admin('GET', $path);

    //         $data = Api::data($response);

    //         //Le moins indique qu'il y a une erreur sur le serveur distant
    //         if ($data === -1) {
    //             return $this->errorResponse('Aucun résultat trouvé', 404);
    //         }

    //         return $this->successResponse($data);
    //     } catch (\Throwable $e) {
    //         logger()->error($e);
    //         return $this->errorResponse('Une erreur est survenue lors de la récupération des restrictions.', 500);
    //     }
    // }
}
