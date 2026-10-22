<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Amplía la columna security.users.token de varchar(50) a varchar(64).
     * El flujo de recuperación de contraseña (SecurityController::recovery) genera
     * un hash SHA-256 de 64 caracteres. Con varchar(50), PostgreSQL rechaza el UPDATE
     * por truncamiento de datos (RF-03).
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE security.users ALTER COLUMN token TYPE varchar(64);');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE security.users ALTER COLUMN token TYPE varchar(50);');
    }
};
