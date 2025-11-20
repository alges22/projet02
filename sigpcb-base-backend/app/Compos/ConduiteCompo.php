<?php

namespace App\Compos;

use App\Models\JuryCandidat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ExaminateurCategoriePermis;
use App\Models\CategoriePermis;

class ConduiteCompo
{
    private $assignations = null;
    private $compTempfile = "compositions-conduite/{annexe_id}.json";
    protected $vagues;
    protected $groups;
    protected $weightGroups;

    private const POIDS_LOURDS = ['CE', 'C', 'D', 'D1', 'E'];
    private const POIDS_LEGERS = ['A1', 'A2', 'A3', 'B', 'B1', 'F'];

    public function __construct(protected array $dossiers = [], protected int $centre_id, protected array $jurie)
    {
        if (!empty($dossiers)) {
            // Vérifier et supprimer les doublons dans le tableau de dossiers
            $this->dossiers = $this->removeDuplicates($dossiers);
            $this->assignJuryToCandidat();
        }
    }

    /**
     * Supprime les doublons des dossiers basés sur le NPI
     */
    private function removeDuplicates(array $dossiers): array
    {
        $uniqueDossiers = [];
        $seenNpis = [];

        foreach ($dossiers as $dossier) {
            if (!isset($dossier['npi'])) {
                // Si pas de NPI, on garde quand même le dossier (cas rare)
                $uniqueDossiers[] = $dossier;
                continue;
            }

            if (!isset($seenNpis[$dossier['npi']])) {
                $uniqueDossiers[] = $dossier;
                $seenNpis[$dossier['npi']] = true;
            } else {
                // Optionnel: enregistrer un log pour les doublons trouvés
                logger()->info("Doublon détecté et ignoré - NPI: {$dossier['npi']}, ID: {$dossier['id']}");
            }
        }

        logger()->info("Nombre de dossiers après déduplication: " . count($uniqueDossiers) . " sur " . count($dossiers) . " initial");
        return $uniqueDossiers;
    }

    private function getWeightCategory($dossier)
    {
        $permis = CategoriePermis::find($dossier['categorie_permis_id']);
        if (!$permis) return 'autre';

        if (in_array($permis->name, self::POIDS_LOURDS)) {
            return 'poids_lourds';
        }
        if (in_array($permis->name, self::POIDS_LEGERS)) {
            return 'poids_legers';
        }
        return 'autre';
    }

    private function groupDossierByPermis()
    {
        // Grouper par catégorie de poids
        $this->weightGroups = collect($this->dossiers)->groupBy(function ($dossier) {
            return $this->getWeightCategory($dossier);
        });

        // Sous-grouper par auto-école pour chaque catégorie de poids
        $this->groups = collect();
        foreach ($this->weightGroups as $weightCategory => $dossiers) {
            $this->groups[$weightCategory] = $dossiers->groupBy('auto_ecole_id');
        }
    }

    private function initialDistribution($autoEcoleGroups, $juryPermis, $moyenneParJury)
    {
        $distribution = [];
        $remainingCandidats = clone $autoEcoleGroups;
        $assignedNpis = []; // Pour suivre les NPI déjà assignés

        foreach ($juryPermis as $jury) {
            $juryAssignments = [];
            $totalAssigned = 0;

            // Vérifier les permissions de l'examinateur
            $examPermis = $jury['permis'];

            foreach ($remainingCandidats as $autoEcoleId => $candidats) {
                if ($totalAssigned >= $moyenneParJury && !empty($examPermis)) break;

                // Filtrer les candidats selon les permissions de l'examinateur
                $eligibleCandidats = $candidats->filter(function ($candidat) use ($examPermis, $assignedNpis) {
                    return in_array($candidat['categorie_permis_id'], $examPermis) &&
                           !isset($assignedNpis[$candidat['npi']]);
                });

                if ($eligibleCandidats->isEmpty()) continue;

                $toAssign = min(
                    $moyenneParJury - $totalAssigned,
                    $eligibleCandidats->count()
                );

                if ($toAssign > 0 || empty($examPermis)) {
                    $assigned = $eligibleCandidats->take($toAssign);

                    // Marquer les NPI comme assignés
                    foreach ($assigned as $candidat) {
                        $assignedNpis[$candidat['npi']] = true;
                        $juryAssignments[] = $candidat;
                    }

                    // Mise à jour des candidats restants
                    $remainingCandidats[$autoEcoleId] = $candidats->filter(function ($candidat) use ($assigned) {
                        return !$assigned->contains('id', $candidat['id']);
                    });

                    $totalAssigned += $toAssign;
                }
            }

            if (!empty($juryAssignments)) {
                $distribution[] = [
                    'jury' => $jury,
                    'candidats' => $juryAssignments,
                    'count' => count($juryAssignments)
                ];
            }
        }

        return [$distribution, $remainingCandidats, $assignedNpis];
    }

