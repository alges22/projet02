<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\EserviceMail;
use App\Models\AnnexeAnatt;
use App\Models\Examinateur;
use App\Mail\NewUserWelcome;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use App\Models\ExaminateurCategoriePermis;
use App\Models\Examinateur\ExaminateurNewI;
use App\Models\Examinateur\DemandeExaminateur;
use App\Models\Examinateur\DemandeExaminateurRejet;
use App\Models\Examinateur\ExaminateurParcourSuivi;



class DemandeExaminateurController extends ApiController
{
    public function index(Request $request)
    {

        $this->hasAnyPermission(["all", "read-examiner-recruitment","edit-examiner-recruitment"]);

        $query = DemandeExaminateur::orderByDesc('id');

        // Appliquer les filtres
        $query = $this->applyFilters($query);

        $demandes = $query->paginate(10);

        // Obtient les npi distincts des demandes d'agrément
        $npiCollection = $demandes->filter(function ($demande) {
            return !is_null($demande->npi) && $demande->npi !== '';
        })->pluck('npi')->unique();

        // Obtient les candidats en fonction des valeurs de npi
        $candidats = collect(GetCandidat::get($npiCollection->all()));

        // Associe les informations des candidats aux demandes d'agrément
        $demandes->each(function ($demande) use ($candidats) {
            $demandeur = $candidats->where('npi', $demande->npi)->first();
            $demande->demandeur_info = $demandeur;

            // Récupère les informations de AnnexeAnatt
            $annexeAnatt = AnnexeAnatt::find($demande->annexe_anatt_id);

            // Associe les informations de AnnexeAnatt à la demande
            $demande->annexe_anatt_info = $annexeAnatt;
        });

        return $this->successResponse($demandes);
    }


    public function applyFilters($query)
    {
        // Filtre par état (state)
        if ($state = request('state')) {
            $states = explode(',', $state);
            $query->whereIn('state', $states);
        }
        // Filtre par recherche
        if ($search = request('search')) {
            $query->where('npi', 'LIKE', "%$search%");
        }

        return $query;
    }

