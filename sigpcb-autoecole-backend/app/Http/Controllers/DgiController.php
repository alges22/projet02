<?php

namespace App\Http\Controllers;

use App\Services\Dgi;
use App\Services\Sms;
use App\Services\Help;
use App\Models\PromoteurIfu;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class DgiController extends ApiController
{
    public function fetchData($ifu)
    {
        //Récupère depuis la DGI
        $dgi = new Dgi($ifu);

        if ($dgi->exists()) {
            // Renvoyer la réponse de l'API
            return $this->successResponse($dgi->data());
        } else {
            // Renvoyer une réponse d'erreur
            return $this->errorResponse('La vérification du numéro ifu a échoué', null, null, 500);
        }
    }

    public function verifyIfu($ifu, Request $request)
    {
        $v = Validator::make($request->all(), [
            'npi' => "required"
        ]);
        if ($v->fails()) {
            return $this->errorResponse("Validation échouée", $v->errors(), statuscode: 422);
        }
        //Récupère depuis la DGI
        $dgi = new Dgi($ifu);

        if ($dgi->exists()) {
            $raisonSocial =  $dgi->raisonSociale() ?? "Sans raison sociale";
            if (!$raisonSocial) {
                return $this->errorResponse("Ce numéro IFU ne dispose pas de raison sociale ou il n'est peut-être pas associé à une entreprise.");
            }

            $otpCode = rand(100000, 999999);
            //$text = 'Votre code de verification est: ' . $otpCode;
            DB::beginTransaction();
            try {
                # Retrait les anciennes vérifications
                PromoteurIfu::where([
                    'npi' => $request->get('npi'),
                ])->delete();

                PromoteurIfu::create([
                    'npi' => $request->get('npi'),
                    'verify_code' => $otpCode,
                    'verified' => false,
                    'ifu' => $ifu,
                    'verify_code_expire' => Carbon::now()->addMinutes(5)
                ]);
            } catch (\Throwable $th) {
                logger()->error($th);
                DB::rollBack();
                return $this->errorResponse('Echec! Nous ne pouvons pas vérifier votre IFU actuellement', null, null, 500);
            }
            DB::commit();
            return $this->successResponse([
                'categorie' => $dgi->categorie(),
                "phone" =>  $dgi->telephone(),
                'raisonSocial' => $raisonSocial
            ],);
        } else {
            // Renvoyer une réponse d'erreur
            return $this->errorResponse('Cet numéro IFU est introuvable.', null, null, 500);
        }
    }

    public function ifuVerified(Request $request)
    {
        try {
            // Valider les données de la requête
            $v = Validator::make($request->all(), [
                'npi' => "required",
                'ifu' => "required",
                'code' => "required",
            ]);

            if ($v->fails()) {
                return $this->errorResponse("Validation échouée", $v->errors(), 422);
            }
            $data = $v->validated();
            $code = $data['code'];
            $data['verify_code'] = $code;
            unset($data['code']);
            // Récupérer les données du modèle PromoteurIfu
            $promoteurIfu = PromoteurIfu::where($data)
                ->first();

            // Vérifier que les données existent
            if (!$promoteurIfu) {
                return $this->errorResponse("Le code de vérification est incorrect.", null, 404);
            }

            if (Carbon::now()->greaterThan($promoteurIfu->verify_code_expire)) {
                return $this->errorResponse("Le code de vérification a expiré.", null, 400);
            }

            // Mettre à jour le statut de vérification
            $promoteurIfu->verified = true;
            $promoteurIfu->save();

            // Renvoyer une réponse de succès
            return $this->successResponse(null, "La vérification IFU a été effectuée avec succès.");
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("La vérification du numéro ifu a échoué");
        }
    }

    public function resendIfuVerificationCode(Request $request)
    {
        // Valider les données de la requête
        $v = Validator::make($request->all(), [
            'npi' => "required",
            'ifu' => "required",
        ]);

        if ($v->fails()) {
            return $this->errorResponse("Validation échouée", $v->errors(), 422);
        }

        // Récupérer les données de PromoteurIfu
        $promoteurIfu = PromoteurIfu::where($v->validated())
            ->first();

        // Vérifier que les données existent
        if (!$promoteurIfu) {
            return $this->errorResponse("Les informations IFU ou NPI ne correspondent à aucun enregistrement.", null, 404);
        }
        DB::beginTransaction();
        // Générer un nouveau code de vérification à 6 chiffres
        $newVerifyCode = rand(100000, 999999);

        try {
            $dgi = new Dgi($request->get('ifu'));
            $phone = $dgi->telephone();
            $country_code = '229';
            $num = str($phone)->after("+229");
            $text = 'Votre nouveau code de vérification est: ' . $newVerifyCode;
            # Retrait les anciennes vérifications
            PromoteurIfu::where([
                'npi' => $request->get('npi'),
            ])->delete();

            PromoteurIfu::create([
                'npi' => $request->get('npi'),
                'verify_code' => $newVerifyCode,
                'verified' => false,
                'ifu' => $request->get('ifu'),
                'verify_code_expire' => Carbon::now()->addMinutes(5)
            ]);
        } catch (\Throwable $th) {
            logger()->error($th);
            DB::rollBack();
            return $this->errorResponse("Impossible d'envoyer le nouveau code de vérification.", null, 500);
        }
        DB::commit();
        return $this->successResponse(null, "Un nouveau code de vérification a été envoyé avec succès.");
    }
}
