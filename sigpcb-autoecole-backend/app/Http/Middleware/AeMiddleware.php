<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Help;
use App\Services\Resp;
use App\Models\Moniteur;
use Illuminate\Http\Request;
use App\Models\AutoEcole; // Ajoutez cette ligne pour utiliser le modèle AutoEcole

class AeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Récupérer la valeur de l'en-tête 'x-ae'
        $autoEcoleId = $request->header('X-Ae');

        // Vérifier si la valeur de l'en-tête 'x-ae' est présente
        if ($autoEcoleId) {
            $autoEcole = AutoEcole::find($autoEcoleId);

            if (!$autoEcole) {
                return Resp::error("Nous n'avons trouvé aucune auto-école active associée à votre compte");
            }

            $hasAccess = false;
            if (auth()->check()) {
                $user = auth()->user();
                $isOwner = $autoEcole->promoteur_id == auth()->id();
                $isMoniteur = false;
                $moniteurAccounts = Moniteur::where([
                    "npi" => $user->npi,
                    "active" => true
                ])->latest()->get();
                $isMoniteur = $moniteurAccounts->some(fn (Moniteur $m) => $m->autoEcole->id == $autoEcole->id);

                $hasAccess = $isMoniteur || $isOwner;
            } else {
                $moniteur = Help::moniteurAuth();
                $hasAccess = ($moniteur->auto_ecole_id == $autoEcoleId) && intval($moniteur->active);
            }

            if (!$hasAccess) {
                return Resp::error("Accès non autorisé", statuscode: 403);
            }

            $request->attributes->add(['auto_ecole' => $autoEcole]);
        }

        return $next($request);
    }
}
