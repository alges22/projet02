<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    const APPROVED = "approved";
    protected $table = "auto_ecole_transactions";
    protected $fillable = [
        'service',
        'service_id',
        'amount',
        'npi',
        'note',
        'perform_time',
        'refund_time',
        'transaction_id',
        'status',
        'uuid'
    ];

    protected $casts = [
        "perform_time" => "datetime",
    ];


    public function scopeFilter(Builder $query, array $filters)
    {
        $query->when(
            data_get($filters, "uuid"),
            fn($query, $uuid) => $query->where('uuid', $uuid)
        );

        $query->when(
            data_get($filters, "service"),
            fn($query, $service) => $query->where('service', $service)
        );

        $query->when(
            data_get($filters, "service_id"),
            fn($query, $service_id) => $query->where('service_id', $service_id)
        );

        $query->when(
            data_get($filters, "npi"),
            fn($query, $npi) => $query->where('npi', $npi)
        );

        $query->when(
            data_get($filters, "status"),
            fn($query, $status) => $query->where('status', $status)
        );

        return $query;
    }
    /**
     * Recupère juste
     * @param array $filters
     * @return  Transaction|null
     */
    public static function findJustOne(array $filters)
    {
        return static::filter($filters)->first();
    }
}
