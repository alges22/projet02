<?php

namespace App\Models\Examinateur;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExaminateurParcourSuivi extends Model
{
    use HasFactory;
    protected $connection = "examinateur";
    protected  $table = "eservice_parcour_suivis";

    protected $guarded = [];

}