<?php

namespace App\Services\Pdf;

use App\Models\Examen;
use App\Services\Help;
use App\Services\GetCandidat;
use App\Models\DossierSession;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Services\Exception\ValidationFailedException;
use Illuminate\Support\Facades\Auth;

class CandidatList
{
    public function __construct(protected $data)
    {
    }

    public function generate()
    {

        $ae = Help::authAutoEcole();

        $examenId = request('examen_id');
        if (!is_numeric($examenId)) {

            $session = Examen::recent();

            if (!$session) {
                throw  new ValidationFailedException("Vous devez sélectionner une session");
            }

            $examenId  = $session->id;
        } else {
            $session = Examen::find(intval($examenId));
        }

        $state = request('state');
        $collection = $this->statusValidations($examenId, $state);
        $first = $collection->first();

        if (!$session) {
            throw  new ValidationFailedException("Aucune session programmée actuellement");
        }
        if (!$first) {
            throw  new ValidationFailedException("Aucun candidat trouvé");
        }
        $pdfData =  $collection;


        $filename = sprintf('pdfs/%s__%s.pdf', str($ae->name)->slug(), str(Carbon::parse($session->date_code)->format('l d F Y'))->slug());

        if (Storage::exists($filename)) {
            Storage::delete($filename);
        }

        $auth = $ae->promoteur->withInfos();

        $promoteur = $auth->infos;

        $pdf = Pdf::loadView('pdf.candidats', [
            'state' => $state,
            'candidats' => $pdfData,
            "ae" => $ae,
            'promoteur' => $promoteur,
            'session' => Help::sessionDate(Carbon::parse($session->date_code), 'long'),
            "today" => Help::sessionDate(Carbon::parse($session->date_code), 'long'),
            "logo" => Help::b64URl(public_path('logo.png'))
        ]);
        $pdf->save($filename, 'local');

        return route('download', ['token' => encrypt($filename)]);
    }

    private function statusValidations($examenId, ?string $state = null)
    {
        $wheres = [
            'examen_id' => $examenId,
            'closed' => false,
            "auto_ecole_id" => Help::autoEcoleId()
        ];

        if ($state) {
            $wheres['state'] = $state;
        }

        if ($state == 'init' || $state == 'pending') {
            unset($wheres['examen_id']);
        }
        $collection = DossierSession::where($wheres)->get();
        $npis = $collection->map(function ($s) {
            return $s->npi;
        })->toArray();
        $candidats = GetCandidat::get($npis);

        return  $collection->map(function ($ds) use ($candidats) {
            $ds->withCandidat($candidats);
            $ds->withCategoriePermis();
            $ds->withAutoEcole();
            $ds->withLangue();
            $ds->withAnnexe();
            return $ds;
        });
    }
}
