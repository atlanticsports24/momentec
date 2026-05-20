<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('shield:generate', [
            '--all' => true,
            '--minimal' => true,
            '-n' => true,
        ]);

        $role = Role::query()->firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'web']
        );

        $user = User::query()->firstOrCreate(
            ['email' => 'admin@momentec.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }
    }
}
