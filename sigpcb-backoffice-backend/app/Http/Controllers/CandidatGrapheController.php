<?php

namespace App\Http\Controllers;

use App\Models\AnnexeAnatt;
use App\Models\Base\Langue;
use Illuminate\Http\Request;
use App\Models\Candidat\Candidat;
use App\Models\Base\CategoriePermis;
use App\Http\Controllers\ApiController;
use App\Models\Candidat\DossierSession;
use Illuminate\Database\Eloquent\Collection;

class CandidatGrapheController extends ApiController
{
    public function __invoke(Request $request)
    {
        $this->hasAnyPermission(["all", "read-statistics"]);
        $data = $request->validate([
            "type" => "required|in:line,bar,pie",
            "statfor" => "required|in:langue,permis,sexe,annexe",
            'factor' => "required|in:inscription,admissibility",
        ]);

        $collection = DossierSession::inscrits($request->all())->get();

        switch ($data['statfor']) {
            case 'permis':
                return $this->categoriePermis($data, $collection);
            case 'langue':
                return $this->langue($data, $collection);
            case 'sexe':
                $collection = $collection->map(function (DossierSession $ds) {
                    $candidat = Candidat::whereNpi($ds->npi)->first();
                    //sexe M ou F
                    $ds->setAttribute('sexe', $candidat->sexe);
                    return $ds;
                });
                return $this->sexe($data, $collection);
            default:
                return  $this->annexe($data, $collection);
        }
    }
    /**
     * Statique des candidats selon les catégoary de permis
     *
     * @param array $data
     */
    private function categoriePermis(array $data, Collection $collection)
    {

        $factor = data_get($data, 'factor');

        # Récupération des permis qui ne sont pas des extensions
        $categoriePermis = CategoriePermis::orderBy('name')->get();

        $dataset = [];
        $admisDataset = [];
        $echecDataset = [];
        foreach ($categoriePermis as $key => $cat) {

            $cat->withExtensions();
            $catName = $cat->name;

            if (!empty($cat->ext)) {
                $catName .= " + " . collect($cat->ext)->map(fn ($e) => $e->name)->join(' + ');
            }
            $inscriptsCollection = $collection->where('categorie_permis_id', $cat->id);

            $admisCount = $inscriptsCollection->where('resultat_conduite', 'success')->count();
            $echecsCount = $inscriptsCollection->where('resultat_conduite', 'failed')->count();
            if ($factor == 'inscription') {
                $dataset[] = ["x" => $catName, "y" => $inscriptsCollection->count()];
            } else {
                $admisDataset[] = ["x" => $catName, "y" => $admisCount];
                $echecDataset[] = ["x" => $catName, "y" => $echecsCount];
            }
        }
        if ($factor == 'inscription') {
            return $this->successResponse([
                'type' => data_get($data, 'type'),
                'data' => [
                    'datasets' => [
                        [
                            'data' => $dataset,
                            "label" => "Inscriptions",
                            'borderColor' => '#006F6F',
                            "borderWidth" => 1,
                            "backgroundColor"  => '#006F6F',
                        ]
                    ]
                ]
            ]);
        } else {
            return $this->successResponse([
                'type' => data_get($data, 'type'),
                'data' => [
                    'datasets' => [
                        [
                            'data' => $admisDataset,
                            "label" => "Les admis définitifs",
                            'borderColor' => '#006F6F',
                            "borderWidth" => 1,
                            "backgroundColor"  => '#006F6F',
                        ],
                        [
                            'data' => $echecDataset,
                            "label" =>  "Les récalés définitifs",
                            'borderColor' => '#F49E24',
                            "borderWidth" => 1,
                            "backgroundColor"  => '#F49E24',
                        ]
                    ]
                ]
            ]);
        }
    }

    private function langue(array $data, Collection $collection)
    {
        $factor = data_get($data, 'factor');

        // Récupération des langues
        $langues = Langue::orderBy('name')->get();

        $dataset = [];
        foreach ($langues as $langue) {
            $inscriptsCollection = $collection->where('langue_id', $langue->id);
            $successCount = $inscriptsCollection->where('resultat_conduite', 'success')->count();
            $failureCount = $inscriptsCollection->where('resultat_conduite', 'failed')->count();

            if ($factor == 'inscription') {
                $dataset[] = ["x" => $langue->name, "y" => $inscriptsCollection->count()];
            } else {
                $admisDataset[] = ["x" => $langue->name, "y" => $successCount];
                $echecDataset[] = ["x" => $langue->name, "y" => $failureCount];
            }
        }

        # La préparation du graphe
        if ($factor == 'inscription') {
            return $this->successResponse([
                'type' => data_get($data, 'type'),
                'data' => [
                    'datasets' => [
                        [
                            'data' => $dataset,
                            "label" => "Inscriptions",
                            'borderColor' => '#006F6F',
                            "backgroundColor"  => '#006F6F',
                            "borderWidth" => 1,
                        ]
                    ]
                ]
            ]);
        } else {
            return $this->successResponse([
                'type' => data_get($data, 'type'),
                'data' => [
                    'datasets' => [
                        [
                            'data' => $admisDataset,
                            "label" => "Les admis définitifs",
                            'borderColor' => '#006F6F',
                            "borderWidth" => 1,
                            "backgroundColor"  => '#006F6F',
                        ],
                        [
                            'data' => $echecDataset,
                            "label" =>  "Les récalés définitifs",
                            'borderColor' => '#F49E24',
                            "backgroundColor"  => '#F49E24',
                            "borderWidth" => 1,
                        ]
                    ]
                ]
            ]);
        }
    }


