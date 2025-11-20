<?php

namespace App\Http\Controllers;

use App\Models\DossierCandidat;
use App\Models\DossierSession;
use App\Models\User;
use Illuminate\Http\Request;

class CountController extends ApiController
{
    protected $counts = [
        "dossier_rejets_count",
        "dossier_init_count",
        "dossier_pending_count",
        "dossier_validate_count",
    ];



    /**
     * @OA\Get(
     *     path="/api/anatt-candidat/counts",
     *     summary="Récupère les compteurs",
     *     tags={"Compteurs"},
     *     @OA\Parameter(
     *         name="counts",
     *         in="query",
     *         description="Liste des compteurs à récupérer, séparés par des virgules",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compteurs récupérés avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="monitoring_count", type="integer", example=69),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête invalide",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Les paramètres 'counts' sont obligatoires")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Une erreur s'est produite lors de la récupération des compteurs")
     *         )
     *     )
     * )
     */
    public function __invoke()
    {
        //recupère les paramètres counts s'il en existe

        $_parameters = request('counts');

        // Formate sous forme [users_count,roles_count, titres] dépend de ce que le frontend envoie
        $counter_parameters = explode(',', $_parameters);
        //Filtre pour éviter les valeurs truquées
        $counts = array_intersect($this->counts, $counter_parameters);

        $data = [];
        try {
            foreach ($counts as $key => $c) {
                $data[$c] = call_user_func_array([$this, trim($c)], []);
            }
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
        // Si tout se passe bien, il renverra : [users_count=>69, etc]
        return $this->successResponse($data);
    }


    private function dossier_rejets_count()
    {
        // Récupérer l'ID de l'auto-école à partir de la requête
        $autoEcoleId = request('auto_ecole_id');

        // Vérifier si l'ID de l'auto-école est spécifié dans la requête
        if (!$autoEcoleId) {
            // Si l'ID n'est pas spécifié, renvoyer une erreur
            return $this->errorResponse('ID de l\'auto-école non spécifié dans la requête.', null, 400);
        }

        // Récupérer le nombre de dossiers rejetés de l'auto-école spécifiée
        $count = DossierSession::where('auto_ecole_id', $autoEcoleId)
            ->where('state', 'rejet')
            ->count();

        return $count;
    }

    private function dossier_init_count()
    {
        // Récupérer l'ID de l'auto-école à partir de la requête
        $autoEcoleId = request('auto_ecole_id');

        // Vérifier si l'ID de l'auto-école est spécifié dans la requête
        if (!$autoEcoleId) {
            // Si l'ID n'est pas spécifié, renvoyer une erreur
            return $this->errorResponse('ID de l\'auto-école non spécifié dans la requête.', null, 400);
        }

        // Récupérer le nombre de dossiers rejetés de l'auto-école spécifiée
        $count = DossierSession::where('auto_ecole_id', $autoEcoleId)
            ->where('state', 'init')
            ->count();

        return $count;
    }

    private function dossier_pending_count()
    {
        // Récupérer l'ID de l'auto-école à partir de la requête
        $autoEcoleId = request('auto_ecole_id');

        // Vérifier si l'ID de l'auto-école est spécifié dans la requête
        if (!$autoEcoleId) {
            // Si l'ID n'est pas spécifié, renvoyer une erreur
            return $this->errorResponse('ID de l\'auto-école non spécifié dans la requête.', null, 400);
        }

        // Récupérer le nombre de dossiers rejetés de l'auto-école spécifiée
        $count = DossierSession::where('auto_ecole_id', $autoEcoleId)
            ->where('state', 'pending')
            ->count();

        return $count;
    }

    private function dossier_validate_count()
    {
        // Récupérer l'ID de l'auto-école à partir de la requête
        $autoEcoleId = request('auto_ecole_id');

        // Vérifier si l'ID de l'auto-école est spécifié dans la requête
        if (!$autoEcoleId) {
            // Si l'ID n'est pas spécifié, renvoyer une erreur
            return $this->errorResponse('ID de l\'auto-école non spécifié dans la requête.', null, 400);
        }

        // Récupérer le nombre de dossiers rejetés de l'auto-école spécifiée
        $count = DossierSession::where('auto_ecole_id', $autoEcoleId)
            ->where('state', 'validate')
            ->count();

        return $count;
    }
}
