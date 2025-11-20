<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historique extends Model
{
    use HasFactory;
    protected $casts = [
        'created_at' => 'datetime',
    ];
    protected $guarded = [];

    protected $table = "auto_ecole_notifications";
}