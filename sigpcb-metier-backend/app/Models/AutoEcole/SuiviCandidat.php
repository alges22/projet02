<?php

namespace App\Models\AutoEcole;

use App\Models\Base\Chapitre;
use App\Models\Base\CategoriePermis;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class SuiviCandidat
 *
 * @package App\Models\AutoEcole
 *
 * @property int $id
 * @property int $auto_ecole_id
 * @property int $categorie_permis_id
 * @property int $langue_id
 * @property int|null $examen_id
 * @property int $annexe_id
 * @property int $dossier_candidat_id
 * @property int $dossier_session_id
 * @property string $chapitres_id
 * @property string $npi
 * @property bool $status
 * @property bool $certification
 * @property string $state
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 */
class SuiviCandidat extends AutoEcole
{
    use HasFactory;

    protected $table = "suivi_candidats";

    protected $guarded = [];



    /**
     * Recupère les chapitres
     */
    public function getChapitres($select = ['id', "name"])
    {
        $chapitesIds = explode(',', $this->chapitres_id);
        return Chapitre::findMany($chapitesIds, $select);
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
}
