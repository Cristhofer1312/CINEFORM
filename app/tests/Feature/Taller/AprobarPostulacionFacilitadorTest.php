<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\PostulacionFacilitador;
use Tests\TestCase;

/**
 * Aprobar una postulación a facilitador debe dejar al postulante con el
 * perfil Facilitador activo y utilizable.
 */
class AprobarPostulacionFacilitadorTest extends TestCase
{
    use DatabaseTransactions;

    private User $coordinador;

    private User $postulante;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->coordinador = User::whereHas('perfiles', fn ($q) => $q->where('security.profiles.id', Perfil::COORDINADOR))
            ->where('id', '>', 1)->first() ?? User::findOrFail(2);

        $this->postulante = User::whereHas('perfiles', fn ($q) => $q->where('security.profiles.id', Perfil::PARTICIPANTE))
            ->whereDoesntHave('perfiles', fn ($q) => $q->whereIn('security.profiles.id', [Perfil::FACILITADOR, Perfil::COORDINADOR, Perfil::ADMINISTRADOR]))
            ->whereHas('personalData')
            ->where('id', '>', 1)
            ->firstOrFail();
    }

    private function aprobar(PostulacionFacilitador $postulacion)
    {
        return $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR, 'session_token' => $this->coordinador->session_token])
            ->from(route('taller.postulacion-facilitador.admin'))
            ->post(route('taller.postulacion-facilitador.aprobar', ['postulacion' => $postulacion->crypt_id]));
    }

    public function test_aprobar_asigna_el_perfil_facilitador_activo(): void
    {
        $postulacion = PostulacionFacilitador::create([
            'id_persona' => $this->postulante->personalData->id_persona,
            'estado' => PostulacionFacilitador::ESTADO_PENDIENTE,
        ]);

        $this->aprobar($postulacion)->assertSessionHas('success');

        $this->assertSame(PostulacionFacilitador::ESTADO_APROBADA, $postulacion->fresh()->estado);
        $this->assertSame(1, (int) DB::table('security.profiles_users')
            ->where('id_users', $this->postulante->id)
            ->where('id_rol', Perfil::FACILITADOR)
            ->value('status'));
    }
}
