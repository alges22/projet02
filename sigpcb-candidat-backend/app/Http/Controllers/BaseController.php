<?php

namespace App\Http\Controllers;

use App\Services\Api;
use App\Models\Langue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Base\CategoriePermis;

class BaseController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-candidat/agregateurs-base",
     *     operationId="getAllAgregateur",
     *     tags={"Base"},
     *     summary="Récupérer la liste des agregateurs",
     *     description="Récupère une liste de tous les agregateurs enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des chapitres récupérés avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'agregateur",
     *                      type="integer"
     *                  ),
     *             @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      description="Nom de l'agregateur"),
     *              ),
     *             @OA\Property(
     *                      property="photo",
     *                      type="string",
     *                      description="La photo"),
     *              ),
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $path = "agregateurs";
            $response = Api::base('GET', $path);

            $data = Api::data($response);

            //Le moins indique qu'il y a une erreur sur le serveur distant
            if ($data === -1) {
                return $this->errorResponse('Aucun résultat trouvé', 404);
            }

            return $this->successResponse($data);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des dossiers de suivi des candidats.', 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/anatt-candidat/langues-base",
     *     operationId="getAllLangues",
     *     tags={"Base"},
     *     summary="Récupérer la liste des langues",
     *     description="Récupère une liste de toutes les langues enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des langues récupérés avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de la langue",
     *                      type="integer"
     *                  ),
     *             @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      description="Nom de la langue"),
     *              ),
     *         )
     *     )
     * )
     */
    public function getLangues()
    {
        try {
            $data = Langue::where('status', true)->get();
            return $this->successResponse($data);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/anatt-candidat/categorie-permis-base",
     *     operationId="getAllCatPermis",
     *     tags={"Base"},
     *     summary="Récupérer la liste des categories de permis",
     *     description="Récupère une liste des categorie de permis enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des categorie de permis récupérés avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID du categorie permis",
     *                      type="integer"
     *                  ),
     *             @OA\Property(
     *                      property="name",
     *                      type="string",
     *                      description="Nom du categorie permis"),
     *              ),
     *         )
     *     )
     * )
     */
 
    public function getCatPermis()
    {
        try {
            $categorie_permis = CategoriePermis::with(['trancheage', 'extensions', 'permisPrealable'])
                ->orderBy('name', 'asc')
                ->get();

            return $this->successResponse($categorie_permis);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }


    public function getBaremeConduiteByPermis($categorie_permis_id)
    {
        try {
            $path = "bareme-conduites/categorie-permis/" . $categorie_permis_id;
            $response = Api::base('GET', $path);

            $data = Api::data($response);

            if ($data === -1) {
                return $this->errorResponse('Aucun résultat trouvé', 404);
            }

            return $this->successResponse($data);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération', 500);
        }
    }
}
