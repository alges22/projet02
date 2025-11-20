<?php

namespace App\Services\Pdf;

use App\Models\DemandeAgrement;
use Carbon\Carbon;
use App\Services\Help;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Services\Exception\ValidationFailedException;
use App\Services\GetCandidat;

class AgrementPdf
{

    public function __construct(protected $data)
    {
    }

    public function generate()
    {

        $demandeId = request('demandeId');

        if (!is_numeric($demandeId)) {
            throw  new ValidationFailedException("Donnée incorrecte");
        }


        $demande = DemandeAgrement::find($demandeId);

        if (!$demande) {
            throw  new ValidationFailedException("L'agrément que vous essayez de télécharger n'existe pas ou a été retiré");
        }

        if ($demande->state !== 'validate') {
            throw  new ValidationFailedException("L'agrément que vous essayez de télécharger n'est pas valide");
        }

        $promoteur = $demande->promoteur;

        $promoteur = GetCandidat::findOne($promoteur->npi);

        if (!$promoteur) {
            throw  new ValidationFailedException("Le compte du promoteur sur ANIP, n'existe pas");
        }
        $agrement = $demande->agrement;

        if (!$agrement) {
            throw  new ValidationFailedException("L'agrément que vous essayez de télécharger n'existe pas ou a été retiré");
        }

        $filename = sprintf('pdfs/agrement__%s.pdf', $agrement->id);

        if (Storage::exists($filename)) {
            Storage::delete($filename);
        }

        $agrement->load('autoEcole');
        $ae = $agrement->autoEcole;

        $pdf = Pdf::loadView('pdf.agrement', [
            'today' => Help::sessionDate(now(), 'long'),
            "logo" => Help::b64URl(public_path('logo.png')),
            'promoteur' => $promoteur,
            'ae' => $ae,
            'signature'  => Help::b64URl(public_path('images/signature.png')),
            'agrement' => $agrement,
            'signataire' => "Richard DADA",
        ]);
        $pdf->save($filename, 'local');

        return route('download', ['token' => encrypt($filename)]);
    }
}
