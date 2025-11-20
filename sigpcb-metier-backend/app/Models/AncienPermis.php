<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AncienPermis extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function oldPermis()
    {
        return $this->belongsTo(DossierCandidat::class);
    }
}