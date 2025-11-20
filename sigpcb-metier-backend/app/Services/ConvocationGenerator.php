<?php
namespace App\Services;

use PDF;
use App\Services\Api;
use App\Models\Candidat;
use App\Models\Base\Langue;
use App\Models\Recrutement;
use App\Services\GetCandidat;
use App\Models\DossierSession;
use App\Models\ConvocationCode;
use App\Models\Admin\AnnexeAnatt;
use App\Models\Base\CategoriePermis;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class ConvocationGenerator
{
    public function generate(array $data)
    {
        // Récupérer la valeur du token depuis les données
        $id = $data['token'];
    
        $candidat = Candidat::find($id);
        if (!$candidat) {
            return "Candidat introuvable";
        }

        $recrutement_id = $candidat->recrutement_id;
        $convocationCode = ConvocationCode::where('candidat_id', $id)->where('recrutement_id',$recrutement_id)->first();

        $recrutement = Recrutement::find($recrutement_id);
        $date_compo = $recrutement->date_compo;
        $categorie_permis_id = $recrutement->categorie_permis_id;
        $annexe_id = $recrutement->annexe_id;

        $categorie_permis = CategoriePermis::find($categorie_permis_id);

        $annexe = AnnexeAnatt::find($annexe_id);

        $langueId = $candidat->langue_id;
        $langue = Langue::find($langueId);

        $code = $convocationCode->code;

        $npi = $candidat->npi;
    
        $candidatsInfo = collect(GetCandidat::findOne($npi));

        // $allInformation = Api::base('GET', "dossier-sessions/{$decryptedDossierId}");
        // $fullDossier = $allInformation->json()['data'];
        $fullDossier['convocationCode'] = $code;
        $fullDossier['categorie_permis'] = $categorie_permis;
        $fullDossier['annexe'] = $annexe;
        $fullDossier['langue'] = $langue;
        $fullDossier['date_compo'] = $date_compo;
        $fullDossier['candidat'] = $candidatsInfo;
        // Génération du code QR
        $qrCode = QrCode::size(100)->generate($code);


        // Génération du contenu de la convocation avec les données récupérées
        $content = view('pdf.codeconvocation', [
            'dossierSession' => $fullDossier,
            'qrCode' => $qrCode,
        ])->render();
    
        // Utilisation de Dompdf pour générer le PDF
        $pdf = PDF::loadHtml($content);
        return $pdf->output();
    }
    
}
