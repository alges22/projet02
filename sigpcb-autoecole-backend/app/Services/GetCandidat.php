<?php

namespace App\Services;

class GetCandidat
{


    protected static $errors = [];

    public static function get(array $npis)
    {

        if (count($npis) < 1) {
            return [];
        }
        $response = Api::base('POST', "candidats", [
            'npis' => implode(',', $npis)
        ]);

        if ($response->successful()) {
            return $response->json('data');
        } else {
            static::$errors = $response->json('errors');
        }

        # Extrait les candidats et les renvoies
        return  Api::data($response);
    }


    /**
     *  Recupère un candidat depuis ANIP
     * @param string $npi
     * @return array|null
     */
    public static function findOne(string $npi)
    {
        $tab = static::get([$npi]);

        if (isset($tab[0])) {
            return $tab[0];
        }

        return null;
    }
}
