<?php

namespace App\Http\Middleware;

use App\Models\Admin\Examen;
use Closure;
use App\Services\Resp;
use Illuminate\Http\Request;
use App\Models\Admin\Examinateur;
use App\Models\Admin\Jurie;

class HasExaminatorAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        # Examen
        
        if (!$request->has('examen_id')) {
            return Resp::error("L'examen est requis", statuscode: 404);
        }
        $examen = Examen::find($request->get('examen_id'));
        if (!$examen) {
            return Resp::error("Cet examen n'existe pas. <br/> Veuillez repasser quand vous serez programmé(e) pour un examen.", statuscode: 403);
        }

        # Vérification des examinateurs

        if (!$request->has('examinateur_id')) {
            return Resp::error("L' idendifiant de l'inspecteur est requis", statuscode: 404);
        }
        $examinateur = Examinateur::where('id', $request->get('examinateur_id'))->first();
        if (!$examinateur) {
            return Resp::error("L'examinateur est introuvable", statuscode: 404);
        }

        //On ajoute l'examinateur à la requête
        $request->attributes->set('_examinateur', $examinateur);

        # Vérifions Si L'examinateur n'est pas pour l'examen actuell
        $juryExaminateur = Jurie::where([
            'examinateur_id' => $examinateur->id,
            'examen_id' => $examen->id
        ])->first();
        if (!$juryExaminateur) {
            return Resp::error("Vous n'êtes pas examinateur pour cet examen", statuscode: 403);
        }

        //On ajoute l'inspecteur à la requête
        $request->attributes->set('_examinateur_jury', $juryExaminateur);

        return $next($request);
    }
}
