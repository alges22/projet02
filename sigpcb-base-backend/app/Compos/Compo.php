<?php

namespace App\Compos;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Compo
{
    private $tempfile = "compositions/{annexe_id}-{examen_id}.json";

    private $usedSalles = [];

    public function __construct(
        private array $dossiers = [],
        private int $centre_id,
        private array $salles,
        private $examen_id
    ) {}

    public function getVagues()
    {
        return $this->vagueFroms();
    }
    public function generate(): array
    {
        // Trier les candidats par plus grand groupe de permis et par langue
        $groupesParCategorie = $this->sortByLagestPermisGroup($this->sortByLangue(collect($this->dossiers)));

        $vagues = [];
        $salles = $this->salles;

        // Pour chaque groupe de permis
        foreach ($groupesParCategorie as $k => $groupe) {

            // Obtenir les vagues pour ce groupe avec les salles disponibles
            $result = $this->fillRoomFromGroup($groupe, $salles);

            // Ajouter les vagues générées au tableau final
            if (!empty($result['vagues'])) {
                foreach ($result['vagues'] as $vague) {
                    $vagues[] = [
                        'salle_compo_id' => $vague['salle_compo_id'],
                        'candidats' => collect($vague['candidats'])->values()->all(),
                    ];
                }
            }
            if (collect($this->salles)->every(fn($s) => in_array($s['id'], $this->usedSalles))) {
                $salles = $this->salles;

                $this->usedSalles = [];
                if (!$result['freeRooms']) {
                    $result['freeRooms'] = $salles;
                }
            }

            $salles = $result['freeRooms'] ?: $salles;
        }
        return $vagues;
    }

    public function intoTempFile(&$stats = [])
    {
        $vagues = $this->generate();
        $stats['total'] = count($vagues);
        return Storage::put($this->tempfilePath(), json_encode($vagues));
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

    public function canGenerateSalle()
    {
        return Storage::exists($this->tempfilePath());
    }

    public function removeTempFile()
    {
        $path = $this->tempfilePath();
        if (Storage::exists($path)) {
            Storage::delete($path);
        }
    }

    public function findOptimalRoom(int $x, array $salles): array
    {

        // Extraire les contenances
        $capacities = array_column($salles, 'contenance');
        $min = min($capacities);
        $max = max($capacities);

        // Appliquer la logique de recherche
        if ($x <= $min) {
            $targetCapacity = $min;
        } elseif ($x >= $max) {
            $targetCapacity = $max;
        } else {
            $targetCapacity = min(array_filter($capacities, fn($cap) => $cap >= $x));
        }

        // Trouver la salle correspondante
        return current(array_filter($salles, fn($salle) => $salle['contenance'] === $targetCapacity));
    }

    private function sortByLangue(Collection $candidats)
    {
        return $candidats->sortBy('langue_id');
    }


    private function sortByLagestPermisGroup($dossiers)
    {
        // Grouper les candidats par catégorie de permis
        $groupesParPermis = collect($dossiers)->groupBy('categorie_permis_id');

        // Trier les groupes par taille (nombre de candidats) de manière décroissante
        return $groupesParPermis->sortByDesc(function ($groupe) {
            return $groupe->count();
        });
    }

    public function fillRoomFromGroup(Collection $collection, array $salles = []): array
    {
        if ($collection->isEmpty()) {
            return [
                'vagues' => [],
                'remainingCandidats' => $collection
            ];
        }


        // Vérifions d'abord si nous avons besoin de réinitialiser les salles
        if (collect($this->salles)->every(fn($s) => in_array($s['id'], $this->usedSalles))) {
            $this->usedSalles = [];
            $salles = $this->salles; // Réinitialiser avec toutes les salles
        }

        if (!$salles) {
            $salles = $this->salles;
        }

        // Filtrer les salles déjà utilisées
        $availableRooms = array_filter(
            $salles,
            fn($salle) =>
            !in_array($salle['id'], $this->usedSalles)
        );

        // Si pas de salles disponibles après filtrage, réinitialiser
        if (empty($availableRooms)) {
            $this->usedSalles = [];
            $availableRooms = $salles;
        }

        // Si pas de salles disponibles OU si le groupe est plus grand que la capacité maximale disponible,
        // réinitialiser les salles
        $count = $collection->count();
        $maxAvailableCapacity = empty($availableRooms) ? 0 : max(array_column($availableRooms, 'contenance'));

        if (empty($availableRooms) || $count > $maxAvailableCapacity) {
            $this->usedSalles = [];
            $availableRooms = $this->salles;
        }


        $matchedRoom = $this->findOptimalRoom($count, $availableRooms);

        $taken = $collection->take($matchedRoom['contenance']);
        $notTaken = $collection->slice($matchedRoom['contenance']);

        $this->usedSalles[] = $matchedRoom['id'];

        $currentVague = [
            'salle_compo_id' => $matchedRoom['id'],
            'candidats' => $taken->toArray()
        ];



        if ($notTaken->isNotEmpty()) {
            $nextResult = $this->fillRoomFromGroup($notTaken, $availableRooms);
            return [
                'vagues' => array_merge([$currentVague], $nextResult['vagues']),
                'remainingCandidats' => $nextResult['remainingCandidats'],
                'freeRooms' => $availableRooms,
            ];
        }

        return [
            'vagues' => [$currentVague],
            'remainingCandidats' => $notTaken,
            'freeRooms' => $availableRooms,
        ];
    }
}
