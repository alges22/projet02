<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoniteurToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'moniteur_id',
        'auto_ecole_id',
        'expire_at',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
    ];

    protected $table = "moniteur_tokens";
}