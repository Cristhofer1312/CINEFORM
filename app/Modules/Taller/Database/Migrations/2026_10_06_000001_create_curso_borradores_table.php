<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Borradores del wizard de creación (uno o varios por usuario).
     *
     * El wizard es de guardado diferido: nada se crea en taller.cursos
     * hasta finalizar(). Esta tabla persiste el avance por pasos para
     * que el borrador sobreviva a la expiración de la sesión y pueda
     * retomarse desde "Mis borradores". El payload guarda los slices
     * {paso1, paso2, contenidos, requisitos}.
     */
    public function up(): void
    {
        Schema::create('taller.curso_borradores', function (Blueprint $table) {
            $table->id('id_borrador');
            $table->unsignedBigInteger('user_id');
            $table->string('token', 64)->unique();
            $table->unsignedTinyInteger('paso_actual')->default(1);
            $table->json('payload')->nullable();
            $table->string('foto_path')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('security.users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taller.curso_borradores');
    }
};
