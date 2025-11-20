<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Vague;
use App\Services\Help;
use App\Models\CompoToken;
use App\Models\SalleCompo;
use App\Models\Admin\Examen;
use App\Models\CompoSession;
use Illuminate\Http\Request;
use App\Services\GetCandidat;
use App\Models\CategoriePermis;
use App\Models\SalleCompoVague;
use App\Models\Admin\Inspecteur;
use App\Models\Admin\AnnexeAnatt;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\AnnexeResultatState;
use App\Models\CandidatExamenSalle;
use App\Models\Candidat\ParcoursSuivi;
use App\Models\Candidat\DossierSession;
use App\Models\Candidat\DossierCandidat;
use App\Models\CandidatQuestion;
use App\Models\CandidatReponse;
use App\Models\CompoCandidatDeconnexion;
use App\Models\CompoPage;
use Illuminate\Support\Facades\Validator;
use App\Services\Exceptions\GlobalException;

/**
 * Certaines validations de ce controlleur sont faites dans le midlleware \App\Http\Middleware\HasInspectorAccess
 *
 */
class CodeInspectionController extends ApiController
{

    /**
     * Récupère et affiche la liste des agendas pour l'examen en cours dans une salle donnée.
     *
     * @param  Request $request Les données de la requête HTTP.     Une réponse JSON contenant les statistiques des agendas.
     */
    public function agendas(Request $request)
    {

        $request->validate([
            'salle_compo_id' => "required|exists:salle_compos,id"
        ]);
        $examen = $this->examen();

        # Récupération des candidats présents pour cet examen et cette salle
        /**
         * @var \Illuminate\Database\Eloquent\Collection<int,SalleCompoVague>
         */
        $candidat_presentes = CandidatExamenSalle::where([
            'examen_id' => $examen->id,
            'salle_compo_id' => $request->salle_compo_id
        ])->get();

        # Groupement des candidats par jour de composition
        $candidats_groupes_par_jour = $candidat_presentes->groupBy(function ($candidat_presente) {
            $date_compo = $candidat_presente->vague->date_compo;
            return Carbon::parse($date_compo)->format('d-m-Y');
        });

        # Formatage des statistiques pour chaque groupe de candidats
        $resultats = $candidats_groupes_par_jour->map(function (Collection $parJours, $date_compo) {
            $dateCompCarbon = Carbon::parse($date_compo);
            $formattedDate = ucfirst($dateCompCarbon->isoFormat('dddd D MMMM YYYY'));
            $stats = [
                'date' => $formattedDate
            ];

            $groupeParVagues = $parJours->groupBy('vague_id');
            $stats['vagues_count'] = $groupeParVagues->count();
            $stats['candidats_count'] = $parJours->count();
            return $stats;
        });

        # Calcul du total des vagues
        $vagues_total =  Vague::where([
            'examen_id' => $examen->id,
            'salle_compo_id' => $request->salle_compo_id
        ])->has('candidats')->count();

        # Calcul du nombre d'émargés
        $emarges = $candidat_presentes->filter(function (CandidatExamenSalle $candidatExamenSalle) {
            return !is_null($candidatExamenSalle->presence);
        })->count();

        # Calcul du nombre  d'absents
        $absents = $candidat_presentes->filter(function (CandidatExamenSalle $candidatExamenSalle) {
            return $candidatExamenSalle->presence === 'abscent';
        })->count();

        # Création de l'ensemble de données à renvoyer en réponse
        $data = [
            'agendas' => $resultats->values(),
            "date_count" => count($resultats),
            "candidats_total" => $candidat_presentes->count(),
            "vagues_total" => $vagues_total,
            "candidat_emages" => $emarges,
            "absents" => $absents,
        ];
        return  $this->successResponse($data);
    }

