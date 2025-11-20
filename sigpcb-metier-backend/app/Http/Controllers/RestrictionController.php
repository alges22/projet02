<?php

namespace App\Http\Controllers;
use Throwable;
use App\Services\Api;
use Illuminate\Http\Request;

use App\Models\Admin\Restriction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class RestrictionController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-candidat/restrictions",
     *     operationId="getAllRestrictions",
     *     tags={"Restrictions"},
     *     summary="Récupérer la liste des restrictions",
     *     description="Récupère une liste de toutes les restrictions enregistrées dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des restrictions récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la restriction",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de la restriction",
     *                      type="string"
     *                  )
     *              )
     *         )
     *     )
     * )
     */


    public function index()
    {
        try {
            $restrictions = Restriction::orderBy('id', 'desc')->get();
            return $this->successResponse($restrictions);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse($e->getMessage());
        }

    }
}
