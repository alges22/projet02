<?php

namespace App\Http\Controllers;

use App\Services\GetCandidat;
use Illuminate\Http\Request;

abstract class DataController extends ApiController
{
    private $startYear = 2023;
    protected $champ_filtres = [];
    protected $per_page = 25;

    protected $max_per_page = 50;


    /**
     * S'il faut prendre les candidats un à un, la requête sera lente,
     * On prend les candidats d'un seul coup
     *
     * @return array
     */
    protected function getCandidats($collection)
    {
        /**
         * On prend les npis des candidats puis on va prendre la liste des candidats d'un seul coup,
         * Après en fonction du npi de chaque candidat on récupérère chaque candidat chez anip
         */
        $npis = $collection->map(function ($s) {
            return $s->npi;
        })->toArray();
        return GetCandidat::get($npis);
    }


    protected function getFilters(Request $request)
    {
        $replaces = $this->replaceWiths();

        # Par ex le paramètre: "list" dans l'URL sera transformé à "state" ce qui correspond à notre champ
        foreach ($replaces as $input => $field) {

            if ($request->has($input) || $request->has($field)) {

                ## Si nos champs sont passés on les laisse quand-même (pour faire fonction nos apis exitantes)
                $valueFromOriginal = $request->input($field, null); //On suppose que nos champs sont passés et on prend la valeur

                # On essaie de prendre la valeur attendue,
                # si ce n'est pas trouvé alors on prend la valeur par défaut ci dessus
                # Cela suppose que l'un ou l'autre exist sinon null par défaut
                $value = $request->input($input, $valueFromOriginal);


                # On change les informations  de façon silencieusement
                $request->query->set($field, $value);

                # On nettoie la requête
                $request->query->remove($input);
            }
        }
        // Laravel nous prendra uniquement les champs de filtres
        return $request->all();
    }



    protected function getPerpage()
    {
        $per_page = intval(request('per_page', $this->per_page)); // nombre de pagination ou 25 par défaut

        return  $per_page > $this->max_per_page ? $this->max_per_page : $per_page; //Le nombre maximal de pagination autorisé est 50
    }


    /**
     * Les paramètrs de l'URL à remplacer par nos champs
     * Ceci permettra de racourcir nos URL et de camoufler un peu nos
     */
    abstract protected function replaceWiths(): array;
}
