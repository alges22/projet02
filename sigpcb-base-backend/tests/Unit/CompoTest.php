<?php

namespace Tests\Unit;

use App\Compos\Compo;
use PHPUnit\Framework\TestCase;

class CompoTest extends TestCase
{
    private Compo $compo;
    private array $salles;

    private array $dossiers;

    protected function setUp(): void
    {
        $this->salles = [
            ["id" => 1, "contenance" => 5],
            ["id" => 2, "contenance" => 7],
            ["id" => 3, "contenance" => 10],
        ];

        $this->dossiers = [
            // Catégorie Permis 1 (13 candidats)
            ["id" => 101, "langue_id" => 1, "categorie_permis_id" => 1], // Français
            ["id" => 102, "langue_id" => 1, "categorie_permis_id" => 1],
            ["id" => 103, "langue_id" => 1, "categorie_permis_id" => 1],
            ["id" => 104, "langue_id" => 1, "categorie_permis_id" => 1],
            ["id" => 105, "langue_id" => 1, "categorie_permis_id" => 1],
            ["id" => 106, "langue_id" => 2, "categorie_permis_id" => 1], // Anglais
            ["id" => 107, "langue_id" => 2, "categorie_permis_id" => 1],
            ["id" => 108, "langue_id" => 2, "categorie_permis_id" => 1],
            ["id" => 109, "langue_id" => 2, "categorie_permis_id" => 1],
            ["id" => 110, "langue_id" => 3, "categorie_permis_id" => 1], // Arabe
            ["id" => 111, "langue_id" => 3, "categorie_permis_id" => 1],
            ["id" => 112, "langue_id" => 3, "categorie_permis_id" => 1],
            ["id" => 113, "langue_id" => 3, "categorie_permis_id" => 1],

            // Catégorie Permis 2 (9 candidats)
            ["id" => 114, "langue_id" => 1, "categorie_permis_id" => 2],
            ["id" => 115, "langue_id" => 1, "categorie_permis_id" => 2],
            ["id" => 116, "langue_id" => 1, "categorie_permis_id" => 2],
            ["id" => 117, "langue_id" => 1, "categorie_permis_id" => 2],
            ["id" => 118, "langue_id" => 2, "categorie_permis_id" => 2],
            ["id" => 119, "langue_id" => 2, "categorie_permis_id" => 2],
            ["id" => 120, "langue_id" => 3, "categorie_permis_id" => 2],
            ["id" => 121, "langue_id" => 3, "categorie_permis_id" => 2],
            ["id" => 122, "langue_id" => 3, "categorie_permis_id" => 2],

            // Catégorie Permis 3 (8 candidats)
            ["id" => 123, "langue_id" => 1, "categorie_permis_id" => 3],
            ["id" => 124, "langue_id" => 1, "categorie_permis_id" => 3],
            ["id" => 125, "langue_id" => 2, "categorie_permis_id" => 3],
            ["id" => 126, "langue_id" => 2, "categorie_permis_id" => 3],
            ["id" => 127, "langue_id" => 2, "categorie_permis_id" => 3],
            ["id" => 128, "langue_id" => 3, "categorie_permis_id" => 3],
            ["id" => 129, "langue_id" => 3, "categorie_permis_id" => 3],
            ["id" => 130, "langue_id" => 3, "categorie_permis_id" => 3],
        ];

        $this->compo = new Compo(
            dossiers: $this->dossiers,
            centre_id: 1,
            salles: $this->salles,
            examen_id: 1
        );
    }

    /**
     * @dataProvider roomScenarios
     */
    public function test_find_optimal_room(int $requiredCapacity, array $expectedRoom): void
    {
        $result = $this->compo->findOptimalRoom($requiredCapacity, [
            ["id" => 1, "contenance" => 5],
            ["id" => 2, "contenance" => 7],
            ["id" => 3, "contenance" => 10],
        ]);
        $this->assertEquals($expectedRoom, $result);
    }

    public function roomScenarios(): array
    {
        return [
            'capacity_below_minimum' => [3, ["id" => 1, "contenance" => 5]],
            'capacity_exact_match' => [7, ["id" => 2, "contenance" => 7]],
            'capacity_between_values' => [6, ["id" => 2, "contenance" => 7]],
            'capacity_above_maximum' => [13, ["id" => 3, "contenance" => 10]],
        ];
    }

    public function test_fill_room_from_group_with_exact_capacity()
    {
        // Prendre exactement 5 candidats
        $collection = collect(array_slice($this->dossiers, 0, 5));

        $result = $this->compo->fillRoomFromGroup($collection, $this->salles);

        $this->assertCount(1, $result['vagues']);
        $this->assertCount(5, $result['vagues'][0]['candidats']);
        $this->assertEquals(1, $result['vagues'][0]['salle_compo_id']); // Salle de contenance 5
        $this->assertTrue($result['remainingCandidats']->isEmpty());
    }

    public function test_fill_room_from_group_with_overflow()
    {
        // Prendre 13 candidats (nécessite plusieurs salles)
        $collection = collect(array_slice($this->dossiers, 0, 13));

        $result = $this->compo->fillRoomFromGroup($collection, $this->salles);

        $this->assertEquals(2, count($result['vagues']));
        $this->assertEquals(10, count($result['vagues'][0]['candidats'])); // Premier groupe max 10

        //La salle 2 doit contenir 3 candidats
        $this->assertEquals(3, count($result['vagues'][1]['candidats']));

        $this->assertEquals(3, $result['vagues'][0]['salle_compo_id']); // Plus grande salle d'abord

        //La deuxième vague doit être, salle 1 (contenance 5, plus proche de 3)
        $this->assertEquals(1, $result['vagues'][1]['salle_compo_id']);
    }

    public function test_fill_room_from_group_with_small_group()
    {
        // Prendre 3 candidats (moins que la plus petite salle)
        $collection = collect(array_slice($this->dossiers, 0, 3));

        $result = $this->compo->fillRoomFromGroup($collection, $this->salles);

        $this->assertCount(1, $result['vagues']);
        $this->assertCount(3, $result['vagues'][0]['candidats']);
        $this->assertEquals(1, $result['vagues'][0]['salle_compo_id']); // Plus petite salle
        $this->assertTrue($result['remainingCandidats']->isEmpty());
    }

    public function test_fill_room_from_group_with_empty_collection()
    {
        $result = $this->compo->fillRoomFromGroup(collect([]), $this->salles);

        $this->assertEmpty($result['vagues']);
        $this->assertTrue($result['remainingCandidats']->isEmpty());
    }
    public function test_fill_room_from_group_respects_room_capacity()
    {
        $collection = collect(array_slice($this->dossiers, 0, 15));

        $result = $this->compo->fillRoomFromGroup($collection, $this->salles);

        foreach ($result['vagues'] as $vague) {
            $salle = current(array_filter($this->salles, fn($s) => $s['id'] === $vague['salle_compo_id']));
            $this->assertLessThanOrEqual($salle['contenance'], count($vague['candidats']));
        }
    }
}
