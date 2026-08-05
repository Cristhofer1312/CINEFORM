<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $allPermissions = [
            1 => [
                ['slug' => 'view', 'name' => 'Ver sección de perfiles'],
                ['slug' => 'create', 'name' => 'Crear nuevo perfil'],
                ['slug' => 'edit', 'name' => 'Editar datos del perfil'],
                ['slug' => 'permissions', 'name' => 'Asignar permisos a perfiles'],
            ],
            2 => [
                ['slug' => 'view', 'name' => 'Ver lista de usuarios'],
                ['slug' => 'create', 'name' => 'Crear cuentas de usuario'],
                ['slug' => 'edit', 'name' => 'Editar datos de usuarios'],
                ['slug' => 'security', 'name' => 'Cambiar contraseñas'],
            ],
            3 => [
                ['slug' => 'view', 'name' => 'Acceso a creación de cursos'],
            ],
            4 => [
                ['slug' => 'view', 'name' => 'Ver catálogo de cursos'],
                ['slug' => 'create_course', 'name' => 'Planificar nuevos cursos'],
                ['slug' => 'manage_course', 'name' => 'Gestor o Coordinador del curso'],
                ['slug' => 'edit_course', 'name' => 'Dictar curso (Facilitador)'],
                ['slug' => 'edit_course_e', 'name' => 'Editar curso finalizado'],
                ['slug' => 'grade_course', 'name' => 'Calificar participantes'],
                ['slug' => 'accept_course', 'name' => 'Aceptar/Rechazar facilitación'],
                ['slug' => 'approve_course', 'name' => 'Aprobar e iniciar inscripciones'],
                ['slug' => 'enroll', 'name' => 'Inscribirse en cursos'],
                ['slug' => 'view_participants', 'name' => 'Ver Participantes Inscritos'],
                ['slug' => 'cancel_enrollment', 'name' => 'Cancelar inscripciones de participantes'],
                ['slug' => 'mark_attendance', 'name' => 'Marcar asistencia propia'],
                ['slug' => 'manage_attendance', 'name' => 'Gestionar asistencia de cursos'],
            ],
            5 => [
                ['slug' => 'view', 'name' => 'Ver mis cursos inscritos'],
            ],
            6 => [
                ['slug' => 'view', 'name' => 'Ver asignación de perfiles'],
                ['slug' => 'assign', 'name' => 'Asignar perfiles a usuarios'],
            ],
            7 => [
                ['slug' => 'view', 'name' => 'Acceso a gestión de catálogos'],
            ],
        ];

        foreach ($allPermissions as $processId => $permissions) {
            foreach ($permissions as $perm) {
                DB::table('security.permissions')->updateOrInsert(
                    ['process_id' => $processId, 'slug' => $perm['slug']],
                    ['name' => $perm['name'], 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}
