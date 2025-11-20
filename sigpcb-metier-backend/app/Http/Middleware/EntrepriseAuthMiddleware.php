<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Resp;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use App\Models\EntrepriseToken;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class EntrepriseAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
    
        if (!$token) {
            return Resp::error("Vous n'êtes pas connecté",statuscode: 401,responsecode:'entreprise');

        }
    
        $identifier = $token[0];
    
        if (!in_array($identifier, ["u", "e","m"])) {
            return Resp::error("Votre session est invalide ou expirée", statuscode: 401);
        }
    
        $token = substr($token, 1);
    
        if ($identifier === 'e') {
            
            list($entrepriseTokenId, $random) = explode('|', $token);
            $entrepriseToken = EntrepriseToken::find($entrepriseTokenId);

            if (is_null($entrepriseToken) || !$this->isValidToken($entrepriseToken, $random)) {
                return Resp::error("Vos informations de connexion sont incorrectes ou votre session a expiré", statuscode: 401);
            }
    

            $entreprise = Entreprise::find($entrepriseToken->entreprise_id);
            $request->attributes->add(['entreprise' => $entreprise]);
            return $next($request);
        }
    
        return Resp::error("Vous n'avez pas accès a ces fonctions", statuscode: 401);
    }
    protected function isValidToken($entrepriseToken, $random)
    {
        return $entrepriseToken && hash_equals($entrepriseToken->token, $random) && $entrepriseToken->expire_at > now();
    }
}
