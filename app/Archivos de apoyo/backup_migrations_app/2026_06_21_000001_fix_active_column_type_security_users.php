<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Fix #15 — Corrección de tipo de columna `active` en security.users.
 *
 * Diagnóstico: la columna fue declarada como unsignedBigInteger pero
 * el código PHP asigna booleanos (true/false) y compara con == 0/1.
 * La semántica real es booleana, por lo que se migra a boolean.
 *
 * Nota: En PostgreSQL, BOOLEAN y SMALLINT son compatibles para los
 * valores 0/1, por lo que los datos existentes no se pierden.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Usar DB::statement para ALTER TABLE en PostgreSQL con esquema custom
        DB::statement('ALTER TABLE security.users ALTER COLUMN active TYPE BOOLEAN USING active::boolean');
        DB::statement('ALTER TABLE security.users ALTER COLUMN active SET DEFAULT FALSE');

        // También corregir el valor del admin (active=0 → false ya estaba bien semánticamente,
        // pero si quedó como entero 0, el cast a boolean lo mantiene correcto)
    }

    public function down(): void
    {
        // Revertir a unsignedBigInteger si se hace rollback
        DB::statement('ALTER TABLE security.users ALTER COLUMN active TYPE BIGINT USING active::int');
        DB::statement('ALTER TABLE security.users ALTER COLUMN active SET DEFAULT 0');
    }
};
