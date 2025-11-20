<?php

namespace App\Models;

use App\Models\Admin\AnnexeAnatt;
use Illuminate\Database\Eloquent\Model;
use App\Models\Candidat\DossierCandidat;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalleCompo extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'annexe_anatt_id', 'contenance'];

    // public function centreExamen()
    // {
    //     return $this->belongsTo(CentreExamen::class);
    // }

    public function vagues()
    {
        return $this->hasMany(Vague::class);
    }
    public function vaguesCompos()
    {
        return $this->belongsToMany(DossierCandidat::class, 'salle_compos_vague');
    }

    /**
     * Ajoute l'annexe
     *
     * @param array $attrs
     * @return $this
     */
    public function withAnnexe($attrs = ['*'])
    {
        $annexe = AnnexeAnatt::find($this->annexe_anatt_id, $attrs);

        $this->setAttribute("annexe", $annexe);

        return $this;
    }
}
