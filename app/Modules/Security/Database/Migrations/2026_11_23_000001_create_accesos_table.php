<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro de inicios y cierres de sesión: quién entró (o intentó entrar),
     * desde qué IP y con qué navegador.
     *
     * Va aparte de security.auditoria (acciones de administración) porque crece
     * con cada visita. También es de solo inserción: un trigger rechaza UPDATE y
     * DELETE para que el historial no se pueda reescribir.
     */
    public function up(): void
    {
        Schema::create('security.accesos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // null si el usuario no existe
            $table->string('usuario', 100)->nullable();         // lo que se escribió en el login
            $table->string('evento', 30);                       // ver App\Services\Security\RegistroAccesos
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 300)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('ip');
            $table->index('created_at');
        });

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION security.accesos_inmutable() RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'security.accesos es de solo inserción';
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER accesos_sin_cambios
                BEFORE UPDATE OR DELETE ON security.accesos
                FOR EACH ROW EXECUTE FUNCTION security.accesos_inmutable();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS accesos_sin_cambios ON security.accesos;');
        DB::unprepared('DROP FUNCTION IF EXISTS security.accesos_inmutable();');
        Schema::dropIfExists('security.accesos');
    }
};
