<?php

namespace App\Programmation;

use App\Models\Vague;
use App\Models\Langue;
use App\Models\SalleCompo;
use App\Services\GetCandidat;
use Illuminate\Support\Carbon;
use App\Models\CategoriePermis;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\CandidatExamenSalle;
use App\Models\JuryCandidat;

class ProgrammationConduite
{

    private $candidats = [];

    /**
     * Les candidats présentes
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $presentes;
    public function __construct(private int $examen_id, private int $annexe_id)
    {
        $this->presentes = collect();
    }
    public function get(&$message = "")
    {
        # Creation de la collection
        $this->_createCollection();

        # Groupage par date de composition
        $date_group =  $this->groupByDateCompo();


        # Groupage par catégorie de permis
        $categorieGroup = $date_group->map(function (Collection $candidats, $key) {
            # Régroupement par catégories
            return $candidats->groupBy(function (JuryCandidat $candida) {
                return $candida['categorie_permis']->name;
            });
        });
        # Trie par ordre décroissant de nombre de candidat dans une catégorie
        # Return directiement le résulat
        return $categorieGroup->sortByDesc(function ($byPermis) {
            return count($byPermis);
        });
    }

    private function groupByDateCompo()
    {
        return $this->presentes->groupBy(function (JuryCandidat $candidat_presente) {
            $date_compo = $candidat_presente->vague->date_compo;
            return Carbon::parse($date_compo)->format('m-d-Y');
        });
    }


    private function map(JuryCandidat $candidat_presente)
    {

        $candidat_presente->withCategoriePermis();
        $candidat_presente->withLangue();
        $candidat_presente->withVague();
        $candidat_presente->withAutoEcole();
        $candidat_presente->withJuries();

        $found = collect($this->candidats)->where(function ($cand) use ($candidat_presente) {
            return $cand['npi'] == $candidat_presente['npi'];
        })->first() ?? [];

        $candidat_presente->setAttribute("candidat", $found);

        return $candidat_presente;
    }


    private function _createCollection()
    {
        //Récupération des candidats seleon l'annexe et l'examen
        /**
         * @var \Illuminate\Database\Eloquent\Builder $query
         */
        $query =  JuryCandidat::where([
            'examen_id' => $this->examen_id,
            'annexe_id' => $this->annexe_id,
        ]);
        //Les npis
        $npis = $query->get(['npi'])->map(function ($dossier) {
            return $dossier['npi'];
        });

        # Récupa des candidats
        $this->candidats = GetCandidat::get($npis->all());


        if (empty($this->candidats)) {
            return collect();
        }

        $this->presentes = $query->get()->map(function (JuryCandidat $juryCandidat) {
            # Charge les données importantes sur le candidat
            return $this->map($juryCandidat);
        });


        return $this->presentes;
    }
}
