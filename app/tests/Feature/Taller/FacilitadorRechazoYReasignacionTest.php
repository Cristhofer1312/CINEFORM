<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Tests\TestCase;

class FacilitadorRechazoYReasignacionTest extends TestCase
{
    use DatabaseTransactions;

    private User $coordinador;

    private User $facilitador1;

    private User $facilitador2;

    private Curso $curso;

    protected function setUp(): void
    {
        parent::setUp();

        // Obtener coordinador (rol 2)
        $this->coordinador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::COORDINADOR);
        })->where('id', '>', 1)->first() ?? User::find(2);
        $this->soloConPerfiles($this->coordinador, Perfil::COORDINADOR);

        // Obtener dos facilitadores distintos (sin perfil de gestión: los permisos suman perfiles)
        $facilitadores = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::FACILITADOR);
        })->whereDoesntHave('perfiles', fn ($q) => $q->whereIn('security.profiles.id', [Perfil::ADMINISTRADOR, Perfil::COORDINADOR]))
            ->where('id', '>', 1)->whereNotNull('session_token')->get();

        $this->facilitador1 = $facilitadores->first() ?? User::findOrFail(5);
        $this->facilitador2 = $facilitadores->skip(1)->first() ?? User::findOrFail(6);

        $idPersonaFac1 = $this->facilitador1->personalData->id_persona;

        // Crear curso en estado POR_ACEPTAR asignado a facilitador 1
        $this->curso = Curso::withoutEvents(function () use ($idPersonaFac1) {
            $c = new Curso;
            $c->nombre = 'Taller Dirección de Fotografía';
            $c->codigo = 'TDF-'.random_int(1000, 9999);
            $c->id_persona = $idPersonaFac1;
            $c->id_actividad_formativa = 1;
            $c->id_modalidad = 1;
            $c->es_nacional = true;
            $c->save();

            $c->agregarEstado(EstadoCurso::POR_ACEPTAR->value);

            return $c;
        });
    }

    /**
     * Al rechazar la asignación, se retira id_persona del curso, el estado pasa a RECHAZADO
     * y el facilitador pierde todo acceso de gestión y edición.
     */
    public function test_facilitador_al_rechazar_curso_pierde_asignacion_y_acceso_de_gestion(): void
    {
        $idCurso = $this->curso->id_curso;

        // 1. El facilitador asignado rechaza la asignación (estado 2) con un motivo
        $response = $this->actingAs($this->facilitador1)
            ->withSession([
                'profile_id' => Perfil::FACILITADOR,
                'session_token' => $this->facilitador1->session_token,
            ])
            ->putJson(route('taller.cursos.updateStatus', $this->curso->id_curso), [
                'id_estado' => EstadoCurso::RECHAZADO->value,
                'motivo' => 'No tengo disponibilidad horaria en las fechas propuestas.',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 2. Verificar que el curso pasó a RECHAZADO y se le retiró la asignación (id_persona es null)
        $cursoFresco = $this->curso->fresh();
        $this->assertEquals(EstadoCurso::RECHAZADO->value, $cursoFresco->estado_actual->id_estado);
        $this->assertNull($cursoFresco->id_persona);

        // 3. Verificar que se registró la observación del rechazo
        $this->assertDatabaseHas('taller.observaciones_curso', [
            'id_curso' => $idCurso,
            'creado_por' => $this->facilitador1->id,
        ]);

        // 4. El facilitador visita el detalle del curso: NO debe ver panel de gestión, ni editar ni ver contenidos
        $showResponse = $this->actingAs($this->facilitador1)
            ->withSession([
                'profile_id' => Perfil::FACILITADOR,
                'session_token' => $this->facilitador1->session_token,
            ])
            ->get(route('taller.cursos.show', $idCurso));

        $showResponse->assertStatus(200);
        $showResponse->assertDontSee('Editar Programa');
        $showResponse->assertDontSee('Editar Requisitos');
        $showResponse->assertDontSee('Enviar a Revisión');
        $showResponse->assertDontSee('Ver contenidos del programa');

        // 5. El facilitador intenta entrar directamente a la ruta de edición y recibe 403 Forbidden
        $editResponse = $this->actingAs($this->facilitador1)
            ->withSession([
                'profile_id' => Perfil::FACILITADOR,
                'session_token' => $this->facilitador1->session_token,
            ])
            ->get(route('taller.cursos.edit', $this->curso->crypt_id));

        $editResponse->assertStatus(403);
    }

    /**
     * El coordinador puede reasignar un curso rechazado a otro facilitador,
     * devolviendo el curso a POR_ACEPTAR para el nuevo facilitador.
     */
    public function test_coordinador_puede_reasignar_curso_rechazado_a_otro_facilitador(): void
    {
        // 1. Dejar el curso en estado RECHAZADO sin facilitador asignado
        $this->curso->update(['id_persona' => null]);
        $this->curso->agregarEstado(EstadoCurso::RECHAZADO->value);

        // 2. El coordinador ve el detalle del curso: ve la opción de reasignar
        $showCoordinador = $this->actingAs($this->coordinador)
            ->withSession([
                'profile_id' => Perfil::COORDINADOR,
                'session_token' => $this->coordinador->session_token,
            ])
            ->get(route('taller.cursos.show', $this->curso->id_curso));

        $showCoordinador->assertStatus(200);
        $showCoordinador->assertSee('Reasignar Facilitador');

        // 3. El coordinador reasigna al Facilitador 2
        $idPersonaFac2 = $this->facilitador2->personalData->id_persona;

        $reasignarResponse = $this->actingAs($this->coordinador)
            ->withSession([
                'profile_id' => Perfil::COORDINADOR,
                'session_token' => $this->coordinador->session_token,
            ])
            ->putJson(route('taller.cursos.reasignarFacilitador', $this->curso->id_curso), [
                'id_persona' => $idPersonaFac2,
            ]);

        $reasignarResponse->assertStatus(200);
        $reasignarResponse->assertJson(['success' => true]);

        // 4. El curso ahora está asignado al Facilitador 2 y su estado es POR_ACEPTAR
        $cursoFresco = $this->curso->fresh();
        $this->assertEquals($idPersonaFac2, $cursoFresco->id_persona);
        $this->assertEquals(EstadoCurso::POR_ACEPTAR->value, $cursoFresco->estado_actual->id_estado);

        // 5. El nuevo facilitador ve las opciones de Aceptar o Rechazar Asignación
        $showNuevoFacilitador = $this->actingAs($this->facilitador2)
            ->withSession([
                'profile_id' => Perfil::FACILITADOR,
                'session_token' => $this->facilitador2->session_token,
            ])
            ->get(route('taller.cursos.show', $this->curso->id_curso));

        $showNuevoFacilitador->assertStatus(200);
        $showNuevoFacilitador->assertSee('Aceptar Asignación');
        $showNuevoFacilitador->assertSee('Rechazar Asignación');
    }

    /**
     * El coordinador puede reasignar un curso usando el ID cifrado (crypt_id)
     * tal como viaja desde el modal en el frontend.
     */
    public function test_coordinador_puede_reasignar_curso_con_id_cifrado(): void
    {
        $this->curso->update(['id_persona' => null]);
        $this->curso->agregarEstado(EstadoCurso::DECLINADO->value);

        $idPersonaFac2 = $this->facilitador2->personalData->id_persona;

        $response = $this->actingAs($this->coordinador)
            ->withSession([
                'profile_id' => Perfil::COORDINADOR,
                'session_token' => $this->coordinador->session_token,
            ])
            ->putJson(route('taller.cursos.reasignarFacilitador', $this->curso->crypt_id), [
                'id_persona' => $idPersonaFac2,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals($idPersonaFac2, $this->curso->fresh()->id_persona);
    }

    /**
     * En caso de incumplimiento durante 'En Curso' (7) o 'Inscripción' (6),
     * el facilitador puede ser reasignado manteniendo el curso en su fase operativa.
     */
    public function test_coordinador_puede_reasignar_facilitador_por_incumplimiento_en_curso(): void
    {
        $this->curso->agregarEstado(EstadoCurso::EDICION->value);
        $this->curso->agregarEstado(EstadoCurso::APROBACION->value);
        $this->curso->agregarEstado(EstadoCurso::INSCRIPCION->value);
        $this->curso->agregarEstado(EstadoCurso::EN_CURSO->value);

        $idPersonaFac2 = $this->facilitador2->personalData->id_persona;

        $response = $this->actingAs($this->coordinador)
            ->withSession([
                'profile_id' => Perfil::COORDINADOR,
                'session_token' => $this->coordinador->session_token,
            ])
            ->putJson(route('taller.cursos.reasignarFacilitador', $this->curso->crypt_id), [
                'id_persona' => $idPersonaFac2,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $cursoFresco = $this->curso->fresh();
        $this->assertEquals($idPersonaFac2, $cursoFresco->id_persona);
        // Se mantiene en EN_CURSO sin interrumpir a los alumnos
        $this->assertEquals(EstadoCurso::EN_CURSO->value, $cursoFresco->estado_actual->id_estado);
    }
}
