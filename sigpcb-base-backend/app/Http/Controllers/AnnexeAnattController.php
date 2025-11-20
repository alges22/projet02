<?php

namespace App\Http\Controllers;

use App\Models\Admin\AnnexeAnatt;
use App\Models\InspecteurSalle;
use App\Models\SalleCompo;
use Illuminate\Http\Request;

class AnnexeAnattController extends ApiController
{
    public function salleInspecteurs($id)
    {
        $annexe = AnnexeAnatt::find($id);
        if (!$annexe) {
            return $this->errorResponse('Annexe anatt introuvable');
        }
        $salleCompos =  SalleCompo::where('annexe_anatt_id', $id)->get();

        #
        $salleCompos = $salleCompos->map(function (SalleCompo $salleCompo) {
            # On récupére les inspecteurs et les examens
            $inspecteurs = InspecteurSalle::where('salle_compo_id', $salleCompo->id)
                ->get()
                ->map(function (InspecteurSalle $inspecteurSalle) {
                    $inspecteurSalle->withInspecteur()
                        ->withExamen();

                    return [
                        'inspecteur' => $inspecteurSalle->inspecteur,
                        'examen' => $inspecteurSalle->examen
                    ];
                });

            $salleCompo->setAttribute('inspecteurs', $inspecteurs);

            return $salleCompo;
        });

        return $this->successResponse([
            'annexe' => $annexe,
            "salles_inspecteurs" => $salleCompos
        ]);
    }
}
