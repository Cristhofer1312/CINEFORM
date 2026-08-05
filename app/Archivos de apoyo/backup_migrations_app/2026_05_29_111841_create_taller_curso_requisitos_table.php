<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('taller.curso_requisitos', function (Blueprint $table) {
            $table->id('id_requisito');
            $table->unsignedInteger('id_curso');
            $table->string('tipo', 50); // 'pregunta', 'recurso', 'documento'
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->boolean('obligatorio')->default(true);
            $table->timestamps();

            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taller.curso_requisitos');
    }
};
