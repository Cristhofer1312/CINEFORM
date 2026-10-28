<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Deja al usuario solo con los perfiles indicados (asignaciones activas).
     *
     * Los permisos son la suma de los perfiles activos, y en la BD de desarrollo
     * varios usuarios tienen más de uno (p. ej. el usuario 2 es Coordinador,
     * Facilitador y Participante). Las pruebas que verifican lo que NO puede
     * hacer un perfil deben aislarlo. Usar solo con DatabaseTransactions.
     */
    protected function soloConPerfiles($user, int ...$perfiles): void
    {
        DB::table('security.profiles_users')
            ->where('id_users', $user->id)
            ->whereNotIn('id_rol', $perfiles)
            ->update(['status' => 0]);

        foreach ($perfiles as $perfil) {
            $asignacion = ['id_users' => $user->id, 'id_rol' => $perfil];
            if (DB::table('security.profiles_users')->where($asignacion)->exists()) {
                DB::table('security.profiles_users')->where($asignacion)->update(['status' => 1]);
            } else {
                DB::table('security.profiles_users')->insert($asignacion + [
                    'status' => 1, 'creado_por' => 1, 'creado_en' => now(),
                ]);
            }
        }

        app(\App\Services\Security\Autorizacion::class)->invalidar();
    }
}
