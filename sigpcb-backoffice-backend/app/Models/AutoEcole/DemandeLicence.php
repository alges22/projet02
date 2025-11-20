<?php

namespace App\Models\AutoEcole;

use App\Models\Base\Commune;
use App\Models\Base\Departement;
use App\Models\AutoEcole\AutoEcole;
use App\Models\AutoEcole\Promoteur;
use Illuminate\Database\Eloquent\Model;
use App\Models\AutoEcole\DemandeLicenceFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DemandeLicence extends Model
{
    use HasFactory;
    /**
     * Indique à laravel de se connecter à la base de donnée de autoecole
     * Cette connection est configurée dans config/database.php
     *
     * @var string
     */
    protected $connection  = "base";

    protected $table = "demande_licences";
    protected $guarded = [];

    public function fiche()
    {
        return $this->hasOne(DemandeLicenceFile::class);
    }
    public function autoecole()
    {
        return $this->belongsTo(AutoEcole::class, 'auto_ecole_id');
    }
    public function promoteur()
    {
        return $this->belongsTo(Promoteur::class, 'promoteur_id');
    }

    /**
     * Renverra les véhicules sous forme  tableau
     * Enregistre les véhicules comme json
     */
    protected function vehicules(): Attribute
    {
        $get = function ($vehicule) {
            if (is_array($vehicule)) {
                return $vehicule;
            }
            return json_decode($vehicule, true) ?? [];
        };
        $set = function ($vehicule) {
            if (is_array($vehicule)) {
                return json_encode($vehicule);
            }
            return $vehicule;
        };
        return new Attribute(get: $get, set: $set);
    }

    /**
     * Renverra les moniteurs sous forme  tableau
     * Enregistre les moniteurs comme json
     */
    protected function moniteurs(): Attribute
    {
        $get = function ($moniteur) {
            if (is_array($moniteur)) {
                return $moniteur;
            }
            return json_decode($moniteur, true) ?? [];
        };
        $set = function ($moniteur) {
            if (is_array($moniteur)) {
                return json_encode($moniteur);
            }
            return $moniteur;
        };
        return new Attribute(get: $get, set: $set);
    }
}