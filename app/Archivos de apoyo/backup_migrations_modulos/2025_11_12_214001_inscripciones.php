<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taller.inscripciones', function (Blueprint $table) {
            $table->id('id_inscripcion');
            $table->unsignedBigInteger('id_curso')->nullable();
            $table->unsignedBigInteger('id_persona')->nullable();
            $table->date('fecha_inscripcion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taller.inscripciones');
    }
};
