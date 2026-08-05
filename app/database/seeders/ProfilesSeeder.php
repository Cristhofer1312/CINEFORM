<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfilesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('security.profiles')->insert([
            ['name' => 'Administrator', 'description' => 'Administrator', 'active' => true, 'user_id' => 1, 'register_date' => now(), 'ip' => '127.0.0.1'],
            ['name' => 'Facilitador', 'description' => 'Facilitador', 'active' => true, 'user_id' => 1, 'register_date' => now(), 'ip' => '127.0.0.1'],
            ['name' => 'Participante', 'description' => 'Participante', 'active' => true, 'user_id' => 1, 'register_date' => now(), 'ip' => '127.0.0.1'],
            ['name' => 'Coordinador', 'description' => 'Coordinador', 'active' => true, 'user_id' => 1, 'register_date' => now(), 'ip' => '127.0.0.1'],
        ]);
    }
}
