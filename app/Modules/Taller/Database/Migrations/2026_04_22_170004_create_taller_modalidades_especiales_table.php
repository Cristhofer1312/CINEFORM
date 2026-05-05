<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Catálogo de público objetivo para la nomenclatura PROCINEC.
     * Elemento 7 del código: LAB-TA220FTP[AD]-2026
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taller.modalidades_especiales', function (Blueprint $table) {
            $table->id('id_modalidad_especial');
            $table->string('nombre');           // Niño, Adolescente, Adulto
            $table->string('abreviatura', 2);   // NN, AL, AD
            $table->string('status')->default('Activo');
            $table->timestamps();

            $table->unique('abreviatura');
        });

        DB::table('taller.modalidades_especiales')->insert([
            ['nombre' => 'Niño',        'abreviatura' => 'NÑ', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Adolescente', 'abreviatura' => 'AL', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Adulto',      'abreviatura' => 'AD', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taller.modalidades_especiales');
    }
};
