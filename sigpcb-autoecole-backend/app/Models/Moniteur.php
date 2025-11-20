<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moniteur extends Model
{
    use HasFactory;

    protected $with = ['autoEcole'];
    protected $guarded = [];
    protected $table = "auto_ecole_moniteurs";

    public function autoEcole()
    {
        return $this->belongsTo(AutoEcole::class);
    }
}