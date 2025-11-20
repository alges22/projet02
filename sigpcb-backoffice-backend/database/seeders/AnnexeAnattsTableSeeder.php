<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnexeAnattsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $path = storage_path("data/annexe.txt");
        $contents = file_get_contents($path);

        $data = explode('_', $contents);
        foreach ($data as $key => $anexe_phone) {
            $anexe_phone_array = array_map("trim", explode("\n", $anexe_phone));
            # Supprime les chaines vides
            $anexe_phone_array = array_filter($anexe_phone_array);

            # Réindexer les clés
            $anexe_phone_array = array_values($anexe_phone_array);
            $name =  trim($anexe_phone_array[0]);
            $phone = trim($anexe_phone_array[1]);
            DB::table('annexe_anatts')->insert([
                ['name' => $name, 'adresse_annexe' => $name, 'phone' => $phone, 'commune_id' => '48', 'status' => true],
            ]);
        }
    }
}
