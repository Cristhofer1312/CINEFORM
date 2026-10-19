<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dirección editable desde "Mi Perfil" -> "Editar Datos".
     */
    public function up(): void
    {
        Schema::table('security.users', function (Blueprint $table) {
            $table->string('address', 255)->nullable()->default(null)->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('security.users', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
};