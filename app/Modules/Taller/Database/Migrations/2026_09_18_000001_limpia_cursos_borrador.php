<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Limpia cursos BORRADOR huérfanos que quedaron de la creación en dos
     * fases (antes de existir el wizard por sesión). Estos cursos nunca
     * completaron los 3 pasos de creación y no deben listarse.
     */
    public function up(): void
    {
        // Cursos cuyo estado actual es BORRADOR (10)
        $borradorId = DB::table('taller.estados_curso')
            ->where('nombre', 'Borrador')
            ->value('id_estado');

        if (!$borradorId) {
            return;
        }

        $cursoIds = DB::table('taller.curso_estado')
            ->where('id_estado', $borradorId)
            ->distinct()
            ->pluck('id_curso');

        foreach ($cursoIds as $idCurso) {
            // Solo eliminar si BORRADOR es su estado actual (no en historial)
            $estadoActual = DB::table('taller.curso_estado')
                ->where('id_curso', $idCurso)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->value('id_estado');

            if ((int) $estadoActual === (int) $borradorId) {
                // Los registros dependientes se eliminan por cascada
                DB::table('taller.cursos')->where('id_curso', $idCurso)->delete();
            }
        }

        // Eliminar los archivos de certificado huérfanos asociados a cursos eliminados
        foreach ($cursoIds as $idCurso) {
            foreach (['png', 'jpg', 'jpeg'] as $ext) {
                $path = storage_path('app/public/Certificados/cursos/' . $idCurso . '_firma.' . $ext);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
            foreach (['png', 'json'] as $ext) {
                $path = storage_path('app/public/Certificados/cursos/' . $idCurso . '.' . $ext);
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
        }
    }

    public function down(): void
    {
        // No se puede revertir: los cursos eliminados no se restauran.
    }
};