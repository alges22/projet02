<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatPermisTrancheAge extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'categorie_permis_id', 'tranche_age_id', 'validite'];

    public function categoriePermis()
    {
        return $this->belongsTo(CategoriePermis::class);
    }

    public function trancheAge()
    {
        return $this->belongsTo(TrancheAge::class);
    }
}
