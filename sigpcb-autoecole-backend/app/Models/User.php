<?php

namespace App\Models;

use App\Services\GetCandidat;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = "promoteurs";
    protected $fillable = ['npi', 'email'];

    public function demandes()
    {
        return $this->hasMany(DemandeAgrement::class, 'promoteur_id');
    }

    public function withInfos()
    {
        $infos =  GetCandidat::findOne($this->npi) ?? [];
        $this->setAttribute('infos', $infos);
        return $this;
    }
}