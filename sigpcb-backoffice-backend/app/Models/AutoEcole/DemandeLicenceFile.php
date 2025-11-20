<?php

namespace App\Models\AutoEcole;

use App\Models\Base\Commune;
use App\Models\Base\Departement;
use App\Models\AutoEcole\Promoteur;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemandeLicenceFile extends Model
{
    use HasFactory;
    /**
     * Indique à laravel de se connecter à la base de donnée de autoecole
     * Cette connection est configurée dans config/database.php
     *
     * @var string
     */
    protected $connection  = "base";

    protected $table = "demande_licence_files";
    protected $guarded = [];

    protected function carteGrise(): Attribute
    {
        $get = function ($file) {
            $data = json_decode($file, true);
            return $data ?? [];
        };
        $set = function ($file) {
            if (is_array($file)) {
                $file = json_encode($file);
            }
            return $file;
        };
        return new Attribute(get: $get, set: $set);
    }

    protected function assuranceVisite(): Attribute
    {
        $get = function ($file) {
            $data = json_decode($file, true);
            return $data ?? [];
        };

        $set = function ($file) {
            if (is_array($file)) {
                $file = json_encode($file);
            }
            return $file;
        };
        return new Attribute(get: $get, set: $set);
    }
    protected function photoVehicules(): Attribute
    {
        $get = function ($file) {
            $data = json_decode($file, true);
            return $data ?? [];
        };
        $set = function ($file) {
            if (is_array($file)) {
                $file = json_encode($file);
            }
            return $file;
        };
        return new Attribute(get: $get, set: $set);
    }
}