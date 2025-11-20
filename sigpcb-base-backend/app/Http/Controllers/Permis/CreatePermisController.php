<?php

namespace App\Http\Controllers\Permis;

use App\Models\Permis;
use App\Models\TrancheAge;
use App\Models\JuryCandidat;
use Illuminate\Support\Carbon;
use App\Models\CategoriePermis;
use App\Models\Admin\AnnexeAnatt;
use App\Models\Candidat\Candidat;
use App\Services\GlobalException;
use App\Services\Permis\CreatePermis;
use App\Http\Controllers\ApiController;
use App\Models\Candidat\DossierSession;
use App\Models\Candidat\DossierCandidat;

class CreatePermisController extends ApiController
{
    public function checkPermisCombination($candidatId, $permisPrealableId)
    {
        try {
            $permis = Permis::where('candidat_id', $candidatId)
                ->where('categorie_permis_id', $permisPrealableId)
                ->get();

            if ($permis->isEmpty()) {
                return $this->errorResponse('Pas de résultat trouvé', null, null, 422);
            }

            return $this->successResponse($permis);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération de la liste.', null, null, 500);
        }
    }

    public function createPermis($id)
    {
        $creator = new CreatePermis($id);
        $creator->create();
        return $this->successResponse([], 'La délibération des permis a été  faite avec succès');
    }
}
