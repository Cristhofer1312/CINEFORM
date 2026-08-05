<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadosCursoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('taller.estados_curso')->insert([
            ['nombre' => 'Por_aceptar', 'descripcion' => 'Facilitador aun no acepta el curso'],
            ['nombre' => 'Rechazado', 'descripcion' => 'Facilitador rechaza el curso'],
            ['nombre' => 'Declinado', 'descripcion' => 'Curso declinado por el organizador'],
            ['nombre' => 'Edicion', 'descripcion' => 'Facilitador evalua la plantilla entregada para edicion de la misma a sus necesidades'],
            ['nombre' => 'Aprobacion', 'descripcion' => 'Fase donde el analista o el coordinador aprueba el curso'],
            ['nombre' => 'Inscripcion', 'descripcion' => 'Fase donde el curso esta listo para inscribirse'],
            ['nombre' => 'En_curso', 'descripcion' => 'Fase donde se imparte el curso'],
            ['nombre' => 'Finalizado', 'descripcion' => 'Fase de emision de certificados y aclaraciones con el facilitador'],
            ['nombre' => 'Cerrado', 'descripcion' => 'Fase final del curso donde solamente se puede solicitar la emision de certificados'],
        ]);
    }
}
