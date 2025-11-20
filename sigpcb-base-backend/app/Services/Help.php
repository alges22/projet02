<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class Help
{
    public static function sessionDate(Carbon $sessionInstance, string $type = "short", $withHours = false)
    {
        $mois = ucfirst($sessionInstance->monthName);
        $annee = $sessionInstance->year;
        $days = $sessionInstance->day;
        $dayName = $sessionInstance->dayName;
        $hours = $sessionInstance->format('H:i'); // Extract hours and minutes

        if ($type == "full") {
            return ucwords(sprintf("%s %s %s %s %s", $dayName, static::pad($days), $mois, $annee, $withHours ? " $hours" : ""));
        } elseif ($type === 'long') {
            return ucwords(sprintf("%s %s %s %s", static::pad($days), $mois, $annee, $withHours ? " $hours" : ""));
        } else {
            return sprintf("%s %s %s", $mois, $annee, $withHours ? " $hours" : "");
        }
    }


    public static function pad($str, $length = 2, $carac = "0", $pad = STR_PAD_LEFT)
    {
        return str_pad($str, $length, $carac, $pad);
    }

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
}