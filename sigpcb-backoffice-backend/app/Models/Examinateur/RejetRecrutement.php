<?php

namespace App\Models\Examinateur;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejetRecrutement extends Model
{
    use HasFactory;
    protected $connection = "examinateur";
    protected  $table = "recrutement_rejets";

    protected $guarded = [];

}