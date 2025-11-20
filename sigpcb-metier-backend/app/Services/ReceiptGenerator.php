<?php

namespace App\Services;

use App\Models\CandidatPayment;
use PDF;
use App\Services\Api;
use App\Models\DossierSession;

class ReceiptGenerator
{
    public function generate(array $data)
    {
        // Récupérer la valeur du token depuis les données
        $token = $data['token'];
    
        // Déchiffrer le token
        $decryptedDossierId = decrypt($token);
    
        // Maintenant, vous avez l'ID du dossier déchiffré, vous pouvez l'utiliser pour récupérer les informations nécessaires depuis la table DossierSession
        $dossierSession = DossierSession::find($decryptedDossierId);
        if (!$dossierSession) {
            return "Dossier session introuvable";
        }
        $candidatPayment = CandidatPayment::where('dossier_session_id', $decryptedDossierId)->latest()->first();
        $montant = $candidatPayment->montant;
        $phone_payment = $candidatPayment->phone_payment;
        $date_payment = $candidatPayment->date_payment;
        $allInformation = Api::base('GET', "dossier-sessions/{$decryptedDossierId}");
        $fullDossier = $allInformation->json()['data'];
        $fullDossier['montant'] = $montant;
        $fullDossier['phone_payment'] = $phone_payment;
        $fullDossier['date_payment'] = $date_payment;
        // Génération du contenu de la convocation avec les données récupérées
        $content = view('pdf.paiementfacture', [
            'dossierSession' => $fullDossier,
        ])->render();
    
        // Utilisation de Dompdf pour générer le PDF
        $pdf = PDF::loadHtml($content);
        return $pdf->output();
    }
    
}
