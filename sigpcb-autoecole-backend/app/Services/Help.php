<?php

namespace App\Services;

use App\Models\AnnexeAnatt;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Payment;
use App\Models\Moniteur;
use App\Models\AutoEcole;
use App\Models\Historique;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Models\AnnexeAnattDepartement;
use Illuminate\Database\Eloquent\Model;

class Help
{
    public static function sessionDate(Carbon $sessionInstance, string $type = "short", $withDate = false)
    {

        $mois = ucfirst($sessionInstance->monthName);
        $annee = $sessionInstance->year;
        $days = $sessionInstance->day;
        $dayName = $sessionInstance->dayName;
        if ($type == "full") {
            $str = sprintf("%s %s %s %s ", $dayName, $days, $mois, $annee);

            if ($withDate) {
                $str .= ' ' . str_pad($sessionInstance->hour, 2, "0", STR_PAD_LEFT) . ':' . $sessionInstance->minute;
            }
            return ucwords($str);
        } elseif ($type === 'long') {
            return ucwords(sprintf("%s %s %s", $days, $mois, $annee));
        } else {
            return  ucwords(sprintf("%s %s",   $mois, $annee));
        }
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
    public static function hashPhone(string $phone)
    {
        // Vérifier si la chaîne a au moins 8 caractères
        if (strlen($phone) >= 8) {
            // Extraire les deux premiers et les deux derniers caractères
            $premiers = substr($phone, 0, 2);
            $derniers = substr($phone, -2);

            $milieuMasque = '****';

            $numeroMasque = $premiers . $milieuMasque . $derniers;

            return $numeroMasque;
        } else {
            // Retourner le numéro tel quel s'il a moins de 8 caractères
            return $phone;
        }
    }

    public static function smsTo($to, $text)
    {

        $user = env('SMS_LOGIN');
        $password = env('SMS_PASSWORD');
        $apikey = env('SMS_APIKEY');
        $from = env('APP_NAME');
        $to = 229 . $to;

        $url = env('SMS_ENDPOINT') . "?user={$user}&password={$password}&apikey={$apikey}&from={$from}&to={$to}&text={$text}";

        $response = Http::get($url);

        return $response->successful();
    }

    /**
     * Crée facilement les paiements
     *
     * @param array $attrs
     * @param Model $model
     * @return Payment
     */
    public static function paid(array $attrs, Model $model)
    {
        $data = json_encode([
            'table' => $model->getTable(),
            'id' => $model->id
        ]);
        $attrs['data'] = $data;
        return Payment::create($attrs);
    }

    /**
     * Permet de créer les historiques facilement
     *
     * @param string $service le service
     * @param string $message le message
     * @param string $title le titre
     * @param string $action l'action
     * @param User $promoteur le promoteur
     * @param array $button un boutton optionnelle
     * @param Model|null $modelConcerne le model concerné
     * @param array $options autre options comme des données supplémentaires à ajoutés
     * @return Historique
     */
    public static function historique(string $service, string $title, string $action, $message,  User $promoteur, ?Model $modelConcerne = null, array $button = [],  array $options = [])
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

    public static function authAutoEcole(): AutoEcole | null
    {
        return request()->attributes->get('auto_ecole') ?? null;
    }

    public static function  autoEcoleId()
    {
        $ae = static::authAutoEcole();
        if ($ae) {
            return $ae->id;
        }

        return null;
    }
    public static function moniteurAuth(): Moniteur | null
    {
        return request()->attributes->get('moniteur') ?? null;
    }

    public static function autoEcoleAnnexe($departement_id): AnnexeAnatt|null
    {
        $ad = AnnexeAnattDepartement::where(['departement_id' => $departement_id])->first();
        if (!$ad) {
            return null;
        }

        return   AnnexeAnatt::find($ad->annexe_anatt_id);
    }

    public static function demandeAgrementAmount()
    {
        return 1500;
    }
}