    private function sexe(array $data, Collection $collection)
    {

        $factor = data_get($data, 'factor');

        $dataset = [];

        $sexes = ["F", "M"];
        $admisDataset = [];
        $echecDataset = [];
        foreach ($sexes as $sexe) {
            $inscriptsCollection = $collection->where('sexe', $sexe);

            $successCount = $inscriptsCollection->where('resultat_conduite', 'success')->count();
            $failureCount = $inscriptsCollection->where('resultat_conduite', 'failed')->count();

            if ($factor == 'inscription') {
                $dataset[] = ["x" => $sexe, "y" => $inscriptsCollection->count()];
            } elseif ($factor == 'admissibility') {
                $admisDataset[] = ["x" => $sexe, "y" => $successCount];
                $echecDataset[] = ["x" => $sexe, "y" => $failureCount];
            }
        }

        if ($factor == 'inscription') {
            return $this->successResponse([
                'type' => data_get($data, 'type'),
                'data' => [
                    'datasets' => [
                        [
                            'data' => $dataset,
                            "label" => "Inscriptions",
                            'borderColor' => '#006F6F',
                            "backgroundColor"  => '#006F6F',
                            "borderWidth" => 1,
                        ]
                    ]
                ]
            ]);
        } else {
            return $this->successResponse([
                'type' => data_get($data, 'type'),
                'data' => [
                    'datasets' => [
                        [
                            'data' => $admisDataset,
                            "label" => "Les admis définitifs",
                            'borderColor' => '#006F6F',
                            "backgroundColor"  => '#006F6F',
                            "borderWidth" => 1,
                        ],
                        [
                            'data' => $echecDataset,
                            "label" =>  "Les récalés définitifs",
                            'borderColor' => '#F49E24',
                            "backgroundColor"  => '#F49E24',
                            "borderWidth" => 1,
                        ]
                    ]
                ]
            ]);
        }
    }
    private function annexe(array $data, Collection $collection)
    {

        $factor = data_get($data, 'factor');

        // Récupération des annexes
        $annexes = AnnexeAnatt::orderBy('name')->get();

        $dataset = [];
        $admisDataset = [];
        $echecDataset = [];
        foreach ($annexes as $annexe) {
            $inscriptsCollection = $collection->where('annexe_id', $annexe->id);
            $successCount = $inscriptsCollection->where('resultat_conduite', 'success')->count();
            $failureCount = $inscriptsCollection->where('resultat_conduite', 'failed')->count();

            if ($factor == 'inscription') {
                $dataset[] = ["x" => $annexe->name, "y" => $inscriptsCollection->count()];
            } elseif ($factor == 'admissibility') {
                $admisDataset[] = ["x" => $annexe->name, "y" => $successCount];
                $echecDataset[] = ["x" => $annexe->name, "y" => $failureCount];
            }
        }

        if ($factor == 'inscription') {
            return $this->successResponse([
                'type' => data_get($data, 'type'),
                'data' => [
                    'datasets' => [
                        [
                            'data' => $dataset,
                            "label" => "Inscriptions",
                            'borderColor' => '#006F6F',
                            "backgroundColor"  => '#006F6F',
                            "borderWidth" => 1,
                        ]
                    ]
                ]
            ]);
        } else {
            return $this->successResponse([
                'type' => data_get($data, 'type'),
                'data' => [
                    'datasets' => [
                        [
                            'data' => $admisDataset,
                            "label" => "Les admis définitifs",
                            'borderColor' => '#006F6F',
                            "borderWidth" => 1,
                            "backgroundColor"  => '#006F6F',
                        ],
                        [
                            'data' => $echecDataset,
                            "label" =>  "Les récalés définitifs",
                            'borderColor' => '#F49E24',
                            "backgroundColor"  => '#F49E24',
                            "borderWidth" => 1,
                        ]
                    ]
                ]
            ]);
        }
    }
}
