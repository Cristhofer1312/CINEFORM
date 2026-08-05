<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taller.inscripciones', function (Blueprint $table) {
            $table->boolean('certificado_aprobado')->nullable()->default(null)
                ->comment('NULL=pendiente, true=aprobado, false=denegado');
            $table->unsignedBigInteger('certificado_aprobado_por')->nullable();
            $table->foreign('certificado_aprobado_por')->references('id')->on('security.users')->onDelete('set null');
            $table->timestamp('certificado_fecha_aprobacion')->nullable();
            $table->text('certificado_motivo_denegacion')->nullable();
        });

        DB::table('taller.inscripciones')
            ->whereNull('certificado_aprobado')
            ->where('estado', 'aprobado')
            ->update(['certificado_aprobado' => true]);
    }

    public function down(): void
    {
        Schema::table('taller.inscripciones', function (Blueprint $table) {
            $table->dropForeign(['certificado_aprobado_por']);
            $table->dropColumn([
                'certificado_aprobado',
                'certificado_aprobado_por',
                'certificado_fecha_aprobacion',
                'certificado_motivo_denegacion',
            ]);
        });
    }
};
