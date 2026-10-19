<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taller.curso_estado', function (Blueprint $table) {
            $table->text('motivo')->nullable()->after('id_estado');
        });
    }

    public function down(): void
    {
        Schema::table('taller.curso_estado', function (Blueprint $table) {
            $table->dropColumn('motivo');
        });
    }
};
