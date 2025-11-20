<?php

namespace App\Models\Candidat;

use App\Models\Examen;
use App\Models\Permis;
use App\Models\Base\Langue;
use App\Models\Restriction;
use App\Models\Base\Chapitre;
use App\Models\AutoEcole\AutoEcole;
use App\Models\Base\CategoriePermis;
use App\Models\Candidat\SuiviCandidat;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base\CandidatExamenSalle;
use App\Models\Base\DispensePaiement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $examen_id
 * @property int $dossier_id
 * @property int $auto_ecole_id
 * @property int $categorie_permis_id
 * @property string $npi
 * @property int $annexe_id
 * @property int $permis_extension_id
 * @property string $state
 * @property bool $abandoned
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read  AutoEcole|null $autoEcole
 * @property-read CategoriePermis|null $categoriePermis
 * @property-read Permis[] | Collection<int, Permis> $permis
 * @property-read DossierCandidat $dossier
 * @property-read array|null $candidat
 */

class DossierSession extends Model
{
    use HasFactory;
    protected  $connection = "base";
    protected $guarded = [];

    public function scopeInscrits(Builder $query, array $conditions)
    {
        $conditions['state'] = "validate";
        $conditions['abandoned'] = false;
        return $query->filter($conditions);
    }

    public function withAutoEcole()
    {
        $autoEcole = AutoEcole::find($this->auto_ecole_id, ['id', 'name']);
        $this->setAttribute('auto_ecole', $autoEcole);
        return $this;
    }

    public function autoEcole()
    {
        return $this->belongsTo(AutoEcole::class);
    }

    public function categoriePermis()
    {
        return $this->belongsTo(CategoriePermis::class);
    }
    // Relation inverse avec DispensePaiement
    public function dispenses()
    {
        return $this->hasMany(DispensePaiement::class);
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
    public function scopeFilter(Builder $query, array $filters)
    {

        $query->when(intval(data_get($filters, 'permis_extension_id')), function ($query, $permisExtensionId) {
            return $query->where('permis_extension_id', $permisExtensionId);
        });

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
        $presence_conduite = data_get($filters, 'presence_conduite');
        if (!in_array($presence_conduite, ['present', 'absent'])) {
            $presence_conduite =  null;
        }
        $query->when($presence_conduite, function ($query, $presence_conduite) {
            return $query->where('presence_conduite', $presence_conduite);
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

    public function scopeAdmis(Builder $query, array $filters = [])
    {
        $query->filter($filters + [
            'resultat_code' => 'success',
            'resultat_conduite' => 'success',
            'state' => 'validate',
            'abandoned' => false,
        ]);

        return $query;
    }

    public function permis()
    {
        return $this->hasMany(Permis::class);
    }

    public function dossier()
    {
        return $this->belongsTo(DossierCandidat::class, 'dossier_candidat_id');
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class);
    }

    public function exmaneSalle()
    {
        return $this->hasOne(CandidatExamenSalle::class);
    }

    public function langue()
    {
        return $this->belongsTo(Langue::class);
    }

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
}
