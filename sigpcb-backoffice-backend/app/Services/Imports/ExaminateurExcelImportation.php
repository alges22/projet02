<?php

namespace App\Services\Imports;

use App\Models\User;
use App\Services\Api;
use App\Services\Help;
use App\Models\AnnexeAnatt;
use App\Models\Examinateur;
use Illuminate\Support\Str;
use App\Mail\NewUserWelcome;
use App\Models\Base\Commune;
use App\Services\GetCandidat;
use Illuminate\Support\Carbon;
use App\Models\Base\Departement;
use App\Models\AutoEcole\Licence;
use App\Models\AutoEcole\Agrement;
use App\Models\AutoEcole\Moniteur;
use App\Models\AutoEcole\Vehicule;
use Illuminate\Support\Facades\DB;
use App\Models\AutoEcole\AutoEcole;
use App\Models\AutoEcole\Promoteur;
use App\Models\Base\CategoriePermis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\AutoEcole\OldAutoEcole;
use App\Models\AutoEcole\DemandeLicence;
use App\Models\AutoEcole\DemandeAgrement;
use App\Models\ExaminateurCategoriePermis;
use App\Models\Examinateur\ExaminateurNewI;
use App\Services\Imports\FromExaminateurRow;
use App\Models\Examinateur\DemandeExaminateur;
use App\Services\Exception\ImportationException;
use App\Models\Examinateur\ExaminateurParcourSuivi;

