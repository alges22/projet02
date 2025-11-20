<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // ChapitresTableSeeder::class,
            // LanguesTableSeeder::class,
            // SalleSeeder::class,
            // ApiClientSeeder::class,
            // CategoryPermisSeeder::class,
            //AssignDossiersToJuriesSeeder::class
        ]);
        $chapitres = [
            [
                'name' => 'GENERALITES (définitions et rappels)',
            ],
            [
                'name' => 'Signalisations routières',
            ],
            [
                'name' => 'Règles de priorité - Dépassement - croisement',
            ],
            [
                'name' => 'Arrêt & stationnement- Intégration - Changement de direction - Vitesse & manoeuvres',
            ],
            [
                'name' => 'Route - Route pour automobile - autoroute',
            ],
            [
                'name' => 'Infractions aux dispositions - Du code de la route- Civisme - secourisme',
            ],
            [
                'name' => 'Permis de conduire A1, A2, A3',
                'permis' => ['A1', 'A2', 'A3'],
            ],

            [
                'name' => 'Permis de conduire catégorie B',
                'permis' => ['B']
            ],

            [
                'name' => 'Permis de conduire catégories C & C1',
                'permis' => ['C', 'C1'],
            ],
            [
                'name' => 'Permis de conduire catégories D',
                'permis' => ['D'],
            ],

            [
                'name' => 'Equipement - Entretien - Documents administratifs',

            ],
        ];
    }
}