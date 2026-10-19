<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El Observer ya garantiza código PROCINEC en cada creación y el
     * UNIQUE uq_cursos_codigo permite N nulos en PostgreSQL: sin NOT NULL
     * un fallo silencioso del generador dejaría cursos sin código.
     */
    public function up(): void
    {
        $nulos = DB::table('taller.cursos')->whereNull('codigo')->count();

        if ($nulos > 0) {
            $ids = DB::table('taller.cursos')
                ->whereNull('codigo')
                ->limit(20)
                ->pluck('id_curso')
                ->implode(', ');
            throw new \RuntimeException(
                "No se puede exigir código PROCINEC: hay {$nulos} curso(s) sin código (ids: {$ids}). Regenera sus códigos antes de migrar."
            );
        }

        DB::statement('ALTER TABLE taller.cursos ALTER COLUMN codigo SET NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE taller.cursos ALTER COLUMN codigo DROP NOT NULL');
    }
};
