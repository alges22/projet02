<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Otp;
use App\Models\User;
use App\Mail\OtpMail;
use App\Services\Sms;
use App\Models\Moniteur;
use App\Models\AutoEcole;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends ApiController
{


    /**
     * @OA\Post(
     *     path="/api/anatt-autoecole/confirm-account",
     *     summary="Confirme le compte de l'auto-école",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", description="Token d'auto-école")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte vérifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Votre compte a été vérifié avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token invalide ou manquant",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token invalide ou manquant")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Une erreur s'est produite lors de la vérification du compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Une erreur s'est produite lors de la vérification du compte.")
     *         )
     *     )
     * )
     */
    public function confirmAccount(Request $request)
    {
        // Récupérer le code OTP encrypté et l'e-mail de l'utilisateur depuis l'URL
        $encryptedOtp = $request->input('token');
        try {
            // Déchiffrer le code OTP encrypté
            $otp = Crypt::decrypt($encryptedOtp);
            // Rechercher le code OTP déchiffré dans la table OTPs
            $otpEntry = Otp::where('code', $otp)
                ->where('action', 'confirmation_de_compte')
                ->first();
            // Vérifier si le code OTP existe et s'il est toujours valide
            if ($otpEntry && $otpEntry->expire > Carbon::now()) {
                $user_id = $otpEntry->user_id;
                $auto_ecole = User::findOrFail($user_id);
                if ($auto_ecole->is_verify) {
                    return $this->errorResponse('Votre compte est déjà vérifié.', null, null, 200);
                }
                $auto_ecole->is_verify = true;
                $auto_ecole->save();

                // Supprimer l'entrée OTP après avoir vérifié le compte avec succès
                $otpEntry->delete();
                // Authenticate user with Laravel's authentication system
                if (Auth::loginUsingId($auto_ecole->id)) {
                    /**
                     * @var User
                     */
                    $user = Auth::user();
                    // Remove old token
                    $user->tokens()->where('name', 'auth')->delete();
                    // Create new token
                    $success['access_token'] =  $user->createToken('auth')->plainTextToken;
                    // Ajouter les informations de l'utilisateur dans la réponse
                    $success['user'] = $user;
                    return $this->successResponse($success, 'Votre compte a été vérifié avec succès. Vous êtes maintenant connecté');
                }
            } else {
                // Le code OTP n'est pas valide, renvoyer une réponse d'erreur
                return $this->errorResponse("Le token est invalide. Veuillez vérifier votre e-mail", null, null, 422);
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue lors de la vérification du compte.', null, null, 500);
        }
    }




    /**
     * @OA\Post(
     *     path="/api/anatt-autoecole/login",
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
            $validator = Validator::make(
                $request->all(),
                [
                    'npi' => 'required|digits:10',
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée.', $validator->errors(), null, statuscode: 422);
            }

            $user = User::whereNpi($request->npi)->first();
            if (!$user) {
                return app(MoniteurRegistrationController::class)->verify($request);
            }



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

            $promoteurData = GetCandidat::findOne($request->npi);

            if (!$promoteurData) {
                return $this->errorResponse("Vos informations de connexion sont incorrectes. Veuillez les corriger ou formuler une demande d’autorisation", ['npi' => "Votre n'existe pas sur ANIP"], null, 422);
            }

            try {
                $country_code = $promoteurData['telephone_prefix'];
                $num = $promoteurData['telephone'];
                $text = 'Votre code de connexion est: ' . $otp_code;
                Sms::sendSMS($country_code, $num, $text);
            } catch (\Throwable $th) {
                logger()->error($th);
            }
            return $this->successResponse([
                'user_id' => $user->id,
                'action' => 'login',
                "type" => "promoteur"
            ], 'Un code de vérification a été envoyé à votre numéro de téléphone');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur est survenue lors de la connexion");
        }
    }



    /**
     * @OA\Post(
     *     path="/api/anatt-autoecole/login/verify-otp",
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
                    'code' => 'required|digits:6',
                    'action' => 'required|in:login',
                ],
                [
                    'user_id.required' => 'Le champ utilisateur est obligatoire',
                    'user_id.integer' => 'Le champ utilisateur doit être un entier',
                    'code.required' => 'Le champ code est obligatoire',
                    'code.digits' => 'Le champ code doit être nombre',
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
                $promoteur = GetCandidat::findOne($user->npi);
                if (!$promoteur) {
                    return $this->errorResponse('Votre NPI n\'est plus retrouvé sur le compte de l\'ANIP', null, null, 422);
                }
                foreach (['created_at', 'updated_at', 'id'] as   $key) {
                    unset($promoteur[$key]);
                }

                $promoteur['email'] = $user->email;
                // Remove old token
                $user->tokens()->where('name', 'auth')->delete();
                // Create new token
                //Le p au début indique que c'est un token  pour un promoteur
                $success['access_token'] = "p" . $user->createToken('auth')->plainTextToken;
                $success['user'] = $promoteur;

                $aes = AutoEcole::with(['agrement'])->where('promoteur_id', $user->id)->get();
                $success['auto_ecoles'] = $aes->map(function (AutoEcole $ae) {
                    $ae->lastLicence();
                    $ae->annexe();
                    return $ae;
                });
                $moniteur = Moniteur::where([
                    "npi" => $user->npi,
                    "active" => true
                ])->latest()->first();
                if ($moniteur) {
                    $success["moniteur_auto_ecoles"] = [$moniteur->autoEcole];
                }

                return $this->successResponse($success, 'Connexion effectuée avec succès');
            }
        } catch (\Throwable $e) {
            return $this->errorResponse("Une erreur s'est produite lors de l'authentification", $e->getMessage(), null, statuscode: 500);
        }
    }



    /**
     * @OA\Post(
     *     path="/api/anatt-autoecole/login/resend-code",
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
                return app(MoniteurRegistrationController::class)->resendMoniteurCode($request);
                // return $this->errorResponse('Utilisateur introuvable', null, null, 422);
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

            $promoteurData = GetCandidat::findOne($user->npi);

            if (!$promoteurData) {
                return $this->errorResponse('Informations de connexion incorrectes', ['npi' => "Votre npi n'existe pas chez ANIP"], null, 422);
            }

            // Send the SMS using the SMS APIlly
            $country_code = $promoteurData['telephone_prefix'];
            $num = $promoteurData['telephone'];
            $text = 'Votre code de connexion est: ' . $otp_code;
            $succes = Sms::sendSMS($country_code, $num, $text);

            if ($succes) {
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



    public function verifyNpi(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required|string|max:10|exists:promoteurs,npi',
            ], [
                'npi.required' => 'Le numéro NPI est requis',
                'npi.integer' => 'Le numéro NPI doit être un entier',
                'npi.exists' => 'Ce numéro NPI n\'est pas enregistré, veuillez vous inscrire',
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
}
