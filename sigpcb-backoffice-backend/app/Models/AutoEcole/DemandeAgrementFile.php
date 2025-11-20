<?php

namespace App\Models\AutoEcole;

use App\Models\Base\Commune;
use App\Models\Base\Departement;
use App\Models\AutoEcole\Promoteur;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemandeAgrementFile extends Model
{
    use HasFactory;
    /**
     * Indique à laravel de se connecter à la base de donnée de autoecole
     * Cette connection est configurée dans config/database.php
     *
     * @var string
     */
    protected $connection  = "base";

    protected $table = "demande_agrement_files";
    protected $guarded = [];

    /**
     * Renverra les carteGrise sous forme  tableau
     * Enregistre les carteGrise comme json
     */
    protected function carteGrise(): Attribute
    {
        $get = function ($file) {
            $data = json_decode($file, true);
            $data = $data ?? [];
            return array_map(fn ($file) => $file, $data);
        };
        $set = function ($file) {
            if (is_array($file)) {
                $file = json_encode($file);
            }
        };
        return new Attribute(get: $get, set: $set);
    }

    /**
     * Renverra les assuranceVisite sous forme  tableau
     * Enregistre les assuranceVisite comme json
     */
    protected function assuranceVisite(): Attribute
    {
        $get = function ($file) {
            $data = json_decode($file, true);
            $data = $data ?? [];
            return array_map(fn ($file) => $file, $data);
        };

        $set = function ($file) {
            if (is_array($file)) {
                $file = json_encode($file);
            }
        };
        return new Attribute(get: $get, set: $set);
    }
    /**
     * Renverra les photoVehicules sous forme  tableau
     * Enregistre les photoVehicules comme json
     */
    protected function photoVehicules(): Attribute
    {
        $get = function ($file) {
            $data = json_decode($file, true);
            $data = $data ?? [];
            return array_map(fn ($file) => $file, $data);
        };
        $set = function ($file) {
            if (is_array($file)) {
                $file = json_encode($file);
            }
        };
        return new Attribute(get: $get, set: $set);
    }
}