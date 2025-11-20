<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Sms
{

    public static function sendSMS($country_code,$num,$text)
    {
        // Données à envoyer dans la requête
        $requestData = [
            'contacts' => [
                [
                    'country_code' => $country_code,
                    'msisdn' => $num
                ]
            ],
            'body' => $text
        ];

        // Effectuer la requête HTTP avec les données JSON
        $response = Http::withHeaders([ 'Content-Type' => 'application/json', 'api_key' => env('SMS_ANaTT_APIKEY'),
        ])->post(env('SMS'), $requestData);

        // Vérifier si la requête a été réussie
        if ($response->successful()) {
                return true;
        } else {
            // Renvoyer une réponse d'erreur
            return response()->json(['error' => 'Erreur lors de l\'envoi du SMS'], $response->status());
        }
    }

}
