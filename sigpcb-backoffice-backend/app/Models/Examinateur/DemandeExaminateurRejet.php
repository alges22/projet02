<?php

namespace App\Models\Examinateur;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeExaminateurRejet extends Model
{
    use HasFactory;
    protected $connection = "examinateur";
    protected  $table = "demande_examinateur_rejets";

    protected $guarded = [];

}
