<?php

namespace App\Services\Imports\Validation;

use Maatwebsite\Excel\HeadingRowImport;
use App\Services\Exception\ImportationException;

class AutoEcoleCellsValidation
{
    private $cells = [
        "n",
        "auto_ecole",
        "departement",
        "commune",
        "npi_du_promoteur",
        "e_mail_du_promoteur",
        "e_mail_professionnel",
        "telephone_professionnel",
        "adresse_quartierilotparcelle",
        "date_expiration_de_la_licence",
        "reference_autorisation",
        "npis_des_moniteurs_separes_par",
        "ifu",
        "code_licence",
        "vehicules_de_lauto_ecole"
    ];
    public function validate($file)
    {
        $headings = (new HeadingRowImport)->toArray($file);
        if (count($headings) != 1) {
            throw new ImportationException("Le fichier n'est pas conforme au modèle d'importation, une seule feuille de style est exigée.");
        }
        $firstsheet = $headings[0];
        if (!$firstsheet) {
            throw new ImportationException("Le fichier n'est pas conforme au modèle d'importation, les colonnes semblent être absentes.");
        }

        if (!is_array($firstsheet)) {
            throw new ImportationException("Le fichier n'est pas conforme au modèle d'importation, vérifiez les feuilles.");
        }
        $cells = $firstsheet[0];
        if (is_array($cells) && count($cells) < 15) {
            throw new ImportationException("Le fichier n'est pas conforme au modèle d'importation, des colonnes sont manquantes.");
        }

        $difference = array_diff($cells ?? [], $this->cells);
        if (count($difference) > 0) {
            throw new ImportationException("Le fichier n'est pas conforme au modèle d'importation, vérifiez les colonnes sont.");
        }
    }
}