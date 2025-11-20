<?php

namespace App\Services\Pdf;

use App\Models\Examen;
use App\Services\Help;
use App\Models\AnnexeAnatt;
use App\Services\GetCandidat;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Base\CategoriePermis;
use App\Models\Candidat\DossierSession;
use Illuminate\Support\Facades\Storage;
use App\Services\Exception\ValidationFailedException;
use App\Services\Permission\HasPermissions;
;
class ResultatExamen
{
    use HasPermissions;

    public function __construct(protected $data) {}



    public function generate()
    {
        $this->hasAnyPermission(["all", "read-exam-results-management"]);

        //locale('fr_FR')->isoFormat('Do MMMM YYYY')
        Carbon::setLocale('fr_FR');

        $annexeId =  data_get($this->data, 'annexe_id');
        if (!$annexeId) {
            throw new ValidationFailedException("Veuillez sélectionner une annexe");  # code...
        }
        $examenId = data_get($this->data, 'examen_id');
        if (!$examenId) {
            throw new ValidationFailedException("Veuillez sélectionner une session");  # code...
        }
        $annexe = AnnexeAnatt::find($annexeId);
        $session = Examen::find($examenId);
        $filename = sprintf('pdfs/examen_%s_%s.pdf', str($annexe->name)->slug(), str(Carbon::parse($session->date_code)->format('l d F Y'))->slug());

        if (Storage::exists($filename)) {
            Storage::delete($filename);
        }
        $inscripts = DossierSession::with(['autoEcole', 'categoriePermis'])->inscrits($this->data)->get();
        $npis = $inscripts->pluck("npi")->toArray();
        //Récupère les candidats sur anip
        $candidats = GetCandidat::get($npis);

        //Charge les dépendancees de chaque  dossier
        $inscripts = $inscripts->map(function (DossierSession $ds) use ($candidats) {
            $ds->withCandidat($candidats);
            return $ds;
        });

        if ($inscripts->isEmpty()) {
            throw new ValidationFailedException("Aucun résultat trouvé à télécharger.");  # code...
        }


        $pdf = Pdf::loadView('pdf.resultat-examen', [
            'resultats' => $inscripts,
            "annexe" => $annexe,
            'session' => $session,
            "title" => $this->getTitle(data_get($this->data, "result")),
            "today" => Carbon::now()->format('d F Y'),
            "logo" => Help::b64URl(public_path('logo.png')),
            "result" => data_get($this->data, "result")
        ]);
        $pdf->save($filename, 'local');

        return route('download', ['token' => encrypt($filename)]);
    }

    private function getTitle($list)
    {
        if ($list == 'admis') {
            return "Liste des admis";
        } elseif ($list == 'recales') {
            return "Liste des recalés";
        } elseif ($list == 'admis-code') {
            return "Liste des admis au code";
        } elseif ($list == 'recales-code') {
            return "Liste des recalés au code";
        } elseif ($list == 'absents-code') {
            return "Liste des absents au code";
        } elseif ($list == 'absents-conduite') {
            return "Liste des absents à la conduite";
        } elseif ($list == 'recales-conduite') {
            return "Liste des recalés à la conduite";
        }
        return "Tous les candidats";
    }
}