    private function rebalanceDistribution($distribution, $autoEcoleGroups, $moyenneParJury, $assignedNpis)
    {
        // Trier les jurys par nombre de candidats (croissant)
        usort($distribution, function($a, $b) {
            return $a['count'] - $b['count'];
        });

        $remainingCandidats = collect($autoEcoleGroups)->flatten(1)->filter(function ($candidat) use ($assignedNpis) {
            return !isset($assignedNpis[$candidat['npi']]);
        });

        foreach ($distribution as &$juryDist) {
            // Ne pas rééquilibrer si l'examinateur n'a pas les permissions nécessaires
            if (empty($juryDist['jury']['permis'])) continue;

            $diff = $moyenneParJury - $juryDist['count'];
            if ($diff <= 0) continue;

            // Filtrer les candidats éligibles pour ce jury
            $eligibleCandidats = $remainingCandidats->filter(function ($candidat) use ($juryDist, $assignedNpis) {
                return in_array($candidat['categorie_permis_id'], $juryDist['jury']['permis']) &&
                       !isset($assignedNpis[$candidat['npi']]);
            });

            $toAdd = $eligibleCandidats->take($diff);
            if ($toAdd->isEmpty()) continue;

            foreach ($toAdd as $candidat) {
                $juryDist['candidats'][] = $candidat;
                $assignedNpis[$candidat['npi']] = true;
            }

            $juryDist['count'] += $toAdd->count();

            // Mettre à jour les candidats restants
            $remainingCandidats = $remainingCandidats->filter(function ($candidat) use ($toAdd) {
                return !$toAdd->contains('id', $candidat['id']);
            });
        }

        // Gérer les candidats restants qui n'ont pas encore été assignés
        if ($remainingCandidats->isNotEmpty()) {
            foreach ($remainingCandidats as $candidat) {
                // Vérifier si ce candidat a déjà été assigné
                if (isset($assignedNpis[$candidat['npi']])) continue;

                // Trouver les jurys éligibles
                $eligibleJurys = collect($distribution)->filter(function ($dist) use ($candidat) {
                    return in_array($candidat['categorie_permis_id'], $dist['jury']['permis']);
                });

                if ($eligibleJurys->isNotEmpty()) {
                    // Assigner au jury ayant le moins de candidats
                    $jury = $eligibleJurys->sortBy('count')->first();
                    $juryIndex = array_search($jury, $distribution);

                    $distribution[$juryIndex]['candidats'][] = $candidat;
                    $distribution[$juryIndex]['count']++;
                    $assignedNpis[$candidat['npi']] = true;
                }
            }
        }

        return $distribution;
    }

    private function processWeightCategory($weightCategory)
    {
        if (!isset($this->groups[$weightCategory]) || $this->groups[$weightCategory]->isEmpty()) {
            return [];
        }

        // Obtenir les jurys éligibles pour cette catégorie
        $juryPermis = collect($this->juryPermis())->filter(function ($jury) use ($weightCategory) {
            return $this->isJuryEligibleForCategory($jury, $weightCategory);
        });

        if ($juryPermis->isEmpty()) {
            return [];
        }

        $totalCandidats = $this->groups[$weightCategory]->sum(function ($group) {
            return $group->count();
        });

        $moyenneParJury = ceil($totalCandidats / $juryPermis->count());
        $assignedNpis = []; // Pour suivre les NPI déjà assignés

        // Distribution initiale
        [$distribution, $remainingGroups, $assignedNpis] = $this->initialDistribution(
            $this->groups[$weightCategory],
            $juryPermis,
            $moyenneParJury
        );

        // Rééquilibrage
        return $this->rebalanceDistribution(
            $distribution,
            $remainingGroups,
            $moyenneParJury,
            $assignedNpis
        );
    }