    /**
     * Récupère et affiche la liste des agendas pour l'examen en cours dans une salle donnée.
     *
     * @param Request $request
     */
    public function vagues(Request $request)
    {
        $request->validate([
            'salle_compo_id' => "required|exists:salle_compos,id"
        ]);
        $examen = $this->examen();
        try {

            /**
             * Récupération des candidats présents pour cet examen et cette salle
             *
             * @var \Illuminate\Database\Eloquent\Builder $query
             */
            $query =  CandidatExamenSalle::select(['id', 'examen_id',  'vague_id', 'categorie_permis_id', 'langue_id', 'salle_compo_id', 'npi', 'presence', 'num_table', 'closed'])->where([
                'examen_id' => $examen->id,
                'salle_compo_id' => $request->salle_compo_id,
            ])->with('question')->withCount(['reponses']);

            /**
             * Lorsque le menu est égal ont-composé
             */
            if ($request->get('menu') == 'ont-composes') {
                $query->where([
                    'closed' => true,
                    'presence' => "present"
                ]);
            }
            $collection = $query->get();
            # Récupération des numéros de npi des candidats de la vague
            $npis = $collection->map(fn($instance) => $instance->npi);

            $candidats = GetCandidat::get($npis->toArray());

            # Ajout des informations des candidats à la collection de candidats présents
            $collection = $collection->map(function (CandidatExamenSalle $candidat_presente) use ($candidats) {
                $found = collect($candidats)->where(function ($cand) use ($candidat_presente) {
                    return $cand['npi'] == $candidat_presente['npi'];
                })->first() ?? [];

                $candidat_presente->setAttribute("candidat", $found);
                $sessionExists = CompoToken::whereDate("expire_at", "<=", now())->where([
                    "candidat_salle_id" => $candidat_presente->id
                ])->exists();
                # Set candidat connecting status
                $candidat_presente->setAttribute("connected", $sessionExists);

                $question = $candidat_presente->question;
                $question_count = 0;
                if ($question) {
                    $question_count = count($question->questions);
                }
                $candidat_presente->setAttribute("questions_count", $question_count);

                return $candidat_presente;
            });

            # Groupement des candidats par vague
            $vagues = $collection->groupBy('vague_id');

            # Calcul des statistiques
            $stats = $this->mapVagues($vagues);
            # Trie par date de composition
            $stats =  $stats->sortBy(function ($vague) {
                return $vague['vague']->numero;
            });

            # Renvoie la réponse
            return $this->successResponse($stats->values());
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse();
        }
    }

    /**
     * Renvoie les récapitulatif de l'inspection
     * Récupère et renvoie un récapitulatif de l'inspection en cours, incluant les informations sur la session d'examen,
     * la salle de composition et l'inspecteur actuel.
     *
     * @param  Request $request  Les données de la requête HTTP.
     * @param Request $request
     */
    public function recapts(Request $request)
    {
        $examen = $this->examen();

        $inspecteur = $this->getCurrentInspecteur($request)->withUser(['id', 'first_name', 'last_name']);

        # Récupération des détails de la salle de composition
        $salleCompo = SalleCompo::find($request->salle_compo_id, ['id', 'name', 'annexe_anatt_id']);

        if (!$salleCompo) {
            return $this->errorResponse("Votre salle de composition n'existe pas ou a été supprimée", statuscode: 404);
        }
        # On charge l'annexe
        $salleCompo = $salleCompo->withAnnexe(['id', 'name']);
        $user = $inspecteur->user;

        # Création de l'ensemble de données pour le récapitulatif
        $recatps = [
            "session" => [
                'id' => $examen->id,
                "label" => Help::sessionDate(Carbon::parse($examen->date_code), 'long'),
            ],
            "salle" => $salleCompo,
            'inspecteur' => $user->last_name . ' ' . $user->first_name,
            "epreuve" => "Code de la route"
        ];

        return $this->successResponse($recatps);
    }

    /**
     * Récupère l'inspecteur à partir de son ID
     * Depuis le  \App\Http\Middleware\HasInspectorAccess::class middleware
     * @param Request $request
     *  @return Inspecteur
     */
    private function getCurrentInspecteur(Request $request)
    {
        return $request->attributes->get("_inspecteur");
    }


