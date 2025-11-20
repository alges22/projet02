<?php

namespace App\Services;

use App\Models\ConvocationConduite;
use PDF;
use App\Services\Api;
use App\Models\DossierSession;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class ConvocationCteGenerator
{
    public function generate(array $data)
    {
        // Récupérer la valeur du token depuis les données
        $token = $data['token'];

        // Déchiffrer le token
        $decryptedDossierId =$token;

        // Maintenant, vous avez l'ID du dossier déchiffré, vous pouvez l'utiliser pour récupérer les informations nécessaires depuis la table DossierSession
        $dossierSession = DossierSession::find($decryptedDossierId);
        if (!$dossierSession) {
            // Gérer le cas où le dossier n'a pas été trouvé
            return "Dossier session introuvable";
        }

        $convocationConduite = ConvocationConduite::where('dossier_session_id', $decryptedDossierId)->first();
        $code = $convocationConduite->code;

        $npi = $dossierSession->npi;

        $allInformation = Api::base('GET', "dossier-sessions/{$decryptedDossierId}");
        $fullDossier = $allInformation->json()['data'];
        $fullDossier['convocationConduite'] = $code;

        // Génération du code QR
        $qrCode = QrCode::size(100)->generate($code);
        // Génération du contenu de la convocation avec les données récupérées
        $content = view('pdf.conduiteconvocation', [
            'dossierSession' => $fullDossier,
            'qrCode' => $qrCode,
        ])->render();

        // Utilisation de Dompdf pour générer le PDF
        $pdf = PDF::loadHtml($content);
        return $pdf->output();
    }

}
