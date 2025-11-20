<?php

namespace Database\Seeders;

use App\Models\DemandeAgrement;
use App\Models\Promoteur;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemandeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $promoteur = User::create([
            "npi" => "8888888888",
            "email" => "dev.claudy@gmail.com",
        ]);
        $demande = $promoteur->demandes()->create([
            'state' => 'init',
            'auto_ecole' => "Feu Vert",
            'ifu' => 111111111111,
            'departement_id' => 8,
            'commune_id' => 48,
            'moniteurs' => json_encode(['1234567890', '1234567890']),
            'email_pro' => 'dev.claudy@gmail.com',
            'telephone_pro' => "61441378",
            "email_promoteur" => "dev.claudy@gmail.com",
            'promoteur_npi' => '8888888888',
            "vehicules" => json_encode([])
        ]);

        $demande->fiche()->create([
            'nat_promoteur' => "fiches/1.png",
            'casier_promoteur' => "fiches/1.png",
            'ref_promoteur' => "fiches/1.png",
            'reg_commerce' => "fiches/1.png",
            'attest_fiscale' => "fiches/1.png",
            'attest_reg_organismes' => "fiches/1.png",
            'descriptive_locaux' => "fiches/1.png",
            'permis_moniteurs' => "fiches/1.png",
            'copie_statut' => "fiches/1.png",
        ]);


        #2

        $promoteur = User::create([
            "npi" => "1111111111",
            "email" => "ulrichjaures2@gmail.com",
        ]);
        $demande = $promoteur->demandes()->create([
            'state' => 'init',
            'auto_ecole' => "Bon Voyage",
            'ifu' => 111111111112,
            'departement_id' => 8,
            'commune_id' => 48,
            'moniteurs' => json_encode(['1234567890', '1234567890']),
            'email_pro' => 'ulrichjaures2@gmail.com',
            'telephone_pro' => "62700942",
            "email_promoteur" => "ulrichjaures2@gmail.com",
            'promoteur_npi' => '1111111111',
            "vehicules" => json_encode([])
        ]);

        $demande->fiche()->create([
            'nat_promoteur' => "fiches/1.png",
            'casier_promoteur' => "fiches/1.png",
            'ref_promoteur' => "fiches/1.png",
            'reg_commerce' => "fiches/1.png",
            'attest_fiscale' => "fiches/1.png",
            'attest_reg_organismes' => "fiches/1.png",
            'descriptive_locaux' => "fiches/1.png",
            'permis_moniteurs' => "fiches/1.png",
            'copie_statut' => "fiches/1.png",
        ]);
    }
}