    /**
     * Marquer un candidat comme un absence
     *
     * @param  Request $request  Les données de la requête HTTP.
     */
    public function markAsAbscent(Request  $request)
    {
        DB::beginTransaction();
        try {
            $examen = $this->examen();
            if ($examen->isClosed()) {
                return $this->errorResponse("L'examen est clôturé", statuscode: 404);
            }
            $validator = Validator::make($request->all(), [
                'candidat_salle_id' => "required|exists:candidat_examen_salles,id",
                'salle_compo_id' => "required|exists:salle_compos,id"
            ]);

            if ($validator->fails()) {
                return $this->sendValidatorErrors($validator, "Impossible de continuer, les données ne sont pas valides");
            }
            /**
             * @var CandidatExamenSalle $candidat
             */
            $candidat = CandidatExamenSalle::find($request->candidat_salle_id);

            if (!is_null($candidat->presence)) {
                return $this->errorResponse("Impossible de marquer le candidat comme absent, le candidat a déjà émargé {$candidat->presence}", statuscode: 409);
            }

            # Il faut empêcher de faire signer un candidat qui n'est dans la vague courante
            $currentVague = Vague::current($request->salle_compo_id, $candidat->examen_id);
            if ($currentVague->id !== $candidat->vague_id) {
                return $this->errorResponse("Vous ne pouvez pas marquer ce candidat absent, il est dans la vague  n° {$candidat->vague->numero}.", statuscode: 404);
            }


            if (!is_null($candidat->presence)) {
                return $this->errorResponse("Impossible de marquer le candidat comme absent, le candidat a déjà marqué présent", statuscode: 409);
            }
            $currentVague = $this->currentVague($request->salle_compo_id, $examen->id);

            if ($currentVague->id !== $candidat->vague_id) {
                return $this->errorResponse("Vous ne pouvez pas encore marquer ce candidat absent.", statuscode: 404);
            }
            $ds = DossierSession::find($candidat->dossier_session_id);

            $dossier = DossierCandidat::find($ds->dossier_candidat_id);
            $dossier->state = "failed";
            $dossier->save();

            $candidat->presence = 'abscent';
            $candidat->closed = true;
            $ds->presence = "abscent";
            $ds->closed = true;
            $ds->resultat_code = "failed";
            $ds->resultat_conduite = "failed";
            $candidat->abscence_at = now();

            $candidat->save();
            $ds->save();

            //Fermer la composition au besoin
            $this->closeVagueIfNeed($candidat->vague_id);

            $this->onMarkAsAbsence($ds, $dossier);
            DB::commit();
            return $this->successResponse(true, "Le candidat a été marqué absent avec succès");
        } catch (\Throwable $th) {
            DB::rollBack();
            logger()->error($th);
            return $this->errorResponse("Une erreur est survenue");
        }
    }


    public function openSession(Request  $request)
    {
        $examen = $this->examen();
        if ($examen->isClosed()) {
            return $this->errorResponse("L'examen est clôturé.", statuscode: 404);
        }
        $validator = Validator::make($request->all(), [
            'candidat_salle_id' => "required|exists:candidat_examen_salles,id"
        ]);

        if ($validator->fails()) {
            return $this->sendValidatorErrors($validator, "Impossible de continuer, les données ne sont pas valides");
        }
        /**
         * @var CandidatExamenSalle $candidat
         */
        $candidat = CandidatExamenSalle::find($request->candidat_salle_id);

        if (!$candidat) {
            return $this->errorResponse("Impossible de continuer, le candidat est introuvable", statuscode: 404);
        }

        try {
            $cquery = CompoToken::where('candidat_salle_id', $candidat->id);
            if ($cquery->exists()) {
                $message = "Session ouverte avec succès, le candidat peut composer.";
            } else {
                $message = "Le candidat n'était pas connecté(e). Le candidat peut se connecter";
            }
            // Suppression de la session du candidat
            $cquery->delete();

            //Autorise la session
            CompoSession::where('candidat_salle_id', $candidat->id)->update([
                'authorized' => true
            ]);

            return $this->successResponse(true, $message);
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse("Une erreur inattendue s'est produite lors de l'ouverture de la session du candidat.", statuscode: 404);
        }
    }

    private function mapVagues(Collection $vagues,)
    {

        return $vagues->map(function (Collection $vagues) {
            /**
             * @var \App\Models\CandidatExamenSalle $first
             */
            $first = $vagues->first();
            $first->withLangue(['id', 'name']);
            $first->withCategoriePermis(['id', 'name']);
            return [
                'candidats_count' => count($vagues),
                'candidats' => $vagues,
                'langue' => $first->langue,
                "categorie_permis" => $first->categorie_permis,
                'vague' => $first->vague,
                'date_compo' => Help::sessionDate(Carbon::parse($first->vague->date_compo), 'long')
            ];
        });
    }

