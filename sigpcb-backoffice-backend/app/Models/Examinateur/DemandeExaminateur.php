<?php

namespace App\Models\Examinateur;

use App\Models\AnnexeAnatt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemandeExaminateur extends Model
{
    use HasFactory;
    protected $connection = "examinateur";
    protected  $table = "demande_examinateurs";

    protected $guarded = [];

}
