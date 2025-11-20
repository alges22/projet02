<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiClient;
use Illuminate\Support\Facades\Hash;

class AuthApiClientMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si les en-têtes contiennent les clés publiques et privées
        if (!$request->hasHeader('X-ATK-PUBLIC') || !$request->hasHeader('X-ATK-PRIVATE')) {
            return response()->json(['message' => 'Clés d\'authentification manquantes'], 401);
        }

        // Récupérer les clés publiques et privées depuis les en-têtes de la requête
        $publicKey = $request->header('X-ATK-PUBLIC');
        $privateKey = $request->header('X-ATK-PRIVATE');

        // Rechercher l'enregistrement correspondant dans la table ApiClient par rapport à la clé publique
        $client = ApiClient::where('atk_public', $publicKey)->first();

        // Vérifier si le client existe et si la clé privée correspond à la clé hachée stockée dans la base de données
        if (!$client || !Hash::check($privateKey, $client->atk_private)) {
            return response()->json(['message' => 'Clés invalides'], 401);
        }

        // Ajouter le client à la requête pour une utilisation ultérieure si nécessaire
        //Pzr exemple dans le controller
        $request->attributes->add(['api_client' => $client]);

        // Continuer le traitement de la requête
        return $next($request);
    }
}
