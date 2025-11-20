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
        // $response = Api::anip('POST', "candidats", [
        //     'npis' => implode(',', $npis)
        // ]);
        $response = Anip::get($npis);
        return  $response;
        # Extrait les candidats et les renvoies
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

    public static function getImage(array $npis)
    {

        if (count($npis) < 1) {
            return [];
        }
        $response = Anip::getPicture($npis);
        return  $response;

        # Extrait les candidats et les renvoies
        return  Api::data($response);
    }


    /**
     *  Recupère un candidat depuis ANIP
     * @param string $npi
     * @return array|null
     */
    public static function findOneImage(string $npi)
    {
        $tab = static::getImage([$npi]);

        if (isset($tab[0])) {
            return $tab[0];
        }

        return null;
    }
}
