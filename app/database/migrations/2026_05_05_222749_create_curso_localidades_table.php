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
        Schema::create('taller.curso_localidades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_curso');
            $table->unsignedBigInteger('id_estado'); // Representa la localidad

            $table->foreign('id_curso')->references('id_curso')->on('taller.cursos')->onDelete('cascade');
            // La tabla es comun.estados y la PK es 'id'
            $table->foreign('id_estado')->references('id')->on('comun.estados')->onDelete('cascade');
            
            $table->unique(['id_curso', 'id_estado']);
        });

        // Migración de datos existentes: Mover el id_estado actual a la nueva tabla pivote
        $cursos = DB::table('taller.cursos')->whereNotNull('id_estado')->get();
        foreach ($cursos as $curso) {
            try {
                DB::table('taller.curso_localidades')->insert([
                    'id_curso' => $curso->id_curso,
                    'id_estado' => $curso->id_estado
                ]);
            } catch (\Exception $e) {
                // Probablemente el estado ya no exista o ID inválido, ignoramos
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taller.curso_localidades');
    }
};
