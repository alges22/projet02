<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Payment;
use App\Models\Moniteur;
use App\Models\Entreprise;
use App\Models\Historique;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;

class Help
{

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

    public static function entrepriseAuth(): Entreprise | null
    {
        return request()->attributes->get('entreprise') ?? null;
    }
    public static function moniteurAuth(): Moniteur | null
    {
        return request()->attributes->get('moniteur') ?? null;
    }
}