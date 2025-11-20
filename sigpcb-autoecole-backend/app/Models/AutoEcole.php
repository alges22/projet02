<?php

namespace App\Models;

use Carbon\Carbon;
use App\Services\Help;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $num_ifu
 * @property int $promoteur_id
 * @property Licence $licence
 * @property Licence $name
 * @property User $promoteur
 * @property int $departement_id
 */
class AutoEcole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'adresse',
        'password',
        'commune_id',
        'phone',
        'numero_autorisation',
        'annee_creation',
        'num_ifu',
        'status',
        'cpu_accepted',
        'commune_id',
        'departement_id',
        'agrement_id',
        'promoteur_id',
        'code',
        'imported'
    ];

    protected $with = ['commune', 'departement', 'agrement'];
    protected $hidden = ['code'];

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function moniteurs()
    {
        return $this->hasMany(Moniteur::class);
    }

    public function  agrement()
    {
        return $this->belongsTo(Agrement::class);
    }

    public function  promoteur()
    {
        return $this->belongsTo(User::class);
    }

    public function lastLicence()
    {
        $licence = Licence::latest()->where('auto_ecole_id', $this->id)->first();
        $this->setAttribute('licence', $licence);
        return $licence;
    }

    public function lastDemandeLicence()
    {
        $dl = DemandeLicence::latest()->where('auto_ecole_id', $this->id)->first();
        $this->setAttribute('demande_licence', $dl);
        return $dl;
    }

    public function hasLicence()
    {
        $licence = $this->lastLicence();
        if ($licence) {
            return $licence->status;
        }

        return false;
    }

    public function annexe()
    {
        return $this->setAttribute('annexe', Help::autoEcoleAnnexe($this->departement_id));
    }
}