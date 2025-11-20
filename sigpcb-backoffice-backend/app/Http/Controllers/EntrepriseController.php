<?php

namespace App\Http\Controllers;

use App\Services\Api;
use App\Mail\EserviceMail;
use App\Models\AnnexeAnatt;
use App\Models\Base\Langue;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Mail\NewUserEntreprise;
use Illuminate\Support\Facades\DB;
use App\Models\Base\CategoriePermis;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Examinateur\Entreprise;
use App\Http\Controllers\ApiController;
use App\Models\Examinateur\Recrutement;
use Illuminate\Support\Facades\Validator;
use App\Models\Examinateur\RejetRecrutement;
use App\Models\Examinateur\EntrepriseCandidat;
use App\Models\Examinateur\RecrutementEpreuve;
use App\Models\Examinateur\CandidatConduiteNote;
use App\Models\Examinateur\EntrepriseParcourSuivi;


class EntrepriseController extends ApiController
{
    public function index()
    {
        $this->hasAnyPermission(["all", "read-company-recruitment-management","edit-company-recruitment-management"]);

        try {
            $query = Entreprise::orderBy('id','desc');

            $query = $this->applyFilters($query);
            $entreprises = $query->paginate(10);

            return $this->successResponse($entreprises);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
    public function applyFilters($query)
    {
        // Filtre par recherche
        if ($search = request('search')) {
            $query->where('name', 'LIKE', "%$search%");
        }

        return $query;
    }

    public function getSession()
    {
        $this->hasAnyPermission(["all", "read-company-recruitment-management","edit-company-recruitment-management"]);
    try {
        $query = Recrutement::orderBy('id','desc');

        $query = $this->applySessionFilters($query);
        $recrutements = $query->paginate(10);
        foreach ($recrutements as $recrutement) {
            // Récupérer les IDs de la catégorie de permis et de l'annexe
            $categorie_permis_id = $recrutement->categorie_permis_id;
            $annexe_id = $recrutement->annexe_id;
            $entreprise_id = $recrutement->entreprise_id;

            // Rechercher la correspondance dans les tables CategoriePermis et AnnexeAnatt
            $categorie_permis = CategoriePermis::find($categorie_permis_id);
            $annexe = AnnexeAnatt::find($annexe_id);
            $entreprise = Entreprise::find($entreprise_id);
            $recrutement->categorie_permis=$categorie_permis;
            $recrutement->annexe=$annexe;
            $recrutement->entreprise=$entreprise;
        }

        if ($recrutements->isEmpty()) {
            // Aucun recrutement trouvé
            return $this->successResponse([], 'Aucun recrutement trouvé pour cette entreprise');
        }

            return $this->successResponse($recrutements, 'Liste des recrutements récupérée avec succès');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
    public function applySessionFilters($query)
    {
        // Filtre par état (state)
        if (request()->has('state')) {
            $states = explode(',', request()->get('state'));

            $query = $query->whereIn('state', $states);
        }
        return $query;
    }


    public function getSessionByAnnexe($id)
    {
    $this->hasAnyPermission(["all", "read-company-recruitment-management","edit-company-recruitment-management"]);
    try {
        $query = Recrutement::where('annexe_id',$id)->orderBy('id','desc');

        $query = $this->applyAnnexeFilters($query);
        $recrutements = $query->get();
        foreach ($recrutements as $recrutement) {
            // Récupérer les IDs de la catégorie de permis et de l'annexe
            $categorie_permis_id = $recrutement->categorie_permis_id;
            $annexe_id = $recrutement->annexe_id;
            $entreprise_id = $recrutement->entreprise_id;

            // Rechercher la correspondance dans les tables CategoriePermis et AnnexeAnatt
            $categorie_permis = CategoriePermis::find($categorie_permis_id);
            $annexe = AnnexeAnatt::find($annexe_id);
            $entreprise = Entreprise::find($entreprise_id);
            $recrutement->categorie_permis=$categorie_permis;
            $recrutement->annexe=$annexe;
            $recrutement->entreprise=$entreprise;
        }

        if ($recrutements->isEmpty()) {
            // Aucun recrutement trouvé
            return $this->successResponse([], 'Aucun recrutement trouvé pour cette entreprise');
        }

            return $this->successResponse($recrutements, 'Liste des recrutements récupérée avec succès');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
    public function applyAnnexeFilters($query)
    {
        // Filtre par état (state)
        if (request()->has('state')) {
            $states = explode(',', request()->get('state'));

            $query = $query->whereIn('state', $states);
        }
        return $query;
    }


    public function getRecrutement($id)
    {
    $this->hasAnyPermission(["all", "read-company-recruitment-management","edit-company-recruitment-management"]);

        try {
            $entreprise = Entreprise::find($id);

            if (!$entreprise) {
                return $this->errorResponse('Aucun résultat trouvé', 404);
            }
            $user_id = $id;
            $query = Recrutement::where('entreprise_id', $user_id)->where('finished',true);
            $query->orderByDesc('id');
            $query = $this->applyRecrutementFilters($query);
            $recrutements = $query->paginate(10);

            foreach ($recrutements as $recrutement) {
                // Récupérer les IDs de la catégorie de permis et de l'annexe
                $categorie_permis_id = $recrutement->categorie_permis_id;
                $annexe_id = $recrutement->annexe_id;

                // Rechercher la correspondance dans les tables CategoriePermis et AnnexeAnatt
                $categorie_permis = CategoriePermis::find($categorie_permis_id);
                $annexe = AnnexeAnatt::find($annexe_id);
                $recrutement->categorie_permis=$categorie_permis;
                $recrutement->annexe=$annexe;
            }

            if ($recrutements->isEmpty()) {
                // Aucun recrutement trouvé
                return $this->successResponse([], 'Aucun recrutement trouvé pour cette entreprise');
            }

            return $this->successResponse($recrutements, 'Liste des recrutements récupérée avec succès');
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Erreur serveur', $th->getMessage(), null, 500);
        }
    }

    public function applyRecrutementFilters($query)
    {
        // Filtre par état (state)
        if (request()->has('state')) {
            $states = explode(',', request()->get('state'));

            $query = $query->whereIn('state', $states);
        }

        return $query;
    }
    public function store(Request $request)
    {
    $this->hasAnyPermission(["all","edit-company-recruitment-management"]);

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
            ], [
                'name.required' => 'Le champ name est obligatoire',
                'phone.required' => 'Le champ téléphone est obligatoire',
                'email.required' => 'Le champ email est obligatoire',
                'email.email' => 'Le champ email n\'est pas un email valide',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), '', 422);
            }
            $email =  $request->input('email');
            $existingRecord = Entreprise::where('email', $email)
                                            ->first();
            if ($existingRecord) {
                return $this->errorResponse('Cet email est déjà pris', null, null, 422);
            }
            $data = $validator->validated();

            // Définir les critères de sécurité du mot de passe
            $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

            do {
                // Générer un mot de passe aléatoire
                $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@$!%*?&'), 0, 8);
            } while (!preg_match($regex, $password));

            // Hasher le mot de passe
            $password_hash = Hash::make($password);

            $data['password'] = $password_hash;

            $user = Entreprise::create($data);

            $mail = new NewUserEntreprise($user, $password);
            $mail->subject('Création de nouveau compte');

            Mail::to($user->email)->send($mail);
            return $this->successResponse($user, "Insitution créé avec succès");
        } catch (\Throwable $e) {
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


    public function update(Request $request, $id)
    {
    $this->hasAnyPermission(["all","edit-company-recruitment-management"]);

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
            ], [
                'name.required' => 'Le champ name est obligatoire',
                'phone.required' => 'Le champ téléphone est obligatoire',
                'email.required' => 'Le champ email est obligatoire',
                'email.email' => 'Le champ email n\'est pas un email valide',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation échouée', $validator->errors(), '', 422);
            }

            // Vérifier si l'utilisateur avec l'ID donné existe
            $entreprise = Entreprise::find($id);

            if (!$entreprise) {
                return $this->errorResponse('L\'entreprise avec cet ID n\'existe pas', null, null, 404);
            }

            $data = $validator->validated();

            // Mettre à jour les données de l'entreprise
            $entreprise->update($data);

            // Renvoyer la réponse de réussite
            return $this->successResponse($entreprise, "Entreprise mise à jour avec succès");
        } catch (\Throwable $e) {
            // Gérer les erreurs
            logger()->error($e);
            return $this->errorResponse("Une erreur inattendue s'est produite",500);
        }
    }


    public function startCompo(Request $request)
    {
    $this->hasAnyPermission(["all","edit-company-recruitment-management"]);

        $response = Api::compo("POST", 'recrutements/generate-questions', $request->all());
        # Retrait des informations d'entete
        $message = $response->json("message", "Une erreur est survenue ");
        $data = $response->json('data', null);
        $errors = $response->json('errors', null);
        $statuscode = $response->status();

        # S'il y a une erreur on retourne l'erreur telle quell
        if (!$response->successful()) {
            return $this->errorResponse($message, $errors, $data, $statuscode);
        }

        # On recupère la bonne information
        $data = Api::data($response);

        return $this->successResponse($data, $message, $statuscode);
    }

    public function storeConduiteEpreuve(Request $request)
    {
    $this->hasAnyPermission(["all","edit-company-recruitment-management"]);

        try {
            // Validation des données de la requête
            $validator = Validator::make($request->all(), [
                'recrutement_id' => 'required',
                'name' => 'required|string|max:255',
                'poids' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Échec de la validation', $validator->errors(), 422);
            }

            // Convertir le nom en minuscules pour une comparaison insensible à la casse
            $normalized_name = strtolower($request->name);

            // Vérifier si une épreuve avec le même nom (en minuscules) existe déjà
            $existingEpreuve = RecrutementEpreuve::whereRaw('LOWER(name) = ?', [$normalized_name])->exists();
            if ($existingEpreuve) {
                return $this->errorResponse('Une épreuve avec le même nom existe déjà', null, 422);
            }
            $recrutement = Recrutement::find($request->recrutement_id);

            if (!$recrutement) {
                return $this->errorResponse('La session avec cet ID n\'existe pas', null, null, 404);
            }
            // Récupérer la liste des épreuves existantes pour cette session de recrutement
            $existingEpreuves = RecrutementEpreuve::where('recrutement_id', $request->recrutement_id)->get();

            // Calculer la somme des poids actuellement enregistrés
            $sumOfPoids = $existingEpreuves->sum('poids');

            // Vérifier si l'ajout de poids dépasse 20
            if (($sumOfPoids + $request->poids) > 20) {
                return $this->errorResponse('La somme des poids ne doit pas dépasser 20', null, 422);
            }

            // Créer une nouvelle épreuve de recrutement
            $epreuve = RecrutementEpreuve::create([
                'recrutement_id' => $request->recrutement_id,
                'name' => $request->name,
                'poids' => $request->poids,
            ]);

            // Retourner une réponse de succès avec les données de l'épreuve créée
            return $this->successResponse($epreuve, 'Épreuve de conduite créée avec succès', 201);
        } catch (\Throwable $e) {
            logger()->error($e);
            // Retourner une réponse d'erreur avec un message générique
            return $this->errorResponse('Une erreur s\'est produite lors de la création de l\'épreuve de la conduite', null, 500);
        }
    }

    public function getEpreuvesByRecrutementId($recrutement_id)
    {
    $this->hasAnyPermission(["all", "read-company-recruitment-management","edit-company-recruitment-management"]);

        try {
            $recrutement = Recrutement::find($recrutement_id);

            if (!$recrutement) {
                return $this->errorResponse('La session avec cet ID n\'existe pas', null, null, 404);
            }
            // Récupérer toutes les épreuves de recrutement pour l'ID donné
            $epreuves = RecrutementEpreuve::where('recrutement_id', $recrutement_id)
            ->orderByDesc('id')
            ->get();

            // Retourner une réponse de succès avec les épreuves récupérées
            return $this->successResponse($epreuves);
        } catch (\Throwable $e) {
            // Logguer l'erreur
            logger()->error($e);
            // Retourner une réponse d'erreur avec un message générique
            return $this->errorResponse('Une erreur s\'est produite lors de la récupération des épreuves de recrutement', null, 500);
        }
    }

    public function showEpreuve($id)
    {
    $this->hasAnyPermission(["all", "read-company-recruitment-management","edit-company-recruitment-management"]);

        try {
            // Récupérer l'épreuve de recrutement par son ID
            $epreuve = RecrutementEpreuve::findOrFail($id);

            // Retourner une réponse de succès avec l'épreuve récupérée
            return $this->successResponse($epreuve);
        } catch (\Throwable $e) {
            // Logguer l'erreur
            logger()->error($e);
            // Retourner une réponse d'erreur avec un message générique
            return $this->errorResponse('Épreuve de recrutement non trouvée', null, 404);
        }
    }

    public function updateEpreuve(Request $request, $id)
    {
    $this->hasAnyPermission(["all","edit-company-recruitment-management"]);

        try {
            // Validation des données de la requête
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255',
                'poids' => 'integer',
            ]);

            // Vérifier si la validation a échoué
            if ($validator->fails()) {
                return $this->errorResponse('Échec de la validation', $validator->errors(), 422);
            }

            // Récupérer l'épreuve de recrutement par son ID
            $epreuve = RecrutementEpreuve::findOrFail($id);

            if ($request->has('name')) {
                $newName = $request->name;
                $existingEpreuve = RecrutementEpreuve::where('id', '<>', $id)
                    ->whereRaw('LOWER(name) = LOWER(?)', [$newName])
                    ->exists();

                if ($existingEpreuve) {
                    return $this->errorResponse('Une épreuve de recrutement avec le même nom existe déjà', null, 422);
                }
            }

            // Mettre à jour les données de l'épreuve
            if ($request->has('name')) {
                $epreuve->name = $request->name;
            }
            if ($request->has('poids')) {
                $epreuve->poids = $request->poids;
            }
            $epreuve->save();

            // Retourner une réponse de succès avec l'épreuve mise à jour
            return $this->successResponse($epreuve, 'Épreuve de recrutement mise à jour avec succès');
        } catch (\Throwable $e) {
            // Logguer l'erreur
            logger()->error($e);
            // Retourner une réponse d'erreur avec un message générique
            return $this->errorResponse('Une erreur s\'est produite lors de la mise à jour de l\'épreuve de recrutement', null, 500);
        }
    }

    public function candidatConduiteNote(Request $request)
    {
    $this->hasAnyPermission(["all","edit-company-recruitment-management"]);

        try {
            // Validation des données de la requête
            $validator = Validator::make($request->all(), [
                'recrutement_id' => 'required',
                'id' => 'required',
                'epreuves' => 'required|array',
                'epreuves.*.recrutement_epreuve_id' => 'required',
                'epreuves.*.note' => 'required|numeric|between:0,99.99',
                'npi' => 'required|string|max:255',
            ]);

            // Vérifier si la validation a échoué
            if ($validator->fails()) {
                return $this->errorResponse('Échec de la validation', $validator->errors(), 422);
            }

            $recrutement = Recrutement::find($request->recrutement_id);
            if (!$recrutement) {
                return $this->errorResponse('La session avec cet ID n\'existe pas', null, null, 422);
            }
            $candidat = EntrepriseCandidat::find($request->id);
            if (!$candidat) {
                return $this->errorResponse('Le candidat avec cet ID n\'existe pas', null, null, 422);
            }

            // Récupérer l'ID de l'examinateur connecté
            $examinateur_id = auth()->id();
            // Initialiser la somme des notes
            $sumOfNotes = 0;
            $sumOfPoids = 0;
            // Parcourir chaque épreuve
            foreach ($request->epreuves as $epreuveData) {
                $recrutementEpreuve = RecrutementEpreuve::find($epreuveData['recrutement_epreuve_id']);
                $poids = $recrutementEpreuve->poids;
                if ($epreuveData['note'] > $poids) {
                    return $this->errorResponse('La note ne peut pas dépasser le total de ' . $poids, null, null, 422);
                }
                if (!isset($epreuveData['note']) || $epreuveData['note'] === null) {
                    return $this->errorResponse('La valeur de la note ne peut pas être nulle', null, 422);
                }

            }
            foreach ($request->epreuves as $epreuveData) {
                $recrutementEpreuve = RecrutementEpreuve::find($epreuveData['recrutement_epreuve_id']);
                if (!$recrutementEpreuve || $recrutementEpreuve->recrutement_id !== $request->recrutement_id) {
                    return $this->errorResponse('L\'épreuve ne correspond pas à cette session', null, null, 422);
                }

                $poids = $recrutementEpreuve->poids;
                if ($epreuveData['note'] > $poids) {
                    return $this->errorResponse('La note ne peut pas dépasser le total de ' . $poids, null, null, 422);
                }
                if (!isset($epreuveData['note']) || $epreuveData['note'] === null) {
                    return $this->errorResponse('La valeur de la note ne peut pas être nulle', null, 422);
                }
                // Ajouter la note à la somme
                $sumOfNotes += $epreuveData['note'];
                $sumOfPoids += $poids;
                // Vérifier l'existence du couple dans la table CandidatConduiteNote
                $exists = CandidatConduiteNote::where([
                    'recrutement_id' => $request->recrutement_id,
                    'recrutement_epreuve_id' => $epreuveData['recrutement_epreuve_id'],
                    'candidat_npi' => $request->npi,
                ])->exists();

                // Si le couple existe déjà, renvoyer un message d'erreur
                if ($exists) {
                    return $this->errorResponse('Une note existe déjà pour ce candidat pour cette épreuve', null, 422);
                }

                // Créer l'enregistrement dans la base de données
                $examennote = CandidatConduiteNote::create([
                    'recrutement_id' => $request->recrutement_id,
                    'recrutement_epreuve_id' => $epreuveData['recrutement_epreuve_id'],
                    'candidat_npi' => $request->npi,
                    'examinateur_id' => $examinateur_id,
                    'note' => $epreuveData['note'],
                ]);
            }
            $examennote->notefinale = $sumOfNotes . '/' . $sumOfPoids;

            $code_note = $candidat->code_note;

            // Vérifier si code_note est null, si c'est le cas, utiliser 0 comme valeur
            if ($code_note === null) {
                $code_note = 0;
            }

            $calcul = $code_note + $sumOfNotes;
            $endNote = $calcul / 2;

            $candidat->update([
                'conduite_note' => $sumOfNotes,
                'note_final' => $endNote,
            ]);

            // Retourner une réponse de succès avec l'examen créé
            return $this->successResponse($examennote, 'Note enregistrée avec succès');
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur s\'est produite lors de la création de la note', null, 500);
        }
    }


