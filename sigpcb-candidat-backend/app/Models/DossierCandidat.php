<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DossierCandidat extends Model
{
    use HasFactory;
    protected $connection = "base";
    protected $guarded =  [];

    public function parcourssuivi()
    {
        return $this->hasMany(ParcoursSuivi::class);
    }

    public function lastDossierSession()
    {
        return $this->hasOne(DossierSession::class)->orderByDesc('created_at');
    }
    public function lastAncienPermis()
    {
        return $this->hasOne(AncienPermis::class)->orderByDesc('created_at');
    }

    public function dossierSessions()
    {
        return $this->hasMany(DossierSession::class);
    }
    public function permisNumPayments()
    {
        return $this->hasMany(PermisNumPayment::class, 'dossier_candidat_id');
    }
}
