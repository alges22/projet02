<?php

namespace App\Services\Pdf;

use App\Models\DemandeAgrement;
use App\Models\Payment;
use Carbon\Carbon;
use App\Services\Help;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Services\Exception\ValidationFailedException;
use App\Services\GetCandidat;

class FacturePdf
{

    public function __construct(protected $data)
    {
    }

    public function generate()
    {

        $paymentId = request('paymentId');

        $service = request('service');
        if (!is_numeric($paymentId)) {
            throw  new ValidationFailedException("Donnée incorrecte");
        }


        $payment = Payment::find($paymentId);

        if (!$payment) {
            throw  new ValidationFailedException("La facture que vous essayez de télécharger n'est plus disponible");
        }


        $promoteur = GetCandidat::findOne($payment->npi);

        if (!$promoteur) {
            throw  new ValidationFailedException("Le compte du promoteur sur ANIP, n'existe pas");
        }


        $filename = sprintf('pdfs/facture__%s.pdf', $payment->id);

        if (Storage::exists($filename)) {
            Storage::delete($filename);
        }


        $pdf = Pdf::loadView('pdf.facture', [
            'today' => Help::sessionDate(now(), 'long'),
            "logo" => Help::b64URl(public_path('logo.png')),
            'promoteur' => $promoteur,
            'transaction' => $payment,
            'service' => $service

        ]);
        $pdf->save($filename, 'local');

        return route('download', ['token' => encrypt($filename)]);
    }
}
