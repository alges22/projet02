<?php

namespace App\Services\Statistics;

use App\Models\Base\Langue;
use App\Models\Candidat\Candidat;
use App\Models\Candidat\DossierSession;
use App\Services\Help;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StatGlabal
{

    /**
     *
     * @var Builder
     */
    private $builder;
    private $totalInscrit = 0;

    public function get(Request $request)
    {

        $this->builder = DossierSession::inscrits($request->all());

        //Total inscrit
        $this->totalInscrit = $this->builder->count();

        $collections = $this->builder->get()->map(function (DossierSession $ds) {
            $candidat = Candidat::whereNpi($ds->npi)->first();
            $ds->setAttribute('sexe', $candidat->sexe);
            return $ds;
        });

        if (is_numeric($request->get('categorie_permis_id'))) {
            $collections = $collections->where("categorie_permis_id", intval($request->get('categorie_permis_id')));
        }

        if (is_numeric($request->get('langue_id'))) {
            $collections = $collections->where("langue_id", intval($request->get('langue_id')));
        }

        if ($request->get('sexe')) {
            $sexe = $request->get('sexe');
            $sexe = $sexe == "H" ? "M" : $sexe;
            if (in_array($sexe, ['M', 'F'])) {
                $collections = $collections->where('sexe', $sexe);
            }
        }
        $data = [
            "codes" => $this->codes($collections),
            "conduites" => $this->conduites($collections),
            "total" => $this->totalInscrit
        ];

        return $data;
    }

    private function codes(Collection $collection, $taux = "admissibility")
    {
        $admisCount = $collection->where('resultat_code', "success")->count();
        $echecsCount = $collection->where('resultat_code', "failed")->count();
        return [
            "admis" => $admisCount,
            "echecs" => $echecsCount,
            "admis_percent" => Help::percent($admisCount, $this->totalInscrit),
            "echecs_percent" => Help::percent($echecsCount, $this->totalInscrit),
        ];
    }

    private function conduites(Collection $collection, $taux = "admissibility")
    {
        $admisCount = $collection->where('resultat_conduite', "success")->count();
        $echecsCount = $collection->where('resultat_conduite', "failed")->count();
        return [
            "admis" => $admisCount,
            "echecs" => $echecsCount,
            "admis_percent" => Help::percent($admisCount, $this->totalInscrit),
            "echecs_percent" => Help::percent($echecsCount, $this->totalInscrit),
        ];
    }
}
