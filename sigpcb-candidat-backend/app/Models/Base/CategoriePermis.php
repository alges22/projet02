<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CategoriePermis extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "categorie_permis";
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
