<?php

namespace Tests\Feature\Security;

use App\Constants\Perfil;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Security\Entities\User;
use Tests\TestCase;

/**
 * D8 (26/10): quien tiene el permiso de asignar perfiles solo asigna o quita
 * los perfiles que él mismo posee. Los demás perfiles del usuario se conservan.
 */
class DelegacionPerfilesTest extends TestCase
{
    use DatabaseTransactions;

    private User $coordinador;

    private User $destino;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinador = User::whereHas('perfiles', fn ($q) => $q->where('security.profiles.id', Perfil::COORDINADOR))
            ->where('id', '>', 1)->first() ?? User::findOrFail(2);

        $this->destino = User::findOrFail(7);
        $this->assertNotSame($this->coordinador->id, $this->destino->id);

        // El coordinador tiene Coordinador, Facilitador y Participante, pero no Administrador.
        $this->dejarPerfiles($this->coordinador->id, [Perfil::FACILITADOR, Perfil::PARTICIPANTE, Perfil::COORDINADOR]);
        $this->dejarPerfiles($this->destino->id, [Perfil::PARTICIPANTE]);
    }

    private function dejarPerfiles(int $userId, array $perfiles): void
    {
        DB::table('security.profiles_users')->where('id_users', $userId)->delete();
        DB::table('security.profiles_users')->insert(array_map(fn ($idRol) => [
            'id_rol' => $idRol,
            'id_users' => $userId,
            'status' => 1,
            'creado_por' => 1,
            'creado_en' => now(),
            'actualizado_por' => 1,
            'actualizado_en' => now(),
        ], $perfiles));
    }

    private function perfilesDe(int $userId): array
    {
        return DB::table('security.profiles_users')->where('id_users', $userId)
            ->orderBy('id_rol')->pluck('id_rol')->map(fn ($id) => (int) $id)->all();
    }

    private function asignar(User $actor, int $perfil, array $perfiles)
    {
        return $this->actingAs($actor)
            ->withSession(['profile_id' => $perfil, 'session_token' => $actor->fresh()->session_token])
            ->post(route('users.assign_profiles'), [
                'id_persona' => $this->destino->personalData->id_persona,
                'perfiles' => $perfiles,
            ]);
    }

    public function test_no_asigna_un_perfil_que_no_posee(): void
    {
        $this->asignar($this->coordinador, Perfil::COORDINADOR, [Perfil::ADMINISTRADOR, Perfil::PARTICIPANTE])
            ->assertForbidden()
            ->assertJsonPath('message', 'Solo puede asignar perfiles que usted posee. No puede asignar: '
                .DB::table('security.profiles')->where('id', Perfil::ADMINISTRADOR)->value('name').'.');

        $this->assertSame([Perfil::PARTICIPANTE], $this->perfilesDe($this->destino->id));
    }

    public function test_asigna_los_perfiles_que_posee(): void
    {
        $this->asignar($this->coordinador, Perfil::COORDINADOR, [Perfil::FACILITADOR, Perfil::PARTICIPANTE])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame([Perfil::FACILITADOR, Perfil::PARTICIPANTE], $this->perfilesDe($this->destino->id));
    }

    public function test_conserva_el_perfil_ajeno_que_ya_tenia_el_usuario(): void
    {
        $this->dejarPerfiles($this->destino->id, [Perfil::ADMINISTRADOR, Perfil::PARTICIPANTE]);

        // La pantalla no envía el perfil deshabilitado; aun así no se pierde.
        $this->asignar($this->coordinador, Perfil::COORDINADOR, [Perfil::FACILITADOR])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame([Perfil::ADMINISTRADOR, Perfil::FACILITADOR], $this->perfilesDe($this->destino->id));
    }

    public function test_el_raiz_asigna_cualquier_perfil(): void
    {
        $this->asignar(User::findOrFail(1), Perfil::ADMINISTRADOR, [Perfil::ADMINISTRADOR, Perfil::PARTICIPANTE])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame([Perfil::ADMINISTRADOR, Perfil::PARTICIPANTE], $this->perfilesDe($this->destino->id));
    }

    public function test_la_pantalla_bloquea_los_perfiles_que_no_posee(): void
    {
        $html = $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR, 'session_token' => $this->coordinador->session_token])
            ->get(route('users.asignar_perfil'))
            ->assertOk()
            ->assertSee('No puede asignarlo ni quitarlo: usted no tiene este perfil.')
            ->getContent();

        $this->assertMatchesRegularExpression('/id="profile_'.Perfil::ADMINISTRADOR.'"[^>]*disabled/', $html);
        $this->assertDoesNotMatchRegularExpression('/id="profile_'.Perfil::FACILITADOR.'"[^>]*disabled/', $html);
    }
}
