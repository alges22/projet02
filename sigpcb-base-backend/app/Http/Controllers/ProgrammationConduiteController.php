<?php

namespace App\Http\Controllers;

use App\Models\Admin\Jurie;
use App\Models\Admin\Examen;
use App\Models\JuryCandidat;
use Illuminate\Http\Request;
use App\Compos\ConduiteCompo;
use App\Models\CategoriePermis;
use App\Models\Admin\Examinateur;
use App\Models\Admin\AnnexeAnattJurie;
use Illuminate\Support\Facades\Validator;
use App\Programmation\ProgrammationConduite;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Admin\ExaminateurCategoriePermis;
use App\Models\Candidat\DossierSession;

class ProgrammationConduiteController extends ApiController
{

    public function __construct() {}

    public function generate(Request $request)
    {
        try {
            $v = Validator::make($request->all(), [
                'annexe_id' => 'required|integer',
                "examen_id" => 'required|integer',
            ]);

            if ($v->fails()) {
                return $this->errorResponse("La validation a échoué", $v->errors());
            }

            $examen_id = $request->examen_id;
            $annexeId = $request->annexe_id;

            // Récupération des jurys et examinateurs
            $JuryParAnnexe = $this->getAnnexeJury($annexeId);
            $ExaminateurParAnnexe = $this->getAnnexeExaminateur($annexeId);

            // Vérifications initiales
            $juryCount = count($JuryParAnnexe);
            $examinateurCount = count($ExaminateurParAnnexe);
            $reste = $juryCount - $examinateurCount;

            // Récupération de l'examen
            $examen = Examen::find($examen_id);

            if (!$examen) {
                return $this->errorResponse("Aucun examen trouvé", statuscode: 422, responsecode: 0);
            }

            if ($examen->closed) {
                return $this->errorResponse("Cette session est déjà clôturée.", responsecode: 1);
            }

            if (empty($JuryParAnnexe)) {
                return $this->errorResponse("Aucun jury trouvé pour ce centre", responsecode: 1);
            }

            if (empty($ExaminateurParAnnexe)) {
                return $this->errorResponse("Aucun examinateur trouvé pour ce centre", responsecode: 3);
            }

            $data = $this->getDossierSessions($annexeId, $examen_id);

            if (empty($data)) {
                return $this->errorResponse("Aucun candidat trouvé à programmer", responsecode: 2);
            }

            // Vérification des permissions des examinateurs
            $dossiers = collect($data);
            $permis = $dossiers->pluck('categorie_permis_id')->unique();
            $examinteurPermis = [];

            foreach ($ExaminateurParAnnexe as $xmt) {
                $currentExaminteurPermis = ExaminateurCategoriePermis::where('examinateur_id', $xmt->id)->get();
                foreach ($currentExaminteurPermis as $ky => $xmtPermis) {
                    $examinteurPermis[] = intval($xmtPermis->categorie_permis_id);
                }
            }

            $sansExaminteurs = $permis->filter(function ($permisId) use ($examinteurPermis) {
                return !in_array($permisId, $examinteurPermis);
            })->all();

            if (!empty($sansExaminteurs)) {
                $pem = collect($sansExaminteurs)->map(function ($id) {
                    return CategoriePermis::find($id);
                })->pluck('name')->join(', ');
                return $this->errorResponse("Les candidats du centre ayant pour catégorie de permis: {$pem} ne semblent pas avoir d'examintateurs");
            }

            // Vérification des compositions existantes
            $juries = JuryCandidat::select(['closed', 'examen_id', 'annexe_id'])
                ->where('examen_id', $examen_id)
                ->where('annexe_id', $annexeId)
                ->get();

            if ($juries->some(fn($jury) => $jury->closed)) {
                return $this->errorResponse("La composition est actuellement en cours, vous ne pouvez plus relancer la programmation.", responsecode: 4);
            }

            // Nettoyage des anciennes compositions
            Jurie::where([
                'annexe_anatt_id' => $annexeId,
                'examen_id' => $examen_id,
            ])->get()->each(function ($jury) {
                JuryCandidat::where('jury_id', $jury->id)->delete();
                $jury->delete();
            });

            // Mélange aléatoire des collections
            $JuryParAnnexe->shuffle();
            $ExaminateurParAnnexe->shuffle();

            // Association des jurys et examinateurs
            $associations = [];
            $securiteIndex = 0;
            $count = $ExaminateurParAnnexe->count();

            while ($ExaminateurParAnnexe->isNotEmpty()) {
                foreach ($JuryParAnnexe as $jury) {
                    if ($ExaminateurParAnnexe->isEmpty()) break;

                    $examinateur = $ExaminateurParAnnexe->shift();
                    $associations[] = [
                        "jury" => $jury,
                        "examinateur" => $examinateur
                    ];
                }

                if ($securiteIndex > $count) break;
                $securiteIndex++;
            }

            if (empty($associations)) {
                return $this->errorResponse("Aucune assignation de jurie trouvé", responsecode: 4);
            }

                // Création des jurys
                $juriesGenereated = [];
                foreach ($associations as $key => $assoc) {
                    $jury = $assoc['jury'];
                    $examinateur = $assoc['examinateur'];
                    $juriesGenereated[] = Jurie::create([
                        "name" => $jury->name,
                        "annexe_jury_id" => $jury->id,
                        "annexe_anatt_id" => $annexeId,
                        "examinateur_id" => $examinateur->id,
                        "examen_id" => $examen_id,
                    ]);
                }
            // Application de la logique de composition
            $compo = new ConduiteCompo($data, $annexeId, $juriesGenereated);
            $stats = [];

            if (!$compo->intoTempFile($stats)) {
                return $this->errorResponse("La programmation a échoué");
            }

            return $this->successResponse($stats, "Programmation des jurys terminée avec succès.");
        } catch (\Throwable $th) {
            logger()->error($th);
            return $this->errorResponse($th->getMessage());
        }
    }

