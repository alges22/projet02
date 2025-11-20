<?php

namespace App\Models\Examinateur;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecrutementEpreuve extends Model
{
    use HasFactory;
    protected $connection = "examinateur";
    protected  $table = "recrutement_epreuves";

    protected $guarded = [];
}
