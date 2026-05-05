<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Catálogo de modos de enseñanza con abreviatura
     * para la nomenclatura PROCINEC.
     * Elemento 6 del código: LAB-TA220FT[P]AD-2026
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taller.modalidad', function (Blueprint $table) {
            $table->id('id_modalidad');
            $table->string('nombre_modalidad');
            $table->string('abreviatura', 1);   // P, V, H
            $table->text('descripcion');
            $table->string('status');
            $table->unsignedBigInteger('creado_por')->nullable();
            $table->timestamp('creado_en')->nullable();
            $table->unsignedBigInteger('actualizado_por')->nullable();
            $table->timestamp('actualizado_en')->nullable();
        });

        DB::table('taller.modalidad')->insert([
            [
                'nombre_modalidad' => 'Presencial',
                'abreviatura'     => 'P',
                'descripcion'     => 'Modalidad presencial',
                'status'          => 'Activo',
                'creado_por'      => 1,
                'creado_en'       => now(),
            ],
            [
                'nombre_modalidad' => 'Virtual',
                'abreviatura'     => 'V',
                'descripcion'     => 'Modalidad virtual',
                'status'          => 'Activo',
                'creado_por'      => 1,
                'creado_en'       => now(),
            ],
            [
                'nombre_modalidad' => 'Híbrida (Bimodal)',
                'abreviatura'     => 'H',
                'descripcion'     => 'Modalidad híbrida o bimodal',
                'status'          => 'Activo',
                'creado_por'      => 1,
                'creado_en'       => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taller.modalidad');
    }
};
