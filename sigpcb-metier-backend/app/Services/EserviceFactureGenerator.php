<?php
namespace App\Services;

use PDF;
use App\Models\User;
use App\Services\Api;
use App\Models\DossierSession;
use App\Models\EservicePayment;
use App\Models\PermisNumPayment;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class EserviceFactureGenerator
{
    public function generate(array $data)
    {
        // Récupérer la valeur du token depuis les données
        $token = $data['token'];

        // Déchiffrer le token
        $decryptedNpi = decrypt($token);

        $npi = $decryptedNpi;

        $candidatPayment = EservicePayment::where('npi', $npi)->latest()->first();
        $montant = $candidatPayment->montant;
        $phone_payment = $candidatPayment->phone;
        $date_payment = $candidatPayment->date_payment;
        $allInformation = Api::base('GET', "candidats/{$npi}");
        $fullDossier = $allInformation->json()['data'];
        $fullDossier['montant'] = $montant;
        $fullDossier['phone_payment'] = $phone_payment;
        $fullDossier['date_payment'] = $date_payment;

        $content = view('pdf.factureeservice', [
            'fullDossier' => $fullDossier,
        ])->render();

        // Utilisation de Dompdf pour générer le PDF
        $pdf = PDF::loadHtml($content);
        return $pdf->output();
    }

}
