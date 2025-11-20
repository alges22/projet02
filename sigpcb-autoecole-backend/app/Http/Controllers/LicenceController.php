<?php

namespace App\Http\Controllers;

use App\Models\Licence;
use App\Services\Help;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LicenceController extends ApiController

{
    /**
     * @OA\Get(
     *     path="/api/anatt-autoecole/licences",
     *     summary="Récupère toutes les licences",
     *     description="Retourne toutes les licences enregistrées dans la base de données",
     *     operationId="getAllLicences",
     *     tags={"Licences"},
     *     @OA\Response(
     *         response=200,
     *         description="La liste des licences",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la licence",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="auto_ecole_id",
     *                      description="ID de l'auto ecole",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="le statut de la demande",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="date_debut",
     *                      description="la date de début de la licence",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="date_fin",
     *                      description="la date de fin de la licence",
     *                      type="string"
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *     )
     * )
     */
    public function index()
    {
        try {
            $licences = Licence::where('auto_ecole_id', Help::authAutoEcole()->id)->get();
            return $this->successResponse($licences);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la récupération des licences", null, null, 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/anatt-autoecole/licences",
     *     tags={"Licences"},
     *     summary="Créer une licence",
     *     description="Créer une nouvelle licence pour une auto-école",
     *     @OA\RequestBody(
     *         required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="auto_ecole_id",
     *                      description="ID de l'auto ecole",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="le statut de la demande",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="date_debut",
     *                      description="la date de début de la licence",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="date_fin",
     *                      description="la date de fin de la licence",
     *                      type="string"
     *                  ),
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Licence créée avec succès",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation des données",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'auto_ecole_id' => 'required|integer|exists:auto_ecoles,id',
                'status' => 'required|boolean',
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date',
            ], [
                'auto_ecole_id.required' => 'Le champ auto_ecole_id est obligatoire.',
                'auto_ecole_id.integer' => 'Le champ auto_ecole_id doit être un entier.',
                'status.required' => 'Le champ status est obligatoire.',
                'date_debut.date' => 'Le champ date_debut doit être une date valide.',
                'date_fin.date' => 'Le champ date_fin doit être une date valide.',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }

            $licence = Licence::create($request->all());
            return $this->successResponse($licence, 'Licence enregistrée avec succès', 201);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la création de la licence", null, null, 500);
        }
    }
}
