<?php

namespace App\Models\AutoEcole;

use App\Models\Base\Commune;
use App\Models\Base\Departement;
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
 *
 * @package App\Models\AutoEcole
 */
class AutoEcole extends Model
{
    use HasFactory;

    protected $connection  = "base";
    protected $table = "auto_ecoles";

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }
}
