<?php

namespace App\Http\Controllers;

use App\Services\Help;
use Illuminate\Http\Request;
use App\Models\DossierSession;
use App\Models\Inscription;
use App\Services\GetCandidat;

class CandidatController extends ApiController

{

    public function index(Request $request)
    {

        try {
            $npis = Inscription::where("auto_ecole_id", Help::autoEcoleId())->get()->pluck("npi")->all();

            $candidats = GetCandidat::get($npis);
            $list = DossierSession::filter($request->all())->whereIn("npi", $npis)->paginate(10);

            /************************** Transformation des suivis ************************************ */
            # Map chaque ligne de suiviCandidat et ajouté le candidat

            $transform = function (DossierSession $dossierSession) use ($candidats) {
                # Récupération du candidat
                $candidat = collect($candidats)->where(function ($c) use ($dossierSession) {
                    return $c['npi'] === $dossierSession->npi;
                })->first();
                $dossierSession->setAttribute('candidat', $candidat);
                return $this->callMap($dossierSession);
            };
            /**************************Fin de la transformation des suivis************************************ */

            return $this->withPagination($list, $transform);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue sur le serveur', statuscode: 500);
        }
    }
    private function callMap(DossierSession $ds)
    {
        $ds->withCategoriePermis();
        $ds->withDossier();
        $ds->withAutoEcole();
        $ds->withLangue();
        $ds->withAnnexe();
        $ds->withExamen();

        return $ds;
    }
}
