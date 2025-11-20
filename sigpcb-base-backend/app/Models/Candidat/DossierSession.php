<?php

namespace App\Models\Candidat;

use App\Models\Vague;
use App\Models\Langue;
use App\Models\Chapitre;
use App\Models\SalleCompo;
use App\Models\Admin\Jurie;
use App\Models\Admin\Examen;
use App\Models\JuryCandidat;
use App\Models\ConduiteVague;
use App\Models\CandidatReponse;
use App\Models\CategoriePermis;
use App\Models\Admin\AnnexeAnatt;
use App\Models\Admin\Restriction;
use App\Models\AutoEcole\AutoEcole;
use App\Models\CandidatExamenSalle;
use App\Models\AutoEcole\SuiviCandidat;
use Illuminate\Database\Eloquent\Model;
use App\Models\Candidat\DossierCandidat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $dossier_candidat_id
 * @property int $examen_id
 * @property int $auto_ecole_id
 * @property int $categorie_permis_id
 * @property int $annexe_id
 * @property string $npi
 * @property string $type_examen
 * @property string $state
 * @property string $resultat_code
 * @property string $resultat_conduite
 * @property bool $abandoned
 * @property bool $closed
 * @property int $permis_extension_id
 * @property int $permis_prealable_id
 * @property int $langue_id
 *
 * @method static Builder presentes(array $conditions), associée au scopePresentes, elle renvoie les candidats présentés à un exeman et un centre
 * @method static Builder presentesConduite(array $conditions), associée au scopePresentesConduite, elle renvoie les candidats présentés à la conduite
 * @method static Builder filter(array $conditions), associée au scopeFilter, elle filtre les dossiers
 */
