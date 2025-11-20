<?php

namespace App\Services\Pdf;

use App\Models\Examen;
use App\Services\Help;
use App\Models\AnnexeAnatt;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Services\Exception\ValidationFailedException;
use App\Http\Controllers\CandidatStatistiqueController;

class RapportStatistique
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
        $response = app(CandidatStatistiqueController::class)->index(request());

        $data =  $response->getData(true);
        if (!$response->isSuccessful()) {
            throw  new ValidationFailedException($data['message']);
        }

        $data =  $response->getData(true);

        $data = $data['data'];


        $annexeId =  request('annexe_id', 0);
        $examenId =  request('examen_id');
        $annexe = AnnexeAnatt::find($annexeId);
        $session = Examen::find($examenId);

        if (!$session) {
            throw  new ValidationFailedException("Aucune session valide trouvée");
        }
        if ($annexe) {
            $filename = sprintf('pdfs/stats_%s__%s.pdf', str($annexe->name)->slug(), str(Carbon::parse($session->date_code)->format('l d F Y'))->slug());
        } else {
            $filename = sprintf('pdfs/stats_anatt_%s.pdf', str(Carbon::parse($session->date_code)->format('l d F Y'))->slug());
        }

        if (Storage::exists($filename)) {
            Storage::delete($filename);
        }

        $pdf = Pdf::loadView('pdf.rapport-statistiques', [
            "annexe" => $annexe,
            'session' => Help::sessionDate(Carbon::parse($session->date_code), 'long'),
            "today" => Help::date(now(), 'DD MMMM YYYY'),
            "logo" => Help::b64URl(public_path('logo.png')),
            'langues' => $data['langues'],
            'data' => $data['data'],
            'total' => $data['total'],
        ]);
        $pdf->save($filename, 'local');

        return route('download', ['token' => encrypt($filename)]);
    }
}
