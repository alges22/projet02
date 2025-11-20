<?php

namespace App\Programmation;

use App\Services\GetCandidat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Models\CandidatExamenSalle;

class Programmation
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
            return $candidats->groupBy(function ($candida) {
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
        //On crée un group des candidats par jour
        return $this->presentes->groupBy(function ($candidat_presente) {
            $date_compo = $candidat_presente->vague->date_compo;
            return Carbon::parse($date_compo)->format('m-d-Y');
        });
    }


    private function map(CandidatExamenSalle $candidat_presente)
    {

        $candidat_presente->withSalleCompo();
        $candidat_presente->withCategoriePermis();
        $candidat_presente->withLangue();
        $candidat_presente->withVague();
        $candidat_presente->withAutoEcole();

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
        $query =  CandidatExamenSalle::where([
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

        $this->presentes = $query->get()->map(function (CandidatExamenSalle $candidatExamenSalle) {
            # Charge les données importantes sur le candidat
            return $this->map($candidatExamenSalle);
        });


        return $this->presentes;
    }
}