    public function resultatCode(Request $request)
    {
        if (!$request->has('annexe_id') || !$request->has('examen_id')) {
            return $this->errorResponse("Les paramètres annexe_id et examen_id sont réquis", statuscode: 400);
        }

        $filters = $request->only(['annexe_id', 'examen_id', 'categorie_permis_id']);

        //Récupération des candidats
        $dossiers = $this->getDossierSessions($request->annexe_id, $request->examen_id);


        return  $this->successResponse($dossiers);
    }

    public function vagues(Request $request)
    {
        if (!$request->has('annexe_id') || !$request->has('examen_id')) {
            return $this->errorResponse("Les paramètres annexe_id et examen_id sont réquis", statuscode: 400);
        }
        $getter = new ProgrammationConduite($request->examen_id, $request->annexe_id);
        $vagues = $getter->get($message);
        return $this->successResponse($vagues, $message);
    }

    private function getDossierSessions($annexeId, $examen_id)
    {
        return  DossierSession::presentesConduite([
            'examen_id' => $examen_id,
            'annexe_id' => $annexeId,
        ])->get()->toArray();
    }

    /**
     * Undocumented function
     *
     * @param [type] $annexeId
     * @return Collection<int,AnnexeAnattJurie>
     */
    protected function getAnnexeJury($annexeId)
    {
        return AnnexeAnattJurie::select(['id', 'name'])
            ->where('annexe_anatt_id', $annexeId)
            ->get();
    }

    protected function getJurie($annexeId, $examen_id)
    {
        return Jurie::select(['id', 'name', 'annexe_anatt_id', 'examinateur_id', 'examen_id', 'annexe_jury_id'])
            ->where('annexe_anatt_id', $annexeId)
            ->where('examen_id', $examen_id)
            ->get()
            ->toArray();
    }
    /**
     *
     * @return Collection<int,Examinateur>
     */
    protected function getAnnexeExaminateur($annexeId)
    {
        return Examinateur::select(['id', 'user_id'])
            ->where('annexe_anatt_id', $annexeId)
            ->get();
    }
}
