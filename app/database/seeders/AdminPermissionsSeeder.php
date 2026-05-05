<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar que existe el permiso 'approve_course' para el proceso 3 (Cursos)
        $processId = 3;
        $exists = DB::table('security.permissions')
            ->where('process_id', $processId)
            ->where('slug', 'approve_course')
            ->exists();

        if (!$exists) {
            DB::table('security.permissions')->insert([
                'name' => 'Aprobar curso (Abrir Inscripciones)',
                'slug' => 'approve_course',
                'process_id' => $processId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("✅ Permiso 'approve_course' creado.");
        }

        // 2. Obtener todos los IDs de permisos actuales
        $permissionIds = DB::table('security.permissions')->pluck('id');

        // 3. Asignar TODOS los permisos al perfil ADMINISTRADOR (Perfil 1)
        $adminProfileId = 1;
        foreach ($permissionIds as $pId) {
            DB::table('security.profile_permissions')->updateOrInsert(
                ['profile_id' => $adminProfileId, 'permission_id' => $pId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info("✅ Todos los permisos ({$permissionIds->count()}) asignados al perfil Administrador.");

        // 4. Asignar permisos básicos y de gestión al COORDINADOR (Perfil 4)
        $coordinadorProfileId = 4;
        $coordinadorPerms = DB::table('security.permissions')
            ->whereIn('slug', ['view', 'manage_course', 'create_course', 'approve_course', 'edit_course', 'grade_course'])
            ->where('process_id', $processId)
            ->pluck('id');

        foreach ($coordinadorPerms as $pId) {
            DB::table('security.profile_permissions')->updateOrInsert(
                ['profile_id' => $coordinadorProfileId, 'permission_id' => $pId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info("✅ Permisos de gestión asignados al perfil Coordinador.");
    }
}
