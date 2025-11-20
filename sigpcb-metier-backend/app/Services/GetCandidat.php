<?php

namespace App\Services;

class GetCandidat
{
    /**
     * Le nombre de candidat à prendre d'un seul coup
     *
     * @var integer
     */
    public static $chunk = 25;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $npisCollection;




    public static function get(array $npis)
    {

        if (count($npis) < 1) {
            return [];
        }
        $response = Api::base('POST', "candidats", [
            'npis' => implode(',', $npis)
        ]);

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
