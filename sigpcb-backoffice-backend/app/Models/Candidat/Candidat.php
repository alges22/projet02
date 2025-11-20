<?php

namespace App\Models\Candidat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidat extends Model
{
    use HasFactory;
    /**
     * Indique à laravel de se connecter à la base de donnée de candidat
     * Cette connection est configurée dans config/database.php
     *
     * @var string
     */
    protected $connection = "base";

    protected $table = "candidats";
}