    public function destroyEpreuve($id)
    {
    $this->hasAnyPermission(["all","edit-company-recruitment-management"]);

        try {
            // Récupérer l'épreuve de recrutement par son ID
            $epreuve = RecrutementEpreuve::findOrFail($id);

            // Vérifier si l'epreuve existe
            if (!$epreuve) {
                return $this->errorResponse('Cette épreuve n\'a pas été trouvée.', [], null, 422);
            }

            // Vérifier s'il y a des recrutements liés à cette entreprise
            $candidatnote = CandidatConduiteNote::where('recrutement_epreuve_id', $id)->get();

            // Si des notation existent, renvoyer un message indiquant que l'épreuve ne peut pas être supprimée actuellement
            if (!$candidatnote->isEmpty()) {
                return $this->errorResponse('Cette épreuve ne peut pas être supprimée actuellement car elle est associée à des candidats.', null, 422);
            }

            // Supprimer l'épreuve de recrutement
            $epreuve->delete();

            // Retourner une réponse de succès avec un message
            return $this->successResponse(null, 'Épreuve de recrutement supprimée avec succès');
        } catch (\Throwable $e) {
            // Logguer l'erreur
            logger()->error($e);
            // Retourner une réponse d'erreur avec un message générique
            return $this->errorResponse('Une erreur s\'est produite lors de la suppression de l\'épreuve de recrutement', null, 500);
        }
    }

