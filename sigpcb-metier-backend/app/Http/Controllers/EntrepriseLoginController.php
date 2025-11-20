<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Otp;
use App\Models\User;
use App\Mail\OtpMail;
use App\Models\Entreprise;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\EntrepriseOtp;
use App\Services\GetCandidat;
use App\Services\Sms;
use App\Models\EntrepriseToken;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;


class EntrepriseLoginController extends ApiController
{
    private function sendSMS($to, $text)
    {
        if (!App::isLocal()) {
            $user = env('SMS_LOGIN');
            $password = env('SMS_PASSWORD');
            $apikey = env('SMS_APIKEY');
            $from = 'ANaTT+BENIN';
            $text = 'Votre+code+de+connexion+est+' . $text;

            $url = env('SMS_ENDPOINT') . "?user={$user}&password={$password}&apikey={$apikey}&from={$from}&to={$to}&text={$text}";
            return  Http::get($url)->successful();
        }
        return true;
    }


    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:6'
            ], [
                'email.required' => 'Adresse e-mail requise',
                'email.email' => 'Adresse e-mail invalide',
                'password.required' => 'Mot de passe requis',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée.', $validator->errors(), null, statuscode: 419);
            }

            $user = Entreprise::whereEmail($request->email)->first();
            if (!$user) {
                return $this->errorResponse('Informations de connexion incorrectes', ['email' => "Adresse ou mot de passe incorrect"], null, 422);
            }

            // Check if there is an existing OTP code for this user and action
            $existing_otp = EntrepriseOtp::where('entreprise_id', $user->id)->where('action', 'login')->first();

            // If there is an existing OTP code, delete it
            if ($existing_otp) {
                $existing_otp->delete();
            }

            // Check if the entered password matches the user's password
            if (!Hash::check($request->password, $user->password)) {
                return $this->errorResponse('Informations de connexion incorrectes', ['email' => "Adresse ou mot de passe incorrect"], null, 422);
            }

            // Generate OTP code
            $user_phone = $user->phone; // Numéro de téléphone complet
            $masked_phone = substr_replace($user_phone, '****', 2, -2); // Remplace les chiffres intermédiaires par des astérisques
            $otp_code = mt_rand(100000, 999999);

            // Set expiration time for OTP
            $expire_time = Carbon::now()->addMinutes(5);

            // Create OTP record in database
            $otp = EntrepriseOtp::create([
                'entreprise_id' => $user->id,
                'code' => $otp_code,
                'expire' => $expire_time,
                'action' => 'login',
            ]);

            // Add "229" prefix to the user phone number
            $user_phone = '229' . $user_phone;
            $to = $user_phone;
            $text = $otp_code;

            $country_code = '229';
            $num = $user_phone;
            $text = 'Votre code de connexion est:' .$otp_code;


            // Send OTP code to user's email
            Mail::to($user->email)->send(new OtpMail($otp_code));
            // Send the SMS using the SMS APIlly
            try {
                Sms::sendSMS($country_code,$num,$text);
            } catch (Exception $th) {
                logger()->error($th);
            }

            return $this->successResponse([
                'user_id' => $user->id,
                'phone' => $masked_phone,
                'action' => 'login',
            ], 'Un code de vérification a été envoyé à votre numéro de téléphone');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur est survenue lors de la connexion");
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|integer',
                    'code' => 'required|integer',
                    'action' => 'required|in:login',
                ],
                [
                    'user_id.required' => 'Le champ utilisateur est obligatoire',
                    'user_id.integer' => 'Le champ utilisateur doit être un entier',
                    'code.required' => 'Le champ code est obligatoire',
                    'code.integer' => 'Le champ code doit être un entier',
                    'action.required' => 'Le champ action est obligatoire',
                    'action.in' => 'Le champ action doit être login',
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée.', $validator->errors(), null, statuscode: 422);
            }

            // Get OTP record from database
            $otp = EntrepriseOtp::whereEntrepriseId($request->user_id)
                ->whereCode($request->code)
                ->whereAction($request->action)
                ->where('expire', '>=', now())
                ->first();

            if (!$otp) {
                return $this->errorResponse('Code de vérification invalide ou expiré.', null, null, 422);
            }
            $entreprise = Entreprise::find($request->user_id);

            if (!$entreprise) {
                return $this->errorResponse("L'entreprise n'existe plus ou a été retiré", [], statuscode: 422);
            }

            // Remove OTP record from database
            $otp->delete();

            $random = Str::random(32);

            $entrepriseId = $entreprise->id;
            $expireAt = now()->addDays(30);

            $entrepriseToken = EntrepriseToken::create([
                'token' => $random,
                'entreprise_id' => $entrepriseId,
                'expire_at' => $expireAt,
            ]);

            // Le m indique que c'est un moniteur
            $token = "e" . $entrepriseToken->id . '|' . $random;
            return $this->successResponse([
                'access_token' => $token,
                'entreprise' => $entreprise,
            ], "Connexion confirmée avec succès");
        } catch (\Throwable $e) {
            logger()->error($e);

            return $this->errorResponse('Erreur serveur', $e->getMessage(), null, statuscode: 500);
        }
    }


    public function resendCode(Request $request)
    {
        try {
            // Récupérer l'utilisateur à partir de la logique métier spécifique à votre application
            $user = Entreprise::find($request->user_id);

            // Vérifier si l'utilisateur existe
            if (!$user) {
                return $this->errorResponse('Utilisateur introuvable', null, null, 422);
            }

            // Vérifier si l'utilisateur a déjà un code OTP actif
            $existing_otp = EntrepriseOtp::where('entreprise_id', $user->id)->where('action', 'login')->first();

            if (!$existing_otp) {
                return $this->errorResponse('Vous n\'avez pas de code OTP actif', null, null, 422);
            }

            // Vérifier si l'utilisateur a atteint la limite de tentatives
            $nombre_de_fois = $existing_otp->nombre_de_fois;
            $limite_tentatives = 3; // Définir la limite de tentatives ici

            if ($nombre_de_fois >= $limite_tentatives) {
                // Vérifier si l'utilisateur a attendu suffisamment de temps avant de réessayer
                $temps_attente = Carbon::now()->diffInMinutes($existing_otp->updated_at);
                $temps_attente_requis = 60; // Définir le temps d'attente requis en minutes ici

                if ($temps_attente < $temps_attente_requis) {
                    $temps_restant = $temps_attente_requis - $temps_attente;
                    return $this->errorResponse('Vous avez dépassé la limite de tentatives. Veuillez réessayer dans ' . $temps_restant . ' minutes.');
                } else {
                    // Réinitialiser le nombre de fois et la date de mise à jour
                    $existing_otp->nombre_de_fois = 0;
                    $existing_otp->updated_at = Carbon::now();
                    $existing_otp->save();
                }
            }

            $user_phone = $user->phone;

            // Incrémenter le nombre de fois
            $existing_otp->nombre_de_fois = $nombre_de_fois + 1;
            $existing_otp->save();

            // Générer un nouveau code OTP et le mettre à jour dans la base de données
            $otp_code = mt_rand(100000, 999999);
            $expire_time = Carbon::now()->addMinutes(5);
            $existing_otp->update([
                'code' => $otp_code,
                'expire' => $expire_time,
            ]);

            $to = $user_phone;
            $country_code = '229';
            $num = $user_phone;
            $text = 'Votre code de connexion est:' .$otp_code;
            // Envoyer le nouveau code OTP à l'utilisateur
            Mail::to($user->email)->send(new OtpMail($otp_code));
            // Send the SMS using the SMS APIlly
            if (Sms::sendSMS($country_code,$num,$text)) {
                return $this->successResponse([
                    'user_id' => $user->id,
                    'action' => 'login',
                ], 'Un code de vérification a été envoyé à votre numéro de téléphone');
            } else {
                logger()->error('Failed to send SMS');
                return $this->errorResponse('Une erreur est survenue lors de l\'envoi du code de vérification par SMS');
            }

        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur est survenue lors de l'envoi du code de vérification");
        }
    }

}

