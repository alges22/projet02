<?php

namespace App\Models\Examinateur;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeMoniteurRejet extends Model
{
    use HasFactory;
    protected $connection = "examinateur";
    protected  $table = "demande_moniteur_rejets";

    protected $guarded = [];

}
