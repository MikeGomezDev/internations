<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'admin',
                'password' => bcrypt('admin_password'),
            ],
            [
                'name' => 'user',
                'password' => bcrypt('user_password'),
            ],
        ];
        
        foreach ($users as $user) {
            User::create($user);
        }
    }
}
