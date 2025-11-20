<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Mail\UserUpdated;
use Illuminate\Support\Str;
use App\Mail\NewUserWelcome;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Mail\Transport\TransportException;

class UserController extends ApiController
{

    /**
     * @OA\Get(
     *     path="/api/anatt-admin/users",
     *     operationId="getAllUsers",
     *     tags={"Users"},
     *     summary="Récupérer la liste des users",
     *     description="Récupère une liste de tous les users enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des users récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'user",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="first_name",
     *                      description="Prenoms de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      description="Nom de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="Phone de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="titre_id",
     *                      description="ID du titre de l'user",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="Email de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="Password de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="unite_admin_id",
     *                      description="ID de l'unite admin de l'user",
     *                      type="integer"
     *                  ),
     *              )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $this->hasAnyPermission(["all","read-admin","edit-admin"]);
        try {
            $query = User::with('roles.permissions');

            if ($request->has('search')) {
                $searchTerm = '%' . $request->input('search') . '%';
                $query->where(function ($query) use ($searchTerm) {
                    $query->whereRaw('LOWER(last_name) LIKE ?', [strtolower($searchTerm)])
                        ->orWhereRaw('LOWER(first_name) LIKE ?', [strtolower($searchTerm)])
                        ->orWhereRaw('LOWER(email) LIKE ?', [strtolower($searchTerm)]);
                });
            }

            if (request('liste') == 'paginate') {
                $users = $query->orderByDesc('id')->paginate(10);
            } else {
                $users = $query->orderByDesc('id')->get();
            }

            if ($users->isEmpty()) {
                return $this->successResponse([], "Aucun résultat trouvé", 200);
            }

            return $this->successResponse($users);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite", null, null, 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/anatt-admin/users/getall",
     *     operationId="getAllUsersP",
     *     tags={"Users"},
     *     summary="Récupérer la liste des users sans pagination",
     *     description="Récupère une liste de tous les users enregistrés dans la base de données sans pagination",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="La liste des users récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'user",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="first_name",
     *                      description="Prenoms de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      description="Nom de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="Phone de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="titre_id",
     *                      description="ID du titre de l'user",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="Email de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="Password de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="unite_admin_id",
     *                      description="ID de l'unite admin de l'user",
     *                      type="integer"
     *                  ),
     *              )
     *         )
     *     )
     * )
     */

