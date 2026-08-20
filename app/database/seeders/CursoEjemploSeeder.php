<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursoEjemploSeeder extends Seeder
{
    public function run(): void
    {
        $cursoId = DB::table('taller.cursos')->max('id_curso') + 1;

        DB::table('taller.cursos')->insert([
            'id_curso'                => $cursoId,
            'codigo'                  => 'TEST-2026-001',
            'nombre'                  => 'Curso de Prueba - Aceptar Asignación',
            'id_modalidad'            => 1,
            'id_actividad_formativa'  => 1,
            'id_aspecto'              => 1,
            'id_modalidad_especial'   => 1,
            'id_persona'              => 1,
            'descripcion'             => 'Curso creado automáticamente para probar la funcionalidad del botón Aceptar Curso.',
            'nivel'                   => 'Básico',
            'trimestre'               => 1,
            'correlativo'             => 1,
            'anio'                    => 2026,
            'duracion'                => 40,
            'horas'                   => 40,
            'cantidad_cupos'          => '25',
            'telegram'                => 'https://t.me/test',
            'es_nacional'             => true,
            'creado_por'              => 1,
            'actualizado_por'         => 1,
            'fecha_inicio'            => now()->addDays(15)->toDateString(),
            'fecha_fin'               => now()->addDays(45)->toDateString(),
            'creado_en'               => now(),
            'actualizado_en'          => now(),
        ]);

        DB::table('taller.curso_estado')->insert([
            'id_curso'   => $cursoId,
            'id_estado'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("Curso de prueba creado: ID={$cursoId}");
    }
}
