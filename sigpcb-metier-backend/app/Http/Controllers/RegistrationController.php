<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Otp;
use App\Models\User;
use App\Mail\OtpMail;
use App\Services\Sms;
use App\Mail\EnglishOtpMail;
use Illuminate\Http\Request;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;

class RegistrationController extends ApiController
{

    private function sendSMS($to, $text)
    {
        $user = env('SMS_LOGIN');
        $password = env('SMS_PASSWORD');
        $apikey = env('SMS_APIKEY');
        $from = 'ANaTT+BENIN';

        $text = 'Votre+code+de+connexion+est+' . $text;

        $url = env('SMS_ENDPOINT') . "?user={$user}&password={$password}&apikey={$apikey}&from={$from}&to={$to}&text={$text}";

        $response = Http::get($url);

        return $response->successful();
    }
    /**
     * @OA\Post(
     *     path="/api/anatt-candidat/verify-npi",
     *     summary="Connexion",
     *     description="Connectez-vous en utilisant votre npi.",
     *     operationId="VerifyNpi",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données d'authentification",
     *         @OA\JsonContent(
     *             required={"npi"},
     *             @OA\Property(property="npi", type="string", example="123456789"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
     *             @OA\Property(property="user"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Informations d'identification incorrectes",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Informations de connexion incorrectes"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="email", type="string", example="npiincorrecte"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Validation échouée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation échouée."),
     *             @OA\Property(property="errors", type="object"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue lors de la connexion"),
     *         ),
     *     ),
     * )
     */
    public function verifyNpi(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required|string|max:10|exists:users,npi',
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


    /**
     * @OA\Post(
     *     path="/api/anatt-candidat/login",
     *     summary="Connexion",
     *     description="Connectez-vous en utilisant votre npi.",
     *     operationId="login",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données d'authentification",
     *         @OA\JsonContent(
     *             required={"npi","email","lang"},
     *             @OA\Property(property="npi", type="string", example="123456789"),
     *             @OA\Property(property="email", type="string", format="email", example="user@gmail.com"),
     *             @OA\Property(property="phone", type="string", example="62000001"),
     *             @OA\Property(property="lang", type="string", example="fr"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
     *             @OA\Property(property="user"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Informations d'identification incorrectes",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Informations de connexion incorrectes"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="email", type="string", example="npiincorrecte"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Validation échouée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation échouée."),
     *             @OA\Property(property="errors", type="object"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue lors de la connexion"),
     *         ),
     *     ),
     * )
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required|string|max:10|exists:users,npi',
                'email' => 'nullable|email',
                'phone' => 'required',
                'lang' => ['required']
            ], [
                'npi.required' => 'Le numéro NPI est requis',
                'npi.integer' => 'Le numéro NPI doit être un entier',
                'phone.required' => 'Le numéro de téléphone est requis',
                'email.email' => 'Adresse e-mail invalide',
                'npi.exists' => 'Ce numéro NPI n\'existe pas',
                'lang.required' => "Langue d'affichage obligatoire"
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échoué', $validator->errors(), null, 422);
            }
            $user = User::where('npi', $request->npi)->firstOrFail();
            // Check if there is an existing OTP code for this user and action
            $existing_otp = Otp::where('user_id', $user->id)->where('action', 'login')->first();

            // If there is an existing OTP code, delete it
            if ($existing_otp) {
                $existing_otp->delete();
            }

            // Generate OTP code
            $otp_code = mt_rand(100000, 999999);

            // Set expiration time for OTP
            $expire_time = Carbon::now()->addMinutes(5);

            // Create OTP record in database
            $otp = Otp::create([
                'user_id' => $user->id,
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
            if (Sms::sendSMS($country_code, $num, $text)) {

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

    /**
     * @OA\Post(
     *     path="/api/anatt-candidat/register",
     *     summary="Inscription",
     *     description="Inscrivez-vous en utilisant votre adresse numéro npi.",
     *     operationId="Register",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données d'authentification",
     *         @OA\JsonContent(
     *             required={"npi","email","lang"},
     *             @OA\Property(property="npi", type="string", example="1234567890"),
     *             @OA\Property(property="email", type="string", format="email", example="admin@gmail.com"),
     *             @OA\Property(property="phone", type="string", example="62000001"),
     *             @OA\Property(property="lang", type="string", format="text", example="fr"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inscription réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
     *             @OA\Property(property="user"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Informations d'identification incorrectes",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Informations d'inscrisption incorrectes"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="email", type="string", example="Adresse e-mail ou mot de passe incorrecte"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Validation échouée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation échouée."),
     *             @OA\Property(property="errors", type="object"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue lors de la connexion"),
     *         ),
     *     ),
     * )
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required|string|max:10|unique:users,npi',
                'email' => 'required|email',
                'phone' => 'required',
                'lang' => 'required'
            ], [
                'npi.required' => 'Le numéro NPI est requis',
                'phone.required' => 'Le numéro de téléphone est requis',
                'npi.integer' => 'Le numéro NPI doit être un entier',
                'npi.max' => 'La taille du numéro NPI ne doit pas dépasser 10 caractères',
                'email.required' => 'L\'adresse e-mail est requise',
                'email.email' => 'Adresse e-mail invalide',
                'npi.unique' => 'Ce numéro NPI est déjà utilisé, veuillez vous connecter plutôt.',
                'lang.required' => "Langue d'affichage obligatoire"
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), null, 422);
            }

            // Generate OTP code
            $otp_code = mt_rand(100000, 999999);

            // Set expiration time for OTP
            $expire_time = Carbon::now()->addMinutes(5);

            // Create user record in database
            $npi = $request->npi;
            $email = $request->email;
            $user_phone = $request->phone;
            $to = $user_phone;

            $country_code = '229';
            $num = $to;
            $text = 'Votre code de connexion est:' .$otp_code;

            $user = User::create([
                'npi' => $npi,
                'status' => true,
            ]);

            // Create OTP record in database
            $otp = Otp::create([
                'user_id' => $user->id,
                'code' => $otp_code,
                'expire' => $expire_time,
                'action' => 'register',
            ]);
            if (Sms::sendSMS($country_code,$num,$text)) {
                // Send OTP code to user's
                if ($request->lang == 'fr') {
                    Mail::to($email)->send(new OtpMail($otp_code));
                } else {
                    Mail::to($email)->send(new EnglishOtpMail($otp_code));
                }
                return $this->successResponse([
                    'user_id' => $user->id,
                    'action' => 'register',
                ], 'Un code de vérification a été envoyé à votre numéro de téléphone');
            } else {
                logger()->error('Failed to send SMS');
                return $this->errorResponse('Une erreur est survenue lors de l\'envoi du code de vérification par SMS');
            }

        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur est survenue lors de l'inscription");
        }
    }

    /**
     * @OA\Post(
     *     path="/api/anatt-candidat/verify-otp",
     *     summary="Vérification du code OTP",
     *     description="Vérifie le code OTP et connecte l'utilisateur en cas de succès.",
     *     operationId="verifyOtp",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données de vérification du code OTP",
     *         @OA\JsonContent(
     *             required={"user_id", "code", "action"},
     *             @OA\Property(property="user_id", type="integer", example="123"),
     *             @OA\Property(property="code", type="integer", example="123456"),
     *             @OA\Property(property="action", type="string", example="login"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
     *             @OA\Property(property="user"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Code OTP invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Code de vérification invalide ou expiré"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non autorisé"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Validation échouée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation échouée."),
     *             @OA\Property(property="errors", type="object"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue lors de la connexion"),
     *         ),
     *     ),
     * )
     */
    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'user_id' => 'required|integer',
                    'code' => 'required|integer',
                    'action' => 'required',
                ],
                [
                    'user_id.required' => 'Le champ utilisateur est obligatoire',
                    'user_id.integer' => 'Le champ utilisateur doit être un entier',
                    'code.required' => 'Le champ code est obligatoire',
                    'code.integer' => 'Le champ code doit être un entier',
                    'action.required' => 'Le champ action est obligatoire',
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée.', $validator->errors(), null, 422);
            }

