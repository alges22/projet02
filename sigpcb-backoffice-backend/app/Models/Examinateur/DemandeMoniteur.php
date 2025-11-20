<?php

namespace App\Models\Examinateur;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemandeMoniteur extends Model
{
    use HasFactory;
    protected $connection = "examinateur";
    protected  $table = "demande_moniteurs";

    protected $guarded = [];

}
