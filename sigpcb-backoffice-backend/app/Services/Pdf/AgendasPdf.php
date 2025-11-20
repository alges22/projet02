<?php

namespace App\Services\Pdf;

use App\Models\Examen;
use App\Services\Help;
use App\Models\AnnexeAnatt;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ExamenController;
use App\Services\Exception\ValidationFailedException;
use App\Http\Controllers\CandidatStatistiqueController;

class AgendasPdf
{
    public function __construct(protected $data)
    {
    }



    public function generate()
    {

        /**
         * @var \Illuminate\Http\JsonResponse $response
         */
        $response = app(ExamenController::class)->index(request());

        $year =  request('year', date('Y'));
        $data =  $response->getData(true);
        if (!$response->isSuccessful()) {
            throw  new ValidationFailedException($data['message']);
        }

        $data =  $response->getData(true);

        $sessions = collect($data['data']);


        if ($sessions->isEmpty()) {
            throw  new ValidationFailedException("Aucune session valide trouvée");
        }
        $filename = sprintf('agendas/%s.pdf', $year);

        if (Storage::exists($filename)) {
            Storage::delete($filename);
        }

        $pdf = Pdf::loadView('pdf.agendas', [
            "today" => Help::date(now(), "Do MMMM YYYY"),
            "logo" => Help::b64URl(public_path('logo.png')),
            'sessions' => $sessions,
        ]);
        $pdf->save($filename, 'local');

        return route('download', ['token' => encrypt($filename)]);
    }
}
