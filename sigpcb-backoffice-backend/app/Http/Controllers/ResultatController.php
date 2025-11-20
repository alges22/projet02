<?php

namespace App\Http\Controllers;

use App\Models\Permis;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Http\Resources\PermisResource;
use App\Models\Candidat\DossierSession;
use Illuminate\Pagination\LengthAwarePaginator;

class ResultatController extends ApiController
{

    public function codes()
    {
        $this->hasAnyPermission(["all", "read-exam-results-management"]);
        return $this->exportFromBase('resultats/codes', request()->all());
    }
    public function conduites()
    {
        $this->hasAnyPermission(["all", "read-exam-results-management"]);
        return $this->exportFromBase('resultats/conduites', request()->all());
    }

    public function recales()
    {
        $this->hasAnyPermission(["all", "read-exam-results-management"]);
        return $this->exportFromBase('resultats/recales', request()->all());
    }

    public function admis()
    {
        $this->hasAnyPermission(["all", "read-exam-results-management"]);
        return $this->exportFromBase('resultats/admis', request()->all());
    }
    public function statisticCode()
    {
        $this->hasAnyPermission(["all", "read-exam-results-management"]);

        $data['presentes'] = 0;
        $data['admis'] = 0;
        $data['recales'] = 0;
        $data['abscents'] = 0;
        return $this->exportFromBase('resultats/statistic-code', request()->all());
    }
    public function statisticConduite()
    {
        $this->hasAnyPermission(["all", "read-exam-results-management"]);
        return $this->exportFromBase('resultats/statistic-conduite', request()->all());
    }

    public function resultats(Request $request)
    {
        $this->hasAnyPermission(["all", "read-exam-results-management"]);

        $inscripts = DossierSession::with(['autoEcole', 'categoriePermis'])->inscrits($request->all())->get();
        $npis = $inscripts->pluck("npi")->toArray();
        //Récupère les candidats sur anip
        $candidats = GetCandidat::get($npis);

        //Charge les dépendancees de chaque  dossier
        $inscripts = $inscripts->map(function (DossierSession $ds) use ($candidats) {
            $ds->withCandidat($candidats);
            return $ds;
        });
        return $this->successResponse($inscripts);
    }

    public function listEmargement()
    {
        $this->hasAnyPermission(["all", "read-exam-results-management"]);
        return $this->exportFromBase('resultats/list-emargement', request()->all());
    }

    public function admisPermis(Request $request)
    {
        $this->hasAnyPermission(["all", "read-transmission-menu-access"]);
        /**
         * @var LengthAwarePaginator
         */
        $admis = Permis::with(['categoriePermis'])->filter($request->all())
            ->orderBy("delivered_at")->paginate(10);

        $npis = $admis->pluck("npi")->unique()->all();
        $infos = GetCandidat::get($npis);

        $admis->each(function (Permis $ds) use ($infos) {
            $ds->withCandidat($infos);
        });
        return $this->successResponse([
            "data" => PermisResource::collection($admis),
            "total" => $admis->total(),
        ]);
    }
}
