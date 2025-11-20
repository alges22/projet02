<?php

namespace Database\Seeders;

use App\Models\Jurie;
use App\Models\Examinateur;
use Illuminate\Database\Seeder;
use App\Models\AnnexeAnattJurie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JurySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Récupérer l'annexe avec l'ID 1

        // Récupérer tous les examinateurs existants
        $annexeId = 1;
        $examen_id = 1;
        $JuryParAnnexe = $this->getAnnexeJury($annexeId);
        $ExaminateurParAnnexe = $this->getAnnexeExaminateur($annexeId);

        $juryCount = count($JuryParAnnexe);
        $examinateurCount = count($ExaminateurParAnnexe);

        if ($juryCount > 0 && $examinateurCount > 0) {
            // Mélangez les listes des jurys et des examinateurs
            // shuffle($JuryParAnnexe);
            // shuffle($ExaminateurParAnnexe);

            // Associez les jurys aux examinateurs
            for ($i = 0; $i < $juryCount; $i++) {
                $examinateurIndex = $i % $examinateurCount; // Pour garantir que chaque examinateur est associé à au plus un jury
                $jury = $JuryParAnnexe[$i];
                $examinateur = $ExaminateurParAnnexe[$examinateurIndex];

                // Insérez le jury associé à l'examinateur dans la table
                Jurie::create([
                    "name" => $jury->name,
                    "annexe_jury_id" => $jury->id,
                    "annexe_anatt_id" => $annexeId,
                    "examinateur_id" => $examinateur->id,
                    "examen_id" => $examen_id,
                ]);
            }
        }
    }

    protected function getAnnexeJury($annexeId)
    {
        return AnnexeAnattJurie::select(['id', 'name'])
            ->where('annexe_anatt_id', $annexeId)
            ->get();
    }
    protected function getAnnexeExaminateur($annexeId)
    {
        return Examinateur::select(['id', 'user_id'])
            ->where('annexe_anatt_id', $annexeId)
            ->get();
    }
}
