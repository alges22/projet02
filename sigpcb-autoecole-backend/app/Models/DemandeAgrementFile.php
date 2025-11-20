<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class DemandeAgrementFile
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $demande_agrement_id
 * @property string $nat_promoteur
 * @property string $casier_promoteur
 * @property string $ref_promoteur
 * @property string $reg_commerce
 * @property string $attest_fiscale
 * @property string $attest_reg_organismes
 * @property string $descriptive_locaux
 * @property string $permis_moniteurs
 * @property string|null $copie_statut
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class DemandeAgrementFile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected function natPromoteur(): Attribute
    {
        return new Attribute(get: fn ($nat_promoteur) => asset('storage/' . $nat_promoteur));
    }

    protected function casierPromoteur(): Attribute
    {
        return new Attribute(get: fn ($casier_promoteur) => asset('storage/' . $casier_promoteur));
    }

    protected function refPromoteur(): Attribute
    {
        return new Attribute(get: fn ($ref_promoteur) => asset('storage/' . $ref_promoteur));
    }

    protected function regCommerce(): Attribute
    {
        return new Attribute(get: fn ($reg_commerce) => asset('storage/' . $reg_commerce));
    }

    protected function attestFiscale(): Attribute
    {
        return new Attribute(get: fn ($attest_fiscale) => asset('storage/' . $attest_fiscale));
    }

    protected function attestRegOrganismes(): Attribute
    {
        return new Attribute(get: fn ($attest_reg_organismes) => asset('storage/' . $attest_reg_organismes));
    }

    protected function descriptiveLocaux(): Attribute
    {
        return new Attribute(get: fn ($descriptive_locaux) => asset('storage/' . $descriptive_locaux));
    }

    protected function permisMoniteurs(): Attribute
    {
        return new Attribute(get: fn ($permis_moniteurs) => asset('storage/' . $permis_moniteurs));
    }

    protected function copieStatut(): Attribute
    {
        return new Attribute(get: fn ($copie_statut) => asset('storage/' . $copie_statut));
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