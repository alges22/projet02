<?php

namespace Database\Seeders;
use App\Models\AnipUser as User;
use App\Models\ApiClient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class CandidatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

            $filePath = storage_path('../candidat.json');

            if (File::exists($filePath)) {
                $jsonContent = File::get($filePath);
                $data = json_decode($jsonContent, true);
                foreach ($data as $userData) {

                    $userData['password'] = Hash::make("12345678");
                    if (!array_key_exists('email', $userData)) {
                        $userData['email'] = fake()->email();
                    }
                    unset($userData['id']);

                    $userData['telephone_prefix'] = "229";
                    // Insérer les données du tableau $userData dans la table "users"
                    // en utilisant la méthode create() du modèle User
                    User::create($userData);
                }
            }
    }
}
