<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Services\Sms;
use App\Services\Help;
use App\Models\Moniteur;
use App\Models\AutoEcole;
use App\Models\MoniteurOtp;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\MoniteurToken;
use App\Services\GetCandidat;
use App\Services\Mail\Messager;
use App\Services\Mail\EmailNotifier;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class MoniteurRegistrationController extends ApiController
{
    public function verify(Request $request)
    {
        $v = Validator::make($request->all(), [
            'npi' => "required|digits:10"
        ]);


        if ($v->fails()) {
            return $this->errorResponse("Validation échouée", $v->errors(), statuscode: 422);
        }

        $moniteur = Moniteur::where([
            "npi" => $request->npi,
            "active" => true
        ])->latest()->first();

        if (!$moniteur) {
            return $this->errorResponse("Vos informations de connexion sont incorrectes", $v->errors(), statuscode: 422);
        }


        $ae = AutoEcole::find($moniteur->auto_ecole_id);
        if (!$ae) {
            return $this->errorResponse("L'auto-école n'existe pas ou a été retirée", $v->errors(), statuscode: 422);
        }
        $moniteurData = GetCandidat::findOne($moniteur->npi);


        if (!$moniteurData) {
            return $this->errorResponse("Cet NPI est introuvable chez ANIP", $v->errors(), statuscode: 422);
        }

        $otp_code = mt_rand(100000, 999999);

        MoniteurOtp::where('moniteur_id', $moniteur->id)->delete();
        $motp = MoniteurOtp::create([
            'moniteur_id' => $moniteur->id,
            'code' => $otp_code,
            'expire' =>  now()->addMinutes(5),
            'action' => 'login',
        ]);

        $phone = Help::hashPhone($moniteurData['telephone']);

        $country_code = $moniteurData['telephone_prefix'];
        $num = $moniteurData['telephone'];
        $text = 'Votre code de connexion est: ' . $otp_code;
        $sent = Sms::sendSMS($country_code, $num, $text);
        if (!$sent) {
            return $this->errorResponse("L'envoie du code de confirmation a échoué", $v->errors(), statuscode: 422);
        }

        unset($moniteur->auto_ecole);
        unset($moniteur->autoEcole);
        $moniteur->type = "moniteur";
        $moniteur->moniteur_id = $moniteur->id;
        return $this->successResponse($moniteur, "Un code de confirmation a été envoyé à votre numéro de téléphone {$phone}");
    }


    public function login(Request $request)
    {
        $v = Validator::make($request->all(), [
            'moniteur_id' => 'required|exists:auto_ecole_moniteurs,id',
            'code' => 'required|digits:6'
        ]);

        if ($v->fails()) {
            return $this->errorResponse("Validation échouée", $v->errors(), statuscode: 422);
        }

        $moniteurOtp = MoniteurOtp::where('moniteur_id', $request->moniteur_id)
            ->where('code', $request->code)
            ->where('expire', '>', now())
            ->first();

        if (!$moniteurOtp) {
            return $this->errorResponse("Code de confirmation incorrect ou expiré", [], statuscode: 422);
        }

        $moniteur = Moniteur::find($request->moniteur_id);

        if (!$moniteur) {
            return $this->errorResponse("Le moniteur n'existe plus ou a été retiré", [], statuscode: 422);
        }

        $random = Str::random(32);

        $moniteurId = $moniteur->id;
        $autoEcoleId = $moniteur->auto_ecole_id;
        $expireAt = now()->addDays(30);

        $moniteurToken = MoniteurToken::create([
            'token' => $random,
            'moniteur_id' => $moniteurId,
            'auto_ecole_id' => $autoEcoleId,
            'expire_at' => $expireAt,
        ]);

        // Le m indique que c'est un moniteur
        $token = "m" . $moniteurToken->id . '|' . $random;

        $moniteurData = GetCandidat::findOne($moniteur->npi);

        $ae = $moniteur->autoEcole;
        $ae->load('agrement');
        $ae->annexe();
        return $this->successResponse([
            'access_token' => $token,
            'moniteur' => $moniteurData,
            'hasLicence' => $ae->hasLicence(),
            'agrement' => $ae->agrement,
            'auto_ecole' => $ae,
        ], "Connexion confirmée avec succès");
    }

    public function resendCode(Request $request)
    {
        $v = Validator::make($request->all(), [
            'moniteur_id' => 'required|exists:auto_ecole_moniteurs,id',
        ]);

        if ($v->fails()) {
            return $this->errorResponse("Validation échouée", $v->errors(), statuscode: 422);
        }

        $moniteurOtp = MoniteurOtp::where('moniteur_id', $request->moniteur_id)
            ->delete();

        $moniteur = Moniteur::find($request->moniteur_id);

        if (!$moniteur) {
            return $this->errorResponse("Le moniteur n'existe plus ou a été retiré", [], statuscode: 422);
        }

        $moniteurData = GetCandidat::findOne($moniteur->npi);

        if (!$moniteurData) {
            return $this->errorResponse("Cet NPI est introuvable chez ANIP", $v->errors(), statuscode: 422);
        }

        $otp_code = mt_rand(100000, 999999);

        MoniteurOtp::where('moniteur_id', $moniteur->id)->delete();
        $motp = MoniteurOtp::create([
            'moniteur_id' => $moniteur->id,
            'code' => $otp_code,
            'expire' =>  now()->addMinutes(5),
            'action' => 'login',
        ]);
        $messageBuilder = (new Messager())
            ->subject('Code de connexion')
            ->introParagraph("Votre nouveau code de connexion est: {$otp_code}")
            ->footer();

        (new EmailNotifier($messageBuilder, $moniteurData))->procced();

        $phone = Help::hashPhone($moniteurData['telephone']);

        $country_code = '229';
        $num = $moniteurData['telephone'];
        $text = 'Votre code de connexion est: ' . $otp_code;
        $sent = Sms::sendSMS($country_code, $num, $text);

        if (!$sent) {
            return $this->errorResponse("L'envoie du code de confirmation a échoué", $v->errors(), statuscode: 422);
        }
        unset($moniteur->auto_ecole);
        unset($moniteur->autoEcole);
        return $this->successResponse($moniteur, "Un nouveau code de confirmation a été envoyé à votre numéro de téléphone {$phone}");
    }

    public function resendMoniteurCode(Request $request)
    {
        $v = Validator::make($request->all(), [
            'user_id' => 'required|exists:auto_ecole_moniteurs,id',
        ]);

        if ($v->fails()) {
            return $this->errorResponse("Validation échouée", $v->errors(), statuscode: 422);
        }

        $moniteurOtp = MoniteurOtp::where('moniteur_id', $request->user_id)
            ->delete();

        $moniteur = Moniteur::find($request->user_id);

        if (!$moniteur) {
            return $this->errorResponse("Le moniteur n'existe plus ou a été retiré", [], statuscode: 422);
        }

        $moniteurData = GetCandidat::findOne($moniteur->npi);

        if (!$moniteurData) {
            return $this->errorResponse("Cet NPI est introuvable chez ANIP", $v->errors(), statuscode: 422);
        }

        $otp_code = mt_rand(100000, 999999);

        MoniteurOtp::where('moniteur_id', $moniteur->id)->delete();
        $motp = MoniteurOtp::create([
            'moniteur_id' => $moniteur->id,
            'code' => $otp_code,
            'expire' =>  now()->addMinutes(5),
            'action' => 'login',
        ]);
        $messageBuilder = (new Messager())
            ->subject('Code de connexion')
            ->introParagraph("Votre nouveau code de connexion est: {$otp_code}")
            ->footer();

        (new EmailNotifier($messageBuilder, $moniteurData))->procced();

        $phone = Help::hashPhone($moniteurData['telephone']);

        $country_code = '229';
        $num = $moniteurData['telephone'];
        $text = 'Votre code de connexion est: ' . $otp_code;
        $sent = Sms::sendSMS($country_code, $num, $text);

        if (!$sent) {
            return $this->errorResponse("L'envoie du code de confirmation a échoué", $v->errors(), statuscode: 422);
        }
        unset($moniteur->auto_ecole);
        unset($moniteur->autoEcole);
        return $this->successResponse($moniteur, "Un nouveau code de confirmation a été envoyé à votre numéro de téléphone {$phone}");
    }
}