    private function assignJuryToCandidat()
    {
        $this->groupDossierByPermis();
        $assignations = [];
        $globalAssignedNpis = []; // Pour suivre tous les NPI assignés

        // Traiter d'abord les poids légers
        $poidsLegersDistribution = $this->processWeightCategory('poids_legers');
        foreach ($poidsLegersDistribution as $dist) {
            $uniqueCandidats = []; // Pour s'assurer qu'il n'y a pas de doublons

            foreach ($dist['candidats'] as $candidat) {
                if (!isset($globalAssignedNpis[$candidat['npi']])) {
                    $uniqueCandidats[] = $candidat;
                    $globalAssignedNpis[$candidat['npi']] = true;
                }
            }

            if (!empty($uniqueCandidats)) {
                $assignations[] = [
                    "jury" => $dist['jury']['jury'],
                    "group" => $uniqueCandidats,
                    "weight_category" => 'poids_legers',
                    "count" => count($uniqueCandidats)
                ];
            }
        }

        // Puis traiter les poids lourds
        $poidsLourdsDistribution = $this->processWeightCategory('poids_lourds');
        foreach ($poidsLourdsDistribution as $dist) {
            $uniqueCandidats = []; // Pour s'assurer qu'il n'y a pas de doublons

            foreach ($dist['candidats'] as $candidat) {
                if (!isset($globalAssignedNpis[$candidat['npi']])) {
                    $uniqueCandidats[] = $candidat;
                    $globalAssignedNpis[$candidat['npi']] = true;
                }
            }

            if (!empty($uniqueCandidats)) {
                $assignations[] = [
                    "jury" => $dist['jury']['jury'],
                    "group" => $uniqueCandidats,
                    "weight_category" => 'poids_lourds',
                    "count" => count($uniqueCandidats)
                ];
            }
        }

        $this->assignations = collect($assignations);

        // Log pour confirmer l'absence de doublons
        $this->verifyNoDuplicatesInAssignations();
    }

    /**
     * Vérifie qu'il n'y a pas de doublons dans les assignations
     */
    private function verifyNoDuplicatesInAssignations()
    {
        $allNpis = [];
        $duplicates = [];

        foreach ($this->assignations as $assignation) {
            foreach ($assignation['group'] as $candidat) {
                if (isset($allNpis[$candidat['npi']])) {
                    $duplicates[] = $candidat['npi'];
                }
                $allNpis[$candidat['npi']] = true;
            }
        }

        if (!empty($duplicates)) {
            logger()->warning('ATTENTION: Doublons détectés dans les assignations: ' . implode(', ', array_unique($duplicates)));
        } else {
            logger()->info('Vérification réussie: Aucun doublon dans les assignations');
        }
    }

    private function isJuryEligibleForCategory($jury, $weightCategory)
    {
        $permis = CategoriePermis::whereIn('id', $jury['permis'])->get();
        return $permis->some(function ($p) use ($weightCategory) {
            $dossier = ['categorie_permis_id' => $p->id];
            return $this->getWeightCategory($dossier) === $weightCategory;
        });
    }

    private function juryPermis()
    {
        $juriePossiblesPourPermis = [];
        foreach ($this->jurie as $jury) {
            $examinateurId = $jury['examinateur_id'];
            $permisIds = ExaminateurCategoriePermis::where('examinateur_id', $examinateurId)
                ->pluck('categorie_permis_id')
                ->toArray();

            $juriePossiblesPourPermis[] = [
                'jury' => $jury,
                'permis' => $permisIds
            ];
        }
        return $juriePossiblesPourPermis;
    }

    public function getAssignations()
    {
        if ($this->canDistribute()) {
            return json_decode(Storage::get($this->tempfilePath()), true) ?? [];
        }
        return [];
    }

    public function intoTempFile(&$stats = [])
    {
        // Vérifier à nouveau l'absence de doublons avant de sauvegarder
        $allNpis = [];
        $duplicates = [];
        $candidatsCount = 0;

        foreach ($this->assignations as $assignation) {
            $candidatsCount += count($assignation['group']);
            foreach ($assignation['group'] as $candidat) {
                if (isset($allNpis[$candidat['npi']])) {
                    $duplicates[] = $candidat['npi'];
                }
                $allNpis[$candidat['npi']] = true;
            }
        }

        if (!empty($duplicates)) {
            logger()->warning('Doublons détectés avant sauvegarde: ' . implode(', ', array_unique($duplicates)));
            // Optionnel: corriger les doublons ici
        }

        $stats = [
            'total' => count($this->assignations),
            'total_candidats' => $candidatsCount,
            'total_candidats_uniques' => count($allNpis),
            'poids_lourds' => $this->assignations->where('weight_category', 'poids_lourds')->count(),
            'poids_legers' => $this->assignations->where('weight_category', 'poids_legers')->count(),
            'distribution' => $this->assignations->groupBy('weight_category')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'total_candidats' => $group->sum('count')
                ];
            })
        ];

        return Storage::put($this->tempfilePath(), $this->assignations->toJson());
    }

    public function canDistribute()
    {
        return Storage::exists($this->tempfilePath());
    }

    private function tempfilePath()
    {
        return str_replace('{annexe_id}', $this->centre_id, $this->compTempfile);
    }

    public function removeTempFile()
    {
        $path = $this->tempfilePath();
        if (Storage::exists($path)) {
            Storage::delete($path);
        }
    }
}
