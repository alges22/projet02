<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Resp;
use App\Models\Moniteur;
use Illuminate\Http\Request;
use App\Models\MoniteurToken;
use Illuminate\Http\JsonResponse;
use App\Models\AutoEcoleAccesToken;
use Illuminate\Support\Facades\Auth;

class UnitAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return Resp::error("Vous n'êtes pas connecté (e)", statuscode: 401);
        }

        $identifier = $token[0];


        if (!in_array($identifier, ["p", 'm'])) {
            return Resp::error("Votre session est invalide ou expirée", statuscode: 401);
        }

        $token = substr($token, 1);
        if ($identifier === 'p') {

            $accessToken = AutoEcoleAccesToken::findToken($token);

            if (!$accessToken || $accessToken->created_at->gt(now()->addDays(7))) {
                return Resp::error("Votre session est invalide ou expirée", statuscode: 401);
            }
            $user = $accessToken->tokenable;
            if (!$user) {
                return Resp::error("Votre session est invalide ou expirée", statuscode: 401);
            }

            if (
                method_exists($accessToken->getConnection(), 'hasModifiedRecords') &&
                method_exists($accessToken->getConnection(), 'setRecordModificationState')
            ) {
                tap($accessToken->getConnection()->hasModifiedRecords(), function ($hasModifiedRecords) use ($accessToken) {
                    $accessToken->forceFill(['last_used_at' => now()])->save();

                    $accessToken->getConnection()->setRecordModificationState($hasModifiedRecords);
                });
            } else {
                $accessToken->forceFill(['last_used_at' => now()])->save();
            }

            Auth::login($user);
        } else {
            $request = $this->moniteurHandler($request, $token);
            if ($request instanceof JsonResponse) {
                return $request;
            }
        }
        return $next($request);
    }

    public function moniteurHandler(Request $request, string $token)
    {
        list($moniteurTokenId, $random) = explode('|', $token);

        $moniteurToken = MoniteurToken::find($moniteurTokenId);
        if (is_null($moniteurToken) || !$this->isValidToken($moniteurToken, $random)) {
            return Resp::error("Votre session session est invalide ou expirée", statuscode: 401);
        }

        $moniteur = Moniteur::find($moniteurToken->moniteur_id);
        $request->attributes->add(['moniteur' => $moniteur]);
        return $request;
    }

    protected function isValidToken($moniteurToken, $random)
    {
        return $moniteurToken && hash_equals($moniteurToken->token, $random) && $moniteurToken->expire_at > now();
    }
}