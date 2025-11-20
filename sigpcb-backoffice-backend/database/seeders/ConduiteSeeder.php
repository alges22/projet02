<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Api;
use App\Models\UniteAdmin;
use App\Models\AnnexeAnatt;
use App\Models\Examinateur;
use Illuminate\Database\Seeder;
use App\Models\AnnexeAnattJurie;
use App\Models\ExaminateurCategoriePermis;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ConduiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UniteAdmin::create(["name" => "Ministère de Tutelle"]);
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(TitreTableSeeder::class);
        $this->call(AnnexeAnattsTableSeeder::class);
        $this->call(RestrictionSeeder::class);
        $this->call(ExamensSeeder::class);
        $this->call(UserTableSeeder::class);
        foreach ([1, 3] as $key => $user) {
            Examinateur::create(
                [
                    'user_id' => $user,
                    'agent_id' => 1,
                    'annexe_anatt_id' => 1
                ]
            );
        }


        foreach (Examinateur::all() as $key => $examinateur) {
            if ($examinateur->id == 2) {
                foreach ([1, 2] as $key => $category) {
                    ExaminateurCategoriePermis::create([
                        'examinateur_id' => $examinateur->id,
                        'categorie_permis_id' => $category,
                    ]);
                }
            } else {
                ExaminateurCategoriePermis::create([
                    'examinateur_id' => $examinateur->id,
                    'categorie_permis_id' => 3
                ]);
            }
        }

        AnnexeAnattJurie::create([
            'annexe_anatt_id' => 1,
            'name' => "Jurie XVI",
        ]);

        AnnexeAnattJurie::create([
            'annexe_anatt_id' => 1,
            'name' => "Jurie ANaTT II",
        ]);
    }
}