    public function getAll()
    {
        $this->hasAnyPermission(["all","read-admin","edit-admin"]);

        try {
            $users = User::orderBy('id', 'desc')->get();
            return $this->successResponse($users);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }


    /**
     * @OA\Get(
     *     path="/api/anatt-admin/users/profiles",
     *     operationId="getAuthUsers",
     *     tags={"Users"},
     *     summary="Récupérer les informations de l'utilisateur connecté",
     *     description="Récupère les informations de l'utilisateur connecté enregistrés dans la base de données",
     *     security={{"api_key":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="les informations de l'utilisateur connecté récupéré avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="ID de l'user",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="first_name",
     *                      description="Prenoms de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      description="Nom de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="Phone de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="titre_id",
     *                      description="ID du titre de l'user",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="Email de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="Password de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="unite_admin_id",
     *                      description="ID de l'unite admin de l'user",
     *                      type="integer"
     *                  ),
     *              )
     *         )
     *     )
     * )
     */
    public function getUser(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Utilisateur non authentifié', 401);
            }

            // Charger la relation roles.permissions
            $user->load('roles.permissions');

            // Vérifier si l'utilisateur est un inspecteur
            if ($user->isInspecteur()) {
                // Récupérer les informations de l'inspecteur et de l'annexe
                $inspecteurInfo = $user->inspecteurInfo();
                $user->inspecteur_info = $inspecteurInfo; // Ajouter les informations à l'utilisateur
            }

            return $this->successResponse($user);
        } catch (\Exception $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération des informations de l\'utilisateur', 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/users",
     *      operationId="createUsers",
     *      tags={"Users"},
     *      summary="Crée un nouveau user",
     *      description="Crée un nouveau user enregistré dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="first_name",
     *                      description="Prenoms de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      description="Nom de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="Phone de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="titre_id",
     *                      description="ID du titre de l'user",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="Email de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="role_id",
     *                      description="L'id du rôle de l'utilisateur",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="unite_admin_id",
     *                      description="ID de l'unite admin de l'user",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="le statut de l'user",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Nouveau user créé"
     *      )
     * )
     */
    public function store(Request $request)
    {
        $this->hasAnyPermission(["all","edit-admin"]);

        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'npi' => 'required',
                'status' => 'required|boolean',
                'role_id' => 'required',
                'titre_id' => [
                    'required',
                    'exists:titres,id'
                ],
                'email' => 'required|email|unique:users,email',
                'unite_admin_id' => [
                    'required',
                    'integer',
                    'exists:unite_admins,id'
                ]
            ], [

                'status.required' => 'Le champ statut est obligatoire',
                'role_id.required' => 'Le champ rôle est obligatoire',
                'titre_id.required' => 'Le champ titre est obligatoire',
                'email.required' => 'Le champ email est obligatoire',
                'email.email' => 'Le champ email n\'est pas un email valide',
                'email.unique' => 'Cet email est déjà utilisé',
                'unite_admin_id.required' => 'Le champ unité administrateur est obligatoire',
                'unite_admin_id.integer' => 'Le champ unité administrateur doit être un entier',
                'titre_id.exists' => 'Le titre sélectionné n\'existe.',
                'unite_admin_id.exists' => 'L\'unité administrative sélectionné n\'existe.'

            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), '', 422);
            }
            $userExist = User::where('npi', $request->get('npi'))->first();
            if ($userExist) {
                return $this->errorResponse("Ce numéro npi est déjà pris ", 422);
            }

            $candidat = GetCandidat::findOne($request->npi);
            if (!$candidat) {
                return $this->errorResponse("Ce numéro npi n'existe pas", 422);
            }

            $data = $validator->validated();
            $data['first_name'] = data_get($candidat, 'prenoms');
            $data['last_name'] = data_get($candidat, 'nom');
            $data['phone'] = data_get($candidat, 'telephone');

            // Définir les critères de sécurité du mot de passe
            $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

            do {
                // Générer un mot de passe aléatoire
                $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@$!%*?&'), 0, 8);
            } while (!preg_match($regex, $password));

            // Hasher le mot de passe
            $password_hash = Hash::make($password);

            $data['password'] = $password_hash;

            $rolename = $request->get('role_id');
            $user = User::with('roles.permissions')->create($data);
            $user->assignRole($rolename);
            $user = $user->fresh('roles.permissions');

            $mail = new NewUserWelcome($user, $password);
            $mail->subject('Création de nouveau compte');

            Mail::to($user->email)->send($mail);
            DB::commit();
            return $this->successResponse($user, statuscode: 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            // lorsque le mail n'est pas envoyé
            $type = get_class($e);
            if ($type == "Symfony\Component\Mailer\Exception\TransportException") {
                // le message d'erreur est géré par le front
                return $this->errorResponse("ErrorMail", [], $user, 500);
            }
            logger()->error($e);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-admin/users/mail/{id}",
     *      operationId="sendMailByUserId",
     *      tags={"Users"},
     *      summary="Envoie de mail user à un user",
     *      description="Envoyer un mail de génération de password un user",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'user",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Mail envoyé avec succès"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="User non trouvé"
     *      )
     * )
     */
    public function sendMailByUserId($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->errorResponse('Agent introuvable', [], null, 422);
            }
            $password = Str::random(8);
            $password_hash = Hash::make($password);
            $user->update(['password' => $password_hash]);

            $mail = new NewUserWelcome($user, $password);
            $mail->subject('Création de nouveau compte');

            Mail::to($user->email)->send($mail);
            return $this->successResponse($user, statuscode: 201);
        } catch (\Throwable $e) {
            // lorsque le mail n'est pas envoyé
            $type = get_class($e);
            if ($type == "Symfony\Component\Mailer\Exception\TransportException") {
                logger()->error($e);
                // le message d'erreur est géré par le front
                return $this->errorResponse("ErrorMail", [], $user, 500);
            }
            logger()->error($e);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }

    /**
     * @OA\Put(
     *      path="/api/anatt-admin/users/{id}",
     *      operationId="updateUsers",
     *      tags={"Users"},
     *      summary="Met à jour un user existant",
     *      description="Met à jour un user existant dans la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'user à mettre à jour",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="first_name",
     *                      description="Prenoms de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="last_name",
     *                      description="Nom de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="role_id",
     *                      description="l'id du role de l'agent",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      description="Phone de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="titre_id",
     *                      description="ID du titre de l'user",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="Email de l'user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="unite_admin_id",
     *                      description="ID de l'unite admin de l'user",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="le statut de l'user",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User mis à jour avec succès"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="User non trouvé"
     *      )
     * )
     */
    public function update(Request $request, $id)
    {
        $this->hasAnyPermission(["all","edit-admin"]);

        try {
            $user = User::with('roles.permissions')->find($id);
            if (!$user) {
                return $this->errorResponse('Profil non trouvé', [], null, 422);
            }
            $userMail = $user->email;

            $validator = Validator::make($request->all(), [
                'npi' => 'required',
                'role_id' => 'required',
                'status' => 'required|boolean',
                'titre_id' => [
                    'required',
                    'exists:titres,id'
                ],
                'unite_admin_id' => [
                    'required',
                    'integer',
                    'exists:unite_admins,id'
                ],
                'email' => 'email|unique:users,email,' . $id,
            ], [
                'role_id.required' => 'Le rôle est obligatoire',
                'status.required' => 'Le statut est obligatoire',
                'status.boolean' => 'Le statut doit être un booléen',
                'titre_id.required' => 'Le titre est obligatoire',
                'unite_admin_id.required' => 'L\'unité administrative est obligatoire',
                'unite_admin_id.integer' => 'L\'unité administrative doit être un entier',
                'titre_id.exists' => 'Le titre sélectionné n\'existe.',
                'unite_admin_id.exists' => 'L\'unité administrative sélectionné n\'existe.'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors());
            }

            //Recupération des valeurs valides
            $data = $validator->validated();
            $candidat = GetCandidat::findOne($request->npi);
            if (!$candidat) {
                return $this->errorResponse("Ce numéro npi n'existe pas", 422);
            }

            $data['npi'] = $request->npi;
            $data['first_name'] = data_get($candidat, 'prenoms');
            $data['last_name'] = data_get($candidat, 'nom');
            $data['phone'] = data_get($candidat, 'telephone');

            $rolename = $request->get('role_id');
            $user->update($data);
            $user->syncRoles($rolename);
            // Recharger l'utilisateur avec les rôles et les permissions mises à jour
            $user = $user->fresh('roles.permissions');

            // Si l'e-mail a été modifié, envoyez un e-mail à l'ancienne et à la nouvelle adresse e-mail
            if ($request->email !== $userMail) {
                Mail::to($userMail)->send(new UserUpdated($user));
                Mail::to($request->email)->send(new UserUpdated($user));
            } else {
                // Sinon, envoyez simplement un e-mail à l'adresse e-mail actuelle
                Mail::to($user->email)->send(new UserUpdated($user));
            }

            return $this->successResponse($user, 'Agent mise à jour avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite', [], $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *      path="/api/anatt-admin/users/{id}",
     *      operationId="getUsersById",
     *      tags={"Users"},
     *      summary="Récupère un user par ID",
     *      description="Récupère un user enregistré dans la base de données en spécifiant son ID",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'user à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User récupéré avec succès"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="User non trouvé"
     *      )
     * )
     */
    public function show($id)
    {
        try {
            $user = User::with('roles.permissions')->find($id);

            if (!$user) {
                return $this->errorResponse('Agent introuvable', [], null, 422);
            }

            return $this->successResponse($user);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite");
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/anatt-admin/users/{id}",
     *      operationId="deleteUsers",
     *      tags={"Users"},
     *      summary="Supprime un user",
     *      description="Supprime un user de la base de données",
     *      security={{"api_key":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'user à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User supprimé avec succès"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="User non trouvé"
     *      )
     * )
     */
    public function destroy($id)
    {
        $this->hasAnyPermission(["all","edit-admin"]);

        try {
            $user = User::find($id);

            if (!$user) {
                return $this->errorResponse('Agent introuvable', [], null, 422);
            }

            $user->delete();

            return $this->successResponse(['message' => 'Suppression effectuée avec succès']);
        } catch (\Illuminate\Database\QueryException $e) {
            if (($e->getCode() === '23000') | ($e->getCode() === '23503')) {
                return $this->errorResponse("Impossible de supprimer l'utilisateur car il est lié à d'autres entités.");
            }

            logger()->error($e);
            return $this->errorResponse("Une erreur s'est produite lors de la suppression.");
        }
    }


    private function usersExist(string $user_ids)
    {
        $ids = explode(";", $user_ids);
        // Si tous les users exists
        return collect($ids)->every(fn ($id) => User::whereId(intval($id))->exists());
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/users/deletes",
     *      operationId="createUserDeletes",
     *      tags={"Users"},
     *      summary="Suppression multiple d'users",
     *      description="Suppression multiple d'users",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="user_ids",
     *                      description="Les ids des users",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Users supprimés avec succès"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Users non trouvés"
     *      )
     * )
     */
    public function deletes(Request $request)
    {
        $this->hasAnyPermission(["all","edit-admin"]);

        try {

            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|string'
            ], [
                'user_ids.required' => 'Aucun utilisateur n\'a été sélectionné'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), '', 422);
            }

            $user_ids = $request->get('user_ids');

            if (!$this->usersExist($user_ids)) {
                return $this->errorResponse('Vérifiez que tous les utilisateurs existent', $validator->errors());
            }

            $ids = explode(";", $user_ids);
            User::whereIn('id', $ids)->delete();

            return $this->successResponse(['message' => 'Suppressions effectuées avec succès']);
        } catch (\Throwable $th) {
            // Handle error and log it
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la suppresion");
        }
    }

    /**
     * @OA\Post(
     *      path="/api/anatt-admin/users/status",
     *      operationId="createUserStatus",
     *      tags={"Users"},
     *      summary="Désactivation ou activation d'un utilisateur",
     *      description="Désactivation ou activation d'un utilisateur",
     *      security={{"api_key":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *                  @OA\Property(
     *                      property="user_id",
     *                      description="id de l'utilisateur",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="le statut a modifier",
     *                      type="string"
     *                  )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Mise à jour éffectué avec succès"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="l'utilisateur n'a pas été trouvé"
     *      )
     * )
     */
    public function status(Request $request)
    {
        $this->hasAnyPermission(["all","edit-admin"]);

        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'status' => 'required'
            ], [
                'user_id.required' => 'Aucun utilisateur n\'a été sélectionné',
                'status.required' => 'Aucun statut n\'a été envoyé'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), '', 422);
            }

            $user_id = $request->get('user_id');
            $status = $request->get('status');
            if (!$this->usersExist($user_id)) {
                return $this->errorResponse('Vérifiez que l\'utilisateur sélectionné existe', $validator->errors());
            }

            $user = User::where('id', $user_id)->first();
            if ($user->status != $status && $status == false) {
                $user->tokens()->where('name', 'auth')->delete();
            }

            User::where('id', $user_id)->update(['status' => $status]);
            $user = User::findOrFail($user_id); // récupérer l'utilisateur mis à jour
            return $this->successResponse(['user' => $user, 'message' => 'Mise à jour effectué avec succès']);
        } catch (\Throwable $th) {
            // Gérer l'erreur et la journaliser
            logger()->error($th);
            return $this->errorResponse("Une erreur s'est produite lors de la mise à jour");
        }
    }
}
