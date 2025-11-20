<?php

namespace Database\Seeders;

use App\Models\ApiClient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ApiClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ################################
        # Crée les cliens par défaut pour les instances interenes
        ApiClient::updateOrCreate(
            [
                "name" => "Admin"
            ],
            [
                "atk_public" => env('ANATT_ATK_PUBLIC_ADMIN'),
                "atk_private" => Hash::make(env('ANATT_ATK_PRIVATE_ADMIN'))
            ]
        );

        ApiClient::create([
            "atk_public" => env("ANATT_ATK_PUBLIC_CANDIDAT"),
            "atk_private" => Hash::make(env('ANATT_ATK_PRIVATE_CANDIDAT')),
            "name" => "Candidat"
        ]);

        ApiClient::create([
            "atk_public" => env('ANATT_ATK_PUBLIC_AECOLE'),
            "atk_private" => Hash::make(env('ANATT_ATK_PRIVATE_AECOLE')),
            "name" => "Auto-Ecole"
        ]);

        ApiClient::updateOrCreate(
            [
                "name" => "Examinateur"
            ],
            [
                "atk_public" => env('ANATT_ATK_PUBLIC_EXAMINATEUR'),
                "atk_private" => Hash::make(env('ANATT_ATK_PRIVATE_EXAMINATEUR'))
            ]
        );

        ApiClient::create([
            "atk_public" => env("ANATT_ATK_PUBLIC_COMPO"),
            "atk_private" => Hash::make(env('ANATT_ATK_PRIVATE_COMPO')),
            "name" => "Compo"
        ]);
    }
}
