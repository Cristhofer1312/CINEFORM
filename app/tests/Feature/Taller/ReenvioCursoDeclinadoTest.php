<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Tests\TestCase;

/**
 * Un curso declinado por coordinación vuelve a revisión cuando el facilitador
 * asignado corrige lo observado y lo reenvía («Enviar a revisión»).
 */
class ReenvioCursoDeclinadoTest extends TestCase
{
    use DatabaseTransactions;

    private User $facilitador;

    private User $otroFacilitador;

    private Curso $curso;

    protected function setUp(): void
    {
        parent::setUp();

        $facilitadores = User::whereHas('perfiles', fn ($q) => $q->where('security.profiles.id', Perfil::FACILITADOR))
            ->whereDoesntHave('perfiles', fn ($q) => $q->whereIn('security.profiles.id', [Perfil::ADMINISTRADOR, Perfil::COORDINADOR]))
            ->where('id', '>', 1)->whereNotNull('session_token')->get();

        $this->facilitador = $facilitadores->first() ?? User::findOrFail(5);
        $this->otroFacilitador = $facilitadores->skip(1)->first() ?? User::findOrFail(6);
        $this->soloConPerfiles($this->facilitador, Perfil::FACILITADOR);
        $this->soloConPerfiles($this->otroFacilitador, Perfil::FACILITADOR);

        $idPersona = $this->facilitador->personalData->id_persona;

        $this->curso = Curso::withoutEvents(function () use ($idPersona) {
            $c = new Curso;
            $c->nombre = 'Taller de Montaje';
            $c->codigo = 'TDM-'.random_int(1000, 9999);
            $c->id_persona = $idPersona;
            $c->id_actividad_formativa = 1;
            $c->id_modalidad = 1;
            $c->es_nacional = true;
            $c->save();

            $c->agregarEstado(EstadoCurso::APROBACION->value);
            $c->agregarEstado(EstadoCurso::DECLINADO->value);

            return $c;
        });
    }

    private function enviarARevision(User $user)
    {
        return $this->actingAs($user)
            ->withSession(['session_token' => $user->session_token])
            ->putJson(route('taller.cursos.finalizarEdicion', $this->curso->id_curso));
    }

    public function test_facilitador_reenvia_a_revision_un_curso_declinado(): void
    {
        $this->enviarARevision($this->facilitador)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(EstadoCurso::APROBACION->value, (int) $this->curso->fresh()->estado_actual->id_estado);
    }

    public function test_otro_facilitador_no_reenvia_un_curso_ajeno(): void
    {
        $this->enviarARevision($this->otroFacilitador)
            ->assertForbidden()
            ->assertJson(['message' => 'No autorizado para finalizar la edición de este curso']);

        $this->assertSame(EstadoCurso::DECLINADO->value, (int) $this->curso->fresh()->estado_actual->id_estado);
    }

    public function test_el_panel_del_facilitador_ofrece_enviar_a_revision_en_declinado(): void
    {
        $this->actingAs($this->facilitador)
            ->withSession(['session_token' => $this->facilitador->session_token])
            ->get(route('taller.cursos.show', $this->curso->crypt_id))
            ->assertOk()
            ->assertSee('Enviar a Revisión');
    }
}
