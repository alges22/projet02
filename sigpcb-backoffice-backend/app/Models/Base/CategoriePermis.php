<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use App\Models\Base\CategoriePermisExtensible;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriePermis extends Model
{
    use HasFactory;
    protected $connection  = "base";
    protected $table = "categorie_permis";
    protected $guarded = [];

    public function extensions()
    {
        return $this->hasMany(CategoriePermisExtensible::class, 'categorie_permis_id');
    }
    public function chapitres()
    {
        return $this->belongsToMany(Chapitre::class, 'chapitres_categories_permis', 'categorie_permis_id', 'chapitre_id');
    }
    public function baremes()
    {
        return $this->hasMany(BaremeConduite::class);
    }
    public function withExtensions()
    {
        $cps = CategoriePermisExtensible::where('categorie_permis_id', $this->id)->get();
        $ext = [];
        foreach ($cps as $key => $value) {
            $ext[] = CategoriePermis::find($value['categorie_permis_extensible_id']);
        }

        $this->setAttribute('ext', $ext);

        return $this;
    }

    public function trancheage()
    {
        return $this->hasMany(TrancheAge::class);
    }

    public function permisPrealable()
    {
        return $this->belongsTo(CategoriePermis::class, 'permis_prealable', 'id')->select('id', 'name');
    }
}
