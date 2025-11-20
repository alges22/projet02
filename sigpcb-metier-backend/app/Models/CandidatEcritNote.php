<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CandidatEcritNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidat_id',
        'vague_id',
        'examen_id',
        'note'
    ];

    public function candidat()
    {
        return $this->belongsTo(Candidat::class);
    }

}
