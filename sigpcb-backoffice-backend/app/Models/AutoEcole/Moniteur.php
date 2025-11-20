<?php

namespace App\Models\AutoEcole;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moniteur extends Model
{
    use HasFactory;
    protected $connection  = "base";
    protected $table = "auto_ecole_moniteurs";
    protected $guarded = [];
}
