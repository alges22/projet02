<?php

namespace App\Http\Controllers;

use App\Models\Agregateur;
use App\Models\Arrondissement;
use App\Models\AutoEcole\SuiviCandidat;
use App\Models\Candidat\CandidatJustifAbsence;
use App\Models\Candidat\DossierSession;
use App\Models\CategoriePermis;
use App\Models\Commune;
use App\Models\Departement;
use App\Models\Langue;
use App\Models\User;
use Illuminate\Http\Request;

class CountController extends ApiController
{
    protected $counts = [
        "langues_count",
        "agregateurs_count",
        "category_permis_count",
        "departements_count",
        "communes_count",
        "arrondissements_count",
        "init_monitoring_count",
        "validate_monitoring_count",
        "rejet_monitoring_count",
        "pending_monitoring_count",
        "init_justif_count",
        'rejet_justif_count',
        'validate_justif_count',
        "ds_c"
    ];



    /**
     * @OA\Get(
     *     path="/api/anatt-base/counts",
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
     *             @OA\Property(property="users_count", type="integer", example=69),
     *             @OA\Property(property="roles_count", type="integer", example=42),
     *             @OA\Property(property="titres", type="integer", example=27),
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


    private function langues_count()
    {
        return Langue::count();
    }

    private function agregateurs_count()
    {
        return Agregateur::count();
    }
    private function category_permis_count()
    {
        return CategoriePermis::count();
    }
    private function departements_count()
    {
        return Departement::count();
    }
    private function communes_count()
    {
        return Commune::count();
    }
    private function arrondissements_count()
    {
        return Arrondissement::count();
    }
    /**
     * Affiche le nombre total de suivi en attente
     *
     * @return int
     */
    private function init_monitoring_count()
    {
        return DossierSession::where("state", "payment")
            ->where('closed', false)
            ->count();
    }
    /**
     * Affiche le nombre total de suivi validé
     *
     * @return int
     */
    private function validate_monitoring_count()
    {
        return DossierSession::where("state", "validate")
            ->where('closed', false)
            ->count();
    }
    /**
     * Affiche le nombre total de suivi rejeté
     *
     * @return int
     */
    private function rejet_monitoring_count()
    {
        return DossierSession::where("state", "rejet")->where('closed', false)->count();
    }

    private function pending_monitoring_count()
    {
        return DossierSession::where("state", "payment")
            ->where('closed', false)
            ->count();
    }

    private function init_justif_count()
    {
        return CandidatJustifAbsence::where('state', 'init')->count();
    }

    private function rejet_justif_count()
    {
        return CandidatJustifAbsence::where('state', 'rejet')->count();
    }

    private function validate_justif_count()
    {
        return CandidatJustifAbsence::where('state', 'validate')->count();
    }

    private function ds_c()
    {
        $replaceWith = [
            "aecole_id" => "auto_ecole_id",
            "list" => "state",
            "presence" => "presence",
            "closed" => false
        ];
        $instance = new DossierSession;
        $filters = $this->filterBeforCount($replaceWith, "w_ds_");

        if (!empty($filters)) {
            $instance = $instance->where($filters);
        }
        return $instance->count();
    }

    private function filterBeforCount($replaceWith, $prefixe)
    {
        $filters = [];
        foreach (request()->all() as $key => $value) {
            if (str($key)->startsWith($prefixe)) {
                $attr = str($key)->after($prefixe)->trim()->toString();
                if (array_key_exists($attr, $replaceWith)) {
                    $db_field = $replaceWith[$attr];
                    $filters[$db_field]
                        = trim($value);
                }
            }
        }

        return $filters;
    }
}
