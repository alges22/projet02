<?php

namespace App\Http\Controllers;

use App\Models\Admin\AnnexeAnattDepartement;
use App\Models\AutoEcole\AutoEcole;
use App\Models\AutoEcole\SuiviCandidat;
use App\Models\Commune;
use App\Models\Departement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AutoEcoleController extends DataController
{

    public function index(Request $request)
    {
        $canPaginate = $request->has("paginate");
        $per_page =  $this->getPerpage();

        $autoes = (new AutoEcole())->query();

        # On crée une instance pour les départements couverts par une annexe
        if ($request->has('annexe_id') && intval($request->get('annexe_id')) > 0) {
            $autoes = $this->fromAnnexeAnatt($autoes);
        }

        try {
            //Les clés de filtrage
            $filters = $this->getFilters($request);

            //Recherche d'un suivi par npi
            $search = request('search') ?? request('ifu');

            if ($search) {
                $autoes = $autoes->where('num_ifu', 'LIKE', "%" . trim($search) . "%");
            }

            /**
             * Filtrage
             */
            if (!empty($filters)) {
                $autoes = $autoes->where($filters);
            }


            /************************** Transformation des suivis ************************************ */
            # Map chaque ligne de suiviCandidat et ajouté le candidat

            $transforAutoe = function (AutoEcole $ae) {
                # Récupération du candidat
                return $this->autoeMap($ae, request()->has("partial"));
            };
            /**************************Fin de la transformation des suivis************************************ */

            # Renvoie uniquement les attributes nécessaires
            $autoes = $autoes->select($this->publicAttrs());


            if ($canPaginate) {
                # Faire une pagination
                $autoes = $autoes->paginate($per_page);
                return $this->withPagination($autoes, $transforAutoe);
            } else {
                return $this->successResponse($autoes->get()->map($transforAutoe));
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des auto-écoles', statuscode: 500);
        }
    }

    private function autoeMap(AutoEcole $autoEcole, $partial = false)
    {
        $commune = Commune::find(intval($autoEcole->commune_id), ['id', 'name']);
        $autoEcole->setAttribute('commune', $commune);
        $dep = Departement::find(intval($autoEcole->departement_id), ['id', 'name']);
        $autoEcole->setAttribute('departement', $dep);
        return $autoEcole;
    }
    public function publicAttrs(): array
    {
        return [
            "id",
            'name',
            'email',
            'phone',
            "commune_id",
            "departement_id",
            "num_ifu",
            "code",
            "annee_creation",
            "type"
        ];
    }

    public function filtreAttrs(): array
    {
        return [
            'adresse',
            'code',
            'num_ifu',
            'status',
            'commune_id',
            'departement_id',
        ];
    }

    public function replaceWiths(): array
    {
        return [
            "dep_id" => "departement_id",
            "com_id" => "commune_id",
            "verified" => "status",
        ];
    }

    public function defaultValues(): array
    {
        return [
            'cpu_accepted' => false,
            'status' => false,
        ];
    }

    public function toIntegers(): array
    {
        return [
            'commune_id',
            'departement_id',
            'annee_creation',
        ];
    }

    /**
     * Filtre les auto-écoles sur les annexes
     *
     * @param Builder $builder
     * @return Builder
     */
    private function fromAnnexeAnatt(Builder $builder)
    {
        $annexe_id = request('annexe_id');
        # Récupération des IDS de départements sous forme d'un tableau
        $annexe_dep_ids = AnnexeAnattDepartement::select(['departement_id'])->where('annexe_anatt_id', $annexe_id)->get()->toArray();

        # On recupère maintenant les auto-écoles suivants départements trouvés
        return $builder->whereIn('departement_id', $annexe_dep_ids);
    }

    public function candidats($id)
    {
        /**
         * Les suivis
         */
        $suivis = SuiviCandidat::select(['npi', 'created_at', 'id', 'auto_ecole_id', 'categorie_permis_id'])->where('auto_ecole_id', $id)
            ->orderByDesc('created_at')
            ->paginate(25);


        $candidats = $this->getCandidats($suivis);

        $map = function (SuiviCandidat $suiviCandidat) use ($candidats) {
            $suiviCandidat->withCategoriePermis();
            return $suiviCandidat->withCandidat($candidats);
        };
        return  $this->withPagination($suivis, $map);
    }
}
