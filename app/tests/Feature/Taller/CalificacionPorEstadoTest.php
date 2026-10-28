<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\ContenidoCurso;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Tests\TestCase;

/**
 * E5 (RF-39): en Finalizado se califica y se corrigen notas; en Cerrado la
 * planilla queda de solo lectura y el guardado se rechaza.
 */
class CalificacionPorEstadoTest extends TestCase
{
    use DatabaseTransactions;

    private User $facilitador;

    private User $participante;

    private Curso $curso;

    private ContenidoCurso $evaluacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->facilitador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::FACILITADOR);
        })->where('id', '>', 1)->first() ?? User::findOrFail(5);

        $this->participante = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->where('id', '>', 1)->first() ?? User::findOrFail(7);

        $this->curso = Curso::withoutEvents(function () {
            $c = new Curso;
            $c->nombre = 'Curso Calificación por Estado';
            $c->codigo = 'CAL-EST-'.random_int(1000, 9999);
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

        $this->evaluacion = ContenidoCurso::create([
            'id_curso' => $this->curso->id_curso,
            'titulo' => 'Evaluación final',
            'descripcion' => 'Descripción de prueba',
            'descripcion_breve' => 'Breve',
            'fecha_contenido' => now(),
            'hora_inicio' => '09:00',
            'hora_fin' => '11:00',
            'es_evaluacion' => true,
            'creado_por' => 1,
            'orden' => 1,
        ]);

        Inscripcion::create([
            'id_curso' => $this->curso->id_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'user_id' => $this->participante->id,
            'estado' => Inscripcion::ESTADO_APROBADO,
            'fecha_inscripcion' => now()->subMonth(),
        ]);
    }

    private int $minutos = 0;

    // Cada estado se registra un minuto después del anterior para que el
    // estado actual no dependa del desempate de marcas de tiempo iguales.
    private function ponerEstado(EstadoCurso $estado): void
    {
        $momento = now()->addMinutes(++$this->minutos);

        DB::table('taller.curso_estado')->insert([
            'id_curso' => $this->curso->id_curso,
            'id_estado' => $estado->value,
            'created_at' => $momento,
            'updated_at' => $momento,
        ]);
    }

    private function guardarNota(float $nota)
    {
        return $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->post(route('taller.calificaciones.store', [
                'curso' => $this->curso->id_curso,
                'contenido' => $this->evaluacion->id_contenido_curso,
            ]), [
                'calificaciones' => [
                    $this->participante->personalData->id_persona => ['nota' => $nota],
                ],
            ]);
    }

    private function notaGuardada(): ?float
    {
        $nota = DB::table('taller.calificaciones')
            ->where('id_contenido_curso', $this->evaluacion->id_contenido_curso)
            ->where('id_persona', $this->participante->personalData->id_persona)
            ->value('calificacion');

        return $nota === null ? null : (float) $nota;
    }

    public function test_en_finalizado_se_califica_y_se_corrige_la_nota(): void
    {
        $this->ponerEstado(EstadoCurso::FINALIZADO);

        $this->guardarNota(70)->assertSessionHas('success', 'Calificaciones guardadas correctamente.');
        $this->assertSame(70.0, $this->notaGuardada());

        $this->guardarNota(85)->assertSessionHas('success', 'Calificaciones guardadas correctamente.');
        $this->assertSame(85.0, $this->notaGuardada());
    }

    public function test_en_finalizado_la_planilla_es_editable(): void
    {
        $this->ponerEstado(EstadoCurso::FINALIZADO);

        $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->get(route('taller.calificaciones.index', [
                'curso' => $this->curso->id_curso,
                'contenido' => $this->evaluacion->id_contenido_curso,
            ]))
            ->assertOk()
            ->assertViewHas('soloLectura', false)
            ->assertSee('Guardar Calificaciones');
    }

    public function test_en_cerrado_no_se_modifica_la_nota(): void
    {
        $this->ponerEstado(EstadoCurso::FINALIZADO);
        $this->guardarNota(70);
        $this->ponerEstado(EstadoCurso::CERRADO);

        $this->guardarNota(95)->assertStatus(403)
            ->assertSee('No se pueden modificar calificaciones: el curso ya está Cerrado.');
        $this->assertSame(70.0, $this->notaGuardada());
    }

    public function test_en_cerrado_la_planilla_es_de_solo_lectura(): void
    {
        $this->ponerEstado(EstadoCurso::CERRADO);

        $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->get(route('taller.calificaciones.index', [
                'curso' => $this->curso->id_curso,
                'contenido' => $this->evaluacion->id_contenido_curso,
            ]))
            ->assertOk()
            ->assertViewHas('soloLectura', true)
            ->assertSee('El curso está Cerrado: las calificaciones se pueden consultar, pero ya no se pueden modificar.')
            ->assertDontSee('Guardar Calificaciones');
    }

    public function test_el_boton_de_la_evaluacion_cambia_segun_el_estado(): void
    {
        $verContenido = fn () => $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->get(route('taller.cursos.contenido', [
                'curso' => $this->curso->id_curso,
                'contenido_id' => $this->evaluacion->id_contenido_curso,
            ]));

        $this->ponerEstado(EstadoCurso::FINALIZADO);
        $verContenido()->assertOk()->assertSee('Calificar')->assertDontSee('Ver calificaciones de');

        $this->ponerEstado(EstadoCurso::CERRADO);
        $verContenido()->assertOk()->assertSee('Ver calificaciones de');
    }
}
