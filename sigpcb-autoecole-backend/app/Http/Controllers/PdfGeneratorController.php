<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Pdf\FacturePdf;
use App\Services\Pdf\LicencePdf;
use App\Services\Pdf\AgrementPdf;
use App\Services\Pdf\CandidatList;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class PdfGeneratorController extends ApiController
{

    protected $documentGenerators = [
        'candidats' => CandidatList::class,
        'agrement' => AgrementPdf::class,
        'facture' => FacturePdf::class,
        'licence' => LicencePdf::class,
    ];


    public function download(Request $request)
    {
        if (!$request->has('token')) {
            abort(404, "La page que vous demandez est introuvable");
        }

        $path = decrypt(request('token'));

        if (!Storage::exists($path)) {
            abort(404, "La page que vous demandez est introuvable");
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
            $url = $path;
            return $this->successResponse($url);
        } catch (\App\Services\Exception\ValidationFailedException $e) {
            logger()->error($e);
            return $this->errorResponse(
                $e->getMessage()
            );
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse($e->getMessage());
        }
    }
}
