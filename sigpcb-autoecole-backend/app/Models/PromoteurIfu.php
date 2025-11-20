<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Importez Carbon pour manipuler les dates et heures

class PromoteurIfu extends Model
{
    use HasFactory;

    protected $fillable = [
        'npi',
        'verify_code',
        'verify_code_expire',
        'verified',
        'ifu',
    ];

    protected $casts = [
        'verify_code_expire' => 'datetime',
    ];
}
