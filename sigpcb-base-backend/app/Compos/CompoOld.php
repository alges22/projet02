<?php

namespace App\Compos;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CompoOld
{
    private $tempfile = "compositions/{annexe_id}-{examen_id}.json";


    /**
     * $data a essentiellement les clés, langue_id, categorie_permis_id, id
     *
     * @param array $dossiers
     *
     * @param int $centre_id
     */
    public function __construct(private  array $dossiers = [], private int $centre_id, private array $salles, private $examen_id) {}


    public function getVagues()
    {
        return $this->vagueFroms();
    }

    /**
     * Cette méthode fera un tri des dossiers suivant la même langue et la même catégorie permis
     * Et les met dans le tableau $groups
     */
    private function getCandidatsByLangueAndPermsis()
    {
        /**
         * Création des groups langues
         * La concatétanation permet d'avoir une clé unique de filtre
         */
        $groups = collect($this->dossiers)->groupBy(function ($ds) {
            return 'group_' . $ds['langue_id'] . '_' . $ds['categorie_permis_id'];
        });

        //Trie par nombre de candidat
        return $groups->sortByDesc(function ($group) {
            $first = $group->first();
            return  collect($this->dossiers)->filter(function ($ds) use ($first) {
                return $ds['categorie_permis_id'] === $first['categorie_permis_id'];
            })->count() + count($group);
        });
    }

    private function generate(): array
    {
        //Pour chaque group il faut créer le nombre de vague
        //Les groups étaient triés par nombre de candidats suivant tout le centre

        $salles = collect($this->salles)->sortByDesc('contenance');


        $vagues = [];
        foreach ($this->getCandidatsByLangueAndPermsis() as  $candidatsParLanguePermis) {

            do {
                foreach ($salles as $key => $salle) {
                    $contenance = $salle['contenance'];
                    # On prend la contenance
                    $candidats = collect($candidatsParLanguePermis)->take($contenance)->all();

                    $vagues[] = [
                        'salle_compo_id' => $salle['id'],
                        'candidats' => $candidats
                    ];

                    # On coupe la contenance de la salle une fois prise.
                    $candidatsParLanguePermis = $candidatsParLanguePermis->slice($contenance);
                }
            } while ($candidatsParLanguePermis->isNotEmpty());

            $salles = $salles->reverse();
        }

        return $vagues;
    }



    public function intoTempFile(&$stats = [])
    {
        $vagues = $this->generate();
        $stats['total'] = count($vagues);
        return Storage::put($this->tempfilePath(), json_encode($vagues));
    }


    public function canGenerateSalle()
    {
        return Storage::exists($this->tempfilePath());
    }
    private function tempfilePath()
    {
        $name = str_replace('{annexe_id}', $this->centre_id, $this->tempfile);

        return str_replace('{examen_id}', $this->examen_id, $name);
    }

    private function vagueFroms()
    {
        if ($this->canGenerateSalle()) {
            return json_decode(Storage::get($this->tempfilePath()), true) ?? [];
        }

        return [];
    }

    public function removeTempFile()
    {
        $path = $this->tempfilePath();

        if (Storage::exists($path)) {
            Storage::delete($path);
        }
    }
}
