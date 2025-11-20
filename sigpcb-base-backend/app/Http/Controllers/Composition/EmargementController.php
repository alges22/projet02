<?php

namespace App\Http\Controllers\Composition;


use App\Models\Vague;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CandidatExamenSalle;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmargementController extends ApiController
{
    public function __invoke(Request $request)
    {
        DB::beginTransaction();
        try {
            $examen = $this->examen();
            if ($examen->isClosed()) {
                return $this->errorResponse("L'examen est clôturé.", statuscode: 404);
            }
            $validator = Validator::make($request->all(), [
                'candidat_salle_id' => "required|exists:candidat_examen_salles,id",
            ]);

            if ($validator->fails()) {
                return $this->sendValidatorErrors($validator, "Impossible de continuer, les données ne sont pas valides.");
            }

            $response = $this->emarges($request);
            DB::commit();
            return $response;
        } catch (\Throwable $th) {
            DB::rollBack();
            logger()->error($th);
            return $this->errorResponse("Impossible de faire signer le candidat, une erreur inattendue s'est produite.");
        }
    }

    private function emarges(Request $request)
    {
        try {
            /**
             * @var CandidatExamenSalle $candidat
             */
            $candidat = CandidatExamenSalle::find($request->candidat_salle_id);

            if (!$candidat) {
                return $this->errorResponse("Impossible de  marquer ce candidat présent, le candidat est introuvable.", statuscode: 404);
            }

            $currentVague = Vague::current($request->salle_compo_id, $candidat->examen_id);

            if ($currentVague->id !== $candidat->vague_id) {
                return $this->errorResponse("Vous ne pouvez pas marquer ce candidat présent, il est dans la vague  n° {$candidat->vague->numero}.", statuscode: 404);
            }
            $message = "Le candidat a été marqué présent avec succès";
            if (is_null($candidat->presence)) {

                $candidat->presence = 'present';

                $candidat->withDossierSession();
                $ds = $candidat->dossier_session;
                //Il faut supprimer le dossier_session pour que l'enregsitrement passe
                unset($candidat->dossier_session);
                $ds->update([
                    'presence' => "present",
                    'resultat_code' => "failed",
                ]);
                $candidat->save();
            } else {
                return $this->errorResponse("Ce candidat a été déjà marqué présent.", statuscode: 419);
            }


            return $this->successResponse($candidat, $message);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite, vous devez reprendre peut-être.");
        }
    }



    private function writeSignature(Request $request, &$filename)
    {
        $encoded_image = explode(",", $request->signature)[1];
        $decoded_image = base64_decode($encoded_image);

        $filename = "signatures/" . $request->candidat_salle_id . "_" . time() . '.png';

        return Storage::disk(CandidatExamenSalle::SIGNATURE_DISK)->put($filename, $decoded_image);
    }
}
