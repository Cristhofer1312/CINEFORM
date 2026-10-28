<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Asistencia;
use Modules\Taller\Entities\AsistenciaJustificacion;
use Modules\Taller\Entities\ContenidoCurso;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Tests\TestCase;

/**
 * D3 (26/10): para certificar se exige el 100 % de asistencia a las clases
 * (no a las evaluaciones). El facilitador justifica cada falta con un motivo y
 * la falta justificada cuenta como cumplida.
 */
class CumplimientoAsistenciaTest extends TestCase
{
    use DatabaseTransactions;

    private User $facilitador;

    private User $participante;

    private Curso $curso;

    private ContenidoCurso $claseAsistida;

    private ContenidoCurso $claseFaltada;

    private Inscripcion $inscripcion;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->facilitador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::FACILITADOR);
        })->where('id', '>', 1)->first() ?? User::findOrFail(5);

        $this->participante = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->where('id', '>', 1)->first() ?? User::findOrFail(7);

        $this->curso = Curso::withoutEvents(function () {
            $c = new Curso;
            $c->nombre = 'Taller de Sonido Directo';
            $c->codigo = 'TSD-'.random_int(1000, 9999);
            $c->id_persona = $this->facilitador->personalData->id_persona;
            $c->id_actividad_formativa = 1;
            $c->id_aspecto = 1;
            $c->id_modalidad = 1;
            $c->id_modalidad_especial = 1;
            $c->cantidad_cupos = 25;
            $c->fecha_inicio = now()->subMonth();
            $c->fecha_fin = now();
            $c->save();

            return $c;
        });

        $this->ponerEstado(EstadoCurso::FINALIZADO, 0);

        $this->claseAsistida = $this->contenido('Clase 1: microfonía', false, 1);
        $this->claseFaltada = $this->contenido('Clase 2: grabación en exteriores', false, 2);
        // La evaluación no cuenta para la asistencia: nadie la "asiste".
        $this->contenido('Evaluación final', true, 3);

        $this->inscripcion = Inscripcion::create([
            'id_curso' => $this->curso->id_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'user_id' => $this->participante->id,
            'estado' => Inscripcion::ESTADO_APROBADO,
            'fecha_inscripcion' => now()->subMonth(),
        ]);

        Asistencia::create([
            'id_contenido_curso' => $this->claseAsistida->id_contenido_curso,
            'id_persona' => $this->inscripcion->id_persona,
            'id_inscripcion' => $this->inscripcion->id_inscripcion,
            'fecha_hora_marcado' => now(),
            'activa' => true,
            'metodo_marcado' => 'manual',
        ]);
    }

    private function contenido(string $titulo, bool $esEvaluacion, int $orden): ContenidoCurso
    {
        return ContenidoCurso::create([
            'id_curso' => $this->curso->id_curso,
            'titulo' => $titulo,
            'descripcion' => 'Descripción de prueba',
            'descripcion_breve' => 'Breve',
            'fecha_contenido' => now()->subDays(10 - $orden),
            'hora_inicio' => '09:00',
            'hora_fin' => '11:00',
            'es_evaluacion' => $esEvaluacion,
            'creado_por' => 1,
            'orden' => $orden,
        ]);
    }

    private function ponerEstado(EstadoCurso $estado, int $minutos): void
    {
        DB::table('taller.curso_estado')->insert([
            'id_curso' => $this->curso->id_curso,
            'id_estado' => $estado->value,
            'created_at' => now()->addMinutes($minutos),
            'updated_at' => now()->addMinutes($minutos),
        ]);
    }

    private function como(User $user, int $perfil): self
    {
        return $this->actingAs($user)->withSession([
            'profile_id' => $perfil,
            'session_token' => $user->session_token,
        ]);
    }

    private function rutaFalta(string $nombre, ?ContenidoCurso $clase = null): string
    {
        return route($nombre, [
            'curso' => $this->curso->id_curso,
            'inscripcion' => $this->inscripcion->id_inscripcion,
            'contenido' => ($clase ?? $this->claseFaltada)->id_contenido_curso,
        ]);
    }

    private function justificar(array $datos = ['motivo' => 'Reposo médico presentado'], ?ContenidoCurso $clase = null)
    {
        return $this->como($this->facilitador, Perfil::FACILITADOR)
            ->postJson($this->rutaFalta('taller.asistencia.justificar', $clase), $datos);
    }

    private function aprobar()
    {
        return $this->como($this->facilitador, Perfil::FACILITADOR)
            ->post(route('taller.certificacion.aprobar', [
                'curso' => $this->curso->id_curso,
                'inscripcion' => $this->inscripcion->id_inscripcion,
            ]));
    }

    public function test_no_se_aprueba_la_certificacion_con_una_falta_sin_justificar(): void
    {
        $this->aprobar()
            ->assertRedirect()
            ->assertSessionHas('error', 'No se puede aprobar la certificación de '.$this->inscripcion->persona->nombre_completo
                .': tiene 1 falta sin justificar. Se exige el 100 % de asistencia; justifique cada falta para aprobar.');

        $this->assertNull($this->inscripcion->fresh()->certificado_aprobado);
    }

    public function test_con_la_falta_justificada_se_aprueba_y_queda_auditada(): void
    {
        $this->justificar()->assertOk()->assertJsonPath('message', 'Falta justificada.');

        $justificacion = AsistenciaJustificacion::where('id_inscripcion', $this->inscripcion->id_inscripcion)->sole();
        $this->assertSame($this->claseFaltada->id_contenido_curso, (int) $justificacion->id_contenido_curso);
        $this->assertSame('Reposo médico presentado', $justificacion->motivo);
        $this->assertSame($this->facilitador->id, (int) $justificacion->justificada_por);

        $this->assertTrue(DB::table('security.auditoria')
            ->where('accion', 'asistencia.justificar')
            ->where('entidad_id', (string) $justificacion->id_justificacion)
            ->where('motivo', 'Reposo médico presentado')
            ->exists());

        $this->aprobar()->assertRedirect()->assertSessionHas('success');
        $this->assertTrue($this->inscripcion->fresh()->certificadoAprobado());
    }

    public function test_justificar_exige_motivo(): void
    {
        $this->justificar(['motivo' => ''])->assertStatus(422)->assertJsonValidationErrors('motivo');
        $this->assertSame(0, AsistenciaJustificacion::where('id_inscripcion', $this->inscripcion->id_inscripcion)->count());
    }

    public function test_no_se_justifica_una_clase_a_la_que_asistio(): void
    {
        $this->justificar(clase: $this->claseAsistida)
            ->assertStatus(422)
            ->assertJsonPath('message', 'El participante asistió a esta clase: no hay falta que justificar.');
    }

    public function test_quitar_la_justificacion_vuelve_a_bloquear_la_aprobacion(): void
    {
        $this->justificar()->assertOk();

        $this->como($this->facilitador, Perfil::FACILITADOR)
            ->postJson($this->rutaFalta('taller.asistencia.quitar-justificacion'))
            ->assertOk()
            ->assertJsonPath('message', 'Justificación eliminada.');

        $this->assertSame(0, AsistenciaJustificacion::where('id_inscripcion', $this->inscripcion->id_inscripcion)->count());
        $this->assertTrue(DB::table('security.auditoria')->where('accion', 'asistencia.quitar_justificacion')->exists());

        $this->aprobar()->assertSessionHas('error');
        $this->assertNull($this->inscripcion->fresh()->certificado_aprobado);
    }

    public function test_no_se_quita_la_justificacion_con_la_certificacion_aprobada(): void
    {
        $this->justificar()->assertOk();
        $this->aprobar()->assertSessionHas('success');

        $this->como($this->facilitador, Perfil::FACILITADOR)
            ->postJson($this->rutaFalta('taller.asistencia.quitar-justificacion'))
            ->assertStatus(422)
            ->assertJsonPath('message', 'La certificación ya está aprobada: deniéguela antes de quitar la justificación.');

        $this->assertSame(1, AsistenciaJustificacion::where('id_inscripcion', $this->inscripcion->id_inscripcion)->count());
    }

    public function test_con_el_curso_cerrado_no_se_justifica(): void
    {
        $this->ponerEstado(EstadoCurso::CERRADO, 5);

        $this->justificar()->assertForbidden();
        $this->assertSame(0, AsistenciaJustificacion::where('id_inscripcion', $this->inscripcion->id_inscripcion)->count());
    }

    public function test_el_participante_no_puede_justificar_sus_faltas(): void
    {
        $this->como($this->participante, Perfil::PARTICIPANTE)
            ->postJson($this->rutaFalta('taller.asistencia.justificar'), ['motivo' => 'Me enfermé'])
            ->assertForbidden();

        $this->assertSame(0, AsistenciaJustificacion::where('id_inscripcion', $this->inscripcion->id_inscripcion)->count());
    }

    public function test_el_panel_muestra_la_falta_y_el_boton_justificar(): void
    {
        $this->como($this->facilitador, Perfil::FACILITADOR)
            ->get(route('taller.certificacion.panel', $this->curso->id_curso))
            ->assertOk()
            ->assertSee('1 falta sin justificar')
            ->assertSee('Justificar')
            ->assertSee('Aprobar (requiere justificar las faltas)', false);

        $this->justificar()->assertOk();

        $this->como($this->facilitador, Perfil::FACILITADOR)
            ->get(route('taller.certificacion.panel', $this->curso->id_curso))
            ->assertOk()
            ->assertDontSee('falta sin justificar')
            ->assertSee('Justificada')
            ->assertSee('100%');
    }
}
