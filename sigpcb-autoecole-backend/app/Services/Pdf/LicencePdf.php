<?php

namespace App\Services\Pdf;

use App\Models\DemandeAgrement;
use App\Models\DemandeLicence;
use App\Models\Licence;
use App\Models\Moniteur;
use Carbon\Carbon;
use App\Services\Help;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Services\Exception\ValidationFailedException;
use App\Services\GetCandidat;

class LicencePdf
{

    public function __construct(protected $data)
    {
    }

    public function generate()
    {

        $demandeId = request('licenceId');

        if (!is_numeric($demandeId)) {
            throw  new ValidationFailedException("Donnée incorrecte");
        }


        $demande = DemandeLicence::find($demandeId);

        if (!$demande) {
            throw  new ValidationFailedException("La licence que vous essayez de télécharger n'existe pas ou a été retirée");
        }

        if ($demande->state !== 'validate') {
            throw  new ValidationFailedException("La licence que vous essayez de télécharger n'est pas valide");
        }

        $promoteur = $demande->promoteur;

        $promoteur = GetCandidat::findOne($promoteur->npi);

        if (!$promoteur) {
            throw  new ValidationFailedException("Le compte du promoteur sur ANIP, n'existe pas");
        }

        $ae = $demande->autoEcole;
        $licence = $ae->lastLicence();

        $demandeLicence = $ae->lastDemandeLicence();
        if (!$licence) {
            throw  new ValidationFailedException("La licence que vous essayez de télécharger n'existe pas ou a été retirée");
        }

        $filename = sprintf('pdfs/licence__%s.pdf', $licence->id);

        if (Storage::exists($filename)) {
            Storage::delete($filename);
        }
        $monitorNpis = Moniteur::where([
            'auto_ecole_id' => $ae->id,
            'active' => true
        ])->get()->map(function ($moniteur) {
            return $moniteur->npi;
        })->toArray();

        $moniteurs = GetCandidat::get($monitorNpis);
        $ae->load('agrement');
        $pdf = Pdf::loadView('pdf.licence', [
            'today' => Help::sessionDate(now(), 'long'),
            "logo" => Help::b64URl(public_path('logo.png')),
            'promoteur' => $promoteur,
            'ae' => $ae,
            'signature'  => Help::b64URl(public_path('images/signature.png')),
            'licence' => $licence,
            'date_debut' => Help::sessionDate($licence->date_debut, 'long'),
            'date_fin' => Help::sessionDate($licence->date_fin, 'long'),
            'signataire' => "Richard DADA",
            'agrement' => $ae->agrement,
            'moniteurs' => $moniteurs,
            "vehicules" => json_decode($demandeLicence->vehicules, true)
        ]);
        $pdf->save($filename, 'local');

        return route('download', ['token' => encrypt($filename)]);
    }
}
