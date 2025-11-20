<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrancheAge extends Model
{
    use HasFactory;

    protected $fillable = ['id','categorie_permis_id','age_min', 'validite', 'age_max', 'status'];
}
