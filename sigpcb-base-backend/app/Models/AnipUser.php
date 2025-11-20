<?php

namespace App\Models;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Carbon $last_updated
 */
class AnipUser extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        "last_updated",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        "last_updated" => 'datetime',
        "date_de_naissance" => 'datetime',
    ];

    protected function avatar(): Attribute
    {
        return new Attribute(get: fn ($avatar) => asset($avatar));
    }

    protected function signature(): Attribute
    {
        return new Attribute(get: fn ($signature) => asset($signature));
    }
}
