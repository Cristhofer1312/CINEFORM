<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security.processes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->text('description');
            $table->string('icon', 50);
            $table->string('route', 50);
            $table->string('actions', 200);
            $table->integer('order');
            $table->boolean('active')->default(true);
            $table->foreignId('menu_id')->constrained('security.menus')->onDelete('cascade');

            /*  $table->unsignedBigInteger('menu_id');
             $table->foreign('menu_id')->references('id')->on('security.menus'); */
        });

        DB::table('security.processes')->insert([
            [ // 1
                'name' => 'Perfiles',
                'description' => 'Administración de perfiles y roles del sistema',
                'icon' => 'fas fa-user-friends',
                'route' => 'profiles',
                "actions" => 'view|create|edit|permissions',
                'order' => '1',
                'active' => true,
                'menu_id' => 1,
            ],
            [ // 2
                'name' => 'Usuarios',
                'description' => 'Gestión de cuentas de usuario y credenciales',
                'icon' => 'fas fa-user',
                'route' => 'users',
                "actions" => 'view|create|edit|security',
                'order' => '2',
                'active' => true,
                'menu_id' => 1,
            ],
            [ // 3
                'name' => 'Planificar Curso',
                'description' => 'Diseñar y registrar un nuevo plan de curso',
                'icon' => 'fas fa-plus-circle',
                'route' => 'taller.cursos.create',
                'actions' => 'view',
                'order' => '1',
                'active' => true,
                'menu_id' => 2, // Menú Formación
            ],
            [ // 4
                'name' => 'Cursos',
                'description' => 'Gestión Académica de Cursos y Formaciones',
                'icon' => 'fas fa-graduation-cap',
                'route' => 'taller.cursos.index',
                'actions' => 'view|create_course|manage_course|edit_course|edit_course_e|grade_course|approve_course|enroll',
                'order' => '2',
                'active' => true,
                'menu_id' => 2, // Menú Formación
            ],
            [ // 5
                'name'        => 'Mis Cursos',
                'description' => 'Ver los cursos en los que estoy inscrito',
                'icon'        => 'fas fa-book-reader',
                'route'       => 'taller.mis-cursos',
                'actions'     => 'view',
                'order'       => 3,
                'active'      => true,
                'menu_id'     => 2, // Menú Formación
            ],
            [ // 6
                'name'        => 'Asignar Perfil',
                'description' => 'Buscar usuario por DNI y asignar/quitar perfiles',
                'icon'        => 'fas fa-user-tag',
                'route'       => 'users.asignar_perfil',
                'actions'     => 'view|assign',
                'order'       => 1,
                'active'      => true,
                'menu_id'     => 3, // Menú Administración
            ],
            [ // 7
                'name'        => 'Agregar Tipo de Actividad',
                'description' => 'Gestión de catálogos: Actividades Formativas y Aspectos de Formación',
                'icon'        => 'fas fa-layer-group',
                'route'       => 'taller.catalogos.index',
                'actions'     => 'view',
                'order'       => 2,
                'active'      => true,
                'menu_id'     => 3, // Menú Administración
            ]
        ]);

        // Sincronizar secuencia de PostgreSQL
        DB::statement("SELECT setval(pg_get_serial_sequence('security.processes', 'id'), COALESCE((SELECT MAX(id) FROM security.processes), 0) + 1, false)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security.processes');
    }
};
