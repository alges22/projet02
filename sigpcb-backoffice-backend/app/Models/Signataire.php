<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signataire extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'fichier_signature',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * Get the user that owns the signataire.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Une relation many to many
     * Permet de connaitres les actes signés par cet signataires
     */
    public function acteSignables()
    {
        return $this->belongsToMany(ActeSignable::class);
    }
}
