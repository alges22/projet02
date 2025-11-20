<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuiviCandidat extends Model
{
    use HasFactory;
    protected $fillable = [
        'auto_ecole_id',
        'categorie_permis_id',
        'langue_id',
        'annexe_id',
        'examen_id',
        'dossier_candidat_id',
        "dossier_session_id",
        'chapitres_id',
        'status',
        'is_valid',
        'certification',
        'npi',
        'state'
    ];

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
        $chapitreIds = explode(',', $this->chapitres_id);
        $chapitres = Chapitre::whereIn('id', $chapitreIds)->get($fields)->toArray();
        return $chapitres;
    }
}
