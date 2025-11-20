<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriePermis extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'status', 'age_min', 'is_valid_age', 'montant_militaire', 'montant_etranger', 'montant', 'note_min', 'description', 'permis_prealable', 'permis_prealable_dure', 'is_extension', 'montant_extension'];

    public function chapitres()
    {
        return $this->belongsToMany(Chapitre::class, 'chapitres_categories_permis', 'categorie_permis_id', 'chapitre_id');
    }

    public function baremes()
    {
        return $this->hasMany(BaremeConduite::class);
    }
    public function trancheage()
    {
        return $this->hasMany(TrancheAge::class);
    }

    public function extensions()
    {
        return $this->hasMany(CategoriePermisExtensible::class, 'categorie_permis_id');
    }
    public function permisPrealable()
    {
        return $this->belongsTo(CategoriePermis::class, 'permis_prealable', 'id')->select('id', 'name');
    }
}
