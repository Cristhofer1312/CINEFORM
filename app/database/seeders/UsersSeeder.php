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
            // Usuario 1: Admin (existente)
            [
                'username' => 'admin',
                'email' => 'crisclasyt@gmail.com',
                'password' => Hash::make('58826951'),
                'register_date' => now(),
                'active' => 1,
                'ip' => '127.0.0.1',
            ],
            // Usuario 2: Mariana García
            [
                'username' => 'Maga88',
                'email' => 'cformacioncnac@gmail.com',
                'password' => Hash::make('Paraulata88'),
                'register_date' => now(),
                'active' => 1,
                'ip' => '127.0.0.1',
            ],
            // Usuario 3: Zuleika Yanireth Tovar
            [
                'username' => 'Zule1101',
                'email' => 'zuznder@gmail.com',
                'password' => Hash::make('Chocolate'),
                'register_date' => now(),
                'active' => 1,
                'ip' => '127.0.0.1',
            ],
            // Usuario 4: LLammy Millan Petit
            [
                'username' => 'LMillan',
                'email' => 'Llammyp@gmail.com',
                'password' => Hash::make('242424'),
                'register_date' => now(),
                'active' => 1,
                'ip' => '127.0.0.1',
            ],
            // Usuario 5: Yenni Sira Piñango
            [
                'username' => 'Yeni Sira',
                'email' => 'Cordinaciondeinvestigacioncnac@gmail.com',
                'password' => Hash::make('ysira'),
                'register_date' => now(),
                'active' => 1,
                'ip' => '127.0.0.1',
            ],
            // Usuario 6: Jennifer Lourdes Caballero Moreno
            [
                'username' => 'JCABALLEROM',
                'email' => 'jcaballerom@cineform.ve',
                'password' => Hash::make('Dorado!88'),
                'register_date' => now(),
                'active' => 1,
                'ip' => '127.0.0.1',
            ],
            // Usuario 7: Nicolás Hernández
            [
                'username' => 'NicktheFox',
                'email' => 'nickthefox08@gmail.com',
                'password' => Hash::make('FURRY.y.arte.19'),
                'register_date' => now(),
                'active' => 1,
                'ip' => '127.0.0.1',
            ],
        ]);
    }
}
