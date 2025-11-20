<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeLicenceFile extends Model
{
    use HasFactory;

    protected $fillable =  [
        'diplome_moniteur',
        'permis_moniteurs',
        'carte_grise',
        'assurance_visite',
        'photo_vehicules',
        'demande_licence_id'
    ];

    protected function diplomeMoniteur(): Attribute
    {
        return new Attribute(get: fn ($diplome_moniteur) => asset('storage/' . $diplome_moniteur));
    }

    protected function permisMoniteurs(): Attribute
    {
        return new Attribute(get: fn ($permis_moniteurs) => asset('storage/' . $permis_moniteurs));
    }
    protected function carteGrise(): Attribute
    {
        $get = function ($file) {
            $data = json_decode($file, true);
            $data = $data ?? [];
            return array_map(fn ($file) => asset('storage/' . $file), $data);
        };
        return new Attribute(get: $get);
    }

    protected function assuranceVisite(): Attribute
    {
        $get = function ($file) {
            $data = json_decode($file, true);
            $data = $data ?? [];
            return array_map(fn ($file) => asset('storage/' . $file), $data);
        };
        return new Attribute(get: $get);
    }
    protected function photoVehicules(): Attribute
    {
        $get = function ($file) {
            $data = json_decode($file, true);
            $data = $data ?? [];
            return array_map(fn ($file) => asset('storage/' . $file), $data);
        };
        return new Attribute(get: $get);
    }
}
