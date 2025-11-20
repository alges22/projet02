<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    public const PERMIS_NUMERIQUE = "permis-numerique";
    public const DEMANDE_PERMIS_CONDUIRE = "ds-code-conduite";
    public const DEMANDE_AUTHENTICITE = "authenticite";

    public static function permisNumAmount()
    {
        return 100;
    }

    public static function renouvellementAmount()
    {
        return 2000;
    }

    public static function duplicataAmount()
    {
        return 100;
    }

    public static function remplacementAmount()
    {
        return 100;
    }

    public static function echangeAmount()
    {
        return 100;
    }

    public static function authenticiteAmount()
    {
        return config('anatt.amounts.authenticite.total');
    }

    public static function permisinternationalAmount()
    {
        return 100;
    }
    public static function attestationAmount()
    {
        return 100;
    }
    public static function prorogationAmount()
    {
        return 100;
    }
}
