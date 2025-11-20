<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Otp;
use App\Models\User;
use App\Models\UserAction;
use App\Mail\OtpMail;
use App\Services\Sms;
use Illuminate\Http\Request;
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


class RegistrationController extends ApiController
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
    /**
     * @OA\Post(
     *     path="/api/anatt-admin/login",
     *     summary="Connexion",
     *     description="Connectez-vous en utilisant votre adresse e-mail et votre mot de passe.",
     *     operationId="login",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données d'authentification",
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="mypassword"),
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
    public function login(Request $request)
    {
        try {
            // Nettoyer les espaces autour de l'e-mail
            $request->merge(['email' => trim($request->email)]);
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ], [
                'email.required' => 'Adresse e-mail requise',
                'email.email' => 'Adresse e-mail invalide',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée.', $validator->errors(), null, statuscode: 419);
            }

            $user = User::whereEmail($request->email)->first();
            if (!$user) {
                return $this->errorResponse('Informations de connexion incorrectes', ['email' => "Adresse incorrect"], null, 422);
            }

            if (!$user->status) {
                return $this->errorResponse('Votre compte a été désactivé', null, null, 422);
            }

            // Check if there is an existing OTP code for this user and action
            $existing_otp = Otp::where('user_id', $user->id)->where('action', 'login')->first();

            // If there is an existing OTP code, delete it
            if ($existing_otp) {
                $existing_otp->delete();
            }

            // Generate OTP code
            $user_phone = $user->phone; // Numéro de téléphone complet
            $masked_phone = substr_replace($user_phone, '****', 2, -2); // Remplace les chiffres intermédiaires par des astérisques
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

            // Add "229" prefix to the user phone number
            $country_code = '229';
            $num = $user_phone;
            $text = "Votre code de connexion est " .$otp_code;
            // Send OTP code to user's email
            try {
                Mail::to($user->email)->send(new OtpMail($otp_code));
            } catch (Exception $th) {
                logger()->error('Erreur lors de l\'envoi du mail: ' . $th->getMessage());
            }
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



    /**
     * @OA\Post(
     *     path="/api/anatt-admin/login/verify-otp",
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
                $userWithRole = User::with('roles.permissions')->find($user->id);
                // Remove old token
                $user->tokens()->where('name', 'auth')->delete();
                // Create new token
                $success['access_token'] =  $user->createToken('auth')->plainTextToken;
                $success['user'] =  $userWithRole;

                $latestAction = UserAction::where('user_id', $user->id)->latest()->first();
                $url = $request->fullUrl();
                if ($latestAction) {
                        $latestAction->update(['time' => now(),'url'=>$url]);
                    }else {
                    // Aucune action précédente n'a été enregistrée, enregistre la première action de l'utilisateur
                    UserAction::create([
                        'user_id' => $user->id,
                        'url'=>$url,
                        'time' => now(),
                    ]);
                }
                return $this->successResponse($success, 'Connexion effectuée avec succès');
            }
        } catch (\Throwable $e) {
            return $this->errorResponse('Erreur serveur', $e->getMessage(), null, statuscode: 500);
        }
    }



    /**
     * @OA\Post(
     *     path="/api/anatt-admin/login/resend-code",
     *     summary="Renvoi le code OTP",
     *     description="Renvoie un nouveau code OTP à l'utilisateur pour la vérification.",
     *     operationId="resendCode",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données pour renvoyer le code OTP",
     *         @OA\JsonContent(
     *             required={"user_id", "action"},
     *             @OA\Property(property="user_id", type="integer", example="123"),
     *             @OA\Property(property="action", type="string", example="login"),
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
            // Récupérer l'utilisateur à partir de la logique métier spécifique à votre application
            $user = User::find($request->user_id);

            // Vérifier si l'utilisateur existe
            if (!$user) {
                return $this->errorResponse('Utilisateur introuvable', null, null, 422);
            }

            // Vérifier si l'utilisateur a déjà un code OTP actif
            $existing_otp = Otp::where('user_id', $user->id)->where('action', 'login')->first();

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

            // Add "229" prefix to the user phone number
            $country_code = '229';
            $num = $user_phone;
            $text = "Votre code de connexion est " .$otp_code;
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




    /**
     * @OA\Post(
     *     path="/api/anatt-admin/login/forgot-password",
     *     summary="Récupération de mot de passe",
     *     description="Envoyer un code OTP à l'utilisateur pour la récupération de mot de passe.",
     *     operationId="forgotPassword",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données pour la récupération de mot de passe",
     *         @OA\JsonContent(
     *             required={"email", "url"},
     *             @OA\Property(property="email", type="string", format="email", example="example@example.com"),
     *             @OA\Property(property="url", type="string", format="url", example="https://example.com/reset-password"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code OTP envoyé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer", example="123"),
     *             @OA\Property(property="action", type="string", example="forgot_password"),
     *             @OA\Property(property="message", type="string", example="Un code de vérification a été envoyé à votre adresse e-mail"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Adresse e-mail introuvable",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Adresse e-mail introuvable"),
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
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue lors de la récupération de mot de passe"),
     *         ),
     *     ),
     * )
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'url' => 'required|url'
                ],
                [
                    'email.required' => 'Adresse e-mail requise.',
                    'email.email' => 'Adresse e-mail invalide.',
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée.', $validator->errors(), '', 422);
            }

            $user = User::whereEmail($request->email)->first();
            if (!$user) {
                return $this->errorResponse('Adresse e-mail introuvable', '', '', 422);
            }

            // Check if there is an existing OTP code for this user and action
            $existing_otp = Otp::where('user_id', $user->id)->where('action', 'forgot_password')->first();

            // If there is an existing OTP code, delete it
            if ($existing_otp) {
                $existing_otp->delete();
            }

            // Generate OTP code
            $otp_code = mt_rand(100000, 999999);

            // Encrypt OTP code
            $encrypted_otp = encrypt($otp_code);
            // Set expiration time for OTP
            $expire_time = Carbon::now()->addMinutes(15);
            // Create OTP record in database
            $otp = Otp::create([
                'user_id' => $user->id,
                'code' => $otp_code,
                'expire' => $expire_time,
                'action' => 'forgot_password',
            ]);

            // Send OTP code to user's email
            Mail::to($user->email)->send(new ForgotPasswordMail($encrypted_otp, $request->url));

            return $this->successResponse([
                'user_id' => $user->id,
                'action' => 'forgot_password',
            ], 'Un lien a été envoyé à votre adresse e-mail');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur est survenue lors de la récupération de mot de passe");
        }
    }

    /**
     * @OA\Post(
     *     path="/api/anatt-admin/login/reset-password",
     *     summary="Réinitialisation du mot de passe",
     *     description="Réinitialise le mot de passe de l'utilisateur avec le code OTP fourni.",
     *     operationId="resetPassword",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données pour réinitialiser le mot de passe",
     *         @OA\JsonContent(
     *             required={"otp"},
     *             @OA\Property(property="otp", type="string", example="XXXXXXXXXXXXXXXX"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe réinitialisé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="integer", example="123"),
     *                 @OA\Property(property="otp", type="string", example="XXXXXXXXXXXXXXXX")
     *             ),
     *             @OA\Property(property="message", type="string", example="Mot de passe réinitialisé avec succès"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Code OTP invalide ou expiré",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Le code OTP est invalide. Veuillez vérifier votre e-mail"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Une erreur est survenue lors de la réinitialisation du mot de passe"),
     *         ),
     *     ),
     * )
     */
    public function resetPassword(Request $request)
    {
        // Récupérer le code OTP encrypté et l'e-mail de l'utilisateur depuis l'URL
        $encryptedOtp = $request->input('otp');

        try {
            // Déchiffrer le code OTP encrypté
            $otp = Crypt::decrypt($encryptedOtp);

            // Rechercher le code OTP déchiffré dans la table OTPs
            $otpEntry = Otp::where('code', $otp)
                ->where('action', 'forgot_password')
                ->first();

            // Vérifier si le code OTP existe et s'il est toujours valide
            if ($otpEntry && $otpEntry->expire > Carbon::now()) {
                $user_id = $otpEntry->user_id;
                // Rediriger l'utilisateur vers la page de réinitialisation du mot de passe
                return $this->successResponse([
                ], 'Vérification effectuée avec succès');
            } else {
                // Le code OTP n'est pas valide, renvoyer une réponse d'erreur
                return $this->errorResponse("Le token est invalide. Veuillez vérifier votre e-mail", '', '', 422);
            }
        } catch (DecryptException $e) {
            logger()->error($e);
            // Le code OTP ne peut pas être déchiffré, renvoyer une réponse d'erreur
            return $this->errorResponse("Le token est invalide. Veuillez vérifier votre e-mail");
        }
    }


    /**
     * @OA\Post(
     *     path="/api/anatt-admin/login/update-password",
     *     summary="Mise à jour du mot de passe",
     *     description="Met à jour le mot de passe de l'utilisateur avec le code OTP fourni.",
     *     operationId="updatePassword",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données pour mettre à jour le mot de passe",
     *         @OA\JsonContent(
     *             required={"otp", "password", "confirm_password"},
     *             @OA\Property(property="otp", type="string", example="XXXXXXXXXXXXXXXX"),
     *             @OA\Property(property="password", type="string", format="password", example="nouveau_mot_de_passe"),
     *             @OA\Property(property="confirm_password", type="string", format="password", example="nouveau_mot_de_passe"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="integer", example="123"),
     *             ),
     *             @OA\Property(property="message", type="string", example="Mot de passe mis à jour avec succès"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Mauvaise requête",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Le mot de passe et la confirmation du mot de passe ne correspondent pas."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Le code OTP est invalide. Veuillez vérifier votre e-mail et le code OTP"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Une erreur s'est produite lors de la réinitialisation du mot de passe. Veuillez réessayer ultérieurement."),
     *         ),
     *     ),
     * )
     */
    public function updatePassword(Request $request)
    {
        $encryptedOtp = $request->input('otp');
        $password = $request->input('password');
        $confirm_password = $request->input('confirm_password');

        // Vérifier si le mot de passe et la confirmation du mot de passe correspondent
        if ($password !== $confirm_password) {
            return $this->errorResponse("Le mot de passe et la confirmation du mot de passe ne correspondent pas.", ['password' => "Le mot de passe et la confirmation du mot de passe ne correspondent pas."], '', 422);
        }

        // Vérifier si le mot de passe respecte les critères de sécurité
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            return $this->errorResponse("Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, un caractère spécial et être d'au moins 8 caractères.", ['password' => "Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, un caractère spécial et être d'au moins 8 caractères."], '', 422);
        }

        try {

            $otp = Crypt::decrypt($encryptedOtp);
            // Rechercher l'entrée OTP dans la table OTPs
            $otpEntry = Otp::where('code', $otp)
                ->where('action', 'forgot_password')
                ->first();

            // Vérifier si le code OTP existe et s'il est toujours valide
            if ($otpEntry && $otpEntry->expire > Carbon::now()) {
                $user_id = $otpEntry->user_id;

                // Mettre à jour le mot de passe de l'utilisateur avec le nouveau mot de passe haché
                $user = User::findOrFail($user_id);
                $user->password = Hash::make($password);
                $user->save();

                // Supprimer l'entrée OTP de la table OTPs
                $otpEntry->delete();

                // Rediriger l'utilisateur vers la page de connexion avec un message de succès
                return $this->successResponse(null, 'Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.');
            } else {
                // Le code OTP n'est pas valide, renvoyer une réponse d'erreur
                return $this->errorResponse("Le token est invalide. Veuillez vérifier votre e-mail", 422);
            }
        } catch (\Throwable $e) {
            logger()->error($e);
            // Une erreur s'est produite, renvoyer une réponse d'erreur
            return $this->errorResponse("Une erreur s'est produite lors de la réinitialisation du mot de passe. Veuillez réessayer ultérieurement.");
        }
    }


    /**
     * @OA\Post(
     *     path="/api/anatt-admin/login/password-update",
     *     summary="Mise à jour du mot de passe",
     *     description="Met à jour le mot de passe de l'utilisateur",
     *     operationId="passwordUpdate",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données pour mettre à jour le mot de passe",
     *         @OA\JsonContent(
     *             required={"old_password", "new_password", "confirm_password"},
     *             @OA\Property(property="old_password", type="string", format="password", example="ancien mot de passe"),
     *             @OA\Property(property="new_password", type="string", format="password", example="nouveau_mot_de_passe"),
     *             @OA\Property(property="confirm_password", type="string", format="password", example="nouveau_mot_de_passe"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="integer", example="123"),
     *             ),
     *             @OA\Property(property="message", type="string", example="Mot de passe mis à jour avec succès"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Mauvaise requête",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Le mot de passe et la confirmation du mot de passe ne correspondent pas."),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Une erreur s'est produite lors de la réinitialisation du mot de passe. Veuillez réessayer ultérieurement."),
     *         ),
     *     ),
     * )
     */
    public function passwordUpdate(Request $request)
    {
        try {
            $user = Auth::user();

            // Validation des données d'entrée
            $validator = Validator::make(
                $request->all(),
                [
                    'old_password' => 'required',
                    'new_password' => 'required|min:8',
                    'confirm_password' => 'required|min:8'

                ],
                [
                    'old_password.required' => 'L\'ancien mot de passe est requis.',
                    'new_password.required' => 'Le nouveau mot de passe est requis.',
                    'confirm_password.required' => 'La confirmation du mot de passe est requise.',
                    'new_password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
                    'confirm_password.min' => 'La confirmation du mot de passe doit comporter au moins 8 caractères.',


                ]
            );
            $new_password = $request->input('new_password');
            $confirm_password = $request->input('confirm_password');
            $user_id = $user->id;

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), '', 422);
            }
            // Vérifier si le mot de passe et la confirmation du mot de passe correspondent
            if ($new_password !== $confirm_password) {
                return $this->errorResponse("Le mot de passe et la confirmation du mot de passe ne correspondent pas.", ['new_password' => "Le mot de passe et la confirmation du mot de passe ne correspondent pas."], '', 422);
            }
            // Vérification de l'ancien mot de passe
            if (!Hash::check($request->input('old_password'), $user->password)) {
                return $this->errorResponse('Validation échouée', ['old_password' => 'Le mot de passe actuel est incorrect.']);
            }

            // Vérifier si le mot de passe respecte les critères de sécurité
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
                return $this->errorResponse("Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, un caractère spécial et être d'au moins 8 caractères.", ['new_password' => "Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, un caractère spécial et être d'au moins 8 caractères."], '', 422);
            }

            // Mettre à jour le mot de passe de l'utilisateur avec le nouveau mot de passe haché
            $user = User::findOrFail($user_id);
            $user->password = Hash::make($new_password);
            $user->save();

            return $this->successResponse(null, 'Mot de passe modifié avec succès!');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
        }
    }
}
