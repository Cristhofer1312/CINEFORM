<?php

namespace Tests\Feature\Security;

use App\Constants\Perfil;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\PostulacionFacilitador;
use Tests\TestCase;

/**
 * Pruebas de seguridad para Postulación a Facilitador (RF-55 / P0).
 *
 * 1. Rutas administrativas protegidas por permiso RBAC (403 si no lo tiene).
 * 2. Un usuario no puede aprobar ni rechazar su propia postulación.
 */
class PostulacionFacilitadorSeguridadTest extends TestCase
{
    use DatabaseTransactions;

    private User $participanteUser;

    private User $coordinadorUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Buscar usuarios de prueba sembrados
        // Participante "puro": el usuario 1 es la cuenta raíz y tiene bypass de RBAC.
        $this->participanteUser = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->whereDoesntHave('perfiles', function ($q) {
            $q->whereIn('security.profiles.id', [Perfil::ADMINISTRADOR, Perfil::FACILITADOR, Perfil::COORDINADOR]);
        })->where('id', '>', 1)->first() ?? User::findOrFail(7);

        $this->coordinadorUser = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::COORDINADOR);
        })->first() ?? User::findOrFail(1);
    }

    public function test_participante_no_puede_acceder_al_panel_admin_de_facilitadores(): void
    {
        $response = $this->actingAs($this->participanteUser)
            ->withSession(['profile_id' => Perfil::PARTICIPANTE])
            ->get(route('taller.postulacion-facilitador.admin'));

        $response->assertStatus(403);
    }

    public function test_participante_no_puede_aprobar_postulaciones(): void
    {
        $response = $this->actingAs($this->participanteUser)
            ->withSession(['profile_id' => Perfil::PARTICIPANTE])
            ->post(route('taller.postulacion-facilitador.aprobar', ['postulacion' => 99999]));

        $response->assertStatus(403);
    }

    public function test_usuario_no_puede_aprobar_su_propia_postulacion(): void
    {
        // Crear postulación temporal para el coordinador
        $idPersona = optional($this->coordinadorUser->personalData)->id_persona;
        $this->assertNotNull($idPersona, 'El usuario coordinador debe tener personalData.');

        $postulacion = PostulacionFacilitador::create([
            'id_persona' => $idPersona,
            'estado' => PostulacionFacilitador::ESTADO_PENDIENTE,
        ]);

        $response = $this->actingAs($this->coordinadorUser)
            ->withSession(['profile_id' => Perfil::COORDINADOR])
            ->post(route('taller.postulacion-facilitador.aprobar', ['postulacion' => $postulacion->crypt_id]));

        $response->assertStatus(403);
    }

    public function test_usuario_no_puede_rechazar_su_propia_postulacion(): void
    {
        $idPersona = optional($this->coordinadorUser->personalData)->id_persona;
        $this->assertNotNull($idPersona, 'El usuario coordinador debe tener personalData.');

        $postulacion = PostulacionFacilitador::create([
            'id_persona' => $idPersona,
            'estado' => PostulacionFacilitador::ESTADO_PENDIENTE,
        ]);

        $response = $this->actingAs($this->coordinadorUser)
            ->withSession(['profile_id' => Perfil::COORDINADOR])
            ->post(route('taller.postulacion-facilitador.rechazar', ['postulacion' => $postulacion->crypt_id]), [
                'motivo_rechazo' => 'Motivo de prueba',
            ]);

        $response->assertStatus(403);
    }
}
