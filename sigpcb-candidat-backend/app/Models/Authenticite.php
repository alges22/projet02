<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Authenticite extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected $fillable = [
        'email',
        'npi',
        'num_permis',
        'permis_file',
        'state',
        'date_validation',
        'date_rejet',
    ];
}
