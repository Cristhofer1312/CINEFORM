<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('security.users')->insert([
            [
                'username' => 'admin',
                'email' => 'crisclasyt@gmail.com',
                'password' => Hash::make('12345678'),
                'register_date' => now(),
                'active' => 1,
                'ip' => '127.0.0.1',
            ],
            [
                'username' => 'participante',
                'email' => 'participante@cineform.com',
                'password' => Hash::make('123'),
                'register_date' => now(),
                'active' => 1,
                'ip' => '127.0.0.1',
            ]
        ]);
    }
}
