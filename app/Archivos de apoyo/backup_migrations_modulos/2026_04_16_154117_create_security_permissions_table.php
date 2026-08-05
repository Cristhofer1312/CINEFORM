<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security.permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('Nombre legible del permiso (ej. Crear Curso)');
            $table->string('slug', 50)->comment('Identificador en código (ej. create_course)');
            $table->foreignId('process_id')
                ->constrained('security.processes')
                ->onDelete('cascade')
                ->comment('Asocia este permiso a una tabla de proceso/módulo');
            $table->timestamps();

            // Un permiso numérico o slug es único POR MÓDULO, no globalmente
            $table->unique(['process_id', 'slug']);
        });

        // -------------------------------------------------------------
        // POBLAR DATOS POR DEFECTO PARA QUE EL SISTEMA NO SE BLOQUEE
        // -------------------------------------------------------------

        $allPermissions = [
            // Process 1: Perfiles
            1 => [
                ['slug' => 'view', 'name' => 'Ver sección de perfiles'],
                ['slug' => 'create', 'name' => 'Crear nuevo perfil'],
                ['slug' => 'edit', 'name' => 'Editar datos del perfil'],
                ['slug' => 'permissions', 'name' => 'Asignar permisos a perfiles'],
            ],
            // Process 2: Usuarios
            2 => [
                ['slug' => 'view', 'name' => 'Ver lista de usuarios'],
                ['slug' => 'create', 'name' => 'Crear cuentas de usuario'],
                ['slug' => 'edit', 'name' => 'Editar datos de usuarios'],
                ['slug' => 'security', 'name' => 'Cambiar contraseñas'],
            ],
            // Process 3: Planificar Curso (Acceso Rápido)
            3 => [
                ['slug' => 'view', 'name' => 'Acceso a creación de cursos'],
            ],
            // Process 4: Cursos (Gestión General)
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
            ],
            // Process 5: Mis Cursos
            5 => [
                ['slug' => 'view', 'name' => 'Ver mis cursos inscritos'],
            ],
            // Process 6: Asignar Perfil
            6 => [
                ['slug' => 'view',   'name' => 'Ver asignación de perfiles'],
                ['slug' => 'assign', 'name' => 'Asignar perfiles a usuarios'],
            ],
            // Process 7: Agregar Tipo de Actividad
            7 => [
                ['slug' => 'view', 'name' => 'Acceso a gestión de catálogos'],
            ]
        ];

        foreach ($allPermissions as $processId => $permissions) {
            foreach ($permissions as $perm) {
                // Insertar ignorando duplicados por si se corre dos veces
                \Illuminate\Support\Facades\DB::table('security.permissions')->updateOrInsert(
                    ['process_id' => $processId, 'slug' => $perm['slug']],
                    [
                        'name' => $perm['name'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security.permissions');
    }
};
