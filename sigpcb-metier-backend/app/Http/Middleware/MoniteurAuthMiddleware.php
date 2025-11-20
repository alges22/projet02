<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Resp;
use App\Models\Moniteur;
use Illuminate\Http\Request;
use App\Models\MoniteurToken;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class MoniteurAuthMiddleware
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
            return Resp::error("Vous n'êtes pas connecté",statuscode: 401,responsecode:'moniteur');

        }
    
        $identifier = $token[0];
    
        if (!in_array($identifier, ["u", "e","m"])) {
            return Resp::error("Votre session est invalide ou expirée", statuscode: 401);
        }
    
        $token = substr($token, 1);
    
        if ($identifier === 'm') {
            
            list($moniteurTokenId, $random) = explode('|', $token);
            $moniteurToken = MoniteurToken::find($moniteurTokenId);

            if (is_null($moniteurToken) || !$this->isValidToken($moniteurToken, $random)) {
                return Resp::error("Vos informations de connexion sont incorrectes ou votre session a expiré", statuscode: 401);
            }
    

            $moniteur = Moniteur::find($moniteurToken->moniteur_id);
            $request->attributes->add(['moniteur' => $moniteur]);
            return $next($request);
        }
    
        return Resp::error("Vous n'avez pas accès a ces fonctions", statuscode: 401);
    }
    protected function isValidToken($moniteurToken, $random)
    {
        return $moniteurToken && hash_equals($moniteurToken->token, $random) && $moniteurToken->expire_at > now();
    }
}
