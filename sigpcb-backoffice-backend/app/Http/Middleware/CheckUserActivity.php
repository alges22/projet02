<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\Resp;
use App\Models\UserAction;

class CheckUserActivity
{
    public function handle($request, Closure $next)
    {
        // Vérifie si l'utilisateur est authentifié
        $user = Auth::user();
        if ($user) {
            // Obtient l'action la plus récente de l'utilisateur
            $latestAction = UserAction::where('user_id', $user->id)->latest()->first();
            $url = $request->fullUrl();
            if ($latestAction) {
                // Vérifie si le temps écoulé depuis la dernière action est supérieur à 10 minutes
                $timeElapsed = Carbon::parse($latestAction->time)->diffInMinutes(now());

                if ($timeElapsed >= 60) {
                    // Détruit la session de l'utilisateur
                    $user->tokens()->where('name', 'auth')->delete();
                    // Met à jour le log avec le nouveau temps
                    $latestAction->update(['time' => now(),'url'=>$url]);
                    return Resp::error("Votre session a expiré en raison d'une inactivité prolongée. Veuillez vous reconnecter pour continuer.", statuscode: 401);


                } else {
                    // Met à jour le log avec le nouveau temps
                    $latestAction->update(['time' => now(),'url'=>$url]);
                }
            } else {
                // Aucune action précédente n'a été enregistrée, enregistre la première action de l'utilisateur
                UserAction::create([
                    'user_id' => $user->id,
                    'url'=>$url,
                    'time' => now(),
                ]);
            }
        }

        return $next($request);
    }
}
