<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'SuperAdmin',
                'email' => 'superadmin@test.com',
                'password' => bcrypt('password'),
                'role' => 'super-admin',
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Customer',
                'email' => 'customer@test.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
            ],
        ]);
    }
}
