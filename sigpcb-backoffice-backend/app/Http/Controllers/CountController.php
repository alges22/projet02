<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Titre;
use App\Services\Api;
use App\Models\Inspecteur;
use App\Models\Signataire;
use App\Models\UniteAdmin;
use App\Models\AnnexeAnatt;
use App\Models\Examinateur;
use App\Models\ActeSignable;
use Illuminate\Http\Request;
use App\Models\Candidat\Echange;
use App\Models\AutoEcole\Licence;
use App\Models\Candidat\Duplicata;
use App\Models\AutoEcole\AutoEcole;
use App\Models\Candidat\Prorogation;
use Illuminate\Support\Facades\Http;
use App\Models\Candidat\Authenticite;
use Spatie\Permission\Contracts\Role;
use App\Models\Examinateur\Entreprise;
use App\Http\Controllers\ApiController;
use App\Models\Examinateur\Recrutement;
use App\Models\AutoEcole\DemandeLicence;
use App\Models\AutoEcole\DemandeAgrement;
use App\Models\Examinateur\DemandeMoniteur;
use App\Models\Candidat\PermisInternational;
use App\Models\Examinateur\DemandeExaminateur;
use Spatie\Permission\Models\Role as ModelsRole;

class CountController extends ApiController
{
    protected $counts = [
        "users_count",
        "titres_count",
        "roles_count",
        "uniteadmins_count",
        "langues_count",
        "monitoring_count",
        "signataires_count",
        "acte_signes_count",
        "agregateurs_count",
        "category_permis_count",
        "annexes_anatt_count",
        "departements_count",
        "communes_count",
        "arrondissements_count",
        "init_monitoring_count",
        "rejet_monitoring_count",
        "validate_monitoring_count",
        "pending_monitoring_count",
        "init_justif_count",
        'rejet_justif_count',
        'validate_justif_count',
        'inspecteurs_count',
        'examinateurs_count',
        'auto_ecoles_count',
        'licences_actives_count',
        'licences_expirees_count',
        'agrement_news_count',
        'agrement_rejets_count',
        'agrement_valides_count',
        'demande_licence_news_count',
        'demande_licence_rejets_count',
        'demande_licence_valides_count',
        'authenticite_rejets_count',
        'authenticite_init_count',
        'authenticite_validate_count',

        'permis_inter_init_count',
        'permis_inter_rejets_count',
        'permis_inter_valide_count',

        'duplicata_init_count',
        'duplicata_rejet_count',
        'duplicata_validate_count',

        'echange_permis_init_count',
        'echange_permis_rejets_count',
        'echange_permis_valide_count',

        'prorogation_init_count',
        'prorogation_rejets_count',
        'prorogation_valide_count',

        'r_examinateurs_rejets_count',
        'r_examinateurs_init_count',
        'r_examinateurs_validate_count',

        'r_gestion_pending_count',
        'r_gestion_rejet_count',
        'r_gestion_validate_count',
        'r_entreprises_count',

        'r_moniteurs_rejets_count',
        'r_moniteurs_init_count',
        'r_moniteurs_validate_count'
    ];



