<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Inspecteur;
use App\Models\AnnexeAnatt;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InspecteurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $annexe = AnnexeAnatt::latest()->first();
        if ($annexe) {
            foreach (User::all() as $key => $user) {
                Inspecteur::create([
                    "user_id" => $user->id,
                    "annexe_anatt_id" =>
                    $annexe->id,
                    "agent_id" => 1,
                ]);
            }
        }
    }
}
