<?php

namespace App\Models\AutoEcole;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historique extends Model
{
    use HasFactory;
    protected $connection  = "base";
    protected $table = "auto_ecole_notifications";
    protected $guarded = [];
}
