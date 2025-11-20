<?php

namespace App\Services\Statistics;

use App\Services\Help;
use App\Models\Base\Langue;
use Illuminate\Http\Request;
use App\Models\Candidat\Candidat;
use Illuminate\Support\Collection;
use App\Models\Candidat\DossierSession;
use Illuminate\Database\Eloquent\Builder;

class RapportSyntethique
{

    /**
     *
     *
     * @var Builder
     */
    private $builder;
    private $langues;

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

        $this->setLangues();
        $data = [
            "langues"
            => $this->langues,
            "data" => [
                $this->inscritAuCode($collections),
                $this->absentAuCode($collections),
                $this->presentsAuCode($collections),
                $this->echoueAuCode($collections),
                $this->admisAuCode($collections),
                $this->reconduits($collections),
                $this->absentsConduites($collections),
                $this->presentsConduites($collections),
                $this->echouesConduites($collections),
                $this->admis($collections),
            ],
            "total" => $this->totalInscrit,
        ];

        return $data;
    }


    private function inscritAuCode(Collection $collection)
    {
        $langStats = [];
        $collection = $collection->where('type_examen', 'code-conduite');
        $total = $collection->count();


        foreach ($this->langues as $key => $lang) {
            $count = $collection->where('langue_id', $lang['id'])->count();
            $lang['count'] = $count;
            $langStats[] = $lang;
        }

        return  [
            "name" => "Inscrits au Code",
            "langues" => $langStats,
            "sexes" => [
                [
                    "name" => "H",
                    "count"   => $collection->where('sexe', "M")->count(),
                ],
                [
                    "name" => "F",
                    "count"   =>  $collection->where('sexe', "F")->count(),
                ]
            ],
            "total" => $total,
            "percent" => Help::percent($total, $this->totalInscrit),
        ];
    }

    private function absentAuCode(Collection $collection)
    {

        $langStats = [];
        $data = $collection
            ->where('type_examen', 'code-conduite')
            ->where('presence', 'abscent');
        $total = $data->count();

        foreach ($this->langues as $key => $lang) {
            $lang['count'] = $data->where('langue_id', $lang['id'])->count();
            $langStats[] = $lang;
        }

        return  [
            "name" => "Absents au Code",
            "langues" => $langStats,
            "sexes" => [
                [
                    "name" => "H",
                    "count" => $data->where('sexe', "M")->count(),
                ],
                [
                    "name" => "F",
                    "count" => $data->where('sexe', "F")->count(),
                ]
            ],
            "total" => $total,
            "percent" => Help::percent($total, $this->totalInscrit),
        ];
    }

    private function presentsAuCode(Collection $collection)
    {
        $langStats = [];
        $data = $collection->where('type_examen', 'code-conduite')->where('presence', 'present');
        $total = $data->count();

        foreach ($this->langues as $key => $lang) {
            $lang['count'] = $data->where('langue_id', $lang['id'])->count();
            $langStats[] = $lang;
        }

        return  [
            "name" => "Présents au code",
            "langues" => $langStats,
            "sexes" => [
                [
                    "name" => "H",
                    "count"   =>
                    $data->where('sexe', "M")->count(),
                ],
                [
                    "name" => "F",
                    "count"   =>
                    $data->where('sexe', "F")->count(),
                ]
            ],
            "total" => $total,
            "percent" =>  Help::percent($total, $this->totalInscrit),
        ];
    }

    private function echoueAuCode(Collection $collection)
    {
        $langStats = [];
        $data = $collection->where('resultat_code', 'failed');
        $total = $data->count();
        foreach ($this->langues as $key => $lang) {
            $lang['count'] = $data->where('langue_id', $lang['id'])->count();
            $langStats[] = $lang;
        }

        return  [
            "name" => "Echoues au Code",
            "langues" => $langStats,
            "sexes" => [
                [
                    "name" => "H",
                    "count" => $data->where('sexe', "M")->count(),
                ],
                [
                    "name" => "F",
                    "count" => $data->where('sexe', "F")->count(),
                ]
            ],
            "total" => $total,
            "percent" =>  Help::percent($total, $this->totalInscrit),
        ];
    }

    private function admisAuCode(Collection $collection)
    {

        $langStats = [];
        $data = $collection->where('resultat_code', 'success');
        $total = $data->count();
        foreach ($this->langues as $key => $lang) {
            $lang['count'] = $data->where('langue_id', $lang['id'])->count();
            $langStats[] = $lang;
        }

        return  [
            "name" => "Admis au Code",
            "langues" => $langStats,
            "sexes" => [
                [
                    "name" => "H",
                    "count"   =>
                    $data->where('sexe', "M")->count(),
                ],
                [
                    "name" => "F",
                    "count"   =>
                    $data->where('sexe', "F")->count(),
                ]
            ],
            "total" => $total,
            "percent" =>  Help::percent($total, $this->totalInscrit),
        ];
    }

    private function reconduits(Collection $collection)
    {
        $langStats = [];
        $data = $collection->where('type_examen', 'conduite');
        $total = $data->count();
        foreach ($this->langues as $key => $lang) {
            $lang['count'] = $data->where('langue_id', $lang['id'])->count();
            $langStats[] = $lang;
        }

        return  [
            "name" => "Reconduits",
            "langues" => $langStats,
            "sexes" => [
                [
                    "name" => "H",
                    "count"   => $data->where('sexe', "M")->count(),
                ],
                [
                    "name" => "F",
                    "count"   => $data->where('sexe', "F")->count(),
                ]
            ],
            "total" => $total,
            "percent" =>  Help::percent($total, $this->totalInscrit),
        ];
    }

    private function absentsConduites(Collection $collection)
    {
        $langStats = [];
        $data = $collection->where('resultat_conduite', "failed")->where('presence_conduite', 'absent');
        $total = $data->count();
        foreach ($this->langues as $key => $lang) {
            $lang['count'] = $data->where('langue_id', $lang['id'])->count();
            $langStats[] = $lang;
        }

        return  [
            "name" => "Absents conduites",
            "langues" => $langStats,
            "sexes" => [
                [
                    "name" => "H",
                    "count"   => $data->where('sexe', "M")->count(),
                ],
                [
                    "name" => "F",
                    "count"   => $data->where('sexe', "F")->count(),
                ]
            ],
            "total" => $total,
            "percent" => Help::percent($total, $this->totalInscrit),
        ];
    }
    private function presentsConduites(Collection $collection)
    {
        $langStats = [];
        $data = $collection->where('presence_conduite', 'present');
        $total = $data->count();
        foreach ($this->langues as $key => $lang) {
            $lang['count'] = $data->where('langue_id', $lang['id'])->count();
            $langStats[] = $lang;
        }

        return  [
            "name" => "présents conduites",
            "langues" => $langStats,
            "sexes" => [
                [
                    "name" => "H",
                    "count"   => $data->where('sexe', "M")->count(),
                ],
                [
                    "name" => "F",
                    "count"   => $data->where('sexe', "F")->count(),
                ]
            ],
            "total" => $total,
            "percent" => Help::percent($total, $this->totalInscrit),
        ];
    }

    private function echouesConduites(Collection $collection)
    {
        $langStats = [];
        $data = $collection->where('resultat_conduite', 'failed')->where('resultat_code', 'success');
        $total = $data->count();
        foreach ($this->langues as $key => $lang) {
            $lang['count'] = $data->where('langue_id', $lang['id'])->count();
            $langStats[] = $lang;
        }

        return  [
            "name" => "Echoues Conduites",
            "langues" => $langStats,
            "sexes" => [
                [
                    "name" => "H",
                    "count"   =>
                    $data->where('sexe', "M")->count(),
                ],
                [
                    "name" => "F",
                    "count"   =>
                    $data->where('sexe', "F")->count(),
                ]
            ],
            "total" => $total,
            "percent" =>  Help::percent($total, $this->totalInscrit),
        ];
    }

    private function admis(Collection $collection)
    {

        $langStats = [];
        $data =  $collection->where('resultat_conduite', 'success');
        $total = $data->count();
        foreach ($this->langues as $key => $lang) {
            $lang['count'] = $data->where('langue_id', $lang['id'])->count();
            $langStats[] = $lang;
        }

        return  [
            "name" => "Admis definitifs",
            "langues" => $langStats,
            "sexes" => [
                [
                    "name" => "H",
                    "count"   =>
                    $data->where('sexe', "M")->count(),
                ],
                [
                    "name" => "F",
                    "count"   =>
                    $data->where('sexe', "F")->count(),
                ]
            ],
            "total" => $total,
            "percent" =>  Help::percent($total, $this->totalInscrit),
        ];
    }

    private function setLangues()
    {
        $this->langues = Langue::orderBy("created_at")->get(['name', 'id'])->toArray();
    }
}
