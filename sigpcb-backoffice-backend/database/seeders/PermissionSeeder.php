<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::updateOrCreate(['name' => 'super-admin'], ['name' => 'super-admin', 'guard_name' => 'web', 'nom_complet' => 'Administrateur principal']);
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web', 'nom_complet' => 'Administrateur']);

        $superAdminRole = Role::findByName('super-admin');
        $superAdminRole->givePermissionTo('all');
        Permission::updateOrCreate([
            'name' => 'all',
        ], [
            'name' => 'all',
            'guard_name' => 'web',
            'onglet' => "Tous les onglets",
            'nom_complet' => "Toutes les permissions",
            'description' => 'Accéder à toute la plateforme.',
        ]);
        $superAdminRole = Role::findByName('super-admin');
        $superAdminRole->givePermissionTo('all');

        $file = storage_path('permissions.json');
        $permissions = json_decode(file_get_contents($file), true);

        foreach ($permissions as $key => $data) {
            Permission::updateOrCreate([
                'name' => $data['name'],
            ], [
                'name' => $data['name'],
                'guard_name' => 'web',
                'onglet' => $data['onglet'],
                'nom_complet' => $data['nom_complet'],
                'description' => $data['description'],
            ]);
        }

        foreach (['dev.claudy@gmail.com', 'ulrichjaures2@gmail.com', 'anattpermis@gmail.com', 'abonnementbj@gmail.com'] as $key => $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->assignRole('super-admin');
            }
        }
    }
}
