<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModalidadesEspecialesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('taller.modalidades_especiales')->insert([
            ['nombre' => 'Nino', 'abreviatura' => 'NN', 'status' => 'Activo', 'es_menor' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Adolescente', 'abreviatura' => 'AL', 'status' => 'Activo', 'es_menor' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Adulto', 'abreviatura' => 'AD', 'status' => 'Activo', 'es_menor' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
