<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // IDs de procesos identificados:
        // 1: Perfiles
        // 2: Usuarios
        // 3: Planificar Curso (taller.cursos.create)
        // 4: Cursos (taller.cursos.index)
        // 5: Mis Cursos (taller.mis-cursos)
        // 6: Asignar Perfil
        // 7: Agregar Tipo de Actividad

        // 1. Asegurar que existe el permiso 'approve_course' para el proceso 4 (Cursos)
        // Nota: El slug original en la DB es 'approve_course'.
        $processCursosId = 4;
        $exists = DB::table('security.permissions')
            ->where('process_id', $processCursosId)
            ->where('slug', 'approve_course')
            ->exists();

        if (!$exists) {
            DB::table('security.permissions')->insert([
                'name' => 'Abrir Inscripciones Oficialmente',
                'slug' => 'approve_course',
                'process_id' => $processCursosId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("✅ Permiso 'approve_course' creado en proceso 4.");
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
        $this->command->info("✅ Todos los permisos asignados al perfil Administrador.");

        // 4. Asignar permisos al COORDINADOR (Perfil 4)
        $coordinadorProfileId = 4;
        // El coordinador gestiona casi todo el módulo Taller y Seguridad básica
        // EXCEPTO: Gestionar permisos (slug 'permissions') y seguridad de otros (slug 'security')
        $coordinadorPerms = DB::table('security.permissions')
            ->whereIn('process_id', [1, 3, 4, 6, 7])
            ->whereNotIn('slug', ['permissions', 'security'])
            ->pluck('id');

        foreach ($coordinadorPerms as $pId) {
            DB::table('security.profile_permissions')->updateOrInsert(
                ['profile_id' => $coordinadorProfileId, 'permission_id' => $pId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
        $this->command->info("✅ Permisos de gestión asignados al perfil Coordinador.");

        // 5. Asignar permisos al PARTICIPANTE (Perfil 3)
        // Solo debe ver cursos, inscribirse y ver sus cursos inscritos
        $participanteProfileId = 3;
        
        // Limpiar permisos actuales para asegurar que SOLO tenga los solicitados
        DB::table('security.profile_permissions')->where('profile_id', $participanteProfileId)->delete();

        $participantePerms = DB::table('security.permissions')
            ->where(function($q) use ($processCursosId) {
                $q->where('process_id', $processCursosId)
                  ->whereIn('slug', ['view', 'enroll', 'mark_attendance']);
            })
            ->orWhere(function($q) {
                $q->where('process_id', 5) // Mis Cursos
                  ->where('slug', 'view');
            })
            ->pluck('id');

        foreach ($participantePerms as $pId) {
            DB::table('security.profile_permissions')->insert([
                'profile_id' => $participanteProfileId,
                'permission_id' => $pId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        $this->command->info("✅ Permisos RESTRINGIDOS asignados al perfil Participante (Solo Cursos y Mis Cursos).");
    }
}
