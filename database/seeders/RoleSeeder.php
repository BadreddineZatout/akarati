<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'promoteur',
            'guard_name' => 'web',
        ]);
        Role::create([
            'name' => 'chef chantier',
            'guard_name' => 'web',
        ]);
        Role::create([
            'name' => 'comptable',
            'guard_name' => 'web',
        ]);
    }
}
