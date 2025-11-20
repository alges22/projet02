<?php

namespace App\Models\AutoEcole;

use App\Models\AutoEcole\AutoEcole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicule extends Model
{
    use HasFactory;
    protected $connection  = "base";
    protected $table = "vehicules";
    protected $guarded = [];

    public function atoecole()
    {
        return $this->belongsTo(AutoEcole::class,);
    }
}
