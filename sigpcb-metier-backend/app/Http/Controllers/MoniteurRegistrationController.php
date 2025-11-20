<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Otp;
use App\Models\User;
use App\Mail\OtpMail;
use App\Services\Sms;
use App\Models\Moniteur;
use App\Models\Entreprise;
use App\Models\MoniteurOtp;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\EntrepriseOtp;
use App\Models\MoniteurToken;
use App\Services\GetCandidat;
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


class MoniteurRegistrationController extends ApiController
{
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required|string|max:10|exists:moniteurs,npi',
                'email' => 'nullable|email',
                'phone' => 'required',
            ], [
                'npi.required' => 'Le numéro NPI est requis',
                'npi.integer' => 'Le numéro NPI doit être un entier',
                'phone.required' => 'Le numéro de téléphone est requis',
                'email.email' => 'Adresse e-mail invalide',
                'npi.exists' => 'Ce numéro NPI n\'existe pas',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échoué', $validator->errors(), null, 422);
            }
            $user = Moniteur::where('npi', $request->npi)->firstOrFail();
            // Check if there is an existing OTP code for this user and action
            $existing_otp = MoniteurOtp::where('moniteur_id', $user->id)->where('action', 'login')->first();

            // If there is an existing OTP code, delete it
            if ($existing_otp) {
                $existing_otp->delete();
            }

            // Generate OTP code
            $otp_code = mt_rand(100000, 999999);

            // Set expiration time for OTP
            $expire_time = Carbon::now()->addMinutes(5);

            // Create OTP record in database
            $otp = MoniteurOtp::create([
                'moniteur_id' => $user->id,
                'code' => $otp_code,
                'expire' => $expire_time,
                'action' => 'login',
            ]);
            // Send OTP code to user's
            $email = $request->email;
            $user_phone = $request->phone;
            $to = $user_phone;

            $country_code = '229';
            $num = $to;
            $text = 'Votre code de connexion est:' .$otp_code;
            if ($request->has('email') && !empty($request->input('email'))) {
                $email = $request->input('email');
                // Vérifier si l'email a le format correct
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    // Envoyer l'e-mail
                    Mail::to($email)->send(new OtpMail($otp_code));
                }
            }

            if (Sms::sendSMS($country_code,$num,$text)) {
                // Mail::to($email)->send(new OtpMail($otp_code));
                return $this->successResponse([
                    'user_id' => $user->id,
                    'npi' => $user->npi,
                    'action' => 'login',
                ], 'Un code de vérification a été envoyé à votre numéro de téléphone');
            } else {
                logger()->error('Failed to send SMS');
                return $this->errorResponse('Une erreur est survenue lors de l\'envoi du code de vérification par SMS');
            }

        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur est survenue lors de la connexion");
        }
    }

    public function verifyNpi(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required|string|max:10|exists:moniteurs,npi',
            ], [
                'npi.required' => 'Le numéro NPI est requis',
                'npi.integer' => 'Le numéro NPI doit être un entier',
                'npi.exists' => 'Ce numéro NPI n\'est pas enregistré, veuillez vous inscrire en remplissant le formulaire de demande',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }

            return $this->successResponse(null, 'Le numéro NPI existe dans notre système');
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
            $otp = MoniteurOtp::whereMoniteurId($request->user_id)
                ->whereCode($request->code)
                ->whereAction($request->action)
                ->where('expire', '>=', now())
                ->first();

            if (!$otp) {
                return $this->errorResponse('Code de vérification invalide ou expiré.', null, null, 422);
            }
            $moniteur = Moniteur::find($request->user_id);

            if (!$moniteur) {
                return $this->errorResponse("Le moniteur n'existe plus ou a été retiré", [], statuscode: 422);
            }

            // Remove OTP record from database
            $otp->delete();

            $random = Str::random(32);

            $moniteurId = $moniteur->id;
            $expireAt = now()->addDays(30);

            $moniteurToken = MoniteurToken::create([
                'token' => $random,
                'moniteur_id' => $moniteurId,
                'expire_at' => $expireAt,
            ]);

            // Le m indique que c'est un moniteur
            $token = "m" . $moniteurToken->id . '|' . $random;
            return $this->successResponse([
                'access_token' => $token,
                'moniteur' => $moniteur,
            ], "Connexion confirmée avec succès");
        } catch (\Throwable $e) {
            logger()->error($e);

            return $this->errorResponse('Erreur serveur', $e->getMessage(), null, statuscode: 500);
        }
    }
    public function resendCode(Request $request)
    {
        try {
            // Récupérer l'utilisateur à partir de la logique
            $action = $request->action;
            $email = $request->email;
            $user_phone = $request->phone;
            $user = Moniteur::find($request->user_id);

            // Vérifier si l'utilisateur existe
            if (!$user) {
                return $this->errorResponse('Moniteur introuvable', null, null, 422);
            }

            // Vérifier si l'utilisateur a déjà un code OTP actif
            $existing_otp = MoniteurOtp::where('moniteur_id', $user->id)->where('action', $action)->first();

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
            $num = $to;
            $text = 'Votre code de connexion est : ' . $otp_code;


            if (Sms::sendSMS($country_code,$num,$text)) {
                if ($request->has('email') && !empty($request->input('email'))) {
                    $email = $request->input('email');
                    // Vérifier si l'email a le format correct
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        // Envoyer l'e-mail
                        Mail::to($email)->send(new OtpMail($otp_code));
                    }
                }

                return $this->successResponse([
                    'user_id' => $user->id,
                    'action' => $action,
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

