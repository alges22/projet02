<?php

namespace App\Models\Base;

use App\Models\Examen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Permis extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "permis";
    public function categoriePermis()
    {
        return $this->belongsTo(CategoriePermis::class);
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class, 'examen_id');
    }
}