    public function sendConvocations(Request $request, $id)
    {
    $this->hasAnyPermission(["all","edit-company-recruitment-management"]);

        try {
            $recrutement = Recrutement::find($id);

            if (!$recrutement) {
                return $this->errorResponse('La session avec cet ID n\'existe pas', null, null, 404);
            }

            $recrutement->update([
                'convocation' => true,
            ]);

            $entreprise_id = $recrutement->entreprise_id;

            $daUser = Entreprise::find($entreprise_id);
            if (!$daUser) {
                return $this->errorResponse('L\'entreprise avec cet ID n\'existe pas', null, null, 404);
            }

            $entreprise_id = $daUser->id;

            $parcoursSuiviData = [
                'slug' => 'send-recrutement-convocation',
                'service' => 'Recrutement',
                'entreprise_id' => $entreprise_id,
                'recrutement_id' => $recrutement->id,
                'eservice' => '{"Model":"Recrutement","id":"' . $recrutement->id . '"}',
                'message' => 'Félicitations ! Les convocations sont disponibles pour cette session.',
                'date_action' => now(),
            ];

            // Créer le parcours suivi
            $parcoursSuivi = EntrepriseParcourSuivi::create($parcoursSuiviData);

            // Renvoyer la réponse de réussite
            return $this->successResponse($recrutement, "Convocations envoyées avec succès");
        } catch (\Throwable $e) {
            // Gérer les erreurs
            logger()->error($e);
            return $this->errorResponse("Une erreur inattendue s'est produite",500);
        }
    }


