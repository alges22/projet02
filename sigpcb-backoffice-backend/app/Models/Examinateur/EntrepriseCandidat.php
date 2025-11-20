<?php

namespace App\Models\Examinateur;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntrepriseCandidat extends Model
{
    use HasFactory;
    protected $connection = "examinateur";
    protected  $table = "candidats";

    protected $guarded = [];

}