<?php

namespace App\Models;

use App\Models\Base\CategoriePermis;
use App\Models\Candidat\DossierSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $dossier_session_id
 * @property string $categorie_permis_id
 * @property string $npi
 * @property string $code_permis
 * @property \Carbon\Carbon $delivered_at
 * @property \Carbon\Carbon $expired_at
 * @property-read CategoriePermis $categoriePermis
 * @property-read array $candidat
 * @property-read DossierSession $dossierSession
 */
class Permis extends Model
{
    use HasFactory;
    protected  $connection = "base";
    protected $guarded = [];

    public function categoriePermis()
    {
        return $this->belongsTo(CategoriePermis::class);
    }

    public function dossierSession()
    {
        return $this->belongsTo(DossierSession::class);
    }

    public function scopeFilter(Builder $query, array $filters = [])
    {
        $query->when(intval(data_get($filters, 'annexe_id')), function (Builder $query, $annexeId) {
            $query->whereHas("dossierSession", function ($query) use ($annexeId) {
                $query->where("annexe_id", $annexeId);
            });
        });
        $query->when(intval(data_get($filters, 'examen_id')), function (Builder $query, $id) {
            $query->whereHas("dossierSession", function ($query) use ($id) {
                $query->where("examen_id", $id);
            });
        });

        $query->when(intval(data_get($filters, 'categorie_permis_id')), function ($query) use ($filters) {
            return $query->where('categorie_permis_id', $filters['categorie_permis_id']);
        });

        $npi =  data_get($filters, 'npi') ?? data_get($filters, 'search');
        $query->when($npi, function ($query) use ($npi) {
            return $query->where('npi', 'LIKE', "%" . trim($npi) . "%");
        });
    }

    public function withCandidat(array $candidats)
    {
        $candidat = collect($candidats)->where('npi', $this->npi)->first();
        $this->setAttribute('candidat', $candidat);
        return $this;
    }
}
