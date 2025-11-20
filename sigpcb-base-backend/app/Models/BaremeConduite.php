<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaremeConduite extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'categorie_permis_id', 'poids'];

    public function categoriePermis()
    {
        return $this->belongsTo(CategoriePermis::class);
    }
    public function subBaremes()
    {
        return $this->hasMany(SubBareme::class);
    }

}
