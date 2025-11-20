<?php
namespace App\Services;

use App\Models\Admin\AnnexeAnatt;
use App\Models\Admin\Examen;
use App\Models\Base\CategoriePermis;
use App\Models\DossierSession;
use App\Models\Langue;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Permis;
use App\Services\GetCandidat;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class AttestationGenerator
{
    public function generate(array $data)
    {
        // Récupérer la valeur du token depuis les données
        $token = $data['token'];

        // Déchiffrer le token pour obtenir l'identifiant du permis
        $permisId = decrypt($token); // Assurez-vous que vous déchiffrez pour obtenir l'ID

        // Récupérer le permis correspondant à l'identifiant
        $permis = Permis::find($permisId);

        if (!$permis) {
            return "Permis non trouvé";
        }

        // Récupérer le dossier de session à partir du permis
        $dossierSession = DossierSession::find($permis->dossier_session_id);
        $catPermis = CategoriePermis::find($permis->categorie_permis_id);

        if (!$dossierSession) {
            return "Dossier de session non trouvé pour le permis.";
        }

        // Obtenir les informations du candidat
        $candidat = GetCandidat::findOne($dossierSession->npi);

        if (!$candidat) {
            return "Candidat non trouvé pour le NPI {$dossierSession->npi}.";
        }

        // Récupérer l'examen et l'annexe
        $examen = Examen::find($dossierSession->examen_id);
        $annexe = AnnexeAnatt::find($dossierSession->annexe_id);
        $langue = Langue::find($dossierSession->langue_id);

        // Générer l'URL de vérification
        $verificationLink = route('verify.permit', ['code' => $permis->code_permis]);

        // Générer le QR code avec l'URL de vérification
        $qrCode = QrCode::format('png')->size(300)->generate($verificationLink);
        $qrCodePath = public_path('qrcodes/') . $permis->code_permis . '.png';
        file_put_contents($qrCodePath, $qrCode);

        // Préparer les données à passer à la vue, incluant le chemin du QR code
        $dataToPass = [
            'npi' => $dossierSession->npi,
            'nom' => data_get($candidat, 'nom'),
            'prenoms' => data_get($candidat, 'prenoms'),
            'adresse' => data_get($candidat, 'adresse'),
            'telephone' => data_get($candidat, 'telephone'),
            'date_de_naissance' => data_get($candidat, 'date_de_naissance'),
            'lieu_de_naissance' => data_get($candidat, 'lieu_de_naissance'),
            'code_permis' => $permis->code_permis,
            'cat_permis' => $catPermis->name,
            'langue' => $langue->name,
            'examen_name' => $examen ? $examen->session_long : 'N/A',
            'annexe_name' => $annexe ? $annexe->name : 'N/A',
            'qr_code_path' => asset('qrcodes/' . $permis->code_permis . '.png'),
        ];

        // Passer les données à la vue
        $content = view('pdf.attestation-success', $dataToPass)->render();

        // Utilisation de Dompdf pour générer le PDF
        $pdf = PDF::loadHtml($content);
        return $pdf->output();
    }

}

