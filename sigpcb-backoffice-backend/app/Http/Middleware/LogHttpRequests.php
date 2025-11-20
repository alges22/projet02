<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\AppLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogHttpRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        // Vérifier si un utilisateur est authentifié
        if (Auth::check()) {
            $user = Auth::user();
            $userId = $user->id;
        } else {
            $userId = 0;
        }

        // Enregistrer les informations de la requête dans les logs
        AppLog::create([
            'user_id' => $userId,
            'action' => $request->getMethod(),
            'request_url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent')
        ]);

        return $next($request);
    }
}
