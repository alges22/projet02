<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @property int $id
 * @property string $name
 * @property bool $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class AnnexeAnatt extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'adresse_annexe',
        'phone',
        'conduite_lieu_adresse',
        'commune_id',
        'departement_id',
        'status',
        'email'
    ];

    public function annexeAnattDepartements(){
        return $this->hasMany(AnnexeAnattDepartement::class);
    }
}
