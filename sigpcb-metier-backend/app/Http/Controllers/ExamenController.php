<?php
namespace App\Http\Controllers;
use Exception;
use App\Services\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ExamenController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/anatt-candidat/examens",
     *     operationId="getAllExamen",
     *     tags={"Examen"},
     *     summary="Récupérer la liste des examens",
     *     description="Récupère une liste de tous les examens enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des candidatq récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'examen",
     *                      type="integer"
     *                  ),
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $path = "examens";
            $response = Api::admin('GET', $path);
    
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
    
}