    public function compoEnd(Request $request, $id)
    {
    $this->hasAnyPermission(["all","edit-company-recruitment-management"]);

        try {
            $recrutement = Recrutement::find($id);

            if (!$recrutement) {
                return $this->errorResponse('La session avec cet ID n\'existe pas', null, null, 404);
            }

            // $consigne = $request->consigne;
            $entreprise_id = $recrutement->entreprise_id;

            $daUser = Entreprise::find($entreprise_id);
            if (!$daUser) {
                return $this->errorResponse('L\'entreprise avec cet ID n\'existe pas', null, null, 404);
            }

            $entreprise_id = $daUser->id;

            $recrutement->update([
                'closed' => true,
            ]);

            // Renvoyer la réponse de réussite
            return $this->successResponse($recrutement, "Composition cloturée avec succès");
        } catch (\Throwable $e) {
            // Gérer les erreurs
            logger()->error($e);
            return $this->errorResponse("Une erreur inattendue s'est produite",500);
        }
    }


    public function sendResultat(Request $request, $id)
    {
    $this->hasAnyPermission(["all","edit-company-recruitment-management"]);

        try {
            $recrutement = Recrutement::find($id);

            if (!$recrutement) {
                return $this->errorResponse('La session avec cet ID n\'existe pas', null, null, 404);
            }

            $entreprise_id = $recrutement->entreprise_id;

            $daUser = Entreprise::find($entreprise_id);
            if (!$daUser) {
                return $this->errorResponse('L\'entreprise avec cet ID n\'existe pas', null, null, 404);
            }

            $entreprise_id = $daUser->id;
            $email = $daUser->email;

            $recrutement->update([
                'resultat' => true,
            ]);

            $parcoursSuiviData = [
                'slug' => 'send-recrutement-resultat',
                'service' => 'Recrutement',
                'entreprise_id' => $entreprise_id,
                'recrutement_id' => $recrutement->id,
                'eservice' => '{"Model":"Recrutement","id":"' . $recrutement->id . '"}',
                'message' => 'Félicitations ! Les résultats sont disponibles pour cette session.',
                'date_action' => now(),
            ];

            // Créer le parcours suivi
            $parcoursSuivi = EntrepriseParcourSuivi::create($parcoursSuiviData);
            $consigne = "Félicitations ! Les résultats sont disponibles pour cette session.";
            Mail::to($email)->send(new EserviceMail($consigne));


            // Renvoyer la réponse de réussite
            return $this->successResponse($recrutement, "Recrutement cloturé avec succès");
        } catch (\Throwable $e) {
            // Gérer les erreurs
            logger()->error($e);
            return $this->errorResponse("Une erreur inattendue s'est produite",500);
        }
    }

