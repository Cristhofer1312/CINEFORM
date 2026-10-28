<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Tests\TestCase;

/**
 * E1: aceptar la asignación llevaba el curso a Rechazado.
 * E2: el editor de cursos devolvía 500 por $idPersona sin definir.
 */
class AceptarAsignacionYEditorTest extends TestCase
{
    use DatabaseTransactions;

    private User $coordinador;

    private User $facilitador;

    private Curso $curso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::COORDINADOR);
        })->where('id', '>', 1)->first() ?? User::find(2);

        $this->facilitador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::FACILITADOR);
        })->where('id', '>', 1)->whereNotNull('session_token')->first() ?? User::findOrFail(5);

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

            $c->agregarEstado(EstadoCurso::POR_ACEPTAR->value);

            return $c;
        });
    }

    private function comoFacilitador(): self
    {
        return $this->actingAs($this->facilitador)->withSession([
            'profile_id' => Perfil::FACILITADOR,
            'session_token' => $this->facilitador->session_token,
        ]);
    }

    private function comoCoordinador(): self
    {
        return $this->actingAs($this->coordinador)->withSession([
            'profile_id' => Perfil::COORDINADOR,
            'session_token' => $this->coordinador->session_token,
        ]);
    }

    public function test_aceptar_asignacion_deja_el_curso_en_edicion_y_conserva_al_facilitador(): void
    {
        $response = $this->comoFacilitador()
            ->putJson(route('taller.cursos.updateStatus', $this->curso->id_curso), [
                'id_estado' => EstadoCurso::EDICION->value,
            ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $curso = $this->curso->fresh();
        $this->assertEquals(EstadoCurso::EDICION->value, $curso->estado_actual->id_estado);
        $this->assertEquals($this->facilitador->personalData->id_persona, $curso->id_persona);
    }

    public function test_desde_por_aceptar_un_destino_no_permitido_se_rechaza_y_no_cambia_el_estado(): void
    {
        $response = $this->comoFacilitador()
            ->putJson(route('taller.cursos.updateStatus', $this->curso->id_curso), [
                'id_estado' => EstadoCurso::INSCRIPCION->value,
            ]);

        $this->assertContains($response->status(), [403, 422]);
        $this->assertEquals(EstadoCurso::POR_ACEPTAR->value, $this->curso->fresh()->estado_actual->id_estado);
        $this->assertNotNull($this->curso->fresh()->id_persona);
    }

    public function test_el_facilitador_asignado_abre_el_editor_en_edicion(): void
    {
        $this->curso->agregarEstado(EstadoCurso::EDICION->value);

        $response = $this->comoFacilitador()->get(route('taller.cursos.edit', $this->curso->crypt_id));

        $response->assertStatus(200);
        $response->assertViewIs('taller::a.CursoEditar');
        $response->assertViewHas('esFacilitador', true);
    }

    public function test_el_coordinador_abre_el_editor_sin_ser_facilitador_del_curso(): void
    {
        $this->curso->agregarEstado(EstadoCurso::EDICION->value);

        $response = $this->comoCoordinador()->get(route('taller.cursos.edit', $this->curso->crypt_id));

        $response->assertStatus(200);
        $response->assertViewIs('taller::a.CursoEditar');
        $response->assertViewHas('esFacilitador', false);
    }
}
