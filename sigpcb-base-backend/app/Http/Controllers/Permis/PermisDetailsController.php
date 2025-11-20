<?php

namespace App\Http\Controllers\Permis;

use App\Http\Controllers\ApiController;
use App\Models\Candidat\DossierCandidat;
use App\Models\Candidat\DossierSession;
use App\Models\CategoriePermis;
use App\Models\Permis;
use App\Services\GetCandidat;

class PermisDetailsController extends ApiController
{
    public function __invoke($code_permis)
    {
        $permisQuery = Permis::where('code_permis', $code_permis)->orderBy('delivered_at');

        if (!$permisQuery->exists()) {
            return $this->errorResponse("Aucun permis trouvé pour le code permis fourni", 404);
        }
        $last = $permisQuery->latest()->first();
        $data['candidat'] = $this->getCandidat($last->npi);

        $data['permis'] = $this->getPermis($last->npi);


        $data['code_permis'] = $code_permis;

        $data['delivered_at'] = $last->delivered_at->format('d/m/Y');

        # Signed_at
        $data['signed_at'] = $last->signed_at;
        # Singature
        $data['signataire'] = "Richard DADA - DG/ANaTT";

        # Verification
        $permis = CategoriePermis::find($last->categorie_permis_id);

        $expires = $last->expired_at->format('d/m/Y');

        if (trim($permis->name) == 'B') {
            $expires = "Permanent";
        }
        $data['expired_at'] =  $expires;

        $dossier = DossierCandidat::where("npi", $last->npi)
            ->latest()
            ->first(['group_sanguin', 'is_militaire']);

        # Ajout du dossier
        $data['dossier'] = $dossier;
        return $this->successResponse($data);
    }


    private function getCandidat(string $npi)
    {
        return GetCandidat::findOne($npi);
    }


    private function getPermis(string $npi)
    {
        return CategoriePermis::orderBy('name')->get()->map(function ($p) use ($npi) {
            $permis = Permis::where('categorie_permis_id', $p->id)
                ->where('npi', $npi)
                ->latest('delivered_at')
                ->first();
            if ($permis) {
                /**
                 * @var DossierSession $ds
                 */
                $ds = DossierSession::find($permis->dossier_session_id);
                $ds->withRestriction();

                $p = CategoriePermis::find($permis->categorie_permis_id, ['name']);
                $expires = $permis->expired_at->format('d/m/Y');

                if (trim($p['name']) == 'B') {
                    $expires  = "Permanent";
                }
                $restrictionss = collect($ds->restrictionss)->filter(fn ($r) => $r['id'] != 0);
                return  [
                    "name" => $p['name'],
                    'delivered_at' => $permis->delivered_at->format('d/m/Y'),
                    'expired_at' => $expires,
                    "restrictions" => $restrictionss
                ];
            } else {
                return  [
                    "name" => $p['name'],
                    'delivered_at' => "",
                    'expired_at' => "",
                    "restrictions" => [] // Important pour garder le format sur candidat
                ];
            }
        });
    }
}