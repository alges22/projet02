<?php

namespace App\Models;

use App\Models\Entreprise;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recrutement extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
}