    public function destroy($id)
    {
    $this->hasAnyPermission(["all","edit-company-recruitment-management"]);

        try {
            // Rechercher l'entreprise par ID
            $entreprise = Entreprise::find($id);

            // Vérifier si l'entreprise existe
            if (!$entreprise) {
                return $this->errorResponse('Cette entreprise n\'a pas été trouvée.', [], null, 422);
            }

            // Vérifier s'il y a des recrutements liés à cette entreprise
            $recrutements = Recrutement::where('entreprise_id', $id)->get();

            // Si des recrutements existent, renvoyer un message indiquant que l'entreprise ne peut pas être supprimée actuellement
            if (!$recrutements->isEmpty()) {
                return $this->errorResponse('Cette entreprise ne peut pas être supprimée actuellement car elle est associée à des recrutements.', null, 422);
            }

            // Supprimer l'entreprise si elle existe et n'a pas de recrutements associés
            $entreprise->delete();

            return $this->successResponse($entreprise, 'L\'entreprise a été supprimée avec succès.');
        } catch (\Throwable $th) {
            // Gérer les erreurs, enregistrer l'erreur dans les journaux et renvoyer une réponse d'erreur générique
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }
    public function candidatByRecrutement($id)
    {
    $this->hasAnyPermission(["all", "read-company-recruitment-management","edit-company-recruitment-management"]);

        try {
            $recrutement = Recrutement::find($id);

            if (!$recrutement) {
                return $this->errorResponse('Aucun résultat trouvé', [], 404);
            }

            $query = EntrepriseCandidat::where('recrutement_id', $id)->orderByDesc('id');
            $query = $this->applyCandidatFilters($query);
            $candidats = $query->paginate(10);

            // Obtient les npi distincts
            $npiCollection = $candidats->filter(function ($candidat) {
                return !is_null($candidat->npi) && $candidat->npi !== '';
            })->pluck('npi')->unique();

            // Obtient les candidats en fonction des valeurs de npi
            $candidatsInfo = collect(GetCandidat::get($npiCollection->all()));

            // Associe les informations des candidats aux demandes d'agrément
            $candidats->each(function ($candidat) use ($candidatsInfo) {
                $info = $candidatsInfo->where('npi', $candidat->npi)->first();
                $candidat->candidat_info = $info;
            });
            $candidats->each(function ($candidat) {
                $langueId = $candidat->langue_id;
                $langue = Langue::find($langueId);
                $candidat->langue = $langue;
            });

            return $this->successResponse($candidats);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération de la session.', $e->getMessage(), 500);
        }
    }
    public function applyCandidatFilters($query)
    {
        // Filtre par état (state)
        if (request()->has('state')) {
            $states = explode(',', request()->get('state'));

            $query = $query->whereIn('state', $states);
        }
        // Filtre par recherche
        if ($search = request('search')) {
            $query->where('npi', 'LIKE', "%$search%");
        }
        return $query;
    }
    public function resultatByRecrutement($id)
    {
    $this->hasAnyPermission(["all", "read-company-recruitment-management","edit-company-recruitment-management"]);

        try {
            $recrutement = Recrutement::find($id);

            if (!$recrutement) {
                return $this->errorResponse('Aucun résultat trouvé', [], 404);
            }

            if ($recrutement->closed !== true) {
                return $this->errorResponse('Cette session n\'est pas encore cloturé', [], 422);
            }
            $query = EntrepriseCandidat::where('recrutement_id', $id)->orderByDesc('note_final');
            $query = $this->applyResultatFilters($query);
            $candidats = $query->get();

            // Obtient les npi distincts
            $npiCollection = $candidats->filter(function ($candidat) {
                return !is_null($candidat->npi) && $candidat->npi !== '';
            })->pluck('npi')->unique();

            // Obtient les candidats en fonction des valeurs de npi
            $candidatsInfo = collect(GetCandidat::get($npiCollection->all()));

            // Associe les informations des candidats aux demandes d'agrément
            $candidats->each(function ($candidat) use ($candidatsInfo) {
                $info = $candidatsInfo->where('npi', $candidat->npi)->first();
                $candidat->candidat_info = $info;
            });
            $candidats->each(function ($candidat) {
                $langueId = $candidat->langue_id;
                $langue = Langue::find($langueId);
                $candidat->langue = $langue;
            });

            return $this->successResponse($candidats);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération de la session.', $e->getMessage(), 500);
        }
    }



    public function applyResultatFilters($query)
    {
        // Filtre par recherche
        if ($search = request('search')) {
            $query->where('npi', 'LIKE', "%$search%");
        }
        return $query;
    }
    public function candidatsByRecrutement($id)
    {
    $this->hasAnyPermission(["all", "read-company-recruitment-management","edit-company-recruitment-management"]);

        try {
            $recrutement = Recrutement::find($id);

            if (!$recrutement) {
                return $this->errorResponse('Aucun résultat trouvé', [], 404);
            }

            $query = EntrepriseCandidat::where('recrutement_id', $id)->orderByDesc('id');
            $query = $this->applyCandidatFilter($query);
            $candidats = $query->get();

            // Obtient les npi distincts
            $npiCollection = $candidats->filter(function ($candidat) {
                return !is_null($candidat->npi) && $candidat->npi !== '';
            })->pluck('npi')->unique();

            // Obtient les candidats en fonction des valeurs de npi
            $candidatsInfo = collect(GetCandidat::get($npiCollection->all()));

            // Associe les informations des candidats aux demandes d'agrément
            $candidats->each(function ($candidat) use ($candidatsInfo) {
                $info = $candidatsInfo->where('npi', $candidat->npi)->first();
                $candidat->candidat_info = $info;
            });
            $candidats->each(function ($candidat) {
                $langueId = $candidat->langue_id;
                $langue = Langue::find($langueId);
                $candidat->langue = $langue;
            });

            return $this->successResponse($candidats);
        } catch (\Throwable $e) {
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération de la session.', $e->getMessage(), 500);
        }
    }
    public function applyCandidatFilter($query)
    {
        // Filtre par état (state)
        if (request()->has('state')) {
            $states = explode(',', request()->get('state'));

            $query = $query->whereIn('state', $states);
        }
        // Filtre par recherche
        if ($search = request('search')) {
            $query->where('npi', 'LIKE', "%$search%");
        }

        return $query;
    }

    public function validateDemande(Request $request)
    {
    $this->hasAnyPermission(["all","edit-company-recruitment-management"]);

        $validator = Validator::make($request->all(), [
            'recrutement_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), 422);
        }

        try {
            // Démarrez une transaction de base de données
            DB::beginTransaction();

            $da = Recrutement::findOrFail($request->recrutement_id);

            $state = 'validate';
            $da->update([
                'state' => $state,
                'date_validation' => now(),
            ]);

            $entreprise_id = $da->entreprise_id;

            $daUser = Entreprise::findOrFail($entreprise_id);
            $entreprise_id = $daUser->id;
            $email = $daUser->email;

            $parcoursSuiviData = [
                'slug' => 'demande-recrutement-validate',
                'service' => 'Recrutement',
                'entreprise_id' => $entreprise_id,
                'recrutement_id' => $da->id,
                'eservice' => '{"Model":"Recrutement","id":"' . $da->id . '"}',
                'message' => 'Votre demande de recrutement a été validée avec succès.',
                'date_action' => now(),
            ];

            // Créer le parcours suivi
            $parcoursSuivi = EntrepriseParcourSuivi::create($parcoursSuiviData);
            // Confirmez la transaction si tout s'est bien passé
            DB::commit();

            return $this->successResponse($da);
        } catch (\Throwable $th) {
            logger()->error($th);
            DB::rollBack();
            return $this->errorResponseclient('Une erreur s\'est produite lors de la validation', 500);
        }
    }

    public function rejectDemande(Request $request)
    {
    $this->hasAnyPermission(["all","edit-company-recruitment-management"]);

        $validator = Validator::make($request->all(), [
            'recrutement_id' => "required|integer|min:1",
            'consigne' => "max:5000",
            "motif" => "required"
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('La validation a échouée', $validator->errors()->toArray(), 422);
        }

        try {
            $da = Recrutement::find($request->recrutement_id);
            if (!$da) {
                return $this->errorResponse("La demande est introuvable");
            }
            $entreprise_id = $da->entreprise_id;
            $consigne = $request->consigne;
            $state = 'rejected';
            $da->update([
                'state' => $state,
                'date_rejet' =>now(),

            ]);

            $RejetRecrutement = new RejetRecrutement();
            $RejetRecrutement->recrutement_id = $da->id;
            $RejetRecrutement->motif = $request->motif;
            $RejetRecrutement->consigne = $request->consigne;
            $RejetRecrutement->state = 'init';
            $RejetRecrutement->save();

            $parcoursSuiviData = [
                'slug' => 'demande-recrutement-rejected',
                'service' => 'Recrutement',
                'entreprise_id' => $entreprise_id,
                'recrutement_id' => $da->id,
                'eservice' => '{"Model":"RecrutementRejet","id":"' . $RejetRecrutement->id . '"}',
                'message' => 'Votre demande de recrutement a été rejetée. Consigne :' . $consigne,
                'date_action' => now(),
            ];

            // Créer le parcours suivi
            $parcoursSuivi = EntrepriseParcourSuivi::create($parcoursSuiviData);

            return $this->successResponse($da);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponseclient('Une erreur s\'est produite lors du rejet',500);
        }
    }
}
