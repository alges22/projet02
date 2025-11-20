<?php

namespace App\Services\Pdf;

use App\Http\Controllers\ConduiteController;
use App\Http\Controllers\ProgrammationController;
use App\Models\AnnexeAnatt;
use App\Models\Examen;
use App\Services\Exception\ValidationFailedException;
use App\Services\Help;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ProgrammationConduite
{
    public function __construct(protected $data)
    {
    }



    public function generate()
    {

        Carbon::setLocale('fr');
        /**
         * @var \Illuminate\Http\JsonResponse $response
         */
        $response = app(ConduiteController::class)->programmations();

        if (!$response->isSuccessful()) {
            throw  new ValidationFailedException("Validation échouée");
        }

        $data =  $response->getData(true);

        $collection = collect($data['data']);

        $pdfData = $collection->map(function ($programmation, $date) {
            # Ex: Lundi 27 Mars 2021
            $dateParsed = Help::sessionDate(Carbon::createFromFormat('m-d-Y', $date), 'long');

            # Conversion des données à un format plus structuré
            $collect =  collect($programmation)->map(function ($prog, $permis) {
                return [
                    'permis' => $permis,
                    'candidats' => $prog
                ];
            });

            return [
                'date' => $dateParsed,
                'programmations' => $collect
            ];
        })->values();

        # Ceci est sur parce si ces données n'étaient pas présentes le code serait bloqué sur l'appel du controlleur ci-dessous
        $annexeId =  request('annexe_id');
        $examenId =  request('examen_id');
        $annexe = AnnexeAnatt::find($annexeId);
        $session = Examen::find($examenId);
        $filename = sprintf('pdfs/%s__%s.pdf', str($annexe->name)->slug(), str(Carbon::parse($session->date_conduite)->format('l d F Y'))->slug());

        if (Storage::exists($filename)) {
            Storage::delete($filename);
        }

        $pdf = Pdf::loadView('pdf.programmation-conduite', [
            'collection' => $pdfData,
            "annexe" => $annexe,
            'session' => Carbon::parse($session->date_conduite)->format('F Y'),
            'date_conduite' => Help::sessionDate(Carbon::parse($session->date_conduite), 'long'),
            "today" => Carbon::now()->format('d F Y'),
            "logo" => Help::b64URl(public_path('logo.png'))
        ]);
        $pdf->save($filename, 'local');

        return route('download', ['token' => encrypt($filename)]);
    }
}
