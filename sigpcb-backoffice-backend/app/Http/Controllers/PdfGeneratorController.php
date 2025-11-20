<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Pdf\AgendasPdf;
use App\Services\Pdf\ResultatExamen;
use App\Exports\Resultats\PermisExport;
use App\Services\Pdf\ProgrammationCode;
use Illuminate\Support\Facades\Storage;
use App\Services\Pdf\RapportStatistique;
use Illuminate\Support\Facades\Response;
use App\Services\Excels\StatistiqueExcel;
use App\Services\Pdf\ProgrammationConduite;

class PdfGeneratorController extends ApiController
{

    protected $documentGenerators = [
        'programmation-code' => ProgrammationCode::class,
        'programmation-conduite' => ProgrammationConduite::class,
        'rapport-statistics' => RapportStatistique::class,
        "agendas" => AgendasPdf::class,
        "statistiques-excel" => StatistiqueExcel::class,
        "resultat-examen" => ResultatExamen::class,
        "resultat-permis-excel" => PermisExport::class,
    ];


    public function download(Request $request)
    {
        if (!$request->has('token')) {
            abort(404, "La page que vous demandez est introuvable");
        }

        $path = decrypt(request('token'));

        if (!Storage::exists($path)) {
            if (!$request->has('token')) {
                abort(404, "La page que vous demandez est introuvable");
            }
        }
        return Response::download(Storage::path($path));
    }
    public function generate(string $type, Request $request)
    {
        if (!array_key_exists($type, $this->documentGenerators)) {
            return $this->errorResponse('La page que vous demandez est introuvable', statuscode: 404);
        }

        $generatorClass = $this->documentGenerators[$type];
        $generator = new $generatorClass($request->all());

        try {
            $path = $generator->generate();

            if (!$path) {
                return $this->errorResponse('Une erreur inattendue s\'est produite lors de la génération du PDF', statuscode: 404);
            }
            return $this->successResponse($path);
        } catch (\App\Services\Exception\ValidationFailedException $e) {
            logger()->error($e);
            return $this->errorResponse($e->getMessage());
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse($e->getMessage());
        }
    }
}
