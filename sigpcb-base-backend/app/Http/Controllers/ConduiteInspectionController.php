<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Services\Resp;
use App\Models\Admin\Jurie;
use App\Models\Admin\Examen;
use App\Models\JuryCandidat;
use Illuminate\Http\Request;
use App\Models\ConduiteVague;
use Illuminate\Support\Collection;
use App\Http\Controllers\ApiController;
use App\Http\Middleware\HasInspectorAccess;
use App\Http\Middleware\HasExaminatorAccess;


class ConduiteInspectionController extends ApiController
{
    public function agendas(Request $request)
    {
        // Récupérez l'examen le plus récent
        $examen = Examen::find($request->get('examen_id'));


        // Récupérez l'ID du jury à partir de la requête
        $juryId = $request->input('jury_id');

        // Récupérez le jury associé à cet ID de jury
        $jury = Jurie::find($juryId);

        if (!$jury) {
            return $this->errorResponse("Le jury spécifié n'existe pas ou a été supprimé", statuscode: 404);
        }

        // Récupérez les candidats associés à ce jury pour cet examen
        $candidat_presentes = JuryCandidat::where([
            'jury_id' => $jury->id,
            'examen_id' => $examen->id,
        ])->get();

        # Groupement des candidats par jour de composition
        $candidats_groupes_par_jour = $candidat_presentes->groupBy(function ($candidat_presente) {
            $conduiteVague = $candidat_presente->conduite_vague_id;
            $ConduiteVague = ConduiteVague::find($conduiteVague);
            $date_compo = $ConduiteVague->date_compo;
            return Carbon::parse($date_compo)->format('d-m-Y H:i:s');
        });

        # Formatage des statistiques pour chaque groupe de candidats
        $resultats = $candidats_groupes_par_jour->map(function (Collection $parJours, $date_compo) {
            $dateCompCarbon = Carbon::parse($date_compo);
            $formattedDate = ucfirst($dateCompCarbon->isoFormat('dddd D MMMM YYYY'));
            $stats = [
                'date' => $formattedDate
            ];

            $groupeParVagues = $parJours->groupBy('conduite_vague_id');
            $stats['vagues_count'] = $groupeParVagues->count();
            $stats['candidats_count'] = $parJours->count();
            return $stats;
        });

        # Calcul du total des vagues
        $vagues_total =  $resultats->sum(function ($parJours) {
            return $parJours['vagues_count'];
        });

        # Création de l'ensemble de données à renvoyer en réponse
        $data = [
            'agendas' => $resultats->values(),
            "date_count" => count($resultats),
            "candidats_total" => $candidat_presentes->count(),
            "vagues_total" => $vagues_total,
        ];
        return  $this->successResponse($data);
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
        $examinateur = $this->getCurrentExaminateur($request)->withUser(['id', 'first_name', 'last_name']);

        $examen = Examen::find($request->get('examen_id'));

        # Récupération des détails de la salle de composition
        $jury = Jurie::find($request->jury_id, ['id', 'name', 'annexe_anatt_id']);

        if (!$jury) {
            return $this->errorResponse("Votre jury n'existe pas ou a été supprimée", statuscode: 404);
        }
        # On charge l'annexe
        $jury = $jury->withAnnexe(['id', 'name']);
        $user = $examinateur->user;

        # Création de l'ensemble de données pour le récapitulatif
        $recatps = [
            "session" => [
                'id' => $examen->id,
                "label" => $examen->session_long,
            ],
            "jury" => $jury,
            'examinateur' => $user->last_name . ' ' . $user->first_name,
            "epreuve" => "Conduite"
        ];

        return $this->successResponse($recatps);
    }

    public function vagues(Request $request)
    {

        $examen = Examen::find($request->get('examen_id'));

        // Récupérez l'ID du jury à partir de la requête
        $juryId = $request->input('jury_id');

        // Récupérez le jury associé à cet ID de jury
        $jury = Jurie::find($juryId);

        if (!$jury) {
            return $this->errorResponse("Le jury spécifié n'existe pas ou a été supprimé", statuscode: 404);
        }

        // Récupérez les candidats associés à ce jury pour cet examen
        $query = JuryCandidat::where([
            'jury_id' => $jury->id,
            'examen_id' => $examen->id,
        ]);

        # Groupement des candidats par vague
        $vagues = $query->get()->groupBy('conduite_vague_id');
        # Prend uniquement les vagues non fermées
        $vagues = $vagues
            ->filter(function ($candidat_jury, $vague_id) {
                $vague = ConduiteVague::find($vague_id, ['id', 'closed']);
                return !boolval($vague->closed);
            });

        # Calcul des statistiques
        $stats = $vagues->map(function (Collection $vagues) {

            $first = $vagues->first();

            $first->withLangue(['id', 'name']);
            $first->withCategoriePermis(['id', 'name']);
            $conduiteVague = $first->conduite_vague_id;
            $ConduiteVague = ConduiteVague::find($conduiteVague);
            $date_compo = $ConduiteVague->date_compo;
            return [
                'candidats_count' => count($vagues),
                'langue' => $first->langue,
                "categorie_permis" => $first->categorie_permis,
                'vague' => $ConduiteVague,
                'date_compo' => Carbon::parse($date_compo)->format('d F Y')
            ];
        });

        # Trie par date de composition
        $stats =  $stats->sortBy(function ($vague) {
            return Carbon::parse($vague['date_compo'])->format('d-m-Y');
        });

        # Renvoie la réponse
        return $this->successResponse($stats->values());
    }
    /**
     * Récupère l'inspecteur à partir de son ID
     * Depuis le  \App\Http\Middleware\HasExaminatorAccess::class middleware
     * @param Request $request
     *  @return Inspecteur
     */
    private function getCurrentExaminateur(Request $request)
    {
        return $request->attributes->get("_examinateur");
    }
}
