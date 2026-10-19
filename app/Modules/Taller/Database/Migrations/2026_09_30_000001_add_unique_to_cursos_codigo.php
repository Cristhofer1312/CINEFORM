<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El código PROCINEC identifica al curso en QR y certificados impresos:
     * no puede haber dos cursos con el mismo código.
     *
     * Los nulos repetidos no violan el UNIQUE en PostgreSQL, así que los
     * cursos sin código (legado) no se ven afectados.
     */
    public function up(): void
    {
        $duplicados = DB::table('taller.cursos')
            ->select('codigo', DB::raw('count(*) as total'))
            ->whereNotNull('codigo')
            ->groupBy('codigo')
            ->havingRaw('count(*) > 1')
            ->get();

        if ($duplicados->isNotEmpty()) {
            $lista = $duplicados->pluck('codigo')->implode(', ');
            throw new \RuntimeException(
                'No se puede crear el índice único: hay códigos PROCINEC duplicados ('.$lista.'). Normalízalos antes de migrar.'
            );
        }

        Schema::table('taller.cursos', function (Blueprint $table) {
            $table->unique('codigo', 'uq_cursos_codigo');
        });
    }

    public function down(): void
    {
        Schema::table('taller.cursos', function (Blueprint $table) {
            $table->dropUnique('uq_cursos_codigo');
        });
    }
};
