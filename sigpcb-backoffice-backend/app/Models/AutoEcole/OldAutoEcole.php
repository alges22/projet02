<?php

namespace App\Models\AutoEcole;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldAutoEcole extends Model
{
    use HasFactory;
    protected $connection  = "base";
    protected $table = "old_auto_ecoles";
    protected $guarded = [];
}
