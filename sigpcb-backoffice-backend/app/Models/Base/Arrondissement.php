<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Arrondissement extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "arrondissements";
    protected $guarded = [];
    
    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }
}
