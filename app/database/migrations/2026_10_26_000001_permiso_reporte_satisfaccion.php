<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * D2 (26/10): el coordinador consulta y genera el reporte de satisfacción;
     * el facilitador solo consulta.
     *
     * Se crea el permiso `export_satisfaction` en el proceso de cursos y se otorga
     * a los perfiles que hoy gestionan cualquier curso (`manage_course`), que son
     * los que ya ven los resultados de todos los cursos. A partir de aquí se puede
     * quitar o dar por separado desde la pantalla de permisos.
     *
     * En una instalación limpia el proceso aún no existe (lo crean los seeders,
     * que ya incluyen este permiso), así que la migración no hace nada.
     */
    private const CODIGO = 'taller.cursos.index';

    private const SLUG = 'export_satisfaction';

    public function up(): void
    {
        $procesoId = DB::table('security.processes')->where('codigo', self::CODIGO)->value('id');
        if (! $procesoId) {
            return;
        }

        $descripciones = require database_path('data/permisos_descripciones.php');

        DB::table('security.permissions')->updateOrInsert(
            ['process_id' => $procesoId, 'slug' => self::SLUG],
            [
                'name' => 'Generar reporte de satisfacción',
                'description' => $descripciones[self::SLUG],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $permisoId = DB::table('security.permissions')
            ->where('process_id', $procesoId)
            ->where('slug', self::SLUG)
            ->value('id');

        $gestionar = DB::table('security.permissions')
            ->where('process_id', $procesoId)
            ->where('slug', 'manage_course')
            ->value('id');

        $perfiles = $gestionar
            ? DB::table('security.profile_permissions')->where('permission_id', $gestionar)->pluck('profile_id')
            : collect();

        DB::table('security.profile_permissions')->insertOrIgnore(
            $perfiles->map(fn ($perfilId) => [
                'profile_id' => $perfilId,
                'permission_id' => $permisoId,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all()
        );
    }

    public function down(): void
    {
        $procesoId = DB::table('security.processes')->where('codigo', self::CODIGO)->value('id');
        if (! $procesoId) {
            return;
        }

        // profile_permissions cae por ON DELETE CASCADE
        DB::table('security.permissions')
            ->where('process_id', $procesoId)
            ->where('slug', self::SLUG)
            ->delete();
    }
};
