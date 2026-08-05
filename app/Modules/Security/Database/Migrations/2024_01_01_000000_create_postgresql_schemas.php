<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['comun', 'parametros', 'registro', 'security', 'taller'] as $schema) {
            DB::statement("CREATE SCHEMA IF NOT EXISTS \"{$schema}\";");
        }
    }

    public function down(): void
    {
        foreach (['taller', 'security', 'registro', 'parametros', 'comun'] as $schema) {
            DB::statement("DROP SCHEMA IF EXISTS \"{$schema}\" CASCADE;");
        }
    }
};
