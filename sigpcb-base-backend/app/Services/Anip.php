<?php

namespace App\Services;

use App\Models\AnipUser as User;
use Illuminate\Support\Collection;

class Anip
{
    public static function get(string| array $npi): array | User |null
    {
        $npis = is_array($npi) ? $npi : [$npi];

        $collection = (new AnipRequestProcess($npis))->getCollection();
        if (is_array($npi)) {
            return $collection->toArray();
        } else {
            return $collection->first();
        }
    }

    public static function getPicture(string|array $npi): array
    {
        $npis = is_array($npi) ? $npi : [$npi];
        // Récupération des photos pour chaque NPI
        $collection = (new CandidatPictureRequest($npis))->getCollection();
        // On retourne toutes les photos sous forme de tableau
        if (is_array($npi)) {
            return $collection->toArray();
        } else {
            return $collection->first();
        }
    }

}
