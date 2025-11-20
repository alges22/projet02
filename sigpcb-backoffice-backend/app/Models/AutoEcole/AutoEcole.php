<?php

namespace App\Models\AutoEcole;

use App\Models\Base\Commune;
use App\Models\Base\Departement;
use App\Models\AutoEcole\Agrement;
use App\Models\AutoEcole\Moniteur;
use App\Models\AutoEcole\Vehicule;
use App\Models\AutoEcole\Promoteur;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class AutoEcole
 *
 * @property int $id
 * @property string $promoteur_name
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string|null $adresse
 * @property string $code
 * @property string $password
 * @property string $num_ifu
 * @property string|null $promoteur_phone
 * @property bool $cpu_accepted
 * @property bool $status
 * @property bool $is_verify
 * @property string|null $email_verified_at
 * @property int|null $commune_id
 * @property int|null $departement_id
 * @property string|null $departement_name
 * @property string|null $numero_autorisation
 * @property int|null $annee_creation
 * @property string|null $fichier_ifu
 * @property string|null $fichier_rccm
 *  @property int $promoteur_id
 * @package App\Models\AutoEcole
 */
class AutoEcole extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "auto_ecoles";
    protected $guarded = [];

    public function promoteur()
    {
        return $this->belongsTo(Promoteur::class,);
    }
    public function departement()
    {
        return $this->belongsTo(Departement::class,);
    }
    public function commune()
    {
        return $this->belongsTo(Commune::class,);
    }
    public function moniteur()
    {
        return $this->belongsTo(Moniteur::class,);
    }
    public function agrement()
    {
        return $this->belongsTo(Agrement::class,);
    }
    public function vehicules()
    {
        return $this->hasMany(Vehicule::class);
    }
}