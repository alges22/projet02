<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeAgrementRejet extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function demandeAgrement()
    {
        return $this->belongsTo(DemandeAgrement::class);
    }
}
