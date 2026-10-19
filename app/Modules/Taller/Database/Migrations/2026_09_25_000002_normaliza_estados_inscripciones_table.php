<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $duplicados = DB::table('taller.inscripciones')
            ->select('id_curso', 'id_persona', DB::raw('COUNT(*) as total'))
            ->groupBy('id_curso', 'id_persona')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicados->isNotEmpty()) {
            throw new \RuntimeException('No se puede normalizar: existen ' . $duplicados->count() . ' pares (id_curso, id_persona) duplicados en taller.inscripciones. Resuélvelos antes de migrar.');
        }

        DB::table('taller.inscripciones')
            ->where('estado', 'activa')
            ->update(['estado' => 'aprobado']);

        DB::table('taller.inscripciones')
            ->where('estado', 'rechazada')
            ->update(['estado' => 'rechazado']);

        DB::table('taller.inscripciones')
            ->whereNotIn('estado', ['postulado', 'aprobado', 'rechazado', 'denegado'])
            ->update(['estado' => 'rechazado']);

        Schema::table('taller.inscripciones', function (Blueprint $table) {
            $table->unique(['id_curso', 'id_persona'], 'inscripciones_curso_persona_unica');
        });
    }

    public function down(): void
    {
        Schema::table('taller.inscripciones', function (Blueprint $table) {
            $table->dropUnique('inscripciones_curso_persona_unica');
        });
    }
};
