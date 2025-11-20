<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Compos\Compo;
use Illuminate\Support\Collection;


class CompoGenerateTest extends TestCase
{
    private array $dossiers;
    private array $salles;
    private Compo $compo;

    protected function setUp(): void
    {
        parent::setUp();


        $this->dossiers = [
            // Catégorie Permis 1 (5 candidats)
            ["id" => 1, "langue_id" => 1, "categorie_permis_id" => 4],
            ["id" => 2, "langue_id" => 1, "categorie_permis_id" => 4],
            ["id" => 3, "langue_id" => 1, "categorie_permis_id" => 4],
            ["id" => 4, "langue_id" => 1, "categorie_permis_id" => 4],
            ["id" => 5, "langue_id" => 1, "categorie_permis_id" => 4],
            ["id" => 6, "langue_id" => 1, "categorie_permis_id" => 4],
            ["id" => 7, "langue_id" => 1, "categorie_permis_id" => 4],
            ["id" => 8, "langue_id" => 1, "categorie_permis_id" => 4],
            // Catégorie Permis 2 (5 candidats)
            ["id" => 9, "langue_id" => 1, "categorie_permis_id" => 3],
            ["id" => 10, "langue_id" => 1, "categorie_permis_id" => 3],
            ["id" => 11, "langue_id" => 1, "categorie_permis_id" => 3],
            ["id" => 12, "langue_id" => 1, "categorie_permis_id" => 3],
            ["id" => 13, "langue_id" => 1, "categorie_permis_id" => 3],

            //5 candidats
            ["id" => 14, "langue_id" => 1, "categorie_permis_id" => 6],
            ["id" => 15, "langue_id" => 1, "categorie_permis_id" => 6],
            ["id" => 16, "langue_id" => 2, "categorie_permis_id" => 6],
            ["id" => 17, "langue_id" => 2, "categorie_permis_id" => 6],
            ["id" => 18, "langue_id" => 2, "categorie_permis_id" => 6],
            ["id" => 19, "langue_id" => 2, "categorie_permis_id" => 6],
            ["id" => 20, "langue_id" => 2, "categorie_permis_id" => 6],

        ];

        $this->salles = [
            ["id" => 1, "contenance" => 6],
            ["id" => 2, "contenance" => 5],
        ];

        $this->compo = new Compo(
            dossiers: $this->dossiers,
            centre_id: 1,
            salles: $this->salles,
            examen_id: 1
        );
    }

    /** @test */
    public function test_generate_returns_correct_structure()
    {
        $result = $this->compo->generate();

        $this->assertIsArray($result);

        foreach ($result as $vague) {
            $this->assertArrayHasKey('salle_compo_id', $vague);
            $this->assertArrayHasKey('candidats', $vague);
            $this->assertIsArray($vague['candidats']);
        }
    }

    /** @test */
    public function test_generate_respects_room_capacity()
    {
        $result = $this->compo->generate();

        foreach ($result as $vague) {
            $salle = current(array_filter($this->salles, fn($s) => $s['id'] === $vague['salle_compo_id']));
            $this->assertLessThanOrEqual($salle['contenance'], count($vague['candidats']));
        }
    }

    /** @test */
    public function test_generate_no_mixing_of_permis_categories()
    {
        $result = $this->compo->generate();

        foreach ($result as $vague) {
            $categoriesPermis = array_unique(array_column($vague['candidats'], 'categorie_permis_id'));
            $this->assertCount(1, $categoriesPermis, 'Une salle contient des candidats de différentes catégories de permis');
        }
    }

    /** @test */
    public function test_generate_all_candidates_are_assigned()
    {
        $result = $this->compo->generate();

        $totalAssigned = array_reduce($result, function ($carry, $vague) {
            return $carry + count($vague['candidats']);
        }, 0);


        $this->assertEquals(
            count($this->dossiers),
            $totalAssigned,
            'Tous les candidats ne sont pas assignés'
        );
    }

    /** @test */
    public function test_generate_with_empty_dossiers()
    {
        $compo = new Compo(
            dossiers: [],
            centre_id: 1,
            salles: $this->salles,
            examen_id: 1
        );

        $result = $compo->generate();
        $this->assertEmpty($result);
    }

    /** @test */
    public function test_generate_optimizes_room_usage()
    {
        $result = $this->compo->generate();

        // Vérifier que les grandes salles sont utilisées en priorité pour les grands groupes
        $firstVague = $result[0];
        $biggestRoom = max(array_column($this->salles, 'contenance'));
        $salle = current(array_filter($this->salles, fn($s) => $s['id'] === $firstVague['salle_compo_id']));

        $this->assertEquals(
            $biggestRoom,
            $salle['contenance'],
            'La plus grande salle devrait être utilisée en premier pour le plus grand groupe'
        );
    }

    /** @test */
    /** @test */
    public function test_generate_sorts_by_language()
    {
        $result = $this->compo->generate();

        foreach ($result as $vague) {
            $langueIds = array_column($vague['candidats'], 'langue_id');
            $sortedLangueIds = $langueIds;
            sort($sortedLangueIds); // Utilisation de la fonction PHP native sort()

            // Vérifier que les langues sont groupées
            $this->assertEquals(
                $sortedLangueIds,
                $langueIds,
                'Les candidats ne sont pas correctement triés par langue'
            );
        }
    }
    /** @test */
    public function test_generate_creates_correct_number_of_waves()
    {
        $result = $this->compo->generate();

        $this->assertCount(5, $result);
        $this->assertEquals(1, $result[0]['salle_compo_id']);
        $this->assertEquals(2, $result[1]['salle_compo_id']);
        $this->assertEquals(1, $result[2]['salle_compo_id']);
        $this->assertEquals(
            2,
            $result[3]['salle_compo_id']
        );
        $this->assertEquals(
            2,
            $result[4]['salle_compo_id']
        );
    }
}
