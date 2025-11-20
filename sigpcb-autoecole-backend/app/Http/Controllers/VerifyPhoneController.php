<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Mail\OtpMail;
use App\Services\Sms;
use App\Models\VerifyPhone;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class VerifyPhoneController extends ApiController
{
    public function generate(Request $request)
    {
        $data = array_filter($request->only(['ifu', 'npi']));
        if (!$data) {
            return $this->errorResponse("Validation échouée, npi ou ifu manquant", statuscode: 422);
        }

        $phone = null;
        if (array_key_exists('npi', $data)) {
            $candidat = GetCandidat::findOne($data['npi']);
            $phone = $candidat['telephone'];
        }
        if (!$phone) {
            return $this->errorResponse("Validation échouée, npi ou ifu manquant", statuscode: 422);
        }

        // Générer un code OTP aléatoireS
        $otpCode = mt_rand(100000, 999999);

        // Enregistrer les informations dans la base de données
        $verification = VerifyPhone::create([
            'ifu' => $request->input('ifu'),
            'npi' => $request->input('npi'),
            'code' => $otpCode,
            'expired_at' => Carbon::now()->addMinutes(5),
        ]);
        $country_code = $candidat['telephone_prefix'];
        $num = $candidat['telephone'];
        $text = 'Votre code de verification est: ' . $otpCode;
        $success = Sms::sendSMS($country_code, $num, $text);

        if (!$success) {
            return $this->errorResponse("L'envoie du code de vérification a échoué");
        }
        $email = data_get($candidat, "email");
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Envoyer l'e-mail
            Mail::to($email)->send(new OtpMail($otpCode));
        }

        // Répondre avec succès
        return $this->successResponse(null, "L'envoie du code de vérification a été effectué avec succès");
    }

    public function verify(Request $request)
    {
        // Validation des données de la requête
        $v = Validator::make($request->all(), [
            'ifu' => 'nullable|string',
            'npi' => 'nullable|string',
            'code' => "required|digits:6"
        ]);

        if ($v->fails()) {
            return $this->errorResponse("Validation échouée, npi ou ifu manquant", statuscode: 422);
        }

        $wheres = $request->only([
            'ifu',  'npi', 'code'
        ]);

        // Rechercher la correspondance dans la base de données
        $verification = VerifyPhone::where($wheres)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        // Vérifier si la vérification a réussi
        if ($verification) {
            // Répondre avec succès
            return $this->successResponse(null, 'Numéro de téléphone vérifié avec succès.');
        } else {
            // Répondre avec une erreur
            return $this->errorResponse('Code vérification invalide ou expiré.', 422);
        }
    }
}
