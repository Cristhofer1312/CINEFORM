<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoEvaluacionesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('taller.tipo_evaluaciones')->insert([
            ['id_tipo_evaluacion' => 1, 'nombre' => 'Examen', 'descripcion' => 'Evaluacion escrita sobre un tema en especifico', 'creado_por' => 1, 'creado_en' => now(), 'actualizado_por' => 1, 'actualizado_en' => now()],
            ['id_tipo_evaluacion' => 2, 'nombre' => 'Exposicion', 'descripcion' => 'Evaluacion realizando una exposicion sobre un tema en especifico', 'creado_por' => 1, 'creado_en' => now(), 'actualizado_por' => 1, 'actualizado_en' => now()],
            ['id_tipo_evaluacion' => 3, 'nombre' => 'Trabajo', 'descripcion' => 'Evaluacion realizando un documento sobre un tema en especifico', 'creado_por' => 1, 'creado_en' => now(), 'actualizado_por' => 1, 'actualizado_en' => now()],
        ]);
    }
}
