<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspecteurSalle extends Model
{
    use HasFactory;
    protected $fillable= ['inspecteur_id','salle_compo_id','examen_id'] ;
}
