<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GendersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('security.genders')->insert([
            ['name' => 'Masculino', 'active' => true],
            ['name' => 'Femenino', 'active' => true],
        ]);
    }
}
