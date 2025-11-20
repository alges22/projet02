<?php
namespace App\Services;

use PDF;
use App\Services\Api;
use App\Models\DossierSession;
use App\Models\PermisNumPayment;
use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class FacturePNumeriqueGenerator
{
    public function generate(array $data)
    {
        // Récupérer la valeur du token depuis les données
        $token = $data['token'];

        // Déchiffrer le token
        $decryptedNpi = decrypt($token);

        $npi = $decryptedNpi;

        $candidatPayment = PermisNumPayment::where('npi', $npi)->latest()->first();
        $montant = $candidatPayment->montant;
        $categorie_permis_id = $candidatPayment->categorie_permis_id;
        $phone_payment = $candidatPayment->phone_payment;
        $date_payment = $candidatPayment->date_payment;
        $allInformation = Api::base('GET', "candidats/{$npi}");
        $fullDossier = $allInformation->json()['data'];
        $PermisInformation = Api::base('GET', "categorie-permis/{$categorie_permis_id}");
        $PermisDossier = $PermisInformation->json()['data'];
        $fullDossier['montant'] = $montant;
        $fullDossier['categorie_permis'] = $PermisDossier['name'];
        $fullDossier['phone_payment'] = $phone_payment;
        $fullDossier['date_payment'] = $date_payment;

        $content = view('pdf.facturepermisnum', [
            'fullDossier' => $fullDossier,
        ])->render();

        // Utilisation de Dompdf pour générer le PDF
        $pdf = PDF::loadHtml($content);
        return $pdf->output();
    }

}
