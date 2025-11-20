<?php

namespace App\Http\Controllers;

use App\Models\CategoriePermis;
use App\Models\Chapitre;
use App\Models\Commune;
use App\Models\Departement;
use App\Models\Langue;
use App\Services\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BaseController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-autoecole/chapitres-base",
     *     operationId="getAllChapitres",
     *     tags={"Base"},
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
    public function index()
    {
        try {
            $data = Chapitre::orderBy('name')->get();
            return $this->successResponse($data);
        } catch (\Throwable $th) {
            logger()->error($th);
            // Retourner une réponse d'erreur en cas d'exception
            return $this->errorResponseclient('Une erreur s\'est produite lors de la récupération des chapitres.',);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/anatt-autoecole/langues-base",
     *     operationId="getAllLangues",
     *     tags={"Base"},
     *     summary="Récupérer la liste des langues",
     *     description="Récupère une liste de tous les langues enregistrées dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des langues récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la langue",
     *                      type="integer",
     *                      example=1
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de la langue",
     *                      type="string",
     *                      example="Ain"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut de la langue (optionnel)",
     *                      type="boolean"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function getLangue()
    {
        try {
            $data = Langue::orderBy('name')->get();
            return $this->successResponse($data);
        } catch (\Throwable $th) {
            logger()->error($th);
            // Retourner une réponse d'erreur en cas d'exception
            return $this->errorResponseclient('Une erreur s\'est produite lors de la récupération des informations.',);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/anatt-autoecole/categorie-permis-base",
     *     operationId="getAllCategoriePermis",
     *     tags={"Base"},
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
    public function getPermis()
    {
        try {
            $data = CategoriePermis::orderBy('name')->get();
            return $this->successResponse($data);
        } catch (\Throwable $th) {
            logger()->error($th);
            // Retourner une réponse d'erreur en cas d'exception
            return $this->errorResponseclient('Une erreur s\'est produite lors de la récupération des informations.');
        }
    }

    public function communes()
    {
        try {
            $communes = Commune::with("departement")->orderBy('name')->get()
                ->sortBy(function (Commune $commune) {
                    return $commune->departement->name;
                })->values();
            return $this->successResponse($communes);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors de la récupération des communes.');
        }
    }

    public function departements()
    {
        try {
            $deps = Departement::orderBy('name')->get();
            return $this->successResponse($deps);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors de la récupération des départements.');
        }
    }
}
