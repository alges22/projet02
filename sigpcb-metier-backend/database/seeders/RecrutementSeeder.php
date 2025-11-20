<?php

namespace Database\Seeders;

use App\Services\Api;
use App\Models\Entreprise;
use App\Models\Recrutement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RecrutementSeeder extends Seeder
{
    public function run()
    {
        // Entreprise 1
        $entreprise1 = Entreprise::find(1);   
        // Créer un recrutement pour l'entreprise 1
        Recrutement::create([
            'entreprise_id' => $entreprise1->id,
            'categorie_permis_id' => '5',
            'annexe_id' => '2',
            'date_compo' => now(),
            'finished' => true,
            'convocation' => true,
            'state' => 'validate',
        ]);

        // Entreprise 2
        $entreprise2 = Entreprise::find(2);
        // Créer un recrutement pour l'entreprise 2
        Recrutement::create([
            'entreprise_id' => $entreprise2->id,
            'categorie_permis_id' => '5',
            'annexe_id' => '8',
            'date_compo' => now(),
            'finished' => true,
            'convocation' => true,
            'state' => 'validate',
        ]);
    }
}
