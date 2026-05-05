<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security.profile_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('security.profiles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('security.permissions')->onDelete('cascade');
            $table->timestamps();

            // UNIQUE: un perfil no puede tener el mismo permiso asignado dos veces
            $table->unique(['profile_id', 'permission_id'], 'uniq_profile_permission');
        });

        // -------------------------------------------------------------------------
        // MAPEO CORRECTO: [profile_id => [process_id => [slugs]]]
        //
        // PROBLEMA RESUELTO: el mapeo anterior usaba solo 'slug' sin filtrar por
        // process_id. Esto causaba que 'view' de los procesos 1 (profiles),
        // 2 (users) y 3 (taller) se asignaran TODOS a Facilitadores, Coordinadores
        // y Participantes. Con el nuevo formato se asigna solo el proceso correcto.
        // -------------------------------------------------------------------------
        $mapping = [
            // Administrador: acceso total a todos los procesos y acciones
            1 => [
                1 => ['view', 'create', 'edit', 'permissions'],
                2 => ['view', 'create', 'edit', 'security'],
                3 => ['view'],
                4 => ['view', 'create_course', 'manage_course', 'approve_course', 'edit_course_e', 'grade_course'],
                5 => ['view', 'assign'],
                7 => ['view'],
            ],

            // Coordinador: gestión de perfiles (1), planificación (3), gestión de cursos (4) y asignación de perfiles (6)
            4 => [
                1 => ['view', 'create', 'edit', 'permissions'],
                3 => ['view'],
                4 => ['view', 'create_course', 'manage_course', 'approve_course', 'edit_course_e', 'grade_course'],
                6 => ['view', 'assign'],
                7 => ['view'],
            ],

            // Facilitador: dictar y calificar cursos (4) y ver sus inscripciones en Mis Cursos (5)
            2 => [
                4 => ['view', 'edit_course', 'grade_course', 'accept_course'],
                5 => ['view'],
            ],

            // Participante/Estudiante: ver catálogo (4) y ver sus propios cursos en Mis Cursos (5)
            3 => [
                4 => ['view'],
                5 => ['view'],
            ],
        ];

        foreach ($mapping as $profileId => $processMap) {
            if ($processMap === ['*']) {
                // Administrador: todos los permisos de todos los procesos
                $permissionIds = DB::table('security.permissions')->pluck('id');
            } else {
                $permissionIds = collect();
                foreach ($processMap as $processId => $slugs) {
                    // Clave: filtrar por process_id para no cruzar permisos entre módulos
                    $ids = DB::table('security.permissions')
                        ->where('process_id', $processId)
                        ->whereIn('slug', $slugs)
                        ->pluck('id');

                    $permissionIds = $permissionIds->merge($ids);
                }
            }

            foreach ($permissionIds->unique() as $permId) {
                DB::table('security.profile_permissions')->updateOrInsert(
                    ['profile_id' => $profileId, 'permission_id' => $permId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security.profile_permissions');
    }
};
