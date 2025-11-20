<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Resp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class UserAuthMiddleware
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
            return Resp::error("Vous n'êtes pas connecté",statuscode: 401,responsecode:'examinateur');
        }
    
        $identifier = $token[0];
    
        if (!in_array($identifier, ["u", "e","m"])) {
            return Resp::error("Votre session est invalide ou expirée", statuscode: 401);
        }
    
        $token = substr($token, 1);
    
        if ($identifier === 'u') {
            $accessToken = PersonalAccessToken::findToken($token);
    
            if (!$accessToken || $accessToken->created_at->addDays(7)->isPast()) {
                return Resp::error("Votre session est invalide ou expirée", statuscode: 401);
            }
    
            $user = $accessToken->tokenable;
            $this->refreshAccessToken($accessToken);
    
            Auth::login($user);
    
            return $next($request);
        }
    
        return Resp::error("Identificateur non valide", statuscode: 401);
    }
    
    protected function refreshAccessToken($accessToken)
    {
        $accessToken->forceFill(['last_used_at' => now()])->save();
    }
    
}
