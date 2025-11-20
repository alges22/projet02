<?php

namespace App\Http\Controllers;

use App\Services\Api;
use Illuminate\Http\Request;
use App\Models\Candidat\CandidatPayment;
use App\Models\Candidat\DossierSession;
use Illuminate\Support\Facades\Validator;

class CandidatPaymentController extends DataController
{
    /**
     * L'instance de requête
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    private $instance;
    public function __construct()
    {
        $this->instance = (new CandidatPayment())->query();
    }

    public function candidatsPayment(Request $request)
    {
        $this->hasAnyPermission(["all", "read-reporting-management"]);
        $this->sortData($request);

        $candidats = $this->getCandidats($this->instance->get()->map(function (CandidatPayment $c) {
            $c->withNpi();
            return $c;
        }));

        /**************************TRansformation du dossier session ****************** */
        $transformDs = function (CandidatPayment $ds) use ($candidats) {

            $ds = $this->callScope($ds, $candidats);
            return $ds;
        };
        /**************************Fin de la transformation ****************************** */

        return $this->withPagination($this->instance->paginate($this->getPerpage()), $transformDs);
    }

    private function sortData(Request $request)
    {
        # Si une année  est passé la requête sera fait suivant l'année
        $this->instance = $this->filterIfHasYear($this->instance);

        # Filtrage de la requête
        # Néttoyage des champs
        $filters = $this->getFilters($request);

        # Recherche d'un dossiers via npi
        $search = request('search') ?? request('npi');

        if ($search) {
            $this->instance = $this->instance->where('npi', 'LIKE', "%" . trim($search) . "%");
        }
        $annee = request('annee');

        if ($annee) {
            $this->instance = $this->instance->whereYear('created_at', trim($annee));
        }

        # S'il un des champs de filtre est présent, on le fait sinon rien
        if (!empty($filters)) {
            $this->instance = $this->instance->where($filters);
        }
        $this->instance = $this->instance->select('*');

        return $this->instance;
    }
    /**
     * Ajoute les données et informations nécessaires
     *
     * @param DossierSession $ds
     * @param boolean $partial
     */
    private function callScope(CandidatPayment $ds, array $candidats)
    {
        //Charge les candidats
        $ds->withCandidat($candidats);
        return $ds;
    }
    public function replaceWiths(): array
    {
        return [
            "cat_permis_id" => "categorie_permis_id",
            "exam_id" => "examen_id",
        ];
    }

    public function filtreAttrs(): array
    {
        return ['examen_id'];
    }

    public function toIntegers(): array
    {
        return  ['examen_id'];
    }
}
