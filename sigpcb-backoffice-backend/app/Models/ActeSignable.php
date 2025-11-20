<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActeSignable extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'status',
        'is_one_signataire'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_one_signataire' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Une relation many to many
     * Permet de connaitre signataires de cet acte
     */
    public function signataires()
    {
        return $this->belongsToMany(Signataire::class);
    }
}
