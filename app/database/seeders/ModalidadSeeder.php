<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModalidadSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('taller.modalidad')->insert([
            ['nombre_modalidad' => 'Presencial', 'abreviatura' => 'P', 'descripcion' => 'Modalidad presencial', 'status' => 'Activo', 'creado_por' => 1, 'creado_en' => now()],
            ['nombre_modalidad' => 'Virtual', 'abreviatura' => 'V', 'descripcion' => 'Modalidad virtual', 'status' => 'Activo', 'creado_por' => 1, 'creado_en' => now()],
            ['nombre_modalidad' => 'Hibrida (Bimodal)', 'abreviatura' => 'H', 'descripcion' => 'Modalidad hibrida o bimodal', 'status' => 'Activo', 'creado_por' => 1, 'creado_en' => now()],
        ]);
    }
}
