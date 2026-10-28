<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Asistencia;
use Modules\Taller\Entities\ContenidoCurso;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Tests\TestCase;

/**
 * Valida la cobertura de la Tanda 3 — Huecos de flujo:
 * - UX-12 (RF-33, RF-34): anular, restaurar y marcar manual de asistencias.
 * - UX-14 (RF-38, RF-39): notas no se sobrescriben con valores vacíos.
 * - UX-15 (RF-37, RF-44): helper unificado de umbrales de asistencia (75% / 50%).
 * - UX-16 (RF-59): acceso a resultados de encuesta de satisfacción.
 * - UX-18: unificación de cursos asignados al facilitador en mis-cursos.
 */
class HuecosFlujoTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private User $facilitador;

    private User $participante;

    private Curso $curso;

    private ContenidoCurso $contenido;

    private Inscripcion $inscripcion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::find(1);

        $this->facilitador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::FACILITADOR);
        })->where('id', '>', 1)->first() ?? User::findOrFail(5);

        $this->participante = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->where('id', '>', 1)->first() ?? User::findOrFail(7);

        $idPersonaFacilitador = optional($this->facilitador->personalData)->id_persona ?? 1;

        $this->curso = Curso::withoutEvents(function () use ($idPersonaFacilitador) {
            $c = new Curso;
            $c->nombre = 'Curso Tanda 3 Test';
            $c->codigo = 'LAB-TEST-T3-'.random_int(1000, 9999);
            $c->id_persona = $idPersonaFacilitador;
            $c->id_actividad_formativa = 1;
            $c->id_aspecto = 1;
            $c->id_modalidad = 1;
            $c->id_modalidad_especial = 1;
            $c->cantidad_cupos = 25;
            $c->fecha_inicio = now();
            $c->fecha_fin = now()->addMonths(1);
            $c->save();

            return $c;
        });

        // Estado En Curso (7)
        DB::table('taller.curso_estado')->insert([
            'id_curso' => $this->curso->id_curso,
            'id_estado' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->contenido = ContenidoCurso::create([
            'id_curso' => $this->curso->id_curso,
            'titulo' => 'Clase de Prueba T3',
            'descripcion' => 'Descripción detallada de la clase de prueba',
            'descripcion_breve' => 'Descripción breve',
            'fecha_contenido' => now(),
            'hora_inicio' => '09:00',
            'hora_fin' => '11:00',
            'es_evaluacion' => false,
            'creado_por' => 1,
            'orden' => 1,
        ]);

        $this->inscripcion = Inscripcion::create([
            'id_curso' => $this->curso->id_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'user_id' => $this->participante->id,
            'estado' => Inscripcion::ESTADO_APROBADO,
            'fecha_inscripcion' => now(),
        ]);
    }

    /**
     * UX-12 / RF-33: Marcar asistencia manual por facilitador.
     */
    public function test_facilitador_puede_marcar_asistencia_manual(): void
    {
        $idPersona = $this->participante->personalData->id_persona;

        $response = $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->post("/taller/cursos/{$this->curso->id_curso}/contenido/{$this->contenido->id_contenido_curso}/marcar-manual", [
                'id_persona' => $idPersona,
            ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('taller.asistencias', [
            'id_contenido_curso' => $this->contenido->id_contenido_curso,
            'id_persona' => $idPersona,
            'activa' => true,
            'metodo_marcado' => 'manual',
        ]);
    }

    /**
     * UX-12 / RF-34: Anular y restaurar asistencia con motivo.
     */
    public function test_facilitador_puede_anular_y_restaurar_asistencia(): void
    {
        $idPersona = $this->participante->personalData->id_persona;

        $asistencia = Asistencia::create([
            'id_contenido_curso' => $this->contenido->id_contenido_curso,
            'id_persona' => $idPersona,
            'id_inscripcion' => $this->inscripcion->id_inscripcion,
            'fecha_hora_marcado' => now(),
            'activa' => true,
            'metodo_marcado' => 'link',
        ]);

        // 1. Anular
        $respAnular = $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->post("/taller/cursos/{$this->curso->id_curso}/asistencia/{$asistencia->id_asistencia}/anular", [
                'motivo' => 'Marcado por equivocación',
            ]);

        $respAnular->assertSessionHas('success');
        $asistencia->refresh();
        $this->assertFalse($asistencia->activa);
        $this->assertEquals('Marcado por equivocación', $asistencia->motivo_anulacion);

        // 2. Restaurar
        $respRestaurar = $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->post("/taller/cursos/{$this->curso->id_curso}/asistencia/{$asistencia->id_asistencia}/restaurar");

        $respRestaurar->assertSessionHas('success');
        $asistencia->refresh();
        $this->assertTrue($asistencia->activa);
        $this->assertNull($asistencia->motivo_anulacion);
    }

    /**
     * UX-32: un gestor no puede anular/restaurar asistencias de otro curso (IDOR).
     */
    public function test_no_se_puede_anular_ni_restaurar_asistencia_de_otro_curso(): void
    {
        $cursoAjeno = Curso::withoutEvents(function () {
            $c = new Curso;
            $c->nombre = 'Curso Ajeno UX32';
            $c->codigo = 'LAB-TEST-UX32-'.random_int(1000, 9999);
            $c->id_persona = $this->curso->id_persona;
            $c->id_actividad_formativa = 1;
            $c->id_aspecto = 1;
            $c->id_modalidad = 1;
            $c->id_modalidad_especial = 1;
            $c->fecha_inicio = now();
            $c->fecha_fin = now()->addMonths(1);
            $c->save();

            return $c;
        });

        $contenidoAjeno = ContenidoCurso::create([
            'id_curso' => $cursoAjeno->id_curso,
            'titulo' => 'Clase curso ajeno',
            'descripcion' => 'Descripción detallada de la clase ajena',
            'descripcion_breve' => 'Descripción breve',
            'fecha_contenido' => now(),
            'hora_inicio' => '09:00',
            'hora_fin' => '11:00',
            'es_evaluacion' => false,
            'creado_por' => 1,
            'orden' => 1,
        ]);

        $asistenciaAjena = Asistencia::create([
            'id_contenido_curso' => $contenidoAjeno->id_contenido_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'id_inscripcion' => $this->inscripcion->id_inscripcion,
            'fecha_hora_marcado' => now(),
            'activa' => true,
            'metodo_marcado' => 'link',
        ]);

        $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->post("/taller/cursos/{$this->curso->id_curso}/asistencia/{$asistenciaAjena->id_asistencia}/anular", [
                'motivo' => 'Intento cruzado',
            ])
            ->assertNotFound();

        $this->assertTrue($asistenciaAjena->fresh()->activa);

        $asistenciaAjena->update(['activa' => false]);

        $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->post("/taller/cursos/{$this->curso->id_curso}/asistencia/{$asistenciaAjena->id_asistencia}/restaurar")
            ->assertNotFound();

        $this->assertFalse($asistenciaAjena->fresh()->activa);
    }

    /**
     * UX-33: no se pueden guardar notas de personas no inscritas/aprobadas ni de contenidos de otro curso.
     */
    public function test_calificacion_rechaza_persona_no_inscrita_y_contenido_de_otro_curso(): void
    {
        $evaluacion = ContenidoCurso::create([
            'id_curso' => $this->curso->id_curso,
            'titulo' => 'Evaluación UX33',
            'descripcion' => 'Descripción detallada de la evaluación',
            'descripcion_breve' => 'Breve evaluación',
            'fecha_contenido' => now(),
            'es_evaluacion' => true,
            'creado_por' => 1,
            'orden' => 3,
        ]);

        $idPersonaAjena = $this->admin->personalData->id_persona;

        $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->post("/taller/cursos/{$this->curso->id_curso}/contenido/{$evaluacion->id_contenido_curso}/calificar", [
                'calificaciones' => [$idPersonaAjena => ['nota' => '90', 'observacion' => '']],
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('taller.calificaciones', [
            'id_contenido_curso' => $evaluacion->id_contenido_curso,
            'id_persona' => $idPersonaAjena,
        ]);

        // Contenido que pertenece a otro curso: 404 al usar el id de nuestro curso.
        $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->post("/taller/cursos/{$this->curso->id_curso}/contenido/999999/calificar", [
                'calificaciones' => [$this->participante->personalData->id_persona => ['nota' => '90']],
            ])
            ->assertNotFound();
    }

    /**
     * UX-14 / RF-38 / RF-39: Guardar calificaciones no borra notas existentes con entradas vacías.
     */
    public function test_calificacion_no_sobrescribe_con_valores_vacios(): void
    {
        $evaluacion = ContenidoCurso::create([
            'id_curso' => $this->curso->id_curso,
            'titulo' => 'Evaluación Final T3',
            'descripcion' => 'Descripción detallada de la evaluación',
            'descripcion_breve' => 'Breve evaluación final',
            'fecha_contenido' => now(),
            'es_evaluacion' => true,
            'creado_por' => 1,
            'orden' => 2,
        ]);

        $idPersona = $this->participante->personalData->id_persona;

        // Nota inicial
        DB::table('taller.calificaciones')->insert([
            'id_curso' => $this->curso->id_curso,
            'id_contenido_curso' => $evaluacion->id_contenido_curso,
            'id_persona' => $idPersona,
            'calificacion' => 88.50,
            'observacion' => 'Excelente proyecto',
            'calificado_por' => $this->facilitador->id,
            'actualizado_en' => now(),
        ]);

        // Enviar campo vacío (simulando envío del formulario sin tocar esta fila)
        $response = $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->post("/taller/cursos/{$this->curso->id_curso}/contenido/{$evaluacion->id_contenido_curso}/calificar", [
                'calificaciones' => [
                    $idPersona => [
                        'nota' => '',
                        'observacion' => '',
                    ],
                ],
            ]);

        $response->assertSessionHas('success');

        // La calificación previa debe preservarse intacta
        $calif = DB::table('taller.calificaciones')
            ->where('id_contenido_curso', $evaluacion->id_contenido_curso)
            ->where('id_persona', $idPersona)
            ->first();

        $this->assertNotNull($calif);
        $this->assertEquals(88.50, (float) $calif->calificacion);
        $this->assertEquals('Excelente proyecto', $calif->observacion);
    }

    /**
     * UX-15: Helper unificado de umbrales de asistencia (75% / 50%).
     */
    public function test_helpers_umbral_asistencia_unificado(): void
    {
        // D3 (26/10): se certifica con el 100 %, así que solo el 100 % es verde.
        $this->assertEquals('text-success', asistenciaColorTexto(100));
        $this->assertEquals('text-warning', asistenciaColorTexto(99.9));
        $this->assertEquals('text-warning', asistenciaColorTexto(75));
        $this->assertEquals('text-danger', asistenciaColorTexto(74.9));
        $this->assertEquals('text-danger', asistenciaColorTexto(0));

        $this->assertEquals('bg-success', asistenciaColorBg(100));
        $this->assertEquals('bg-warning', asistenciaColorBg(80));
        $this->assertEquals('bg-danger', asistenciaColorBg(30));
    }

    /**
     * UX-18 / D1: taller.mis-cursos-asignados redirige a taller.mis-cursos.
     */
    public function test_mis_cursos_asignados_redirige_a_mis_cursos(): void
    {
        $response = $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->get('/taller/Cursos-asignados');

        $response->assertRedirect(route('taller.mis-cursos'));
    }

    /**
     * UX-18 / D1: Facilitador ve sus cursos asignados en mis-cursos.
     */
    public function test_facilitador_ve_cursos_asignados_en_mis_cursos(): void
    {
        $response = $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->get('/taller/mis-cursos');

        $response->assertStatus(200);
        $response->assertSee($this->curso->nombre);
        $response->assertSee('Mis Cursos Asignados');
    }

    /**
     * UX-16 / RF-59: Facilitador asignado puede acceder a resultados de satisfacción.
     */
    public function test_facilitador_puede_ver_resultados_de_satisfaccion(): void
    {
        // Pasar curso a estado 8 (FINALIZADO)
        DB::table('taller.curso_estado')->insert([
            'id_curso' => $this->curso->id_curso,
            'id_estado' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->get("/taller/cursos/{$this->curso->id_curso}/satisfaccion/resultados");

        $response->assertStatus(200);
        $response->assertSee('Resultados de la encuesta de satisfacción');
    }
}
