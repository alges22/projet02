<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeLicence extends Model
{
    use HasFactory;

    protected $fillable = ['reference', 'auto_ecole_id', 'moniteurs', 'vehicules', 'state', 'promoteur_id', 'npi', 'date_validation'];

    protected $casts = [
        'date_validation' => 'datetime',
    ];
    public function fiche()
    {
        return $this->hasOne(DemandeLicenceFile::class);
    }

    public function rejets()
    {
        return $this->hasMany(DemandeAgrementRejet::class);
    }

    public function autoEcole()
    {
        return $this->belongsTo(AutoEcole::class);
    }

    public function  promoteur()
    {
        return $this->belongsTo(User::class);
    }
}