    /**
     * @OA\Get(
     *     path="/api/anatt-admin/counts",
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


    private function users_count()
    {
        return User::count();
    }
    private function r_entreprises_count()
    {
        return Entreprise::count();
    }
    private function titres_count()
    {
        return Titre::count();
    }
    private function roles_count()
    {
        return ModelsRole::count();
    }
    private function uniteadmins_count()
    {
        return UniteAdmin::count();
    }
    private function signataires_count()
    {
        return Signataire::count();
    }
    private function acte_signes_count()
    {
        return ActeSignable::count();
    }
    private function annexes_anatt_count()
    {
        return AnnexeAnatt::count();
    }
    private function inspecteurs_count()
    {
        return Inspecteur::count();
    }
    private function examinateurs_count()
    {
        return Examinateur::count();
    }
    private function auto_ecoles_count()
    {
        return AutoEcole::count();
    }
    private function licences_actives_count()
    {
        return Licence::where('date_fin', '>=', now()->toDateString())->count();

    }
    private function licences_expirees_count()
    {
        return Licence::where('date_fin', '<', now()->toDateString())->count();

    }


    private function agrement_news_count()
    {
        return DemandeAgrement::whereIn('state', ['init', 'pending'])->count();
    }
    private function agrement_rejets_count()
    {
        return DemandeAgrement::where('state', 'rejected')->count();

    }
    private function agrement_valides_count()
    {
        return DemandeAgrement::where('state', 'validate')->count();

    }


    private function demande_licence_news_count()
    {
        return DemandeLicence::whereIn('state', ['init', 'pending'])->count();
    }
    private function demande_licence_rejets_count()
    {
        return DemandeLicence::where('state', 'rejected')->count();
    }
    private function demande_licence_valides_count()
    {
        return DemandeLicence::where('state', 'validate')->count();

    }

    private function echange_permis_init_count()
    {
        return Echange::whereIn('state', ['init', 'pending'])->count();
    }
    private function echange_permis_rejets_count()
    {
        return Echange::where('state', 'rejected')->count();
    }
    private function echange_permis_valide_count()
    {
        return Echange::where('state', 'validate')->count();

    }

    private function duplicata_init_count()
    {
        return Duplicata::whereIn('state', ['init', 'pending'])->count();
    }
    private function duplicata_rejet_count()
    {
        return Duplicata::where('state', 'rejected')->count();
    }
    private function duplicata_validate_count()
    {
        return Duplicata::where('state', 'validate')->count();
    }

    private function prorogation_init_count()
    {
        return Prorogation::whereIn('state', ['init', 'pending'])->count();
    }
    private function prorogation_rejets_count()
    {
        return Prorogation::where('state', 'rejected')->count();
    }
    private function prorogation_valide_count()
    {
        return Prorogation::where('state', 'validate')->count();
    }


    private function authenticite_rejets_count()
    {
        return Authenticite::where('state', 'rejected')->count();

    }
    private function authenticite_init_count()
    {
        return Authenticite::whereIn('state', ['init', 'pending'])->count();

    }
    private function authenticite_validate_count()
    {
        return Authenticite::where('state', 'validate')->count();

    }


    private function permis_inter_rejets_count()
    {
        return PermisInternational::where('state', 'rejected')->count();

    }
    private function permis_inter_init_count()
    {
        return PermisInternational::whereIn('state', ['init', 'pending'])->count();

    }
    private function permis_inter_valide_count()
    {
        return PermisInternational::where('state', 'validate')->count();

    }

    private function r_examinateurs_rejets_count()
    {
        return DemandeExaminateur::where('state', 'rejected')->count();

    }
    private function r_examinateurs_init_count()
    {
        return DemandeExaminateur::whereIn('state', ['init', 'pending'])->count();

    }
    private function r_examinateurs_validate_count()
    {
        return DemandeExaminateur::where('state', 'validate')->count();

    }

    private function r_moniteurs_rejets_count()
    {
        return DemandeMoniteur::where('state', 'rejected')->count();

    }
    private function r_moniteurs_init_count()
    {
        return DemandeMoniteur::whereIn('state', ['init', 'pending'])->count();

    }
    private function r_moniteurs_validate_count()
    {
        return DemandeMoniteur::where('state', 'validate')->count();

    }


    private function r_gestion_rejet_count()
    {
        return Recrutement::where('state', 'rejected')->count();

    }
    private function r_gestion_pending_count()
    {
        return Recrutement::where('state', 'pending')->count();

    }
    private function r_gestion_validate_count()
    {
        return Recrutement::where('state', 'validate')->count();

    }

    private function langues_count()
    {
        $data = Api::data(Api::base('GET', "counts", ['counts' => "langues_count"]));
        return $data['langues_count'] ?? 0;
    }
    private function category_permis_count()
    {
        $data = Api::data(Api::base('GET', "counts", ['counts' => "category_permis_count"]));
        return $data['category_permis_count'] ?? 0;
    }
    private function agregateurs_count()
    {
        $data = Api::data(Api::base('GET', "counts", ['counts' => "agregateurs_count"]));
        return $data['agregateurs_count'] ?? 0;
    }
    private function departements_count()
    {
        $data = Api::data(Api::base('GET', "counts", ['counts' => "departements_count"]));
        return $data['departements_count'] ?? 0;
    }
    private function communes_count()
    {
        $data = Api::data(Api::base('GET', "counts", ['counts' => "communes_count"]));
        return $data['communes_count'] ?? 0;
    }
    private function arrondissements_count()
    {
        $data = Api::data(Api::base('GET', "counts", ['counts' => "arrondissements_count"]));
        return $data['arrondissements_count'] ?? 0;
    }

    private function monitoring_count()
    {

        $data = Api::data(Api::base(
            'GET',
            "counts",
            ['counts' => "monitoring_count"]
        ));


        return $data['monitoring_count'] ?? 0;
    }

    private function rejet_monitoring_count()
    {

        $data = Api::data(Api::base(
            'GET',
            "counts",
            ['counts' => "rejet_monitoring_count"]
        ));



        return $data['rejet_monitoring_count'] ?? 0;
    }

    private function validate_monitoring_count()
    {
        $data = Api::data(Api::base(
            'GET',
            "counts",
            ['counts' => "validate_monitoring_count"]
        ));



        return $data['validate_monitoring_count'] ?? 0;
    }

    private function init_monitoring_count()
    {
        $data = Api::data(Api::base('GET', "counts", ['counts' => "init_monitoring_count"]));
        return $data['init_monitoring_count'] ?? 0;
    }

    private function pending_monitoring_count()
    {
        $data = Api::data(Api::base('GET', "counts", ['counts' => "pending_monitoring_count"]));
        return $data['pending_monitoring_count'] ?? 0;
    }

    private function init_justif_count()
    {
        $data = Api::data(Api::base('GET', "counts", ['counts' => "init_justif_count"]));
        return $data['init_justif_count'] ?? 0;
    }

    private function rejet_justif_count()
    {
        $data = Api::data(Api::base('GET', "counts", ['counts' => "rejet_justif_count"]));
        return $data['rejet_justif_count'] ?? 0;
    }

    private function validate_justif_count()
    {
        $data = Api::data(Api::base('GET', "counts", ['counts' => "validate_justif_count"]));
        return $data['validate_justif_count'] ?? 0;
    }
}
