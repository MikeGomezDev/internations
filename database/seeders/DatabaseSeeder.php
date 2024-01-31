<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Group::factory(10)->create();

        $this->call([
            UserSeeder::class,
            RoleSeeder::class,
            RoleUserSeeder::class,
            GroupUserSeeder::class,
        ]);
    }
}
