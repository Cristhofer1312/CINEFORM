<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Constants\SecurityAction;

class EstadisticasPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear proceso y obtener su ID real (autoincremental)
        $processEstadisticasId = DB::table('security.processes')->insertGetId([
            'name' => 'Estadísticas de Cursos',
            'description' => 'Indicadores y gráficos sobre los cursos del sistema',
            'icon' => 'fas fa-chart-bar',
            'route' => 'taller.estadisticas.index',
            'actions' => 'view|view_statistics',
            'order' => 5,
            'active' => true,
            'menu_id' => 3, // 3 = Administración
        ]);

        // 2. Crear permisos asociados al proceso
        $permissionsToInsert = [
            ['process_id' => $processEstadisticasId, 'slug' => 'view', 'name' => 'Acceso a la Sección', 'created_at' => now(), 'updated_at' => now()],
            ['process_id' => $processEstadisticasId, 'slug' => 'view_statistics', 'name' => SecurityAction::labels()[SecurityAction::VER_ESTADISTICAS], 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('security.permissions')->insert($permissionsToInsert);

        // 3. Asignar permisos a perfiles (profile_permissions)
        $permisosEstadisticas = DB::table('security.permissions')
            ->where('process_id', $processEstadisticasId)
            ->get()
            ->keyBy('slug');

        $profilePermissions = [];

        // Perfil 1 (Administrador) y Perfil 4 (Coordinador) → Consultan estadísticas
        if ($permisosEstadisticas->has('view') && $permisosEstadisticas->has('view_statistics')) {
            foreach ([1, 4] as $profileId) {
                $profilePermissions[] = ['profile_id' => $profileId, 'permission_id' => $permisosEstadisticas['view']->id];
                $profilePermissions[] = ['profile_id' => $profileId, 'permission_id' => $permisosEstadisticas['view_statistics']->id];
            }
        }

        if (!empty($profilePermissions)) {
            DB::table('security.profile_permissions')->insertOrIgnore($profilePermissions);
        }
    }
}
