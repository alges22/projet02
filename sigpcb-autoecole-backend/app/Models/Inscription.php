<?php

namespace App\Models;

use App\Models\AutoEcole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'npi',
        'auto_ecole_id',
        'code',
        'date_inscription',
        'status',
        'date_resilience',
    ];

    protected $dates = [
        'date_inscription',
        'date_resilience',
    ];

    protected $table = "auto_ecole_candidat_inscriptions";

    // Relations
    public function autoEcole()
    {
        return $this->belongsTo(AutoEcole::class);
    }
}
