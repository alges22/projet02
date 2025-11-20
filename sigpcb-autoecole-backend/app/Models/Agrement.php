<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agrement extends Model
{

    use HasFactory;

    protected $fillable = ['code', 'promoteur_id', 'date_obtention', 'demande_agrement_id'];

    protected $casts = [
        'date_obtention' => 'datetime',
    ];

    public function autoEcole()
    {
        return $this->hasOne(AutoEcole::class);
    }
}
