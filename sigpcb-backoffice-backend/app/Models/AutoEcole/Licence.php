<?php

namespace App\Models\AutoEcole;

use App\Models\AutoEcole\AutoEcole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Licence extends Model
{
    use HasFactory;
    protected $connection  = "base";
    protected $table = "auto_ecole_licences";
    protected $guarded = [];

    protected $cats = [
        'date_debut' => "datetime",
        'date_fin' => "datetime",
    ];


    public function autoecole()
    {
        return $this->belongsTo(AutoEcole::class, 'auto_ecole_id');
    }
}