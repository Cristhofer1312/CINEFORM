<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security.menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('description', 255);
            $table->string('icon', 50);
            $table->integer('order');
            $table->boolean('active');
            $table->unsignedBigInteger('module_id');
            $table->foreign('module_id')->references('id')->on('security.modules'); 
        });

        DB::table('security.menus')->insert([
            [ //1
                "name" => "Seguridad",
                "description" => "Gestión de perfiles, usuarios y permisos",
                "icon" => "fas fa-user-shield",
                "order" => 1,
                'active' => true,
                "module_id" => 1
            ],
            [ //2
                'name'        => 'Formación',
                'description' => 'Gestión de cursos, talleres y formaciones',
                'icon'        => 'fas fa-graduation-cap',
                'order'       => 2,
                'active'      => true,
                'module_id'   => 1,
            ],
            [ //3
                'name'        => 'Administración',
                'description' => 'Herramientas administrativas del sistema',
                'icon'        => 'fas fa-cogs',
                'order'       => 3,
                'active'      => true,
                'module_id'   => 1,
            ]
        ]);

        // Sincronizar secuencia de PostgreSQL
        DB::statement("SELECT setval(pg_get_serial_sequence('security.menus', 'id'), COALESCE((SELECT MAX(id) FROM security.menus), 0) + 1, false)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security.menus');
    }
};
