<?php

namespace App\Services\Statistics;

use App\Services\Help;
use App\Models\Base\Langue;
use Illuminate\Http\Request;
use App\Models\Candidat\Candidat;
use Illuminate\Support\Collection;
use App\Models\Base\CategoriePermis;
use App\Models\Candidat\DossierSession;
use App\Models\Base\CandidatExamenSalle;

class RapportActivites
{

    /**
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    private $builder;
    private $langues;

    private $totalInscrit = 0;

    public function get(Request $request)
    {
        $this->builder = DossierSession::inscrits($request->all());

        //Total inscrit
        $this->totalInscrit = $this->builder->count();

        $collection = $this->builder->get()->map(function (DossierSession $ds) {
            $candidat = Candidat::whereNpi($ds->npi)->first();
            $ds->setAttribute('sexe', $candidat->sexe);
            return $ds;
        });

        $data = [
            "codes" => $this->codes($collection),
            "conduites" => $this->conduites($collection),
            "total" => $this->totalInscrit
        ];

        return $data;
    }


    private function codes(Collection $collection)
    {
        $catStats = [];
        $categoriePermis = CategoriePermis::orderBy('name')->get();

        $collection =  $collection->where('type_examen', 'code-conduite');
        $totalAdmis = 0;
        $totalInscripts = 0;
        $totalEchoues = 0;
        $totalAbscent = 0;
        $totalPresnet = 0;
        $admisTotal = 0;
        foreach ($categoriePermis as $key => $cat) {
            $cat->withExtensions();

            $catName = $cat->name;

            if (!empty($cat->ext)) {
                $catName .= " + " . collect($cat->ext)->map(fn ($e) => $e->name)->join(' + ');
            }
            $cat->name = $catName;
            $inscriptsCollection = $collection->where('categorie_permis_id', $cat->id);
            $inscrits[0] =  $inscriptsCollection->where('sexe', "M")->count();
            $inscrits[1] = $inscriptsCollection->where('sexe', "F")->count();

            $presentsCollection = $inscriptsCollection->where('presence', 'present');
            $presents[0] = $presentsCollection->where('sexe', "M")->count();
            $presents[1] = $presentsCollection->where('sexe', "F")->count();

            $abscentCollection = $inscriptsCollection->where('presence', 'abscent');
            $abscents[0] = $abscentCollection->where('sexe', "M")->count();
            $abscents[1] = $abscentCollection->where('sexe', "F")->count();

            $admisCollection = $inscriptsCollection->where('resultat_code', 'success');
            $admis[0]  = $admisCollection->where('sexe', "M")->count();
            $admis[1] =  $admisCollection->where('sexe', "F")->count();
            ## Calcule admis
            $totalAdmis += $admisCollection->count();

            $echouesCollection = $inscriptsCollection->where('resultat_code', 'failed');
            $echoues[0] = $echouesCollection->where('sexe', "M")->count();
            $echoues[1] = $echouesCollection->where('sexe', "F")->count();

            $totalInscripts += $inscriptsCollection->count();
            $totalPresnet += $presentsCollection->count();
            $admisTotal += $admisCollection->count();
            $totalAbscent += $abscentCollection->count();
            $totalEchoues += $echouesCollection->count();
            $catStats[] = [
                'categorie_permis' => $cat,
                "inscrits" => $inscrits,
                "presents" => $presents,
                "abscents" => $abscents,
                "admis" => $admis,
                "admis_total" => $admisCollection->count(),
                "echoues" => $echoues,
            ];
        }

        return  [
            "total" => $collection->count(),
            "list" => $catStats,
            "total_presents" => $totalPresnet,
            "total_abscent" => $totalAbscent,
            "total_echoues" => $totalEchoues,
            "total_admis" => $totalAdmis,
            "presence" => Help::percent($totalPresnet, $this->totalInscrit),
            "percent" => Help::percent($totalAdmis, $this->totalInscrit),
        ];
    }

    private function conduites(Collection $collection)
    {
        $catStats = [];
        $categoriePermis = CategoriePermis::orderBy('name')->where('is_extension', false)->get();

        $totalAdmis = 0;
        $totalInscripts = 0;
        $totalEchoues = 0;
        $totalAbscent = 0;
        $totalPresnet = 0;
        $admisTotal = 0;
        foreach ($categoriePermis as $key => $cat) {
            $cat->withExtensions();

            $catName = $cat->name;

            if (!empty($cat->ext)) {
                $catName .= " + " . collect($cat->ext)->map(fn ($e) => $e->name)->join(' + ');
            }
            $cat->name = $catName;
            $inscriptsCollection = $collection->where('categorie_permis_id', $cat->id);
            $inscrits[0] =  $inscriptsCollection->where('sexe', "M")->count();
            $inscrits[1] = $inscriptsCollection->where('sexe', "F")->count();

            $presentsCollection = $inscriptsCollection->where('presence_conduite', 'present');
            $presents[0] = $presentsCollection->where('sexe', "M")->count();
            $presents[1] = $presentsCollection->where('sexe', "F")->count();

            $abscentCollection = $inscriptsCollection->where('presence_conduite', 'abscent');
            $abscents[0] = $abscentCollection->where('sexe', "M")->count();
            $abscents[1] = $abscentCollection->where('sexe', "F")->count();

            $admisCollection = $inscriptsCollection->where('resultat_conduite', 'success');
            $admis[0] = $admisCollection->where('sexe', "M")->count();
            $admis[1] = $admisCollection->where('sexe', "F")->count();
            ## Calcule admis
            $totalAdmis += $admisCollection->count();

            $echouesCollection = $inscriptsCollection->where('resultat_conduite', 'failed');
            $echoues[0] = $echouesCollection->where('sexe', "M")->count();
            $echoues[1] = $echouesCollection->where('sexe', "F")->count();

            $totalInscripts += $inscriptsCollection->count();
            $totalPresnet += $presentsCollection->count();
            $admisTotal += $admisCollection->count();
            $totalAbscent += $abscentCollection->count();
            $totalEchoues += $echouesCollection->count();
            $catStats[] = [
                'categorie_permis' => $cat,
                "inscrits" => $inscrits,
                "presents" => $presents,
                "abscents" => $abscents,
                "admis" => $admis,
                "admis_total" => $admisCollection->count(),
                "echoues" => $echoues,
            ];
        }

        return  [
            "total" => $collection->count(),
            "list" => $catStats,
            "total_presents" => $totalPresnet,
            "total_abscent" => $totalAbscent,
            "total_echoues" => $totalEchoues,
            "total_admis" => $totalAdmis,
            "presence" => Help::percent($totalPresnet, $this->totalInscrit),
            "percent" => Help::percent($totalAdmis, $this->totalInscrit),
        ];
    }
}