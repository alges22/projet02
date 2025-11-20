<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SuiviCandidat;
use Illuminate\Support\Facades\Http;

class CountController extends ApiController
{
    protected $counts = [
        "monitoring_count",
        "rejected_monitoring_count",
        "validate_monitoring_count",
        "new_monitoring_count",
        "init_monitoring_count"
    ];



    /**
     * @OA\Get(
     *     path="/api/anatt-autoecole/counts",
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



    private function monitoring_count()
    {
        return $this->exportFromBase("counts",);
    }

    private function rejected_monitoring_count()
    {
        return  SuiviCandidat::where('state', 'rejet')->count();
    }

    private function validate_monitoring_count()
    {
        return SuiviCandidat::where('state', 'validate')->count();
    }

    private function new_monitoring_count()
    {
        return SuiviCandidat::where('state', 'pending')->count();
    }

    private function init_monitoring_count()
    {
        return SuiviCandidat::where('state', 'init')->count();
    }
}
