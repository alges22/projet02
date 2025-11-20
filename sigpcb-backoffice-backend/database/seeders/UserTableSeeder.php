<?php

namespace Database\Seeders;

use App\Models\Inspecteur;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $user1 = User::create([
            'first_name' => 'Jaurès',
            'last_name' => 'GBADA',
            'phone' => '62700942',
            'email' => "ulrichjaures2@gmail.com",
            'password' => Hash::make('12345678'),
            'status' => true,
            'unite_admin_id' => 1,
        ]);

        $user2 = User::create([
            'first_name' => 'Claude',
            'last_name' => 'FASSINOU',
            'phone' => '61441378',
            'email' => "dev.claudy@gmail.com",
            'password' => Hash::make('12345678'),
            'status' => true,
            'unite_admin_id' => 1,
        ]);

        $user3 = User::create([
            'first_name' => 'Franck',
            'last_name' => 'HOUENOU',
            'phone' => '96 26 11 15',
            'email' => "franckhoundje@gmail.com",
            'password' => Hash::make('12345678'),
            'status' => true,
            'unite_admin_id' => 1,
        ]);

        $user4 = User::create([
            'first_name' => 'Mayaa',
            'last_name' => 'CAPO-CHICHI',
            'phone' => '61952803',
            'email' => "abonnementbj@gmail.com",
            'password' => Hash::make('12345678'),
            'status' => true,
            'unite_admin_id' => 1,
        ]);

        $user5 = User::create([
            'first_name' => 'Gildas',
            'last_name' => 'Zinpke',
            'phone' => '66983958',
            'email' => "gildas.zinkpe@gmail.com",
            'password' => Hash::make('12345678'),
            'status' => true,
            'unite_admin_id' => 1,
        ]);

        $user6 = User::create([
            'first_name' => 'Franck',
            'last_name' => 'Houehou',
            'phone' => '41 00 74 00',
            'email' => "franckhouehou@gmail.com",
            'password' => Hash::make('12345678'),
            'status' => true,
            'unite_admin_id' => 1,
        ]);

        $user7 = User::create([
            'first_name' => 'Kelly',
            'last_name' => 'Noel',
            'phone' => '69482883',
            'email' => "noeldossouyovo24@gmail.com",
            'password' => Hash::make('12345678'),
            'status' => true,
            'unite_admin_id' => 1,
        ]);

        $user8 = User::create([
            'first_name' => 'Philipp',
            'last_name' => 'ADDA',
            'phone' => '64976111',
            'email' => "philippadda9@gmail.com",
            'password' => Hash::make('12345678'),
            'status' => true,
            'unite_admin_id' => 1,
        ]);
        // Récupérer les rôles et les permissions
        $adminRole = Role::where('name', 'super-admin')->first();

        // Assigner les rôles et les permissions à l'utilisateur
        $user1->assignRole($adminRole);
        $user2->assignRole($adminRole);
        $user3->assignRole($adminRole);
        $user4->assignRole($adminRole);
        $user5->assignRole($adminRole);
        $user6->assignRole($adminRole);
        $user7->assignRole($adminRole);
        $user8->assignRole($adminRole);

    }
}
