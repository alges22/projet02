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


class ResultatGenerator
{
    public function generate(array $data)
    {
        // Récupérer la valeur du token depuis les données
        $id = $data['token'];


        $recrutement = Recrutement::find($id);
        $annexe_id = $recrutement->annexe_id;
        $categorie_permis_id = $recrutement->categorie_permis_id;
        $annexe = AnnexeAnatt::find($annexe_id);
        $annexeName = $annexe->name;
        $date_compo = $recrutement->date_compo;

        $categorie_permis = CategoriePermis::find($categorie_permis_id);
        $permisName = $categorie_permis->name;


        $query = Candidat::where('recrutement_id', $id)->orderByDesc('note_final');
        $candidats = $query->get();

        // Obtient les npi distincts
        $npiCollection = $candidats->filter(function ($candidat) {
            return !is_null($candidat->npi) && $candidat->npi !== '';
        })->pluck('npi')->unique();

        // Obtient les candidats en fonction des valeurs de npi
        $candidatsInfo = collect(GetCandidat::get($npiCollection->all()));

        // Associe les informations des candidats aux demandes d'agrément
        $candidats->each(function ($candidat) use ($candidatsInfo) {
            $info = $candidatsInfo->where('npi', $candidat->npi)->first();
            $candidat->candidat_info = $info;
        });
        $candidats->each(function ($candidat) {
            $langueId = $candidat->langue_id;
            $langue = Langue::find($langueId);
            $candidat->langue = $langue;
        });


        // Génération du contenu de la convocation avec les données récupérées
        $content = view('pdf.resultats', [
            'dossierSession' => $candidats,
            'date_compo' => $date_compo,
            'annexeName' => $annexeName,
            'permisName' => $permisName,
        ])->render();
    
        // Utilisation de Dompdf pour générer le PDF
        $pdf = PDF::loadHtml($content);
        return $pdf->output();
    }
    
}
