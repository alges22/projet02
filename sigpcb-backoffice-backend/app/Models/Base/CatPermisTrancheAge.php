<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatPermisTrancheAge extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "cat_permis_tranche_ages";
    protected $guarded = [];

    public function categoriePermis()
    {
        return $this->belongsTo(CategoriePermis::class);
    }

    public function trancheAge()
    {
        return $this->belongsTo(TrancheAge::class);
    }
}
