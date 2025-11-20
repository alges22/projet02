<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\BaremeConduite;
use App\Models\Candidat\ParcoursSuivi;
use App\Models\Candidat\DossierSession;
use App\Models\CandidatConduiteReponse;
use App\Models\Candidat\DossierCandidat;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Permis\CreatePermisController;
use App\Models\CandidatConduiteDetailRpse;
use App\Models\CategoriePermis;
use App\Models\JuryCandidat;
use App\Models\SubBareme;
use Illuminate\Support\Facades\DB;

class CandidatConduiteDetailRpseController extends ApiController
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Valider les données entrantes
            $validator = Validator::make($request->all(), [
                'bareme_conduite_id' => 'required|integer|exists:bareme_conduites,id',
                'sub_bareme_id' => 'array',
                'jury_candidat_id' => 'required|integer|exists:jury_candidats,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse("La validation a échoué.", $validator->errors(), 422);
            }

            $juryCandidat = JuryCandidat::find($request->jury_candidat_id);
            $conduiteVagueId = $juryCandidat->conduite_vague_id;
            $dossierSessionId = $juryCandidat->dossier_session_id;

            // Vérifier si un enregistrement existe déjà
            if (CandidatConduiteReponse::where('jury_candidat_id', $request->jury_candidat_id)
                ->where('dossier_session_id', $dossierSessionId)
                ->where('conduite_vague_id', $conduiteVagueId)
                ->where('bareme_conduite_id', $request->bareme_conduite_id)
                ->exists()) {
                return $this->errorResponse("Une réponse pour ce candidat existe déjà.", null, 409);
            }

            // Vérifier le dossier de session
            $dossierSession = DossierSession::find($dossierSessionId);
            if (!$dossierSession) {
                return $this->errorResponse("Le dossier session est introuvable.", null, 404);
            }

            if (is_null($dossierSession->presence_conduite)) {
                return $this->errorResponse("Ce candidat n'a pas encore émargé", null, 404);
            }
            if ($dossierSession->presence_conduite === "absent") {
                return $this->errorResponse("Ce candidat a été marqué absent, vous ne pouvez plus lui attribuer une note", null, 404);
            }

            // Récupérer le poids depuis la table bareme_conduites
            $bareme = BaremeConduite::find($request->bareme_conduite_id);
            if (!$bareme) {
                return $this->errorResponse("BaremeConduite introuvable.", null, 404);
            }

            // Mettre à jour l'état du jury
            $juryCandidat->closed = true;
            $juryCandidat->save();

            // Vérifier si le tableau sub_bareme_id est vide
            if (empty($request->sub_bareme_id)) {
                // Si le tableau est vide, la note est zéro
                $noteBySubBareme = 0;
                $note = 0;
            } else {
                // Calcul de la note
                $poids = $bareme->poids;
                $eliminatoireExists = SubBareme::whereIn('id', $request->sub_bareme_id)
                    ->where('eliminatoire', true)
                    ->exists();

                $totalSubBareme = SubBareme::where('bareme_conduite_id', $request->bareme_conduite_id)
                    ->where('eliminatoire', false)
                    ->count();

                if ($totalSubBareme === 0) {
                    return $this->errorResponse("Aucun sub_bareme valide trouvé pour ce barème.", null, 404);
                }

                // Calculer la note
                $noteBySubBareme = $eliminatoireExists ? 0 : ($poids / $totalSubBareme);
                $note = $noteBySubBareme * count($request->sub_bareme_id);
            }

            // Insérer les enregistrements dans CandidatConduiteDetailRpse seulement si sub_bareme_id n'est pas vide
            if (!empty($request->sub_bareme_id)) {
                foreach ($request->sub_bareme_id as $subBaremeId) {
                    CandidatConduiteDetailRpse::create([
                        'bareme_conduite_id' => $request->bareme_conduite_id,
                        'conduite_vague_id' => $conduiteVagueId,
                        'sub_bareme_id' => $subBaremeId,
                        'dossier_session_id' => $dossierSessionId,
                        'jury_candidat_id' => $request->jury_candidat_id,
                        'note' => $noteBySubBareme,
                    ]);
                }
            }

            // Créer un nouvel enregistrement avec les données fournies
            $candidatConduiteReponse = CandidatConduiteReponse::create([
                'bareme_conduite_id' => $request->bareme_conduite_id,
                'dossier_session_id' => $dossierSessionId,
                'conduite_vague_id' => $conduiteVagueId,
                'jury_candidat_id' => $request->jury_candidat_id,
                'note' => $note,
            ]);

            // Calculer la note finale
            $noteFinale = $this->noteConduiteFinal(
                $conduiteVagueId,
                $request->jury_candidat_id,
                $dossierSessionId,
                $dossierSession->categorie_permis_id
            );

            // Retourner la réponse de succès
            DB::commit();
            $candidatConduiteReponse['noteFinal'] = $noteFinale;
            return $this->successResponse($candidatConduiteReponse, 'Enregistrement créé avec succès');
        } catch (\Throwable $e) {
            DB::rollBack();
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

                // app(CreatePermisController::class)->createPermis($dossierSessionId);
                // app()->call([CreatePermisController::class, 'createPermis'], ['id' => $dossierSessionId]);
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
}
