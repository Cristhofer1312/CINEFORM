<?php

namespace Tests\Feature\Taller;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Security\Entities\User;
use Tests\TestCase;

/**
 * Rutas que respondían 500 (26/10): /taller/cursos/{texto} y /home.
 */
class RutasSinError500Test extends TestCase
{
    use DatabaseTransactions;

    private function usuarioConPerfiles(int $minimo): User
    {
        $id = DB::table('security.profiles_users as pu')
            ->join('security.profiles as p', 'p.id', '=', 'pu.id_rol')
            ->where('p.active', true)
            ->where('pu.id_users', '>', 1)
            ->groupBy('pu.id_users')
            ->havingRaw('count(*) >= ?', [$minimo])
            ->value('pu.id_users');

        $this->assertNotNull($id, "Se necesita un usuario con al menos {$minimo} perfiles.");

        return User::findOrFail($id);
    }

    private function como(User $user, array $sesion = [])
    {
        return $this->actingAs($user)->withSession($sesion + ['session_token' => $user->session_token]);
    }

    public function test_un_id_de_curso_que_no_es_numero_responde_404(): void
    {
        $user = User::findOrFail(1);

        $this->como($user)->get('/taller/cursos/crear')->assertNotFound();
    }

    public function test_home_ya_no_pide_elegir_perfil_a_quien_tiene_varios(): void
    {
        // Los permisos suman todos los perfiles activos: no hay selección.
        $user = $this->usuarioConPerfiles(2);
        $perfil = DB::table('security.profiles_users')->where('id_users', $user->id)->value('id_rol');

        $this->como($user, ['profile_id' => $perfil])->get('/home')->assertRedirect(route('home'));
        $this->como($user, ['profile_id' => $perfil])->get(route('usuario.cambiar_perfil'))->assertRedirect(route('home'));
    }

    public function test_home_con_un_solo_perfil_lleva_al_inicio(): void
    {
        $user = $this->usuarioConPerfiles(2);
        $unico = DB::table('security.profiles_users')->where('id_users', $user->id)->value('id_rol');
        DB::table('security.profiles_users')->where('id_users', $user->id)->where('id_rol', '!=', $unico)->delete();

        $this->como($user, ['profile_id' => $unico])->get('/home')->assertRedirect(route('home'));
    }
}
