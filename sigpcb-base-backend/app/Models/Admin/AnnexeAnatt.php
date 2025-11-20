<?php

namespace App\Models\Admin;

use App\Models\SalleCompo;
use App\Models\Departement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function getDepartements()
    {
        $departements = AnnexeAnattDepartement::where('annexe_anatt_id', $this->id)
            ->get()->pluck('departement_id')->toArray();
        return Departement::whereIn('id', $departements)->get();
    }
}
