<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normaliza `security.profiles_users.status` a 1 (activo).
     *
     * El panel "Asignar Perfil" insertaba `status = 0` (con un comentario
     * obsoleto "0 = Activo"), mientras que el resto de escritores usaban 1.
     * Al endurecerse ValidarPerfilActivo (exige status = 1), esos usuarios
     * quedaban sin perfiles activos y eran expulsados al login.
     *
     * Ningún flujo del sistema usa 0 de forma intencional (quitar un perfil
     * borra la fila del pivote), por lo que normalizar es seguro.
     */
    public function up(): void
    {
        DB::table('security.profiles_users')
            ->where('status', 0)
            ->update(['status' => 1]);
    }

    public function down(): void
    {
        // No hay vuelta atrás: 0 era el valor erróneo, no un estado válido.
    }
};
