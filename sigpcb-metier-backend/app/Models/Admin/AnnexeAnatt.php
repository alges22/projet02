<?php

namespace App\Models\Admin;

use App\Models\Base\SalleCompo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnexeAnatt extends Model
{
    use HasFactory;

    protected $connection = "admin";
    protected $table = "annexe_anatts";

    /**
     * Liste des salles  de l'annexe courante
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, SalleCompo>
     */
    public function salles()
    {
        $salles = SalleCompo::where('annexe_anatt_id', $this->id)->get();
        return $salles;
    }
    public function annexeAnattDepartements(){
        return $this->hasMany(AnnexeAnattDepartement::class);
    }
}
