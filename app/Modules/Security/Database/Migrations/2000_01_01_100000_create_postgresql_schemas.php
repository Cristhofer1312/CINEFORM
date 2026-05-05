<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Crear esquemas en PostgreSQL si no existen
        DB::statement('CREATE SCHEMA IF NOT EXISTS "comun";');
        DB::statement('CREATE SCHEMA IF NOT EXISTS "parametros";');
        DB::statement('CREATE SCHEMA IF NOT EXISTS "registro";');
        DB::statement('CREATE SCHEMA IF NOT EXISTS "security";');
        DB::statement('CREATE SCHEMA IF NOT EXISTS "taller";');
    }

    /**
     * Reverse the migrations.git merge -X theirs nombre-de-la-rama-entrante
     *
     * @return void
     */
    public function down()
    {
        // Por seguridad, puedes omitir borrar el esquema en down 
        // o hacerlo sólo si está vacío.
        // DB::statement('DROP SCHEMA IF EXISTS "comun" CASCADE;');
        // DB::statement('DROP SCHEMA IF EXISTS "parametros" CASCADE;');
        // DB::statement('DROP SCHEMA IF EXISTS "registro" CASCADE;');
        // DB::statement('DROP SCHEMA IF EXISTS "security" CASCADE;');
        // DB::statement('DROP SCHEMA IF EXISTS "taller" CASCADE;');
    }
};
