<?php

namespace App\Http\Controllers\Composition;

use App\Models\JuryCandidat;
use App\Services\Help;
use Illuminate\Http\Request;
use App\Models\CandidatExamenSalle;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ApiController;
use App\Services\GetCandidat;
use Illuminate\Support\Facades\Storage;

class ListeEmargementController extends ApiController
{
    public function __invoke(Request $request)
    {

        if (!is_numeric($request->examen_id)) {
            return $this->errorResponse("Vous devez sélectionner un examen");
        }

        if (!is_numeric($request->annexe_id)) {
            return $this->errorResponse("Vous devez sélectionner une annexe");
        }
        try {
            if ($request->get("type") == 'code') {
                return $this->code($request);
            }

            return $this->reconduite($request);
        } catch (\Throwable $th) {
            logger($th);
            return $this->errorResponse($th->getMessage());
        }
    }
    private function code(Request $request)
    {
        $emargements = CandidatExamenSalle::filter($request->all())
            ->paginate();
        $candidats = collect($emargements->items());

        $npis = $candidats->pluck('npi')->all();

        $infos = GetCandidat::get($npis);
        $candidats = $candidats->map(function (CandidatExamenSalle $candidat) use ($infos) {
            $emargement = null;
            if ($candidat->emargement) {
                $filename = Storage::disk(CandidatExamenSalle::SIGNATURE_DISK)->path($candidat->emargement);
                $emargement = Help::b64URl($filename);
            }
            # Conduite emargement
            $conduite_emargement = null;
            $jc = JuryCandidat::where('dossier_session_id', $candidat->dossier_session_id)->latest()->first();
            if (data_get($jc, 'signature')) {
                try {
                    $filename = Storage::disk(JuryCandidat::SIGNATURE_DISK)->path($jc->signature);
                    $conduite_emargement = Help::b64URl($filename);
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
            $info = collect($infos)->where("npi", $candidat->npi)->first();
            $candidat->withCategoriePermis();
            $candidat->setAttribute("candidat_info", $info);
            $candidat->setAttribute("code_signature", $emargement);
            $candidat->setAttribute("conduite_signature", $conduite_emargement);
            return $candidat;
        });

        return $this->successResponse($candidats);
    }

    private  function reconduite(Request $request)
    {
        $emargements = JuryCandidat::filter($request->all())
            ->paginate();
        $candidats = collect($emargements->items());

        $npis = $candidats->pluck('npi')->all();

        $infos = GetCandidat::get($npis);
        $candidats = $candidats->map(function (JuryCandidat $candidat) use ($infos) {

            $conduite_emargement = null;
            if (data_get($candidat, 'signature')) {
                $filename = Storage::disk(JuryCandidat::SIGNATURE_DISK)->path($candidat->signature);
                $conduite_emargement = Help::b64URl($filename);
            }
            $info = collect($infos)->where("npi", $candidat->npi)->first();
            $candidat->withCategoriePermis();
            $candidat->setAttribute("candidat_info", $info);
            $candidat->setAttribute("conduite_signature", $conduite_emargement);
            return $candidat;
        });

        return $this->successResponse($candidats);
    }
}
