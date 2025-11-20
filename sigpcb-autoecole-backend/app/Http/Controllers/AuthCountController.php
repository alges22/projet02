<?php

namespace App\Http\Controllers;

use App\Models\DossierSession;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SuiviCandidat;
use App\Services\Help;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthCountController extends ApiController
{
    protected $counts = [
        "monitoring_count",
        "formations_acheves_count",
        "mt_init_c",
        "monitoring_rejets_count",
        "monitoring_validate_count",
        "mt_pending_c",
        'ds_c',
        "ds_rejet_c",
        "ds_pending_c",
        "ds_init_c",
        "ds_validate_c"

    ];



    /**
     * @OA\Get(
     *     path="/api/anatt-autoecole/auth-counts",
     *     summary="Récupère les compteurs",
     *     tags={"AuthCompteurs"},
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
        return $this->ds_pending_c();
    }


    private function formations_acheves_count()
    {
        return $this->ds_pending_c();
    }

    private function monitoring_rejets_count()
    {
        return $this->ds_rejet_c();
    }

    private function monitoring_validate_count()
    {
        return $this->ds_validate_c();
    }

    private function mt_pending_c()

    {
        $params = [
            "auto_ecole_id" => Help::autoEcoleId(),
            "state" => "payment",
            'closed' => false
        ];

        return DossierSession::where($params)->count();
    }

    private function mt_init_c()
    {
        return  SuiviCandidat::where('auto_ecole_id', Help::autoEcoleId())->where('state', 'init')->count();
    }

    private function ds_c()
    {
        $params = [
            "auto_ecole_id" =>
            Help::autoEcoleId(),
        ];
        return SuiviCandidat::where($params)->count();
    }

    private function ds_rejet_c()
    {
        $params = [
            "auto_ecole_id" =>
            Help::autoEcoleId(),
            "state" => "rejet",
            'closed' => false
        ];

        return DossierSession::where($params)->count();
    }


    private function ds_validate_c()
    {
        $params = [
            "auto_ecole_id" =>
            Help::autoEcoleId(),
            "state" => "validate",
            'closed' => false
        ];

        return DossierSession::where($params)->count();
    }

    private function ds_init_c()
    {
        $params = [
            "auto_ecole_id" =>
            Help::autoEcoleId(),
            "state" => "init",
            'closed' => false
        ];

        return DossierSession::where($params)->count();
    }

    private function ds_pending_c()
    {
        $params = [
            "auto_ecole_id" =>
            Help::autoEcoleId(),
            "state" => "init",
        ];
        return SuiviCandidat::where($params)->count();
    }
}
