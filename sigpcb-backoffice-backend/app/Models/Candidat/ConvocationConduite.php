<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConvocationConduite extends Model
{
    use HasFactory;
    protected $connection = "base";
    public function dossierSession()
    {
        return $this->belongsTo(DossierSession::class);
    }
}
