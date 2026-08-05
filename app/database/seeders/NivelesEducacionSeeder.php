<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelesEducacionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('comun.niveles_educacion')->insert([
            ['id_nivel_educacion' => 1, 'nivel' => 'Educación Inicial', 'descripcion' => 'Educación preescolar y jardín'],
            ['id_nivel_educacion' => 2, 'nivel' => 'Educación Primaria', 'descripcion' => 'Ciclo básico de educación formal'],
            ['id_nivel_educacion' => 3, 'nivel' => 'Educación Secundaria', 'descripcion' => 'Educación media obligatoria'],
            ['id_nivel_educacion' => 4, 'nivel' => 'Educación Técnica', 'descripcion' => 'Formación técnica o profesional'],
            ['id_nivel_educacion' => 5, 'nivel' => 'Educación Universitaria', 'descripcion' => 'Programas de pregrado universitario'],
            ['id_nivel_educacion' => 6, 'nivel' => 'Postgrado', 'descripcion' => 'Especialización, maestría, doctorado'],
        ]);
    }
}
