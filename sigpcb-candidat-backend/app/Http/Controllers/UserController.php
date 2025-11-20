<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    /**
     * @OA\Get(
     *      path="/api/anatt-candidat/users",
     *      operationId="getCandidat",
     *      tags={"Users"},
     *      summary="Obtient la liste des candidats",
     *      description="Obtient la liste des candidats enregistrés dans la base de données",
     *      @OA\Response(
     *          response=200,
     *          description="Liste des candidats",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du candidats",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="npi",
     *                      description="npi du candidat",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="has_dossier_permi",
     *                      description="sil a deja un dossier candidat ",
     *                      type="integer",
     *                  ),
     *              )
     *          )
     *      )
     * )
     */
    public function index()
    {
        try {
            $candidat_ecrit_notes = User::all();
            return $this->successResponse($candidat_ecrit_notes, 'Liste de candidats récupérée avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération de la liste');
        }
    }
}
