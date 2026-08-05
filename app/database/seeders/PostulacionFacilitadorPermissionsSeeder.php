<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Constants\SecurityAction;

class PostulacionFacilitadorPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Procesos y obtener sus IDs
        // Proceso para el participante: Ser Facilitador
        $processSerFacilitadorId = DB::table('security.processes')->insertGetId([
            'name' => 'Ser Facilitador',
            'description' => 'Postulación para ser facilitador de CINEFORM',
            'icon' => 'fas fa-chalkboard-teacher',
            'route' => 'taller.postulacion-facilitador.landing',
            'actions' => 'view|apply_facilitator',
            'order' => 4,
            'active' => true,
            'menu_id' => 2, // 2 = Formación
        ]);

        // Proceso para el coordinador: Requisitos Facilitador
        $processRequisitosFacilitadorId = DB::table('security.processes')->insertGetId([
            'name' => 'Requisitos Facilitador',
            'description' => 'Gestión de requisitos y postulaciones a facilitador',
            'icon' => 'fas fa-tasks',
            'route' => 'taller.postulacion-facilitador.admin',
            'actions' => 'view|manage_facilitator_applications',
            'order' => 4,
            'active' => true,
            'menu_id' => 3, // 3 = Administración
        ]);

        // 2. Crear Permisos asociados a los procesos
        $permissionsToInsert = [
            // Permisos para "Ser Facilitador"
            ['process_id' => $processSerFacilitadorId, 'slug' => 'view', 'name' => 'Acceso a la Sección', 'created_at' => now(), 'updated_at' => now()],
            ['process_id' => $processSerFacilitadorId, 'slug' => 'apply_facilitator', 'name' => SecurityAction::labels()[SecurityAction::POSTULARSE_FACILITADOR], 'created_at' => now(), 'updated_at' => now()],
            
            // Permisos para "Requisitos Facilitador"
            ['process_id' => $processRequisitosFacilitadorId, 'slug' => 'view', 'name' => 'Acceso a la Sección', 'created_at' => now(), 'updated_at' => now()],
            ['process_id' => $processRequisitosFacilitadorId, 'slug' => 'manage_facilitator_applications', 'name' => SecurityAction::labels()[SecurityAction::GESTIONAR_POSTULACIONES_FACILITADOR], 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('security.permissions')->insert($permissionsToInsert);

        // 3. Asignar Permisos a Perfiles (profile_permissions)
        // Obtenemos los IDs de los permisos recién creados
        $permisosSerFacilitador = DB::table('security.permissions')->where('process_id', $processSerFacilitadorId)->get()->keyBy('slug');
        $permisosReqFacilitador = DB::table('security.permissions')->where('process_id', $processRequisitosFacilitadorId)->get()->keyBy('slug');

        $profilePermissions = [];

        // Perfil 3 (Participante) → Ve y puede postularse (Ser Facilitador)
        if ($permisosSerFacilitador->has('view') && $permisosSerFacilitador->has('apply_facilitator')) {
            $profilePermissions[] = ['profile_id' => 3, 'permission_id' => $permisosSerFacilitador['view']->id];
            $profilePermissions[] = ['profile_id' => 3, 'permission_id' => $permisosSerFacilitador['apply_facilitator']->id];
        }

        // Perfil 4 (Coordinador) y Perfil 1 (Administrador) → Gestionan requisitos
        if ($permisosReqFacilitador->has('view') && $permisosReqFacilitador->has('manage_facilitator_applications')) {
            // Coordinador
            $profilePermissions[] = ['profile_id' => 4, 'permission_id' => $permisosReqFacilitador['view']->id];
            $profilePermissions[] = ['profile_id' => 4, 'permission_id' => $permisosReqFacilitador['manage_facilitator_applications']->id];
            // Administrador
            $profilePermissions[] = ['profile_id' => 1, 'permission_id' => $permisosReqFacilitador['view']->id];
            $profilePermissions[] = ['profile_id' => 1, 'permission_id' => $permisosReqFacilitador['manage_facilitator_applications']->id];
        }

        if (!empty($profilePermissions)) {
            DB::table('security.profile_permissions')->insertOrIgnore($profilePermissions);
        }
    }
}
