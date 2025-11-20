<?php

namespace App\Http\Controllers;

use App\Models\Admin\Examen;
use App\Models\Admin\Jurie;
use App\Models\AutoEcole\AutoEcole;
use App\Models\Candidat\DossierSession;
use App\Models\CandidatConduiteReponse;
use App\Models\CategoriePermis;
use App\Models\JuryCandidat;
use Illuminate\Http\Request;
use App\Services\GetCandidat;

class JuryCandidatController extends ApiController
{
    public function index()
    {
        try {
            $jury = JuryCandidat::orderByDesc('id')->get();
            return $this->successResponse($jury);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    public function getDossierggbyJvury($jury_id)
    {
        try {
            // Récupérez l'examen récent
            $examenRecent = Examen::recent();
            $examen_id = $examenRecent->id;

            // Récupérez les candidats du jury pour l'examen récent
            $jury = JuryCandidat::where('jury_id', $jury_id)
                ->where('examen_id', $examen_id)
                ->get();

            $dS = $jury->pluck('dossier_session_id')->toArray();

            // Récupérez les numéros de NPI des candidats de la vague
            $npis = $jury->pluck('npi');

            // Récupérez les numéros de NPI des candidats de la vague
            $categorie_permis_ids = $jury->pluck('categorie_permis_id')->toArray();

            // Obtenez les détails des catégories de permis à partir des IDs
            $categoriePermis = CategoriePermis::whereIn('id', $categorie_permis_ids)->get();

            // Obtenez les détails des candidats à partir de leur NPI
            $candidats = GetCandidat::get($npis->toArray());

            // Ajoutez les détails des candidats à la collection de candidats du jury
            $jury = $jury->map(function ($juryItem) use ($candidats) {
                $found = collect($candidats)->firstWhere('npi', $juryItem->npi);
                $juryItem->candidat = $found;

                return $juryItem;
            });

            // Ajoutez les détails des catégories de permis à la collection du jury
            $jury = $jury->map(function ($juryItem) use ($categoriePermis) {
                $found = $categoriePermis->where('id', $juryItem->categorie_permis_id)->first();
                $juryItem->categorie_permis = $found;

                return $juryItem;
            });

            // Récupérez les détails des dossiers de session à partir de leurs IDs
            $dossierSessions = DossierSession::whereIn('id', $dS)->get();

            // Ajoutez les détails des dossiers de session à la collection du jury
            $jury = $jury->map(function ($juryItem) use ($dossierSessions) {
                $found = $dossierSessions->where('id', $juryItem->dossier_session_id)->first();
                $juryItem->dossier_session = $found;

                return $juryItem;
            });

            // Créez l'ensemble de données à renvoyer en réponse
            $data = [
                'jury' => $jury,
            ];

            return $this->successResponse($data);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }

    public function getDossierbyJury(Request $request)
    {
        try {
            // Récupérez l'examen récent
            $examen_id = $request->get('examen_id');
            $jury_id = $request->get('jury_id');

            $examenRecent = Examen::find($examen_id);

            // Récupérez les candidats du jury pour l'examen récent
            $jury = JuryCandidat::where('jury_id', $jury_id)
                ->where('examen_id', $examenRecent->id)
                ->whereNull('resultat_conduite')
                ->orderByDesc('id')
                ->get();


            // Récupérez les numéros de NPI et les IDs nécessaires
            $npis = $jury->pluck('npi');
            $categorie_permis_ids = $jury->pluck('categorie_permis_id')->toArray();
            $dossier_session_ids = $jury->pluck('dossier_session_id')->toArray();

            // Obtenez les détails des catégories de permis
            $categoriePermis = CategoriePermis::whereIn('id', $categorie_permis_ids)->get();

            // Obtenez les détails des dossiers de session
            $dossierSession = DossierSession::whereIn('id', $dossier_session_ids)->get();

            // Obtenez les détails des candidats à partir de leur NPI
            $candidats = GetCandidat::get($npis->toArray());

            // Récupérez les IDs d'auto-écoles
            $autoEcoleIds = $dossierSession->pluck('auto_ecole_id')->unique()->toArray();
            $autoEcoles = AutoEcole::whereIn('id', $autoEcoleIds)->get()->keyBy('id');

            // Ajoutez les détails des candidats, catégories de permis et dossiers de session
            $jury = $jury->map(function ($juryItem) use ($candidats, $categoriePermis, $dossierSession, $autoEcoles) {
                // Ajouter le candidat
                $foundCandidat = collect($candidats)->firstWhere('npi', $juryItem->npi);
                $juryItem->candidat = $foundCandidat;

                // Ajouter la catégorie de permis
                $foundCategorie = $categoriePermis->where('id', $juryItem->categorie_permis_id)->first();
                $juryItem->categorie_permis = $foundCategorie;

                // Ajouter le dossier de session
                $foundDossier = $dossierSession->where('id', $juryItem->dossier_session_id)->first();
                $juryItem->dossier_session = $foundDossier;

                // Ajouter le nom de l'auto-école
                if ($foundDossier && isset($foundDossier->auto_ecole_id)) {
                    $foundAutoEcole = $autoEcoles->get($foundDossier->auto_ecole_id);
                    $juryItem->auto_ecole_name = $foundAutoEcole ? $foundAutoEcole->name : null;
                    $juryItem->auto_ecole_id = $foundAutoEcole ? $foundAutoEcole->id : null;
                } else {
                    $juryItem->auto_ecole_name = null; // Assurez-vous que ce champ existe même si null
                }

                return $juryItem;
            })->sortBy(function ($juryItem) {
                return intval($juryItem->auto_ecole_id) ?? 0;
            })->values();

            // Créez l'ensemble de données à renvoyer en réponse
            return $this->successResponse($jury);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }



    public function getNotedDossierbyJury(Request $request)
    {
        try {
            // Récupérez l'examen récent
            $examen_id = $request->get('examen_id');
            $jury_id = $request->get('jury_id');

            $examenRecent = Examen::find($request->get('examen_id'));
            $examen_id = $examenRecent->id;

            // Récupérez les candidats du jury pour l'examen récent
            $jury = JuryCandidat::where('jury_id', $jury_id)
                ->where('examen_id', $examen_id)
                ->where('signature', '!=', null)
                ->whereIn('resultat_conduite', ['success', 'failed'])
                ->orderByDesc('id')
                ->get();

            // Récupérez les numéros de NPI des candidats de la vague
            $npis = $jury->pluck('npi');

            // Récupérez les numéros de NPI des candidats de la vague
            $categorie_permis_ids = $jury->pluck('categorie_permis_id')->toArray();
            $dossier_session_ids = $jury->pluck('dossier_session_id')->toArray();

            // Obtenez les détails des catégories de permis à partir des IDs
            $categoriePermis = CategoriePermis::whereIn('id', $categorie_permis_ids)->get();

            // Obtenez les détails des dossier session id à partir des IDs
            $dossierSession = DossierSession::whereIn('id', $dossier_session_ids)->get();

            // Obtenez les détails des candidats à partir de leur NPI
            $candidats = GetCandidat::get($npis->toArray());

            // Ajoutez les détails des candidats à la collection de candidats du jury
            $jury = $jury->map(function ($candidat_presente) use ($candidats) {
                $found = collect($candidats)->firstWhere('npi', $candidat_presente->npi);
                $candidat_presente->candidat = $found;

                // Calcul de la note finale pour chaque dossier session
                $sommeNotes = CandidatConduiteReponse::where('dossier_session_id', $candidat_presente->dossier_session_id)
                    ->sum('note');

                // Créer un champ temporaire pour stocker la note finale
                $candidat_presente->notefinal = $sommeNotes;

                return $candidat_presente;
            });

            // Ajoutez les détails des catégories de permis à la collection du jury
            $jury = $jury->map(function ($juryItem) use ($categoriePermis) {
                $found = $categoriePermis->where('id', $juryItem->categorie_permis_id)->first();
                $juryItem->categorie_permis = $found;

                return $juryItem;
            });

            // Ajoutez les détails des dossier session à la collection du jury
            $jury = $jury->map(function ($juryItem) use ($dossierSession) {
                $found = $dossierSession->where('id', $juryItem->dossier_session_id)->first();
                $juryItem->dossier_session = $found;

                return $juryItem;
            });

            // Créez l'ensemble de données à renvoyer en réponse
            return $this->successResponse($jury);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse('Une erreur est survenue, veuillez réessayer svp!');
        }
    }




    public function show($id)
    {
        try {
            // Recherche de la mention par son ID
            $mention = JuryCandidat::find($id);

            // Vérifier si la mention existe
            if (!$mention) {
                return $this->errorResponse('Donnée non trouvée', null, null, 404);
            }

            // Retourner la mention avec une réponse de succès
            return $this->successResponse($mention, 'Donnée récupérée avec succès');
        } catch (\Throwable $e) {
            // Gérer les erreurs imprévues
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération', null, null, 500);
        }
    }
}
