<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro de auditoría de acciones críticas (RF-07 / RNF-06).
     *
     * Es de solo inserción: un trigger rechaza UPDATE y DELETE para que nadie,
     * ni siquiera la propia aplicación, pueda reescribir el historial.
     */
    public function up(): void
    {
        Schema::create('security.auditoria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('perfil_id')->nullable();
            $table->string('accion', 60);
            $table->string('entidad', 60);
            $table->string('entidad_id', 64)->nullable();
            $table->jsonb('antes')->nullable();
            $table->jsonb('despues')->nullable();
            $table->text('motivo')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entidad', 'entidad_id']);
            $table->index('user_id');
            $table->index('created_at');
        });

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION security.auditoria_inmutable() RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'security.auditoria es de solo inserción';
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER auditoria_sin_cambios
                BEFORE UPDATE OR DELETE ON security.auditoria
                FOR EACH ROW EXECUTE FUNCTION security.auditoria_inmutable();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS auditoria_sin_cambios ON security.auditoria;');
        DB::unprepared('DROP FUNCTION IF EXISTS security.auditoria_inmutable();');
        Schema::dropIfExists('security.auditoria');
    }
};
