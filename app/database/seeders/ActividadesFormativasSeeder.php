<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActividadesFormativasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('taller.actividades_formativas')->insert([
            ['nombre' => 'Taller', 'abreviatura' => 'TA', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Foro', 'abreviatura' => 'FR', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Simposio', 'abreviatura' => 'SP', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Conferencia', 'abreviatura' => 'CF', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Altos Estudios', 'abreviatura' => 'AE', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Clase Magistral', 'abreviatura' => 'CM', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Webinar', 'abreviatura' => 'WB', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
