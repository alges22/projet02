<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriePermisExtensible extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "categorie_permis_extensibles";
    protected $guarded = [];
    public function categoriePermis()
    {
        return $this->belongsTo(CategoriePermis::class);
    }
}
