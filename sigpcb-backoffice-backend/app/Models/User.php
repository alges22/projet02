<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $last_name
 * @property string $first_name
 * @property string|null $phone
 * @property Titre|null $titre
 * @property string $email
 * @property string $password
 * @property bool $status
 * @property int|null $unite_admin_id
 * @property string|null $profil_id
 * @property UniteAdmin|null $unite_admin
 * @property Signataire|null $unite_admin
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $with = ["titre", "uniteAdmin"];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'last_name',
        'first_name',
        'phone',
        'titre_id',
        'email',
        'password',
        'status',
        'unite_admin_id',
        "npi"
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Unité associée à l'utilisateur
     *
     */
    public function uniteAdmin()
    {
        return $this->belongsTo(UniteAdmin::class, 'unite_admin_id');
    }


    public function titre()
    {
        return $this->belongsTo(Titre::class, 'titre_id');
    }


    /**
     * Relation entree signataire et utilisateur
     *
     */
    public function signataire()
    {
        return $this->hasOne(Signataire::class);
    }

    public function isInspecteur()
    {
        // Vérifier si l'utilisateur a un enregistrement dans la table Inspecteur
        return Inspecteur::where('user_id', $this->id)->exists();
    }

    public function inspecteurInfo()
    {
        // Récupérer les informations de l'inspecteur, y compris l'annexe
        return Inspecteur::with('annexe')->where('user_id', $this->id)->first();
    }

}