class ExaminateurExcelImportation
{
    public function __construct(private FromExaminateurRow $row, private array $validated)
    {
    }
    /**
     * Faire tout le code d'importation ici
     *
     * @return $this
     */
    public function create()
    {
        /**
         *
         *
         * ----------------------------------------------------------------
         * Ce code est le point d'entrée pour importer toutes les données
         *
         * ----------------------------------------------------------------
         *
         *
         */
        # Récupération par exemple de la dénomination
        try {
            // Appel des méthodes de validation

            $npi = $this->validated['npi'];
            $annexe = $this->validated['annexe'];
            $categoryIds = $this->validated['categorie_permis_ids'];
            $emailExists = $this->validated['email'];


            $fillable = [
                "npi" =>  $this->validated['npi'],
                'email' =>  $this->validated['email'],
                'annexe' => $annexe,
                'categorie_permis_ids' => $categoryIds,
                'num_permis' => trim($this->row->getNumeroPermis()),
            ];



            $emailExists = User::where('email', $this->row->getEmail())->first();
            if ($emailExists) {

                if ($emailExists) {

                    $npi = $emailExists->npi;
                    $id = $emailExists->id;
                    $npiNew = $npi;
                    if($npi != $npiNew){
                        throw new ImportationException("Ce email existe déjà en tant que agent ANaTT : '{$this->row->getEmail()}' et le numéro npi envoyé n'est pas conforme a celui existant dans la base de données, veuillez corriger");
                    }

                    $examinateurExist = Examinateur::where('user_id', $id)->first();
                    if ($examinateurExist) {
                        throw new ImportationException("Ce npi existe déjà en tant que examinateur ANaTT : '{$this->row->getNpi()}'");
                    }

                    $userAuth = Auth::user();

                    if (!$userAuth) {
                        throw new ImportationException("Vous devez être connecté pour effectuer cette action.");
                    }

                    $agent_id = $userAuth->id;
                    // creer dans examinateur
                    $createExaminateur = [
                        'user_id' => $id,
                        'annexe_anatt_id' => $annexe->id,
                        'agent_id' => $agent_id,
                    ];

                    $examinateur = Examinateur::create($createExaminateur);
                    $examinateur_id = $examinateur->id;
                    // Transforme la chaîne CSV en un tableau
                    $categorie_permis_ids_array = $categoryIds ;

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
                    return ;
                }
            }

            $responseP = Api::base('GET', "candidats/" . $this->row->getNpi());
            // Vérifier la réponse de l'API externe
            if (!$responseP->successful()) {
                throw new ImportationException("Le numéro NPI de l'examinateur n'existe pas chez l'ANIP.");
            }
            $npiExists = User::where('npi', trim($this->row->getNpi()))->first();
            if ($npiExists) {
                $id = $npiExists->id;
                $examinateurExist = Examinateur::where('user_id', $id)->first();
                if ($examinateurExist) {
                    throw new ImportationException("Ce npi existe déjà en tant que examinateur ANaTT : '{$this->row->getNpi()}'");
                }

                $userAuth = Auth::user();

                if (!$userAuth) {
                    throw new ImportationException("Vous devez être connecté pour effectuer cette action.");
                }

                $agent_id = $userAuth->id;
                // creer dans examinateur
                $createExaminateur = [
                    'user_id' => $id,
                    'annexe_anatt_id' => $annexe->id,
                    'agent_id' => $agent_id,
                ];

                $examinateur = Examinateur::create($createExaminateur);
                $examinateur_id = $examinateur->id;
                // Extrait les catégories permis de la chaîne CSV
                $categorie_permis_ids_csv = $categoryIds;

                // Transforme la chaîne CSV en un tableau
                $categorie_permis_ids_array = $categoryIds;

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
                return ;
            }

            $npiMetierExists = ExaminateurNewI::where('npi', $this->row->getNpi())->first();
            if ($npiMetierExists) {
                throw new ImportationException("Ce npi existe déjà en tant que examinateur sur metier : '{$this->row->getNpi()}'");
            }

            $userNewI = ExaminateurNewI::create([
                'npi' => $this->row->getNpi(),
                'email' => $this->row->getEmail(),
            ]);
            $categoryIdsJson = json_encode($categoryIds);
            $demande = DemandeExaminateur::create([
                'npi' => $this->row->getNpi(),
                'email' => $this->row->getEmail(),
                'num_permis' => $this->row->getNumeroPermis(),
                'categorie_permis_ids' => $categoryIdsJson,
                'permis_file' => '...',
                'user_id' => $userNewI->id,
                'annexe_anatt_id' => $annexe->id,
                'state' => 'init',
            ]);

            $parcoursSuiviData = [
                'npi' => $this->row->getNpi(),
                'slug' => 'demande-examinateur',
                'service' => 'Examinateur',
                'candidat_id' => $userNewI->id,
                'message' => "Votre demande a été soumise avec succès et est en cours de traitement par l'ANaTT.",
                'date_action' => now(),
            ];
            $parcoursSuivi = ExaminateurParcourSuivi::create($parcoursSuiviData);

            // Utilisation de firstOrFail pour s'assurer que la demande existe
            $da = DemandeExaminateur::findOrFail($demande->id);
            $email = $da->email;
            $npi = $da->npi;

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
                throw new ImportationException("Ce numéro npi n'existe pas");
            }

            $data = [
                'first_name' => data_get($candidat, 'prenoms'),
                'last_name' => data_get($candidat, 'nom'),
                'phone' => data_get($candidat, 'telephone'),
                'status' => true,
                'email' => $email,
                'unite_admin_id' => 1,
                'password' => $password_hash,
                'npi' => $npi,
            ];

            $user = User::create($data);

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

            $userAuth = Auth::user();

            if (!$userAuth) {
                throw new ImportationException("Vous devez être connecté pour effectuer cette action.");
            }

            $agent_id = $userAuth->id;
            // creer dans examinateur
            $createExaminateur = [
                'user_id' => $user->id,
                'annexe_anatt_id' => $da->annexe_anatt_id,
                'agent_id' => $agent_id,
            ];

            $examinateur = Examinateur::create($createExaminateur);
            $examinateur_id = $examinateur->id;
            // Nettoyer chaque élément du tableau en supprimant les espaces et les guillemets
            $categorie_permis_ids_array = $categoryIds;

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

        } catch (\Throwable $th) {
            throw $th;
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

}
