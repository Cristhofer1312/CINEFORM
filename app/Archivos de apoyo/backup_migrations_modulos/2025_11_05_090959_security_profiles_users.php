<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('security.profiles_users', function (Blueprint $table) {
            $table->id('id_rol_persona');
            $table->foreignId('id_rol')->constrained('security.profiles')->onDelete('cascade');
            $table->foreignId('id_users')->constrained('security.users')->onDelete('cascade');
            $table->unsignedBigInteger('status')->default(0);
            $table->date('fecha_aprobacion')->nullable();
            $table->unsignedBigInteger('aprobado_por')->nullable();
            $table->unsignedBigInteger('creado_por');
            $table->dateTime('creado_en');
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->dateTime('actualizado_en')->nullable();
        });


        //1. Administrador
        //2. Facilitador
        //3. Participante
        //4. Coordinador

        DB::table('security.profiles_users')->insert([
            [
                'id_rol' => 1,
                'id_users' => 1,
                'creado_por' => 1,
                'creado_en' => now()
            ],
            [
                'id_rol' => 4,
                'id_users' => 1,
                'creado_por' => 1,
                'creado_en' => now()
            ],
            [
                'id_rol' => 2,
                'id_users' => 1,
                'creado_por' => 1,
                'creado_en' => now()
            ],
            [
                'id_rol' => 3,
                'id_users' => 1,
                'creado_por' => 1,
                'creado_en' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('security.profiles_users');
    }
};
