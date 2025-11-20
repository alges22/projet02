<?php

namespace App\Models\AutoEcole;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemandeAgrementRejet extends Model
{
    use HasFactory;
    /**
     * Indique à laravel de se connecter à la base de donnée de autoecole
     * Cette connection est configurée dans config/database.php
     *
     * @var string
     */
    protected $connection  = "base";

    protected $table = "demande_agrement_rejets";
    protected $guarded = [];
}
