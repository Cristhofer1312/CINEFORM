<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca qué públicos objetivos corresponden a menores de edad.
     *
     * El catálogo `taller.modalidades_especiales` es el selector de
     * "Público objetivo" del curso (Niño, Adolescente, Adulto). La carta de
     * permiso solo aplica a cursos dirigidos a niños y adolescentes, así que
     * la regla queda explícita en el catálogo y no depende de comparar
     * nombres desde el código.
     */
    public function up(): void
    {
        Schema::table('taller.modalidades_especiales', function (Blueprint $table) {
            $table->boolean('es_menor')->default(false);
        });

        DB::table('taller.modalidades_especiales')
            ->where(function ($query) {
                $query->whereRaw('LOWER(nombre) LIKE ?', ['%nino%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%niño%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%adolescente%']);
            })
            ->update(['es_menor' => true]);
    }

    public function down(): void
    {
        Schema::table('taller.modalidades_especiales', function (Blueprint $table) {
            $table->dropColumn('es_menor');
        });
    }
};
