<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Catálogo de tipos de actividad educativa para la nomenclatura PROCINEC.
     * Elemento 2 del código: LAB-[TA]220FTPAD-2026
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taller.actividades_formativas', function (Blueprint $table) {
            $table->id('id_actividad_formativa');
            $table->string('nombre');           // Taller, Foro, Simposio, etc.
            $table->string('abreviatura', 2);   // TA, FR, SP, etc.
            $table->string('status')->default('Activo');
            $table->timestamps();

            $table->unique('abreviatura');
        });

        DB::table('taller.actividades_formativas')->insert([
            ['nombre' => 'Taller',          'abreviatura' => 'TA', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Foro',            'abreviatura' => 'FR', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Símposio',        'abreviatura' => 'SP', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Conferencia',     'abreviatura' => 'CF', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Altos Estudios',  'abreviatura' => 'AE', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Clase Magistral', 'abreviatura' => 'CM', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Webinar',         'abreviatura' => 'WB', 'status' => 'Activo', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taller.actividades_formativas');
    }
};
