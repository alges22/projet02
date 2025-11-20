<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Help;
use App\Services\Resp;
use App\Models\Admin\Examen;
use Illuminate\Http\Request;
use App\Models\InspecteurSalle;
use App\Models\Admin\Inspecteur;
use App\Models\SalleCompo;

class HasInspectorAccess
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
        $examen_id = intval($request->get('examen_id'));
        $examen = Examen::find($examen_id);
        if (!$examen) {
            return Resp::error("L'examen sélectionner n'existe pas", statuscode: 403);
        }
        $examen->withDateCode();

        # Vérification des inspecteurs
        if (!$request->has('inspecteur_id')) {
            return Resp::error("L' idendifiant de l'inspecteur est requis", statuscode: 404);
        }
        $inpescteur = Inspecteur::find($request->get('inspecteur_id'));
        if (!$inpescteur) {
            return Resp::error("L'inspecteur est introuvable", statuscode: 404);
        }
        //On ajoute l'inspecteur à la requête
        $request->attributes->set('_inspecteur', $inpescteur);

        # Vérification de la salle
        if (!$request->has('salle_compo_id')) {
            return Resp::error("La salle n'existe pas.", statuscode: 404);
        }
        $salleCompo = SalleCompo::find(intval($request->get('salle_compo_id')));
        if (!$salleCompo) {
            return Resp::error("La salle de composition n'existe pas.", statuscode: 404);
        }
        return $next($request);
    }
}