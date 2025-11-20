<?php

namespace App\Models\Candidat;

use App\Models\AncienPermis;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DossierCandidat extends Model
{
    use HasFactory;

    protected $table = "dossier_candidats";

    protected $guarded = [];

    // Scope pour filtrer les dossiers candidats avec succès
    public function scopeSuccessful($query)
    {
        return $query->where('state', 'success');
    }
    public function ancienPermis()
    {
        return $this->hasOne(AncienPermis::class, 'dossier_candidat_id'); // Si un DossierCandidat a un AncienPermis
    }
}
