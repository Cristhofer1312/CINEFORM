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
        Schema::create('comun.personas', function (Blueprint $table) {
            $table->id('id_persona');
            $table->foreignId('user_id')->constrained('security.users')->onDelete('cascade');
            $table->foreignId('tipo_dni')->constrained('security.document_types');
            $table->string('dni')->nullable();
            $table->string('pasaporte')->nullable();
            $table->string('rif')->nullable();
            $table->string('reg_nac_cine')->nullable();
            $table->foreignId('genero')->constrained('security.genders');
            $table->string('primer_nombre')->nullable();
            $table->string('segundo_nombre')->nullable();
            $table->string('primer_apellido')->nullable();
            $table->string('segundo_apellido')->nullable();
            $table->string('telefono')->nullable();
            $table->string('telefono_opcional')->nullable();
            $table->foreignId('id_pais')->constrained('security.countries');
            $table->foreignId('id_estado')->references('id')->on('comun.estados');
            $table->foreignId('id_municipio')->references('id')->on('comun.municipios');
            $table->foreignId('id_parroquia')->references('id')->on('comun.parroquias');
            $table->string('direccion')->nullable();
            $table->integer('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->integer('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });


        DB::table('comun.personas')->insert([
            [
                'user_id' => 1,
                'tipo_dni' => 1,
                'dni' => '12345678',
                'pasaporte' => '12345678',
                'rif' => '12345678',
                'reg_nac_cine' => '12345678',
                'genero' => 1,
                'primer_nombre' => 'Cristhofer',
                'primer_apellido' => 'Leon',
                'telefono' => '12345678',
                'telefono_opcional' => '12345678',
                'id_pais' => 1,
                'id_estado' => 1,
                'id_municipio' => 1,
                'id_parroquia' => 1,
                'direccion' => '12345678',
                'creado_por' => 1,
                'creado_en' => now(),
                'actualizado_por' => 1,
                'actualizado_en' => now(),
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
        Schema::dropIfExists('comun.personas');
    }
};