            // Get OTP record from database
            $otp = Otp::whereUserId($request->user_id)
                ->whereCode($request->code)
                ->whereAction($request->action)
                ->where('expire', '>=', now())
                ->first();

            if (!$otp) {
                return $this->errorResponse('Code de vérification invalide ou expiré.', null, null, 422);
            }

            // Remove OTP record from database
            $otp->delete();
            // Authenticate user with Laravel's authentication system
            if (Auth::loginUsingId($request->user_id)) {
                /**
                 * @var User
                 */
                $user = Auth::user();

                // Remove old token
                $user->tokens()->where('name', 'auth')->delete();

                $success['access_token'] = "u" . $user->createToken('auth')->plainTextToken;
                $success['user'] =  $user;

                return $this->successResponse($success, 'Connexion effectuée avec succès');
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Erreur serveur', $e->getMessage(), null, 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/anatt-candidat/resend-code",
     *     summary="Renvoi le code OTP",
     *     description="Renvoie un nouveau code OTP à l'utilisateur pour la vérification.",
     *     operationId="resendCode",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données pour renvoyer le code OTP",
     *         @OA\JsonContent(
     *             required={"user_id", "action", "email","lang"},
     *             @OA\Property(property="user_id", type="integer", example="123"),
     *             @OA\Property(property="action", type="string", example="login"),
     *             @OA\Property(property="email", type="string", example="login@gmail.com"),
     *             @OA\Property(property="phone", type="string", example="62000001"),
     *             @OA\Property(property="lang", type="string", format="text", example="fr"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP envoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Un nouveau code de vérification a été envoyé à votre adresse e-mail"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Code OTP invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Code de vérification invalide ou expiré"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Non autorisé"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Validation échouée",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation échouée."),
     *             @OA\Property(property="errors", type="object"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue lors de la connexion"),
     *         ),
     *     ),
     * )
     */
    public function resendCode(Request $request)
    {
        try {
            // Récupérer l'utilisateur à partir de la logique
            $action = $request->action;
            $email = $request->email;
            $user_phone = $request->phone;
            $user = User::find($request->user_id);

            // Vérifier si l'utilisateur existe
            if (!$user) {
                return $this->errorResponse('Utilisateur introuvable', null, null, 422);
            }

            // Vérifier si l'utilisateur a déjà un code OTP actif
            $existing_otp = Otp::where('user_id', $user->id)->where('action', $action)->first();

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
            $user_phone = '229' . $user_phone;
            $to = $user_phone;

            $country_code = '229';
            $num = $to;
            $text = 'Votre code de connexion est:' .$otp_code;

            if (Sms::sendSMS($country_code,$num,$text)) {
                // Envoyer le nouveau code OTP à l'utilisateur
                if ($request->lang == 'fr') {
                    Mail::to($email)->send(new OtpMail($otp_code));
                } else {
                    Mail::to($email)->send(new EnglishOtpMail($otp_code));
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
