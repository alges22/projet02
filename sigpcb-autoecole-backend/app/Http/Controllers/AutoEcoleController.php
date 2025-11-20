<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Services\Help;
use App\Models\Moniteur;
use App\Models\AutoEcole;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;
use App\Models\AutoEcoleInfo;
use App\Models\AutoEcoleInfoRejet;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AutoEcoleController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/anatt-autoecole/auto-ecoles",
     *     operationId="getAllAutoEcoles",
     *     tags={"AutoEcoles"},
     *     summary="Récupérer la liste des auto-ecoles",
     *     description="Récupère une liste de tous les auto-ecoles enregistrés dans la base de données",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des auto-ecoles récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'auto ecole",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="Email de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="adresse",
     *                      description="Adresse de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="promoteur_name",
     *                      description="le nom du promoteur",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="promoteur_phone",
     *                      description="le numéro de téléphone du promoteur",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="Password de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="commune_id",
     *                      description="ID de la commune associée à l'auto ecole",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="Phone de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="numero_autorisation",
     *                      description="Numero autorisation de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="annee_creation",
     *                      description="Année de création de l'auto ecole",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="num_ifu",
     *                      description="Numéro IFU de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="fichier_ifu",
     *                      description="Fichier IFU de l'auto ecole",
     *                      type="file"
     *                  ),
     *                  @OA\Property(
     *                      property="fichier_rccm",
     *                      description="Fichier RCCM de l'auto ecole",
     *                      type="file"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut de l'auto ecole (optionnel)",
     *                      type="boolean"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $params = request()->all();
        return $this->exprotFromBase("auto-ecoles", $params);
    }



    public function autoEcole()
    {
        try {
            if (!auth()->check()) {
                return $this->errorResponse("Vous devez être connecté (e) en tant que promoteur pour continuer", statuscode: 403);
            }

            $ae = Help::authAutoEcole();
            if (!$ae) {
                return $this->errorResponse("Un problème est survenu avec votre session, veuillez-vous réactualiser la page voir", statuscode: 403);
            }
            $ae->load(['agrement']);
            $ae->lastLicence();

            $npis = Moniteur::where('auto_ecole_id', $ae->id)->where('active', true)->get()->map(fn ($m) => $m->npi)->all();

            $moniteurs = GetCandidat::get($npis);

            $ae->setAttribute('monitors', $moniteurs);

            return $this->successResponse($ae);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue');
        }
    }

    public function updateAutoEcole(Request $request)
    {
        if (!auth()->check()) {
            return $this->errorResponse("Vous devez être connecté (e) en tant que promoteur pour continuer", statuscode: 403);
        }

        try {
            $ae = Help::authAutoEcole();
            if (!$ae) {
                return $this->errorResponse("Un problème est survenu avec votre session, veuillez-vous réactualiser la page voir", statuscode: 403);
            }
            $v = Validator::make($request->all(), [
                "name" => ['required', 'min:2', Rule::unique('auto_ecoles')->ignore($ae->id)],
                'departement_id' => "required|integer",
                "commune_id" => "required|integer",
                "email" => "required|email",
                'phone' => "required|max_digits:13",
                "adresse" => "required",
                "moniteurs" => "required"
            ]);

            if ($v->fails()) {
                return $this->errorResponse("La validation a échoué", $v->errors(), statuscode: 422);
            }
            $data = $v->validated();

            $data['auto_ecole_id'] = $ae->id;
            $data['num_ifu'] = $ae->num_ifu;

            $info = AutoEcoleInfo::create($data);

            /**
             * @var User
             */
            $promoteur = auth()->user();
            Help::historique(
                'information',
                "Demande de mise à jour de: \"{$ae->name}\"",
                'information-update-init',
                $message = "La demande de mise à jour des informations de votre auto-école {$ae->name} a été soumise avec succès, et est en attente de validation par l'ANaTT",
                $promoteur,
                $info
            );


            return $this->successResponse($info);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour');
        }
    }


    public function rejets($rejetId)
    {
        try {
            if (!auth()->check()) {
                return $this->errorResponse("Vous n'avez pas les autorisations nécessaire pour accéder à cette page", statuscode: 403);
            }
            $demandeRejet =  AutoEcoleInfoRejet::find($rejetId);

            if (!$demandeRejet) {
                return $this->errorResponse("Ce rejet de mise à jour des informations est introuvable", statuscode: 404);
            }
            /**
             * @var \App\Models\AutoEcoleInfo $demande
             */
            $demande = $demandeRejet->autoEcoleInfo;
            $moniteurs = GetCandidat::get(json_decode($demande->moniteurs, true));
            $demande->setAttribute('monitors', $moniteurs);
            return $this->successResponse($demande);
        } catch (\Throwable $th) {
            logger()->error($th);

            return $this->errorResponse("Une erreur s'est produite lors de la récupération du rejet");
        }
    }

    public function updateRejets(Request $request, $rejetId)
    {
        if (!auth()->check()) {
            return $this->errorResponse("Vous devez être connecté (e) en tant que promoteur pour continuer", statuscode: 403);
        }

        try {
            $ae = Help::authAutoEcole();
            if (!$ae) {
                return $this->errorResponse("Un problème est survenu avec votre session, veuillez-vous réactualiser la page voir", statuscode: 403);
            }

            $v = Validator::make($request->all(), [
                "name" => ['required', 'min:2', Rule::unique('auto_ecoles')->ignore($ae->id)],
                'departement_id' => "required|integer",
                "commune_id" => "required|integer",
                "email" => "required|email",
                'phone' => "required|max_digits:13",
                "adresse" => "required",
                "moniteurs" => "required"
            ]);

            if ($v->fails()) {
                return $this->errorResponse("La validation a échoué", $v->errors(), statuscode: 422);
            }

            $demandeRejet =  AutoEcoleInfoRejet::find($rejetId);
            if (!$demandeRejet) {
                return $this->errorResponse("Ce rejet de mise à jour des informations est introuvable", statuscode: 404);
            }

            $demandeRejet->update([
                'date_correction' => now(),
                'state' => "pending",
            ]);
            /**
             * @var \App\Models\AutoEcoleInfo $info
             */
            $info = $demandeRejet->autoEcoleInfo;
            $data = $v->validated();

            $data['state'] = "pending";

            $info->update($data);

            /**
             * @var User
             */
            $promoteur = auth()->user();
            Help::historique(
                'information',
                "Rejet de mise à jour de: \"{$ae->name}\" corrigée",
                'information-update-pending',
                $message = "Votre correction de la demande de mise à jour des informations de votre auto-école {$ae->name} a été soumise avec succès, et est en attente de validation par l'ANaTT",
                $promoteur,
                $info
            );


            return $this->successResponse($info);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue lors de la mise à jour');
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/api/anatt-autoecole/auto-ecoles/{id}",
     *      operationId="getAutoEcolesById",
     *      tags={"AutoEcoles"},
     *      summary="Récupère un auto ecole par ID",
     *      description="Récupère un auto ecole enregistré dans la base de données en spécifiant son ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'auto ecole à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="AutoEcole récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="AutoEcole non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            try {
                $auto_ecole = AutoEcole::findOrFail($id);
            } catch (ModelNotFoundException $exception) {
                return $this->errorResponse('L\'auto-école demandée est introuvable');
            }
            return $this->successResponse($auto_ecole);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue');
        }
    }


    /**
     * @OA\Get(
     *      path="/api/anatt-autoecole/auto-ecole/{code}",
     *      operationId="getAutoEcoleByCode",
     *      tags={"AutoEcoles"},
     *      summary="Récupère une auto-école par son code",
     *      description="Récupère une auto-école enregistrée dans la base de données en spécifiant son code",
     *      @OA\Parameter(
     *          name="code",
     *          description="Code de l'auto-école à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Auto-école récupérée avec succès"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Auto-école non trouvée"
     *      )
     * )
     */
    public function showByCode($code)
    {
        try {
            $auto_ecole = AutoEcole::where('code', $code)->first();

            if (!$auto_ecole) {
                return $this->errorResponse('L\'auto-école demandée est introuvable', 404);
            }

            return $this->successResponse($auto_ecole);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue', 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/anatt-autoecole/users/profiles",
     *     operationId="getAutoEcolesProfile",
     *     tags={"AutoEcoles"},
     *     summary="Récupérer le profil de l'auto-ecole connecté",
     *     description="Récupère les informaions de l'auto école connecté",
     *     @OA\Response(
     *         response="200",
     *         description="La liste des auto-ecoles récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'auto ecole",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Nom de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="Email de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="adresse",
     *                      description="Adresse de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="promoteur_name",
     *                      description="le nom du promoteur",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="promoteur_phone",
     *                      description="le numéro de téléphone du promoteur",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="Password de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="commune_id",
     *                      description="ID de la commune associée à l'auto ecole",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="Phone de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="numero_autorisation",
     *                      description="Numero autorisation de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="annee_creation",
     *                      description="Année de création de l'auto ecole",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="num_ifu",
     *                      description="Numéro IFU de l'auto ecole",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="fichier_ifu",
     *                      description="Fichier IFU de l'auto ecole",
     *                      type="file"
     *                  ),
     *                  @OA\Property(
     *                      property="fichier_rccm",
     *                      description="Fichier RCCM de l'auto ecole",
     *                      type="file"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Statut de l'auto ecole (optionnel)",
     *                      type="boolean"
     *                  )
     *              )
     *         )
     *     )
     * )
     */
    public function getProfil()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                $user = Help::moniteurAuth();
                $ae = $user->autoEcole->annexe();
                $ae->lastLicence();
                $user['auto_ecoles'] = [$ae];
                unset($user['auto_ecole']);
            } else {
                $aes = AutoEcole::where('promoteur_id', $user->id)->get();

                $user['auto_ecoles'] = $aes->map(function (AutoEcole $ae) {
                    $ae->lastLicence();
                    $ae->annexe();
                    return $ae;
                });
            }

            if (!$user) {
                return $this->errorResponse('Vous devez être connecté(e) pour effectuer cette action.', null, null, 422);
            }
            return $this->successResponse($user);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue', 500);
        }
    }

    public function monitoringAes(Request $request)
    {
        $request->validate([
            "npi" => "required|digits:10"
        ]);
        $npi = $request->npi;
        $aes = collect();
        $promoteur = User::whereNpi($npi)->first();
        if ($promoteur) {
            $aes = AutoEcole::with(['agrement'])->where('promoteur_id', $promoteur->id)->get();
        }

        $moniteur = Moniteur::where([
            "npi" => $npi,
            "active" => true
        ])->latest()->first();
        if ($moniteur) {
            $ae = $moniteur->autoEcole;
            $ae->load('agrement');
            if ($ae) {
                if (!$aes->where('id', $ae->id)->first()) {
                    $aes->push($ae);
                }
            }
        }

        $aes = $aes->map(function (AutoEcole $ae) use ($promoteur) {
            $ae->lastLicence();
            $ae->annexe();
            $ae->setAttribute("is_owner", $ae->promoteur_id == $promoteur->id);
            return $ae;
        });
        return $this->successResponse($aes);
    }
}
