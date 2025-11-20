<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class Api
{
    public const TIMEOUT_API = 60 * 15; //15min
    /**
     * Faire un appel à l'anip
     *
     * @param string $method
     * @param string $path
     * @param array $data
     * @return \Illuminate\Http\Client\Response
     */
    public static function anip(string $method, string $path, array $data = [])
    {
        $endpoint = env('ANIP_ENDPOINT');
        if (strtolower($method) == 'post') {
            return Http::timeout(self::TIMEOUT_API)->withHeaders(Atk::anipToken())->post($endpoint . $path, $data);
        } else {
            return Http::timeout(self::TIMEOUT_API)->withHeaders(Atk::anipToken())->get($endpoint . $path, $data);
        }
    }

    public static function base(string $method, string $path, array $data = [])
    {
        $endpoint = env('BASE_ENDPOINT');
        if (strtolower($method) == 'post') {
            return Http::timeout(self::TIMEOUT_API)->withHeaders(Atk::anattToken())->post($endpoint . $path, $data);
        } else {
            return Http::timeout(self::TIMEOUT_API)->withHeaders(Atk::anattToken())->get($endpoint . $path, $data);
        }
    }

    /**
     * Faire un appel via l'auto-école
     *
     * @param string $method
     * @param string $path
     * @param array $data
     * @return \Illuminate\Http\Client\Response
     */
    public static function autoEcole(string $method, string $path, array $data = [])
    {
        $endpoint = env('AUTO_ECOLE_ENDPOINT');
        if (strtolower($method) == 'post') {
            return Http::timeout(self::TIMEOUT_API)->withHeaders(Atk::anattToken())->post($endpoint . $path, $data);
        } else {
            return Http::timeout(self::TIMEOUT_API)->withHeaders(Atk::anattToken())->get($endpoint . $path, $data);
        }
    }

    public static function admin(string $method, string $path, array $data = [])
    {
        $endpoint = env('ADMIN_ENDPOINT');
        if (strtolower($method) == 'post') {
            return Http::timeout(self::TIMEOUT_API)->withHeaders(Atk::anattToken())->post($endpoint . $path, $data);
        } else {
            return Http::timeout(self::TIMEOUT_API)->withHeaders(Atk::anattToken())->get($endpoint . $path, $data);
        }
    }

    public static function candidat(string $method, string $path, array $data = [])
    {
        $endpoint = env('CANDIDAT_ENDPOINT');
        if (strtolower($method) == 'post') {
            return Http::timeout(self::TIMEOUT_API)->withHeaders(Atk::anattToken())->post($endpoint . $path, $data);
        } else {
            return Http::timeout(self::TIMEOUT_API)->withHeaders(Atk::anattToken())->get($endpoint . $path, $data);
        }
    }

    /**
     * @param \Illuminate\Http\Client\Response $response
     */
    public static function data($response)
    {
        if ($response->successful()) {
            return  $response->json('data');
        }
        throw $response->toException();;
    }

    /**
     * Concatène les segments du chemin pour former une URL complète.
     *
     * @param string ...$segments
     * @return string
     */
    public static function joinPath(string ...$segments): string
    {
        // Nettoie chaque segment en supprimant les '/' inutiles en début et fin de chaîne
        $cleanedSegments = array_map(static function ($segment) {
            return trim($segment, '/');
        }, $segments);

        // Utilise la fonction implode() pour concaténer les segments avec le délimiteur '/'
        return implode('/', $cleanedSegments);
    }


    public static function urlencode(string $url, array $query = [])
    {
        // Convertit le tableau associatif en une chaîne de requête en utilisant le délimiteur '&'
        $queryStrings = [];
        foreach ($query as $name => $value) {
            $queryStrings[] = urlencode($name) . '=' . urlencode($value);
        }
        $q = implode('&', $queryStrings);
        return $url . '?' . $q;
    }
}
