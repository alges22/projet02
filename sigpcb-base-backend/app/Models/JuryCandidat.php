<?php

namespace App\Models;

use App\Models\Admin\Jurie;
use App\Models\AutoEcole\AutoEcole;
use App\Models\Candidat\DossierSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JuryCandidat extends Model
{
    use HasFactory;

    public const SIGNATURE_DISK = "public";
    protected $fillable = [
        'npi', 'jury_id', 'examen_id', 'resultat_conduite', 'annexe_id', 'dossier_session_id', 'langue_id', 'categorie_permis_id', 'conduite_vague_id', 'closed'
    ];


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

    public function withJuries()
    {
        $cs = JuryCandidat::where('dossier_session_id', $this->id)->first(['jury_id']);
        $conduiteJury = null;
        if ($cs) {
            $conduiteJury = Jurie::find($cs->jury_id);
        }

        $this->setAttribute('conduiteJury', $conduiteJury);
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
    public function vague()
    {
        return $this->belongsTo(ConduiteVague::class);
    }
    public function withVague(array $attr = ['*'])
    {
        $vague = ConduiteVague::find($this->conduite_vague_id, $attr);
        $this->setAttribute('vague', $vague);
        return $this;
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

        $npi =  data_get($filters, 'npi') ?? data_get($filters, 'search');
        $query->when($npi, function ($query) use ($npi) {
            return $query->where('npi', 'LIKE', "%" . trim($npi) . "%");
        });
    }
}