class DossierSession extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Ajoute dynamiquement l'auto école.
     *
     * @return $this
     */
    public function withAutoEcole()
    {
        $autoEcole = AutoEcole::find($this->auto_ecole_id, ['id', 'name']);
        $this->setAttribute('auto_ecole', $autoEcole);
        return $this;
    }

    /**
     * Ajoute dynamiquement le candidat.
     *
     * @param array $candidats
     * @return $this
     */
    public function withCandidat(array $candidats)
    {
        $candidat = collect($candidats)->where('npi', $this->npi)->first();
        $this->setAttribute('candidat', $candidat);
        return $this;
    }

    /**
     * Ajoute dynamiquement la restriction médicale.
     *
     * @return $this
     */
    public function withRestriction(array $attr = ['id', 'name'])
    {
        $ids = json_decode($this->restriction_medical, true) ?? [];

        $restrictions = [];
        foreach ($ids as  $id) {
            $restriction = Restriction::find(intval($id), $attr);
            if (!$restriction) {
                $restrictions[] = [
                    'id' => 0,
                    'name' => "Aucune restriction"
                ];
            } else {
                $restrictions[] = $restriction;
            }
        }

        $this->setAttribute('restrictionss', $restrictions);
        return $this;
    }

    /**
     * Ajoute dynamiquement le dossier candidat et l'ancien permis.
     *
     * @return $this
     */
    public function withDossier(array $attr = ['*'])
    {
        // Récupérer le dossier candidat
        $dossier = DossierCandidat::find($this->dossier_candidat_id, $attr);

        // Vérifier si le dossier existe
        if ($dossier) {
            // Ajouter l'information du dossier à l'attribut 'dossier'
            $this->setAttribute('dossier', $dossier);

            // Récupérer et ajouter l'ancient permis à l'attribut 'ancien_permis'
            $ancienPermis = $dossier->ancienPermis; // Cette relation doit être définie dans DossierCandidat

            if ($ancienPermis) {
                $this->setAttribute('ancien_permis', $ancienPermis);
            }
        }

        return $this;
    }


    /**
     * Ajoute dynamiquement la catégorie de permis.
     *
     * @return $this
     */
    public function withCategoriePermis()
    {
        $permis = CategoriePermis::find($this->categorie_permis_id, ['id', 'name']);
        $this->setAttribute('categorie_permis', $permis);
        return $this;
    }

    /**
     * Ajoute dynamiquement l'annexe.
     *
     * @return $this
     */
    public function withAnnexe(array $attr = ['id', 'name', 'adresse_annexe'])
    {
        $annexe = AnnexeAnatt::find($this->annexe_id, $attr);
        $this->setAttribute('annexe', $annexe);
        return $this;
    }

    /**
     * Ajoute dynamiquement les chapitres.
     *
     * @return $this
     */
    public function withChapitres()
    {
        $this->setAttribute('chapitres', $this->getChapitres(['id', 'name']));
        return $this;
    }

    /**
     * Ajoute dynamiquement la langue.
     *
     * @return $this
     */
    public function withLangue()
    {
        $langue = Langue::find($this->langue_id, ['id', 'name']);
        $this->setAttribute('langue', $langue);
        return $this;
    }

    /**
     * Ajoute dynamiquement la langue.
     *
     * @return $this
     */
    public function withExamen()
    {
        $examen = Examen::find($this->examen_id);
        $this->setAttribute('examen', $examen);
        return $this;
    }

    public function withDateSuivi()
    {
        $date = null;
        $suivi = SuiviCandidat::where('dossier_session_id', $this->id)->first(['created_at']);
        if ($suivi) {
            $date = $suivi->created_at;
        }

        $this->setAttribute('date_suivi', $date);
        return $this;
    }
    /**
     * Récupère les chapitres associés à cette session de dossier.
     *
     * @param array|null $fields Les champs à sélectionner.
     * @return array
     */
    public function getChapitres(array $fields = ['id', 'name']): array
    {
        $suivi = SuiviCandidat::where('dossier_session_id', $this->id)->first(['id', 'chapitres_id']);
        if ($suivi) {
            $chapitreIds = explode(',', $suivi->chapitres_id);
            $chapitres = Chapitre::whereIn('id', $chapitreIds)->get($fields)->toArray();
            return $chapitres;
        }
        return [];
    }

    public function withVague()
    {
        $cs = CandidatExamenSalle::where('dossier_session_id', $this->id)->first(['vague_id']);
        $this->setAttribute('vague', null);
        if ($cs) {

            $vague =  Vague::find($cs->vague_id);

            $this->setAttribute('vague', $vague);
        }

        return $this;
    }

    public function withVagueConduite()
    {
        $cs = JuryCandidat::where('dossier_session_id', $this->id)->first(['conduite_vague_id']);
        $conduiteVague = null;
        if ($cs) {
            $conduiteVague =  ConduiteVague::find($cs->conduite_vague_id);
        }

        $this->setAttribute('conduiteVague', $conduiteVague);
        return $this;
    }
    public function withConduiteJury()
    {
        $cs = JuryCandidat::where('dossier_session_id', $this->id)->first(['jury_id']);
        $conduiteJury = null;
        if ($cs) {
            $conduiteJury = Jurie::find($cs->jury_id);
        }

        $this->setAttribute('conduiteJury', $conduiteJury);
        return $this;
    }
    public function withSalle()
    {
        $salle = null;
        $cs = CandidatExamenSalle::where('dossier_session_id', $this->id)->first(['salle_compo_id']);
        if ($cs) {
            $salle =  SalleCompo::find($cs->salle_compo_id);
        }


        $this->setAttribute('salle', $salle);

        return $this;
    }

    public function withNotes()
    {
        if ($this->type_examen == "conduite") {
            if ($this->candidat_salle_id) {
                $cse = CandidatExamenSalle::find($this->candidat_salle_id);
            }
        } else {
            $cse = CandidatExamenSalle::where('dossier_session_id', $this->id)->first(['id']);
        }
        $qcms = CandidatReponse::where(
            'candidat_salle_id',
            $cse->id
        )->get();


        $correctResponseCount = collect($qcms)->filter(function ($qcm) {
            return boolval($qcm->is_correct);
        })->count();


        $this->setAttribute("notes", [
            "correct_count" => $correctResponseCount,
            "count" => $qcms->count(),
        ]);
    }

    public function withExtension()
    {
        $extId = $this->permis_extension_id;
        $ext = null;
        if ($extId) {
            $ext = CategoriePermis::find($extId, ['id', 'name']);
        }

        $this->setAttribute('extension', $ext);
        return $this;
    }

    public function withPrealable()
    {
        $preableId = $this->permis_prealable_id;
        $p = null;
        if ($preableId) {
            $p = CategoriePermis::find($preableId, ['id', 'name']);
        }

        $this->setAttribute('prealable', $p);
        return $this;
    }

    public function scopePresentesConduite(Builder $query, array $conditions)
    {
        $query->filter([
            'state' => "validate",
            'resultat_code' => "success",
            'closed' => false,
            'examen_id' => data_get($conditions, 'examen_id'),
            'annexe_id' => data_get($conditions, 'annexe_id'),
            'abandoned' => false,
        ]);

        return $query;
    }

    public function scopePresentes(Builder $query, array $conditions)
    {
        $query->filter([
            'state' => "validate",
            "type_examen" => "code-conduite",
            'closed' => false,
            'examen_id' => data_get($conditions, 'examen_id'),
            'annexe_id' => data_get($conditions, 'annexe_id'),
            'abandoned' => false,
        ])->whereNotNull('categorie_permis_id')
            ->whereNotNull('langue_id')
            ->whereNotNull('examen_id')
            ->whereNotNull('annexe_id');

        return $query;
    }

    public function scopeFilter(Builder $query, array $filters)
    {

        $query->when(intval(data_get($filters, 'categorie_permis_id')), function ($query) use ($filters) {
            return $query->where('categorie_permis_id', $filters['categorie_permis_id']);
        });

        $query->when(intval(data_get($filters, 'examen_id')), function ($query) use ($filters) {
            return $query->where('examen_id', data_get($filters, 'examen_id'));
        });

        $query->when(intval(data_get($filters, 'annexe_id')), function ($query) use ($filters) {
            return $query->where('annexe_id', data_get($filters, 'annexe_id'));
        });


        $hasResultat = in_array(data_get($filters, "resultat_code"), ['failed', 'success']);
        $query->when($hasResultat, function ($query) use ($filters) {
            return $query->where('resultat_code', data_get($filters, 'resultat_code'));
        });

        $hasResultat = in_array(data_get($filters, "resultat_conduite"), ['failed', 'success']);
        $query->when($hasResultat, function ($query) use ($filters) {
            return $query->where('resultat_conduite', data_get($filters, 'resultat_conduite'));
        });

        $query->when(data_get($filters, 'state'), function ($query) use ($filters) {
            return $query->where('state', data_get($filters, 'state'));
        });

        $query->when(data_get($filters, 'state'), function ($query) use ($filters) {
            return $query->where('state', data_get($filters, 'state'));
        });

        $query->when(array_key_exists('abandoned', $filters), function ($query) use ($filters) {
            return $query->where('abandoned', boolval(data_get($filters, 'abandoned')));
        });

        $query->when(array_key_exists('closed', $filters), function ($query) use ($filters) {
            return $query->where('closed', boolval(data_get($filters, 'closed')));
        });

        $query->when(intval(data_get($filters, 'langue_id')), function ($query) use ($filters) {
            return $query->where('langue_id', data_get($filters, 'langue_id'));
        });


        $query->when(intval(data_get($filters, 'auto_ecole_id')), function ($query) use ($filters) {
            return $query->where('auto_ecole_id', data_get($filters, 'auto_ecole_id'));
        });

        $typeExamen = data_get($filters, 'type_examen');
        if (!in_array($typeExamen, ['code-conduite', 'conduite'])) {
            $typeExamen =  null;
        }
        $query->when($typeExamen, function ($query) use ($typeExamen) {
            return $query->where('type_examen', $typeExamen);
        });

        $presence = data_get($filters, 'presence');
        if (!in_array($presence, ['present', 'abscent'])) {
            $presence =  null;
        }
        $query->when($presence, function ($query) use ($presence) {
            return $query->where('presence', $presence);
        });

        $query->when(intval(data_get($filters, 'year')), function ($query) use ($filters) {
            return $query->whereYear('date_inscription', $filters['year']);
        });

        $npi =  data_get($filters, 'npi') ?? data_get($filters, 'search');
        $query->when($npi, function ($query) use ($npi) {
            return $query->where('npi', 'LIKE', "%" . trim($npi) . "%");
        });

        $query->when(array_key_exists('old_ds_rejet_id', $filters), function ($query) use ($npi) {
            return $query->whereNull('old_ds_rejet_id');
        });
    }
}
