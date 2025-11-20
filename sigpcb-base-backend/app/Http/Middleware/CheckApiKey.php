<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiKey
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
        // Vérifie si la clé d'API est présente dans les headers
        $apiKey = $request->header('X-API-Key');

        // Récupère la clé d'API depuis le fichier .env
        $expectedApiKey = env('API_KEY');

        // Vérifie si la clé d'API est correcte
        if ($apiKey !== $expectedApiKey) {
            return response()->json(['error' => 'Clés d\'authentification manquantes ou invalides'], 401);
        }

        return $next($request);
    }
}
