<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Tests\TestCase;

/**
 * Conflicto de interés (26/10). Con perfiles sumados, quien es coordinación y
 * facilitador a la vez no puede revisar, aprobar ni reasignar su propio curso,
 * ni asignarse a sí mismo. Otra persona de coordinación sí puede.
 */
class ConflictoInteresCursoTest extends TestCase
{
    use DatabaseTransactions;

    /** Coordinador que además es Facilitador (usuario 2 en desarrollo). */
    private User $coordinadorFacilitador;

    /** Otra persona de coordinación, no raíz. */
    private User $otroCoordinador;

    /** Facilitador puro. */
    private User $facilitador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinadorFacilitador = User::findOrFail(2);
        $this->soloConPerfiles($this->coordinadorFacilitador, Perfil::COORDINADOR, Perfil::FACILITADOR);

        $this->otroCoordinador = User::findOrFail(4);
        $this->soloConPerfiles($this->otroCoordinador, Perfil::COORDINADOR);

        $this->facilitador = User::findOrFail(5);
        $this->soloConPerfiles($this->facilitador, Perfil::FACILITADOR);
    }

    public function test_no_se_aprueba_ni_devuelve_el_curso_propio(): void
    {
        $curso = $this->cursoDe($this->coordinadorFacilitador, EstadoCurso::APROBACION);

        foreach ([EstadoCurso::INSCRIPCION, EstadoCurso::DECLINADO, EstadoCurso::EDICION, EstadoCurso::RECHAZADO] as $destino) {
            $this->assertFalse(
                Gate::forUser($this->coordinadorFacilitador)->allows('cambiarEstado', [$curso, $destino->value]),
                "No debe poder pasar su propio curso a {$destino->name}."
            );
            $this->assertTrue(
                Gate::forUser($this->otroCoordinador)->allows('cambiarEstado', [$curso, $destino->value]),
                "Otra persona de coordinación sí debe poder pasarlo a {$destino->name}."
            );
        }

        $this->como($this->coordinadorFacilitador)
            ->putJson(route('taller.cursos.updateStatus', ['curso' => $curso->crypt_id]), ['id_estado' => EstadoCurso::INSCRIPCION->value])
            ->assertForbidden();
        $this->assertSame(EstadoCurso::APROBACION->value, (int) $curso->fresh()->estado_actual->id_estado);
    }

    public function test_no_se_reasigna_el_curso_propio(): void
    {
        $curso = $this->cursoDe($this->coordinadorFacilitador, EstadoCurso::EDICION);

        $this->como($this->coordinadorFacilitador)
            ->putJson(route('taller.cursos.reasignarFacilitador', ['curso' => $curso->crypt_id]), [
                'id_persona' => $this->facilitador->personalData->id_persona,
            ])
            ->assertForbidden()
            ->assertJson(['message' => 'Eres el facilitador de este curso: otra persona de coordinación debe reasignarlo.']);

        $this->assertSame((int) $this->coordinadorFacilitador->personalData->id_persona, (int) $curso->fresh()->id_persona);
    }

    public function test_coordinacion_no_se_asigna_a_si_misma(): void
    {
        $curso = $this->cursoDe($this->facilitador, EstadoCurso::EDICION);

        $this->como($this->coordinadorFacilitador)
            ->putJson(route('taller.cursos.reasignarFacilitador', ['curso' => $curso->crypt_id]), [
                'id_persona' => $this->coordinadorFacilitador->personalData->id_persona,
            ])
            ->assertStatus(422)
            ->assertJson(['message' => 'No puedes asignarte a ti mismo como facilitador.']);

        $this->assertSame((int) $this->facilitador->personalData->id_persona, (int) $curso->fresh()->id_persona);
    }

    public function test_el_facilitador_no_cambia_el_facilitador_desde_editar(): void
    {
        $curso = $this->cursoDe($this->facilitador, EstadoCurso::EDICION);

        $this->como($this->facilitador)
            ->put(route('taller.cursos.update', ['curso' => $curso->crypt_id]), [
                'id_persona' => $this->coordinadorFacilitador->personalData->id_persona,
            ])
            ->assertSessionHasErrors(['id_persona' => 'No puedes asignar ese facilitador a este curso.']);

        $this->assertSame((int) $this->facilitador->personalData->id_persona, (int) $curso->fresh()->id_persona);
    }

    public function test_el_panel_separa_coordinacion_y_facilitador(): void
    {
        $curso = $this->cursoDe($this->coordinadorFacilitador, EstadoCurso::APROBACION);

        $this->como($this->coordinadorFacilitador)->get('/taller/cursos/'.$curso->id_curso)
            ->assertOk()
            ->assertSee('Como facilitador')
            ->assertSee('Propuesta en Evaluación')
            ->assertSee('Eres el facilitador de este curso')
            ->assertDontSee('Aprobar Propuesta')
            ->assertDontSee('<i class="fas fa-ban me-1"></i> Declinar Propuesta', false)
            ->assertDontSee('onclick="abrirModalReasignarFacilitador()"', false);

        $this->como($this->otroCoordinador)->get('/taller/cursos/'.$curso->id_curso)
            ->assertOk()
            ->assertSee('Aprobar Propuesta')
            ->assertSee('<i class="fas fa-ban me-1"></i> Declinar Propuesta', false)
            ->assertSee('onclick="abrirModalReasignarFacilitador()"', false)
            ->assertDontSee('Como facilitador');
    }

    private function como(User $user)
    {
        return $this->actingAs($user)->withSession(['session_token' => $user->session_token]);
    }

    private function cursoDe(User $user, EstadoCurso $estado): Curso
    {
        $curso = Curso::withoutEvents(function () use ($user) {
            $c = new Curso;
            $c->nombre = 'Taller de Producción';
            $c->codigo = 'TPR-'.random_int(1000, 9999);
            $c->id_persona = $user->personalData->id_persona;
            $c->id_actividad_formativa = 1;
            $c->id_aspecto = 1;
            $c->id_modalidad = 1;
            $c->id_modalidad_especial = 1;
            $c->cantidad_cupos = 25;
            $c->fecha_inicio = now();
            $c->fecha_fin = now()->addMonth();
            $c->save();

            return $c;
        });

        DB::table('taller.curso_estado')->insert([
            'id_curso' => $curso->id_curso,
            'id_estado' => $estado->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $curso;
    }
}
