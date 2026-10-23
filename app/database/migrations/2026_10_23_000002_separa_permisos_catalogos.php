<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Separa "ver catálogos" de "modificar catálogos".
     *
     * Hasta ahora crear, editar y activar/desactivar actividades y aspectos solo
     * exigía VER. Se crean los permisos `create` y `edit` del proceso de catálogos
     * y se otorgan a los perfiles que hoy tienen `view`, para que nadie pierda un
     * acceso que ya usaba; a partir de aquí se pueden retirar por separado.
     *
     * En una instalación limpia el proceso aún no existe (lo crean los seeders,
     * que ya incluyen estos permisos), así que la migración no hace nada.
     */
    private const RUTA = 'taller.catalogos.index';

    private const NUEVOS = [
        'create' => 'Crear actividades y aspectos',
        'edit' => 'Editar y activar/desactivar actividades y aspectos',
    ];

    public function up(): void
    {
        $procesoId = DB::table('security.processes')->where('route', self::RUTA)->value('id');
        if (! $procesoId) {
            return;
        }

        $view = DB::table('security.permissions')
            ->where('process_id', $procesoId)
            ->where('slug', 'view')
            ->value('id');

        $perfilesConView = $view
            ? DB::table('security.profile_permissions')->where('permission_id', $view)->pluck('profile_id')
            : collect();

        foreach (self::NUEVOS as $slug => $nombre) {
            DB::table('security.permissions')->updateOrInsert(
                ['process_id' => $procesoId, 'slug' => $slug],
                ['name' => $nombre, 'created_at' => now(), 'updated_at' => now()]
            );

            $permisoId = DB::table('security.permissions')
                ->where('process_id', $procesoId)
                ->where('slug', $slug)
                ->value('id');

            DB::table('security.profile_permissions')->insertOrIgnore(
                $perfilesConView->map(fn ($perfilId) => [
                    'profile_id' => $perfilId,
                    'permission_id' => $permisoId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all()
            );
        }

        DB::table('security.processes')->where('id', $procesoId)->update(['actions' => 'view|create|edit']);
    }

    public function down(): void
    {
        $procesoId = DB::table('security.processes')->where('route', self::RUTA)->value('id');
        if (! $procesoId) {
            return;
        }

        // profile_permissions cae por ON DELETE CASCADE
        DB::table('security.permissions')
            ->where('process_id', $procesoId)
            ->whereIn('slug', array_keys(self::NUEVOS))
            ->delete();

        DB::table('security.processes')->where('id', $procesoId)->update(['actions' => 'view']);
    }
};
