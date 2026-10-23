<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadosCursoSeeder extends Seeder
{
    /**
     * Los IDs deben coincidir con App\Enums\EstadoCurso (1-9; Borrador = 10 lo
     * crea la migración add_borrador_estado_to_estados_curso). Se insertan con
     * ID explícito porque la migración de Borrador ya adelantó la secuencia.
     */
    public function run(): void
    {
        DB::table('taller.estados_curso')->upsert([
            ['id_estado' => 1, 'nombre' => 'Por_aceptar', 'descripcion' => 'Facilitador aun no acepta el curso'],
            ['id_estado' => 2, 'nombre' => 'Rechazado', 'descripcion' => 'Facilitador rechaza el curso'],
            ['id_estado' => 3, 'nombre' => 'Declinado', 'descripcion' => 'Curso declinado por el organizador'],
            ['id_estado' => 4, 'nombre' => 'Edicion', 'descripcion' => 'Facilitador evalua la plantilla entregada para edicion de la misma a sus necesidades'],
            ['id_estado' => 5, 'nombre' => 'Aprobacion', 'descripcion' => 'Fase donde el analista o el coordinador aprueba el curso'],
            ['id_estado' => 6, 'nombre' => 'Inscripcion', 'descripcion' => 'Fase donde el curso esta listo para inscribirse'],
            ['id_estado' => 7, 'nombre' => 'En_curso', 'descripcion' => 'Fase donde se imparte el curso'],
            ['id_estado' => 8, 'nombre' => 'Finalizado', 'descripcion' => 'Fase de emision de certificados y aclaraciones con el facilitador'],
            ['id_estado' => 9, 'nombre' => 'Cerrado', 'descripcion' => 'Fase final del curso donde solamente se puede solicitar la emision de certificados'],
        ], ['id_estado'], ['nombre', 'descripcion']);

        // Mantener la secuencia alineada con el MAX real para futuros insertGetId().
        DB::statement("SELECT setval(pg_get_serial_sequence('taller.estados_curso', 'id_estado'), (SELECT MAX(id_estado) FROM taller.estados_curso), true)");
    }
}
