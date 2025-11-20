<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'expire',
        'action',
        'nombre_de_fois',
        'retry_times'
    ];

    protected $table = "candidat_otps";
    protected $connection = "base";

    protected $casts = [
        'expire' => 'datetime',
        'retry_times' => 'datetime'
    ];

    /**
     * Get the user that the OTP belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include valid OTPs.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
