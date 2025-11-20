<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['questions', 'candidat_salle_id'];

    protected $casts = [
        'questions' => 'array',
    ];
}
