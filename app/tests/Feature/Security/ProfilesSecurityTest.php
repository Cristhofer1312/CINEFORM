<?php

namespace Tests\Feature\Security;

use App\Constants\Perfil;
use App\Helpers\Encryptor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Security\Entities\Profile;
use Modules\Security\Entities\User;
use Tests\TestCase;

class ProfilesSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $coordinador;
    private User $participante;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::findOrFail(1);

        $this->coordinador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::COORDINADOR);
        })->where('id', '>', 1)->first() ?? User::findOrFail(2);

        // Participante puro (distinto del coordinador)
        $this->participante = User::findOrFail(7);
    }

    public function test_participante_recibe_403_al_intentar_acceder_a_profiles(): void
    {
        $response = $this->actingAs($this->participante)
            ->withSession(['profile_id' => Perfil::PARTICIPANTE])
            ->get(route('profiles'));

        $response->assertStatus(403);
    }

    public function test_coordinador_recibe_403_en_profiles_permissions(): void
    {
        $idEncriptado = Encryptor::encrypt(Perfil::FACILITADOR);

        $response = $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR])
            ->get(route('profiles.permissions', ['id' => $idEncriptado]));

        $response->assertStatus(403);
    }

    public function test_usuario_no_puede_desactivar_su_propio_perfil_activo(): void
    {
        $coordinadorProfile = Profile::findOrFail(Perfil::COORDINADOR);
        $this->assertTrue((bool) $coordinadorProfile->active);

        $idEncriptado = Encryptor::encrypt(Perfil::COORDINADOR);

        $response = $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR])
            ->get(route('profiles.disabled', ['id' => $idEncriptado]));

        $response->assertRedirect(route('profiles'));
        $response->assertSessionHasErrors('error-message');

        // Confirmar que el perfil no fue desactivado
        $coordinadorProfile->refresh();
        $this->assertTrue((bool) $coordinadorProfile->active);
    }

    public function test_asignacion_perfiles_no_permite_modificar_al_administrador_raiz(): void
    {
        $idPersonaAdmin = $this->admin->personalData?->id_persona ?? 1;

        $response = $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR])
            ->post(route('users.assign_profiles'), [
                'id_persona' => $idPersonaAdmin,
                'perfiles' => [Perfil::FACILITADOR],
            ]);

        $response->assertStatus(403);
    }

    public function test_asignacion_perfiles_no_permite_autoasignacion(): void
    {
        $idPersonaCoordinador = $this->coordinador->personalData?->id_persona;
        $this->assertNotNull($idPersonaCoordinador, 'Coordinador debe tener personalData.');

        $response = $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR])
            ->post(route('users.assign_profiles'), [
                'id_persona' => $idPersonaCoordinador,
                'perfiles' => [Perfil::ADMINISTRADOR],
            ]);

        $response->assertStatus(403);
    }

    /**
     * RF-82 (decisión 1b): quien tiene ASIGNAR_PERFIL puede otorgar cualquier
     * perfil, igual que el administrador. No hay jerarquía fija de perfiles.
     */
    public function test_quien_tiene_permiso_de_asignar_puede_otorgar_cualquier_perfil(): void
    {
        $idPersonaTarget = $this->participante->personalData?->id_persona;
        $this->assertNotNull($idPersonaTarget, 'Target debe tener personalData.');

        $response = $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR, 'session_token' => $this->coordinador->session_token])
            ->post(route('users.assign_profiles'), [
                'id_persona' => $idPersonaTarget,
                'perfiles' => [Perfil::ADMINISTRADOR, Perfil::PARTICIPANTE],
            ]);

        $response->assertJson(['success' => true]);
        $this->assertTrue(
            DB::table('security.profiles_users')
                ->where('id_users', $this->participante->id)
                ->where('id_rol', Perfil::ADMINISTRADOR)
                ->exists()
        );
    }

    public function test_sin_permiso_de_asignar_perfiles_recibe_403(): void
    {
        $response = $this->actingAs($this->participante)
            ->withSession(['profile_id' => Perfil::PARTICIPANTE, 'session_token' => $this->participante->session_token])
            ->post(route('users.assign_profiles'), [
                'id_persona' => $this->coordinador->personalData?->id_persona,
                'perfiles' => [Perfil::ADMINISTRADOR],
            ]);

        $response->assertForbidden();
    }

    public function test_coordinador_puede_otorgar_perfil_facilitador_o_participante(): void
    {
        $idPersonaTarget = $this->participante->personalData?->id_persona;
        $this->assertNotNull($idPersonaTarget, 'Target debe tener personalData.');

        $response = $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR])
            ->post(route('users.assign_profiles'), [
                'id_persona' => $idPersonaTarget,
                'perfiles' => [Perfil::FACILITADOR, Perfil::PARTICIPANTE],
            ]);

        $this->assertContains($response->getStatusCode(), [200, 302]);
        $response->assertJson(['success' => true]);

        // La asignación debe quedar activa (status = 1). Un 0 aquí provoca que
        // ValidarPerfilActivo expulse al usuario en su siguiente request.
        $perfilesAsignados = DB::table('security.profiles_users')
            ->where('id_users', $this->participante->id)
            ->pluck('status', 'id_rol');

        $this->assertSame(1, (int) $perfilesAsignados[Perfil::FACILITADOR]);
        $this->assertSame(1, (int) $perfilesAsignados[Perfil::PARTICIPANTE]);

        // Y el usuario destino debe poder seguir operando tras la asignación.
        $targetFresco = $this->participante->fresh();

        $this->actingAs($targetFresco)
            ->withSession([
                'profile_id' => Perfil::FACILITADOR,
                'session_token' => $targetFresco->session_token,
            ])
            ->get(route('home'))
            ->assertStatus(200);
    }

    /**
     * RF-82 (decisión 1b): quien tiene EDITAR sobre perfiles puede activar o
     * desactivar perfiles base, igual que el administrador, y queda auditado.
     */
    public function test_quien_tiene_permiso_de_editar_perfiles_puede_desactivar_un_perfil_base(): void
    {
        $facilitador = Profile::findOrFail(Perfil::FACILITADOR);
        $this->assertTrue((bool) $facilitador->active);

        $response = $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR, 'session_token' => $this->coordinador->session_token])
            ->get(route('profiles.disabled', ['id' => Encryptor::encrypt(Perfil::FACILITADOR)]));

        $response->assertRedirect(route('profiles'));
        $response->assertSessionHasNoErrors();
        $this->assertFalse((bool) $facilitador->fresh()->active);
        $this->assertDatabaseHas('security.auditoria', [
            'accion' => 'perfil.desactivar',
            'entidad_id' => (string) Perfil::FACILITADOR,
        ]);
    }

    public function test_el_perfil_administrador_no_se_puede_desactivar(): void
    {
        $response = $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR, 'session_token' => $this->coordinador->session_token])
            ->get(route('profiles.disabled', ['id' => Encryptor::encrypt(Perfil::ADMINISTRADOR)]));

        $response->assertRedirect(route('profiles'));
        $response->assertSessionHasErrors('error-message');
        $this->assertTrue((bool) Profile::findOrFail(Perfil::ADMINISTRADOR)->active);
    }
}
