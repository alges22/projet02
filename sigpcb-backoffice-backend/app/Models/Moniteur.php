<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moniteur extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        "date_validation" => "datetime"
    ];
}