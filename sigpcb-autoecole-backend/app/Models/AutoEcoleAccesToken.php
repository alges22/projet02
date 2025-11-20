<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AutoEcoleAccesToken extends PersonalAccessToken
{
    use HasFactory;

    protected $table = "auto_ecole_acces_tokens";
}
