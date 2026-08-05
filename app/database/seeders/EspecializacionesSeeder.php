<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EspecializacionesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('comun.especializaciones')->insert([
            ['nombre' => 'Cinematografia', 'descripcion' => 'Especializacion en cinematografia', 'status' => 'Activo', 'creado_por' => 1, 'creado_en' => now(), 'actualizado_por' => 1, 'actualizado_en' => now()],
            ['nombre' => 'Edicion', 'descripcion' => 'Especializacion en edicion', 'status' => 'Activo', 'creado_por' => 1, 'creado_en' => now(), 'actualizado_por' => 1, 'actualizado_en' => now()],
            ['nombre' => 'Iluminacion', 'descripcion' => 'Especializacion en iluminacion', 'status' => 'Activo', 'creado_por' => 1, 'creado_en' => now(), 'actualizado_por' => 1, 'actualizado_en' => now()],
        ]);
    }
}