    public function salles(Request $request)
    {
        $request->validate([
            "inspecteur_id" => "required|integer"
        ]);

        // Vérifier si l'inspecteur existe
        $inspecteur = Inspecteur::find($request->inspecteur_id);
        if (!$inspecteur) {
            throw new GlobalException("Impossible de trouver votre compte.");
        }

        // Vérifier si l'annexe associée à l'inspecteur existe
        $annexe = AnnexeAnatt::find($inspecteur->annexe_anatt_id);
        if (!$annexe) {
            throw new GlobalException("Impossible de trouver l'annexe de l'inspecteur.");
        }

        // Récupérer les salles liées à l'annexe
        $salles = SalleCompo::where('annexe_anatt_id', $annexe->id)->get();

        // Récupérer les examens auxquels l'inspecteur a accès, en filtrant par annexe_ids
        $examens = Examen::whereJsonContains('annexe_ids', $annexe->id)
            ->where('closed', false)  // Assurer que l'examen n'est pas fermé
            ->get();

        return $this->successResponse(
            [
                "annexe" => $annexe,
                "salles" => $salles,
                "sessions" => $examens  // Renvoi des examens filtrés
            ]
        );
    }

    /**
     * Arrête la composition d'un candidat en cas d'un indicident
     *
     * @param Request $request
     */
    public function stopCandidatCompo(Request $request)
    {
        $examen = $this->examen();
        if ($examen->isClosed()) {
            return $this->errorResponse("L'examen est clôturé.", statuscode: 404);
        }
        $validator = Validator::make($request->all(), [
            'candidat_salle_id' => "required|exists:candidat_examen_salles,id",
            "motif" => "required|min:10"
        ], [
            "candidat_salle_id" => "Candidat introuvable dans la liste de vos candidats, vérifiez le numéro NPI."
        ]);

        if ($validator->fails()) {
            return $this->sendValidatorErrors($validator, "Impossible de continuer, les données ne sont pas valides");
        }
        /**
         * @var CandidatExamenSalle $candidat
         */
        $candidat = CandidatExamenSalle::find($request->candidat_salle_id);

        if (!$candidat) {
            return $this->errorResponse("Le candidat est introuvable", statuscode: 404);
        }

        if ($candidat->closed) {
            $message = $candidat->presence == 'present' ? "Ce candidat a terminé déjà sa composition" : 'Ce candidat a été marqué absent(e)';
            return $this->errorResponse($message, statuscode: 404);
        }

        DB::beginTransaction();
        try {
            $data['vague_id'] = $candidat->vague_id;
            $data['salle_compo_id'] = $candidat->salle_compo_id;
            $data['closed'] = false;
            $data['presence'] = "present";

            $anyNotFinish = CandidatExamenSalle::filter($data)->where('id', "!=", $candidat->id)->exists();
            if ($anyNotFinish) {
                return $this->errorResponse("Vous ne pouvez pas terminer la composition de ce candidat actuellement, veuillez tenter à la fin de la composition.", statuscode: 404);
            }

            CompoCandidatDeconnexion::create([
                "motif" => $request->motif,
                "candidat_salle_id" => $candidat->id,
                "npi" => $candidat->npi,
            ]);
            $candidat->closed = false;
            $candidat->save();
            $candidat->refresh();

            $ds = DossierSession::find($candidat->dossier_session_id);
            $dossier = DossierCandidat::find($ds->dossier_candidat_id);

            //Informe le candidat
            $this->onDonnection($ds, $dossier, $request->motif);
            $this->closeVagueIfNeed($candidat->vague_id);

            DB::commit();
            return $this->successResponse(true, "Composition arrêtée avec succès.");
        } catch (\Throwable $th) {
            logger()->error($th);
            DB::rollBack();
            return $this->errorResponse("Impossible de continuer, une erreur serveur s'est produite", statuscode: 404);
        }
    }

    private function onMarkAsAbsence(DossierSession $dossierSession, DossierCandidat $dossier)
    {
        $categorie_permis_id = $dossier->categorie_permis_id;
        $categoriePermis = CategoriePermis::find($categorie_permis_id);
        $permisName = $categoriePermis->name;

        $parcoursSuivi = new ParcoursSuivi();
        $parcoursSuivi->npi = $dossierSession->npi;
        $parcoursSuivi->slug = "resultat-absent-code";
        $parcoursSuivi->service = 'Permis';
        $parcoursSuivi->candidat_id = $dossier->candidat_id;
        $parcoursSuivi->dossier_candidat_id = $dossierSession->dossier_candidat_id;
        $parcoursSuivi->categorie_permis_id = $dossier->categorie_permis_id;
        $parcoursSuivi->message = "Vous avez été marqué(e) absent(e) lors de l'épreuve de code. Par conséquent, vous êtes recalé(e) pour cette session. Toutefois, vous avez la possibilité de reprendre cette catégorie de permis";
        $parcoursSuivi->dossier_session_id = $dossierSession->id;
        $parcoursSuivi->date_action = now();
        $parcoursSuivi->save();
    }

