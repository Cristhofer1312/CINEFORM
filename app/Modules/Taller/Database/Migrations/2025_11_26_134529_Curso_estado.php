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
        Schema::create('taller.curso_estado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_curso');
            $table->unsignedBigInteger('id_estado');
            $table->text('motivo')->nullable();
            $table->timestamps();

            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
            $table->foreign('id_estado')->references('id_estado')->on('taller.estados_curso')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taller.curso_estado');
    }
};
