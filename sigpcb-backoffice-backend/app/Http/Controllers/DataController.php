<?php

namespace App\Http\Controllers;

use App\Services\Api;
use App\Services\GetCandidat;
use Illuminate\Database\Eloquent\Model;
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

        foreach ($replaces as $input => $field) {
            if ($request->has($input) || $request->has($field)) {
                $value = $request->input($input, $request->input($field));

                $request->query->set($field, $value);
                $request->query->remove($input);
            }
        }

        // Laravel prendra uniquement les champs de filtres
        return $this->filterByData($request->only($this->filtreAttrs()));
    }

    protected function cleanValue($key, $value)
    {
        if (!in_array($key, $this->toIntegers())) {
            return $value;
        }
        return intval(trim($value));
    }

    /**
     * Filtre les données suivant le type attendu
     *
     * @param array  $filters
     * @return array
     */
    protected function filterByData($filters)
    {
        ###################### Il faut convertir les données correctements ##########################
        return  collect($filters)
            ->filter(
                function ($value, $key) {

                    if (in_array($key, $this->toIntegers())) {
                        return !is_null($value) && $value != 'null';
                    }
                    return true;
                }
            )
            ->map(function ($value, $key) {
                return $this->cleanValue($key, $value);
            })->all();
    }
    protected function getPerpage()
    {
        $per_page = intval(request('per_page', $this->per_page)); // nombre de pagination ou 25 par défaut

        return  $per_page > $this->max_per_page ? $this->max_per_page : $per_page; //Le nombre maximal de pagination autorisé est 50
    }

    /**
     * Permettra de faire le fitre suivant l'année fournie
     *
     */
    protected function filterIfHasYear($instance)
    {
        $can = request()->has('year');
        if ($can) {
            $year = intval(request('year'));
            $can = $year >= $this->startYear;
        }

        if ($can) {
            // Filtrer les résultats dont l'année de création est égale à l'année spécifiée
            $instance->whereYear('created_at', $year);
        }

        //On ne fait rien
        return $instance;
    }
    /**
     * Les paramètrs de l'URL à remplacer par nos champs
     * Ceci permettra de racourcir nos URL et de camoufler un peu nos
     */
    abstract protected function replaceWiths(): array;

    /**
     * Le champs qui doivent convertir en des entiers
     * @return array
     */
    abstract protected function filtreAttrs(): array;

    /**
     * Les valeurs à convertir  en entier.
     * Utils pour les champs qui sont des integers
     *
     * @return array
     */
    abstract protected function toIntegers(): array;
}
