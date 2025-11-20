<?php

namespace App\Models;

use App\Models\Admin\Examen;
use App\Models\Admin\AnnexeAnatt;
use App\Models\AutoEcole\AutoEcole;
use App\Models\Candidat\DossierSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $npi
 * @property int $vague_id
 * @property int $dossier_session_id
 * @property int $salle_compo_id
 * @property int $annexe_id
 * @property int $examen_id
 * @property int $langue_id
 * @property int $categorie_permis_id
 * @property int $num_table
 * @property int $code
 * @property string|null $emargement
 * @property string|null $presence
 * @property \Carbon\Carbon|null $emargement_at
 * @property \Carbon\Carbon|null $abscence_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property  boolean $closed
 *
 * @property-read \App\Models\Vague|null $vague
 * @property-read  DossierSession|null $dossier_session
 * @property-read  CandidatQuestion|null $question
 * @property-read  Langue|null $langue
 * @property-read  CategoriePermis |null $categorie_permis
 * @property-read \App\Models\SalleCompo|null $salleCompo
 * @property-read  AnnexeAnatt|null $annexe
 * @property-read  Examen|null $examen
 */
class CandidatExamenSalle extends Model
{
    public const SIGNATURE_DISK = "public";
    protected $fillable = [
        'npi',
        'vague_id',
        'dossier_session_id',
        'salle_compo_id',
        'emargement_at',
        'annexe_id',
        'examen_id',
        'num_table',
        'emargement',
        'presence',
        'langue_id',
        'categorie_permis_id',
        'closed',
        'dossier_candidat_id'
    ];

    protected $casts = [
        'emargement_at' => "datetime",
        'abscence_at' => "datetime",
        'closed' => 'boolean',
    ];
    public function vague()
    {
        return $this->belongsTo(Vague::class);
    }

    public function withSalleCompo(array $attr = ['id', 'name'])
    {
        $salle = SalleCompo::find($this->salle_compo_id, $attr);
        $this->setAttribute('salle', $salle);
        return $this;
    }

    public function withDossierSession(array $attr = ['id', 'langue_id', 'categorie_permis_id'])
    {
        $this->setAttribute('dossier_session', DossierSession::find($this->dossier_session_id, $attr));
        return $this;
    }

    public function withLangue(array $attr = ['id', 'name'])
    {
        $langue = Langue::find($this->langue_id, $attr);
        $this->setAttribute('langue', $langue);
        return $this;
    }

    public function withVague(array $attr = ['*'])
    {
        $vague = Vague::find($this->vague_id, $attr);
        $this->setAttribute('vague', $vague);
        return $this;
    }

    public function withCategoriePermis(array $attr = ['id', 'name'])
    {
        $permis = CategoriePermis::find($this->categorie_permis_id, $attr);
        $this->setAttribute('categorie_permis', $permis);
        return $this;
    }

    public function withAutoEcole()
    {
        $ds = DossierSession::find($this->dossier_session_id, ['auto_ecole_id']);
        $this->setAttribute('auto_ecole', AutoEcole::find($ds->auto_ecole_id, ['id', 'name']));
        return $this;
    }
    public function scopeFilter(Builder $query, array $filters)
    {

        $query->when(intval(data_get($filters, 'categorie_permis_id')), function ($query) use ($filters) {
            return $query->where('categorie_permis_id', $filters['categorie_permis_id']);
        });

        $query->when(intval(data_get($filters, 'salle_compo_id')), function ($query) use ($filters) {
            return $query->where('salle_compo_id', $filters['salle_compo_id']);
        });

        if (isset($filters['closed']) && is_bool($filters['closed'])) {
            $query->where('status',  'closed');
        }

        $query->when(intval(data_get($filters, 'vague_id')), function ($query) use ($filters) {
            return $query->where('vague_id', $filters['vague_id']);
        });

        $query->when(intval(data_get($filters, 'examen_id')), function ($query) use ($filters) {
            return $query->where('examen_id', data_get($filters, 'examen_id'));
        });

        $query->when(intval(data_get($filters, 'annexe_id')), function ($query) use ($filters) {
            return $query->where('annexe_id', data_get($filters, 'annexe_id'));
        });

        $npi =  data_get($filters, 'npi') ?? data_get($filters, 'search');
        $query->when($npi, function ($query) use ($npi) {
            return $query->where('npi', 'LIKE', "%" . trim($npi) . "%");
        });

        $presence = data_get($filters, 'presence');
        if (!in_array($presence, ['present', 'abscent'])) {
            $presence =  null;
        }
        $query->when($presence, function ($query) use ($presence) {
            return $query->where('presence', $presence);
        });
    }

    public function reponses()
    {
        return $this->hasMany(CandidatReponse::class, 'candidat_salle_id');
    }

    public function question()
    {
        return $this->hasOne(CandidatQuestion::class, 'candidat_salle_id');
    }
}
