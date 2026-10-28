<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * D7 (26/10): el alcance "todos los cursos" deja de salir de `manage_course`
     * usado como rol escondido. Se crea el permiso explícito `supervise_courses`
     * ("Supervisar todos los cursos"), que extiende a cualquier curso las acciones
     * que el usuario ya tiene. Se otorga a los perfiles que hoy tienen
     * `manage_course`, para que nadie pierda acceso.
     *
     * También se actualizan el nombre y la descripción de `manage_course` y la
     * descripción de `assign` (D8: solo se asignan perfiles que uno posee).
     *
     * En una instalación limpia el proceso aún no existe (lo crean los seeders,
     * que ya incluyen este permiso), así que la migración no hace nada.
     */
    private const CODIGO = 'taller.cursos.index';

    public function up(): void
    {
        $descripciones = require database_path('data/permisos_descripciones.php');

        $procesoId = DB::table('security.processes')->where('codigo', self::CODIGO)->value('id');
        if (! $procesoId) {
            return;
        }

        DB::table('security.permissions')->updateOrInsert(
            ['process_id' => $procesoId, 'slug' => 'supervise_courses'],
            [
                'name' => 'Supervisar todos los cursos',
                'description' => $descripciones['supervise_courses'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('security.permissions')
            ->where('process_id', $procesoId)
            ->where('slug', 'manage_course')
            ->update([
                'name' => 'Gestionar cursos',
                'description' => $descripciones['manage_course'],
                'updated_at' => now(),
            ]);

        DB::table('security.permissions')
            ->where('slug', 'assign')
            ->update(['description' => $descripciones['assign'], 'updated_at' => now()]);

        $supervisar = DB::table('security.permissions')
            ->where('process_id', $procesoId)
            ->where('slug', 'supervise_courses')
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
                'permission_id' => $supervisar,
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

        $supervisar = DB::table('security.permissions')
            ->where('process_id', $procesoId)
            ->where('slug', 'supervise_courses')
            ->value('id');

        if ($supervisar) {
            DB::table('security.profile_permissions')->where('permission_id', $supervisar)->delete();
            DB::table('security.permissions')->where('id', $supervisar)->delete();
        }
    }
};
