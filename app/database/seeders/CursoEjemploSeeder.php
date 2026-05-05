<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursoEjemploSeeder extends Seeder
{
    public function run(): void
    {

        DB::table('taller.cursos')->insert([
            'nombre'         => 'Introducción al Cine Venezolano',
            'descripcion'    => 'Un recorrido histórico y técnico por los fundamentos del cine venezolano: géneros, autores y técnicas de producción audiovisual.',
            'id_modalidad'   => 1,
            'id_persona'     => 1,
            'horas'          => 40,
            'duracion'       => 8,
            'cantidad_cupos' => 20,
            'fecha_inicio'   => '2026-05-01',
            'fecha_fin'      => '2026-05-31',
            'creado_por'     => 1,
            'creado_en'      => now(),
        ]);

        $id = DB::table('taller.cursos')->orderByDesc('id_curso')->value('id_curso');

        DB::table('taller.curso_estado')->insert([
            'id_curso'   => $id,
            'id_estado'  => 6,
            'created_at' => now(),
        ]);

        $contenidos = [
            ['titulo' => 'Historia del Cine Venezolano', 'descripcion' => 'Orígenes y evolución del cine en Venezuela',    'descripcion_breve' => 'Orígenes del cine venezolano', 'orden' => 1, 'es_evaluacion' => false, 'ponderacion' => null],
            ['titulo' => 'Géneros y Formatos',           'descripcion' => 'Géneros cinematográficos del cine nacional',    'descripcion_breve' => 'Géneros del cine nacional',    'orden' => 2, 'es_evaluacion' => false, 'ponderacion' => null],
            ['titulo' => 'Evaluación Final',             'descripcion' => 'Evaluación de los contenidos del curso',        'descripcion_breve' => 'Evaluación final del curso',   'orden' => 3, 'es_evaluacion' => true,  'ponderacion' => 100],
        ];

        foreach ($contenidos as $c) {
            DB::table('taller.contenido_cursos')->insert([
                'id_curso'          => $id,
                'titulo'            => $c['titulo'],
                'descripcion'       => $c['descripcion'],
                'descripcion_breve' => $c['descripcion_breve'],
                'url_contenido'     => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'orden'             => $c['orden'],
                'es_evaluacion'     => $c['es_evaluacion'],
                'ponderacion'       => $c['ponderacion'],
                'creado_por'        => 1,
                'actualizado_por'   => 1,
            ]);
        }

        $this->command->info("✅ Curso de ejemplo creado con ID: {$id}");
    }
}
