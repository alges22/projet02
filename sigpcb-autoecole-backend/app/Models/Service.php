<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    public const DEMANDE_AGREMENT = "demande-agrement";


    public static function demandeAgrementAmount()
    {
        //le fichier de configuration se trouve dans config/anatt.php
        return config('anatt.amounts.demande-agrement');
    }
}
