<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columna para invalidar sesiones de usuarios (driver de sesión es File).
     * Al cambiar el status de un perfil se regenerea el token; el middleware
     * Authenticate compara la sesión contra este valor y cierra la sesión.
     */
    public function up(): void
    {
        Schema::table('security.users', function (Blueprint $table) {
            $table->string('session_token', 64)->nullable()->default(null)->after('token');
        });
    }

    public function down(): void
    {
        Schema::table('security.users', function (Blueprint $table) {
            $table->dropColumn('session_token');
        });
    }
};