    public function validateDemande(Request $request)
    {
        $this->hasAnyPermission(["all","edit-examiner-recruitment"]);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|string',
            'role_id' => 'required',
            'titre_id' => 'required|exists:titres,id',
            'unite_admin_id' => 'required|integer|exists:unite_admins,id',
            'demande_examinateur_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échoué', $validator->errors()->toArray(), 422);
        }
        $userAuth = Auth::user();

        if (!$userAuth) {
            return $this->errorResponse('Vous devez être connecté pour effectuer cette action.', null, null, 422);
        }

        $agent_id = $userAuth->id;
        try {
            // Démarrez une transaction de base de données
            DB::beginTransaction();

            // Utilisation de firstOrFail pour s'assurer que la demande existe
            $da = DemandeExaminateur::findOrFail($request->demande_examinateur_id);
            $email = $da->email;
            $npi = $da->npi;
            // Vérifier l'unicité de l'email
            if (User::where('email', $da->email)->exists()) {
                DB::rollBack();
                return $this->errorResponse('Cet email est déjà utilisé par un autre agent ANaTT',null, null, 422);
            }
            if (User::where('npi', $da->npi)->exists()) {
                DB::rollBack();
                return $this->errorResponse('Cet npi est déjà utilisé par un autre agent ANaTT, veuillez simplement le nommer comme examinateur.',null, null, 422);
            }
            $state = 'validate';
            $da->update([
                'state' => $state,
                'date_validation' => now(),
            ]);

            $npi = $da->npi;

            // Utilisation de firstOrFail pour s'assurer que le candidat existe
            $daUser = ExaminateurNewI::where('npi', $npi)->firstOrFail();
            $candidat_id = $daUser->id;

            // Générer un mot de passe aléatoire
            $password = $this->generateRandomPassword();

            // Hasher le mot de passe
            $password_hash = Hash::make($password);

            $candidat = GetCandidat::findOne($npi);
            if (!$candidat) {
                return $this->errorResponse("Ce numéro npi n'existe pas", 422);
            }

            $data = [
                'first_name' => data_get($candidat, 'prenoms'),
                'last_name' => data_get($candidat, 'nom'),
                'phone' => $request->phone,
                'status' => true,
                'role_id' => $request->role_id,
                'titre_id' => $request->titre_id,
                'email' => $email,
                'unite_admin_id' => $request->unite_admin_id,
                'password' => $password_hash,
                'npi' => $npi,
            ];

            $user = User::create($data);
            $user->assignRole($request->role_id);

            $mail = new NewUserWelcome($user, $password);
            $mail->subject('Création de nouveau compte');
            Mail::to($user->email)->send($mail);

            // Notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-examinateur-validate',
                'service' => 'Examinateur',
                'candidat_id' => $candidat_id,
                'message' => 'Votre demande a été validée avec succès.',
                'eservice' => '{"Model":"DemandeExaminateur","id":"' . $da->id . '"}',
                'date_action' => now(),
            ];

            // Créer le parcours suivi
            $parcoursSuivi = ExaminateurParcourSuivi::create($parcoursSuiviData);
            // creer dans examinateur
            $createExaminateur = [
                'user_id' => $user->id,
                'annexe_anatt_id' => $da->annexe_anatt_id,
                'agent_id' => $agent_id,
            ];

            $examinateur = Examinateur::create($createExaminateur);
            $examinateur_id = $examinateur->id;
            // Extrait les catégories permis de la chaîne CSV
            $categorie_permis_ids_csv = $da->categorie_permis_ids;

            // Supprime les guillemets de la chaîne
            $categorie_permis_ids_csv = str_replace('"', '', $categorie_permis_ids_csv);

            // Transforme la chaîne CSV en un tableau
            $categorie_permis_ids_array = explode(',', $categorie_permis_ids_csv);

            // Nettoyer chaque élément du tableau en supprimant les espaces et les guillemets
            $categorie_permis_ids_array = array_map(function($item) {
                return trim($item, " \t\n\r\0\x0B\"");
            }, $categorie_permis_ids_array);

            // Préparez les données pour l'insertion
            $insertData = [];
            foreach ($categorie_permis_ids_array as $categorie_permis_id) {
                $insertData[] = [
                    'examinateur_id' => $examinateur_id,
                    'categorie_permis_id' => $categorie_permis_id,
                ];
            }

            // Insère les données dans la table ExaminateurCategoriePermis
            ExaminateurCategoriePermis::insert($insertData);

            // Confirmer la transaction si tout s'est bien passé
            DB::commit();

            return $this->successResponse($da);
        } catch (\Throwable $th) {
            logger()->error($th);
            DB::rollBack();
            return $this->errorResponse('Une erreur s\'est produite lors de la validation', 500);
        }
    }

    private function generateRandomPassword()
    {
        // Définir les critères de sécurité du mot de passe
        $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

        do {
            // Générer un mot de passe aléatoire
            $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@$!%*?&'), 0, 8);
        } while (!preg_match($regex, $password));

        return $password;
    }




    public function rejectDemande(Request $request)
    {
        $this->hasAnyPermission(["all","edit-examiner-recruitment"]);


        $validator = Validator::make($request->all(), [
            'demande_examinateur_id' => 'required|integer|min:1',
            'consigne' => 'max:5000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), 422);
        }

        try {
            // Utilisation de firstOrFail pour s'assurer que la demande existe
            $da = DemandeExaminateur::findOrFail($request->demande_examinateur_id);

            $npi = $da->npi;

            // Utilisation de firstOrFail pour s'assurer que le candidat existe
            $daUser = ExaminateurNewI::where('npi', $npi)->firstOrFail();
            $candidat_id = $daUser->id;

            $state = 'rejected';
            $da->update([
                'state' => $state,
                'date_rejet' => now(),
            ]);
            // Créer une nouvelle entrée dans la table
            $DemandeExaminateurRejet = new DemandeExaminateurRejet();
            $DemandeExaminateurRejet->demande_examinateur_id = $da->id;
            $DemandeExaminateurRejet->motif = $request->consigne;
            $DemandeExaminateurRejet->state = 'init';
            $DemandeExaminateurRejet->save();

            // Notifier le candidat
            $parcoursSuiviData = [
                'npi' => $npi,
                'slug' => 'demande-examinateur-rejected',
                'service' => 'Examinateur',
                'candidat_id' => $candidat_id,
                'message' => 'Votre demande a été rejetée. Consigne : ' . $request->consigne,
                'eservice' => '{"Model":"DemandeExaminateurRejet","id":"' . $DemandeExaminateurRejet->id . '"}',
                'date_action' => now(),
            ];

            // Créer le parcours suivi
            $parcoursSuivi = ExaminateurParcourSuivi::create($parcoursSuiviData);

            return $this->successResponse($da);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors du rejet', 500);
        }
    }

}
