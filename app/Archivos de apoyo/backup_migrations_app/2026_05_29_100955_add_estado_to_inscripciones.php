<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('taller.inscripciones', function (Blueprint $table) {
            $table->string('estado', 20)->default('activa');
            $table->integer('rechazada_por')->nullable();
            $table->timestamp('fecha_rechazo')->nullable();
        });
        
        // Ensure existing records are 'activa' (default should handle this, but just in case for existing rows if default isn't applied retroactively in some PG versions)
        \Illuminate\Support\Facades\DB::table('taller.inscripciones')->update(['estado' => 'activa']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taller.inscripciones', function (Blueprint $table) {
            $table->dropColumn('estado');
            $table->dropColumn('rechazada_por');
            $table->dropColumn('fecha_rechazo');
        });
    }
};
