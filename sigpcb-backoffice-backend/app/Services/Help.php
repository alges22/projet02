<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\AutoEcole\Promoteur;
use App\Models\AutoEcole\Historique;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Help
{
    /**
     *  Convertir un fichier en url Base 64
     * @param string $filename
     * @return string
     */
    public static function  b64URl(string $filename)
    {
        $extension = File::extension($filename);

        $b64 = base64_encode(File::get($filename));
        return "data:image/$extension;base64,$b64";
    }

    public static function sessionDate(Carbon $sessionInstance, string $type = "short")
    {

        $mois = ucfirst($sessionInstance->monthName);
        $annee = $sessionInstance->year;
        $days = $sessionInstance->day;
        $dayName = $sessionInstance->dayName;
        if ($type == "full") {
            return sprintf("%s %s %s %s", $dayName, $days, $mois, $annee);
        } elseif ($type === 'long') {
            return sprintf("%s %s %s", $days, $mois, $annee);
        } else {
            return  sprintf("%s %s",   $mois, $annee);
        }
    }

    /**
     * Permet de créer les historiques facilement
     *
     * @param string $service le service
     * @param string $message le message
     * @param Promoteur $promoteur le promoteur
     * @param array $button un boutton optionnelle
     * @param Model|null $modelConcerne le model concerné
     * @param array $options autre options comme des données supplémentaires à ajoutés
     * @return Historique
     */
    public static function historique(string $service, string $title, string $action, $message,  Promoteur $promoteur, ?Model $modelConcerne = null, array $button = [],  array $options = [])
    {
        $data = [];

        if ($modelConcerne) {
            $data['id'] = $modelConcerne->id;
            $data['table'] = $modelConcerne->getTable();
        }

        $data = array_merge($data, $options);

        return Historique::create([
            'service' => $service,
            'action' => $action,
            'title' => $title,
            'message' => $message,
            'npi' => $promoteur->npi,
            'promoteur_id' => $promoteur->id,
            'data' => json_encode($data),
            'bouton' => json_encode($button)
        ]);
    }
    public static function log(string $content, $name)
    {
        $line = "-------------------------------------------------------\n";
        file_put_contents(storage_path('logs/' . $name), $line . $content . "`\n", FILE_APPEND);
    }

    public static function percent(float $a, float $total)
    {
        if ($total == 0) {
            return $total;
        }
        return number_format(($a / $total) * 100, 2);
    }

    public static function date($date_str, $format)
    {
        $str = ucwords(Carbon::parse($date_str)->locale('fr_FR')->isoFormat($format));

        return str_replace(".", "", $str);
    }


    public static function pad($chaine, int $longueur = 2, string $caractere = "0", int $cote = STR_PAD_LEFT): string
    {
        return str_pad($chaine, $longueur, $caractere, $cote);
    }
}
