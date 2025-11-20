<?php

namespace App\Models;

use App\Models\User;
use App\Models\Examen;
use App\Models\Langue;
use App\Models\Chapitre;
use App\Models\AnnexeAnatt;
use App\Models\SuiviCandidat;
use App\Models\CategoriePermis;
use App\Models\DossierCandidat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 *
 * @method static Builder presentes(array $conditions), associée au scopePresentes, elle renvoie les candidats présentés à un exeman et un centre
 * @method static Builder filter(array $conditions), associée au scopeFilter, elle filtre les dossiers
 */
class DossierSession extends Model
{
    use HasFactory;

    protected $connection = "base";
    protected $table = "dossier_sessions";

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
     * Ajoute dynamiquement le dossier candidat.
     *
     * @return $this
     */
    public function withDossier()
    {
        $dossier = DossierCandidat::find($this->dossier_candidat_id);
        $this->setAttribute('dossier', $dossier);
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
    public function withAnnexe()
    {
        $annexe = AnnexeAnatt::find($this->annexe_id, ['id', 'name', 'adresse_annexe']);
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

    public function scopePresentes(Builder $query, $conditions)
    {
        $query = $query->filter([
            'state' => "validate",
            "type_examen" => "code-conduite",
            'closed' => false,
            'examen_id' => data_get($conditions, 'examen_id'),
            'annexe_id' => data_get($conditions, 'annexe_id'),
        ]);

        return $query;
    }

    public function scopeFilter(Builder $query, array $filters)
    {

         $query->when(intval(data_get($filters, 'categorie_permis_id')), function ($query) use ($filters) {
            return $query->where('categorie_permis_id', $filters['categorie_permis_id']);
        });

        /**
         *  Annuels
         */
        $query->when(data_get($filters, 'perYear'), function ($query) use ($filters) {
            $examens = Examen::select(["date_code", "id"])->whereYear("date_code", data_get($filters, 'perYear'))->get()->pluck("id");
            return $query->whereIn('examen_id', $examens);
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