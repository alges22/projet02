<?php

namespace App\Services;

class Atk
{
    public static function anattToken()
    {
        return [
            "X-ATK-PUBLIC" => env("ANATT_ATK_PUBLIC"),
            "X-ATK-PRIVATE" => env("ANATT_ATK_PRIVATE")
        ];
    }

    public static function anipToken()
    {
        return [];
    }
}
