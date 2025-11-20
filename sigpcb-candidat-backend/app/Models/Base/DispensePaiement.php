<?php

namespace App\Models\Base;

use App\Models\DossierSession as ModelsDossierSession;
use App\Models\Examen;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispensePaiement extends Model
{
    use HasFactory;
    protected $connection  = "base";
    protected $table = "dispense_paiements";
    protected $guarded = [];
    
}
