<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin\Mention;
use App\Models\BaremeConduite;
use App\Models\Candidat\ParcoursSuivi;
use App\Models\Candidat\DossierSession;
use App\Models\CandidatConduiteReponse;
use App\Models\Candidat\DossierCandidat;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Permis\CreatePermisController;
use App\Models\CategoriePermis;
use App\Models\JuryCandidat;
use Illuminate\Support\Facades\DB;

class CandidatConduiteReponseController extends ApiController
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Valider les données entrantes
            $validator = Validator::make($request->all(), [
                'bareme_conduite_id' => 'required|integer|exists:bareme_conduites,id',
                'mention_id' => 'required|integer',
                'dossier_session_id' => 'required|integer',
                'conduite_vague_id' => 'required|integer|exists:conduite_vagues,id',
                'jury_candidat_id' => 'required|integer|exists:jury_candidats,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), 422);
            }

            $juryCandidat = JuryCandidat::find($request->jury_candidat_id);
            $juryCandidat->closed = true;
            $juryCandidat->save();

            $dossierSession = DossierSession::find($request->input('dossier_session_id'));
            if (!$dossierSession) {
                return $this->errorResponse("Le dossier session est introuvable.", null, 404);
            }
            $categoriePermisId = $dossierSession->categorie_permis_id;
            $presenceConduite = $dossierSession->presence_conduite;
            if ($presenceConduite === NULL) {
                return $this->errorResponse("Ce candidat n'a pas encore émargé", null, 404);
            }
            if ($presenceConduite === "absent") {
                return $this->errorResponse("Ce candidat a été marqué absent, vous ne pouvez plus lui attribuer une note", null, 404);
            }
            // Récupérer le poids depuis la table bareme_conduites
            $bareme = BaremeConduite::find($request->input('bareme_conduite_id'));
            if (!$bareme) {
                return $this->errorResponse("BaremeConduite introuvable.", null, 404);
            }

            $poids = $bareme->poids;

            // Récupérer le point depuis la table mentions
            $mention = Mention::find($request->input('mention_id'));
            if (!$mention) {
                return $this->errorResponse("Mention introuvable.", null, 404);
            }

            $point = $mention->point;

            // Calculer la note en multipliant le poids et le point, puis en divisant par 100
            $note = ($poids * $point) / 100;

            // Créer un nouvel enregistrement avec les données fournies, y compris la note calculée
            $candidatConduiteReponse = CandidatConduiteReponse::create([
                'bareme_conduite_id' => $request->input('bareme_conduite_id'),
                'mention_id' => $request->input('mention_id'),
                'dossier_session_id' => $request->input('dossier_session_id'),
                'conduite_vague_id' => $request->input('conduite_vague_id'),
                'jury_candidat_id' => $request->input('jury_candidat_id'),
                'note' => $note,
            ]);
            $noteFinale = $this->noteConduiteFinal($request->input('conduite_vague_id'),$request->input('jury_candidat_id'), $request->input('dossier_session_id'), $categoriePermisId);
            // Retourner le nouvel enregistrement avec une réponse de succès
            $candidatConduiteReponse['noteFinal'] = $noteFinale;
            DB::commit();
            return $this->successResponse($candidatConduiteReponse, 'Enregistrement créé avec succès');
        } catch (\Throwable $e) {
            DB::rollBack();
            // Gérer les erreurs imprévues
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la création de l\'enregistrement', null, null, 500);
        }
    }

    protected function noteConduiteFinal($conduiteVagueId, $juryCandidatId, $dossierSessionId, $categoriePermisId)
    {
        // Récupérer le nombre de lignes dans BaremeConduite
        $countBaremeConduite = BaremeConduite::where('categorie_permis_id', $categoriePermisId)->count();
        // Récupérer le nombre de lignes dans CandidatConduiteReponse
        $countCandidatConduiteReponse = CandidatConduiteReponse::where('conduite_vague_id', $conduiteVagueId)
            ->where('dossier_session_id', $dossierSessionId)
            ->count();

        // Vérifier si les deux counts sont égaux
        if ($countBaremeConduite === $countCandidatConduiteReponse) {
            // Les counts sont égaux, on calcul la somme des notes dans CandidatConduiteReponse
            $sommeNotes = CandidatConduiteReponse::where('conduite_vague_id', $conduiteVagueId)
                ->where('dossier_session_id', $dossierSessionId)
                ->sum('note');

            // Mettre à jour le champ resultat_conduite dans DossierSession
            if ($sommeNotes >= 12) {
                $dossierSession = DossierSession::where('id', $dossierSessionId)->update(['resultat_conduite' => 'success', 'closed' => true]);
                $juryCandidat = JuryCandidat::where('id', $juryCandidatId)->update(['resultat_conduite' => 'success']);
                $dossierSession = DossierSession::where('id', $dossierSessionId)->first();
                $dossierCandidat = $dossierSession->dossier_candidat_id;
                $dossier = DossierCandidat::find($dossierCandidat);
                $categorie_permis_id = $dossier->categorie_permis_id;
                $categoriePermis = CategoriePermis::find($categorie_permis_id);
                $permisName = $categoriePermis->name;

                $parcoursSuivi = new ParcoursSuivi();
                $parcoursSuivi->npi = $dossierSession->npi;
                $parcoursSuivi->slug = "resultat-conduite";
                $parcoursSuivi->service = 'Permis';
                $parcoursSuivi->candidat_id = $dossier->candidat_id;
                $parcoursSuivi->dossier_candidat_id = $dossierCandidat;
                $parcoursSuivi->categorie_permis_id = $dossier->categorie_permis_id;
                $parcoursSuivi->message = "Félicitations vous avez réussi à l'épreuve de conduite pour la catégorie de permis " . $permisName . " Note obtenue : " . $sommeNotes;
                // $parcoursSuivi->bouton = json_encode(['bouton' => 'Rejet', 'status' => '1']);
                $parcoursSuivi->dossier_session_id = $dossierSession->id;
                $parcoursSuivi->date_action = now();
                $parcoursSuivi->save();
                // Appeler la fonction createPermis($id)
                $createPermisController = new CreatePermisController();
                $createPermisController->createPermis($dossierSessionId);

            } else {
                $dossierSession = DossierSession::where('id', $dossierSessionId)->update(['resultat_conduite' => 'failed', 'closed' => true]);
                $juryCandidat = JuryCandidat::where('id', $juryCandidatId)->update(['resultat_conduite' => 'failed']);
                $dossierSession = DossierSession::where('id', $dossierSessionId)->first();
                $dossier = DossierCandidat::find($dossierSession->dossier_candidat_id);

                $dossier->update([
                    'state' => "failed",
                ]);
                $categorie_permis_id = $dossier->categorie_permis_id;
                $categoriePermis = CategoriePermis::find($categorie_permis_id);
                $permisName = $categoriePermis->name;

                $parcoursSuivi = new ParcoursSuivi();
                $parcoursSuivi->npi = $dossierSession->npi;
                $parcoursSuivi->slug = "resultat-conduite";
                $parcoursSuivi->service = 'Permis';
                $parcoursSuivi->candidat_id = $dossier->candidat_id;
                $parcoursSuivi->dossier_candidat_id = $dossierSession->dossier_candidat_id;
                $parcoursSuivi->categorie_permis_id = $dossier->categorie_permis_id;
                $parcoursSuivi->message = "Vous avez échoué à l'épreuve de conduite pour la catégorie de permis " . $permisName . " Note obtenue : " . $sommeNotes;
                // $parcoursSuivi->bouton = json_encode(['bouton' => 'Rejet', 'status' => '1']);
                $parcoursSuivi->dossier_session_id = $dossierSession->id;
                $parcoursSuivi->date_action = now();
                $parcoursSuivi->save();
            }

            return $sommeNotes;
        } else {
            // Les counts ne sont pas égaux, renvoyer un message
            return 'Impossible de calculer la somme car il manque des notes.';
        }
    }

    public function show($jury_candidat_id)
    {
        try {
            // Recherche des réponses par leur ID de jury candidat
            $reponses = CandidatConduiteReponse::where('jury_candidat_id', $jury_candidat_id)->get();

            // Vérifier si des réponses existent
            if ($reponses->isEmpty()) {
                return $this->successResponse([], 'Donnée non trouvée', 200);
            }

            // Créer un tableau pour savoir si chaque bareme_conduite_id a été noté
            $baremeNoteExists = [];

            foreach ($reponses as $reponse) {
                $baremeId = $reponse->bareme_conduite_id;
                $baremeNoteExists[$baremeId] = true; // Signaler que ce bareme_conduite_id a été noté
            }

            return $this->successResponse([
                'reponses' => $reponses,
                // 'bareme_note_exists' => $baremeNoteExists
            ], 'Données récupérées avec succès.', 200);
        } catch (\Throwable $e) {
            // Gérer les erreurs imprévues
            logger()->error($e);
            return $this->errorResponse('Une erreur est survenue lors de la récupération', null, null, 500);
        }
    }

}