    private function onDonnection(DossierSession $dossierSession, DossierCandidat $dossier, string $motif)
    {
        $categorie_permis_id = $dossier->categorie_permis_id;
        $categoriePermis = CategoriePermis::find($categorie_permis_id);
        $permisName = $categoriePermis->name;

        $parcoursSuivi = new ParcoursSuivi();
        $parcoursSuivi->npi = $dossierSession->npi;
        $parcoursSuivi->slug = "composition-stopped";
        $parcoursSuivi->service = 'Permis';
        $parcoursSuivi->candidat_id = $dossier->candidat_id;
        $parcoursSuivi->dossier_candidat_id = $dossierSession->dossier_candidat_id;
        $parcoursSuivi->categorie_permis_id = $dossier->categorie_permis_id;
        $parcoursSuivi->message = "Votre composition a été arrêtée par l'inspecteur de salle. <br/> <b>Motif</b> <hr> {$motif}";
        $parcoursSuivi->dossier_session_id = $dossierSession->id;
        $parcoursSuivi->date_action = now();
        $parcoursSuivi->save();
    }

    private function closeVagueIfNeed($vague_id)
    {
        $vague = Vague::find($vague_id);
        $notClosed = CandidatExamenSalle::where([
            "vague_id" => $vague->id,
            "closed" => false
        ]);

        # Si on trouve un candiat dont la session n'est pas cloturée, c'est que la composition ne peut-être terminée
        if ($notClosed->exists()) {
            return;
        }
        $vague->status = "closed";
        $vague->save();


        $allVagues = Vague::where([
            'examen_id' => $vague->examen_id,
            'annexe_anatt_id' => $vague->annexe_anatt_id,
        ])->get();


        //Si toutes les vagues sont fermées c'est que le résultat de la composition est prête
        if ($allVagues->every(fn(Vague $v) => $v->isStatus('closed'))) {
            AnnexeResultatState::create([
                "annexe_id" => $vague->annexe_anatt_id,
                'examen_id' => $vague->examen_id,
                'ready' => true,
                'type' => "code"
            ]);
        }
    }

    private function currentVague($salle_compo_id, $examen_id)
    {
        return  Vague::current($salle_compo_id, $examen_id);
    }

    public function pause(Request $request)
    {
        $request->validate([
            'salle_compo_id' => "required|exists:salle_compos,id",
            "paused" => "required|boolean"
        ]);
        $examen = $this->examen();

        $vague = Vague::where([
            "examen_id" => $examen->id,
            "salle_compo_id" => $request->salle_compo_id
        ])->first();

        if (!$vague) {
            return $this->errorResponse("Impossible de trouver la vague", statuscode: 404);
        }

        if ($vague->status == "closed") {
            return $this->errorResponse("La vague a été cloturée déjà.");
        }
        $vague->update([
            "status" => $request->paused ?  "paused" : "pending"
        ]);

        $message = $request->paused ? "La composition a été mise en pause avec succès." : "La composition a été relancée avec succès.";
        return $this->successResponse($vague, $message);
    }

    public function resetCompo(Request $request)
    {
        $request->validate([
            'vague_id' => "required|exists:vagues,id"
        ]);
        DB::beginTransaction();
        try {

            $vagueId =  $request->get('vague_id');
            $vague = Vague::find($vagueId);

            if ($vague->status == 'closed') {
                return $this->errorResponse("La vague est déjà clôturée", statuscode: 403);
            }
            $candiadts = CandidatExamenSalle::where("vague_id", $vagueId)->get();
            foreach ($candiadts as $candidat) {

                CandidatReponse::where('candidat_salle_id', $candidat->id)
                    ->delete();

                CandidatQuestion::where('candidat_salle_id', $candidat->id)
                    ->delete();
                CompoPage::where('candidat_salle_id', $candidat->id)
                    ->delete();

                $vague->update([
                    'status' => "new"
                ]);

                CompoToken::where("candidat_salle_id", $candidat->id)->delete();

                CompoSession::where('candidat_salle_id', $candidat->id)->delete();

                $candidat->update([
                    "closed" => false
                ]);
            }
            DB::commit();

            return $this->successResponse(null, "Opération effectuée avec succès");
        } catch (\Throwable $th) {
            DB::rollBack();
            logger($th);

            return $this->errorResponse("Une erreur est survenue lors de la réinitialisation de la composition", statuscode: 500);
        }
    }
}
