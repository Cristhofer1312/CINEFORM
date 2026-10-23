<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Asistencia;
use Modules\Taller\Entities\AsistenciaToken;
use Modules\Taller\Entities\ContenidoCurso;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Tests\TestCase;

/**
 * Tier 1 de integridad de flujos: RF-24, RF-27, RF-31, RF-33, RF-34, RF-39 y RF-16.
 */
class IntegridadFlujosTest extends TestCase
{
    use DatabaseTransactions;

    private User $facilitador;

    private User $coordinador;

    private User $participante;

    private Curso $curso;

    private ContenidoCurso $actividad;

    private ContenidoCurso $evaluacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->facilitador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::FACILITADOR);
        })->where('id', '>', 1)->first() ?? User::findOrFail(5);

        $this->coordinador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::COORDINADOR);
        })->where('id', '>', 1)->first() ?? User::find(2);

        $this->participante = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->where('id', '>', 1)->first() ?? User::findOrFail(7);

        $this->curso = Curso::withoutEvents(function () {
            $c = new Curso;
            $c->nombre = 'Curso Integridad Tier 1';
            $c->codigo = 'LAB-TIER1-'.random_int(1000, 9999);
            $c->id_persona = $this->facilitador->personalData->id_persona;
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
            'id_estado' => EstadoCurso::EN_CURSO->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actividad = $this->crearContenido('Clase Tier 1', false);
        $this->evaluacion = $this->crearContenido('Evaluación Tier 1', true);
    }

    public function test_rechazar_no_puede_revertir_inscripcion_aprobada(): void
    {
        $inscripcion = $this->crearInscripcion(Inscripcion::ESTADO_APROBADO);

        $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->postJson(route('taller.inscripciones.rechazar', $inscripcion->id_inscripcion), [
                'motivo' => 'Intento de revertir',
            ])
            ->assertStatus(400);

        $this->assertSame(Inscripcion::ESTADO_APROBADO, $inscripcion->fresh()->estado);
    }

    public function test_retiro_de_gestor_queda_denegado_y_no_recuperable(): void
    {
        $inscripcion = $this->crearInscripcion(Inscripcion::ESTADO_APROBADO);

        $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR])
            ->deleteJson(route('taller.inscripciones.destroy', $inscripcion->id_inscripcion), [
                'motivo' => 'Retiro por incumplimiento',
            ])
            ->assertOk();

        $retirada = $inscripcion->fresh();
        $this->assertSame(Inscripcion::ESTADO_DENEGADO, $retirada->estado);
        $this->assertFalse($retirada->esRecuperable());
    }

    public function test_marcado_manual_rechaza_participante_no_aprobado(): void
    {
        $this->crearInscripcion(Inscripcion::ESTADO_POSTULADO);

        $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->post(route('taller.asistencia.marcar-manual', [
                'curso' => $this->curso->id_curso,
                'contenido' => $this->actividad->id_contenido_curso,
            ]), [
                'id_persona' => $this->participante->personalData->id_persona,
            ])
            ->assertSessionHas('error', 'El participante no está formalmente inscrito y admitido en este curso.');

        $this->assertDatabaseMissing('taller.asistencias', [
            'id_contenido_curso' => $this->actividad->id_contenido_curso,
            'id_persona' => $this->participante->personalData->id_persona,
        ]);
    }

    public function test_qr_no_se_genera_para_token_expirado(): void
    {
        $token = AsistenciaToken::create([
            'id_contenido_curso' => $this->actividad->id_contenido_curso,
            'token' => Str::random(32),
            'activo' => true,
            'fecha_expiracion' => now()->subMinute(),
            'creado_por' => $this->facilitador->id,
        ]);

        $this->get(route('taller.asistencia.qr-image', $token->token))
            ->assertNotFound();
    }

    public function test_no_se_puede_anular_asistencia_sin_motivo(): void
    {
        $asistencia = Asistencia::create([
            'id_contenido_curso' => $this->actividad->id_contenido_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'id_inscripcion' => $this->crearInscripcion(Inscripcion::ESTADO_APROBADO)->id_inscripcion,
            'fecha_hora_marcado' => now(),
            'activa' => true,
            'metodo_marcado' => 'manual',
            'ip_marcado' => '127.0.0.1',
        ]);

        $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->postJson(route('taller.asistencia.anular', [
                'curso' => $this->curso->id_curso,
                'asistencia' => $asistencia->id_asistencia,
            ]), [])
            ->assertStatus(422);

        $this->assertTrue((bool) $asistencia->fresh()->activa);
    }

    public function test_no_se_modifican_calificaciones_con_curso_finalizado(): void
    {
        $this->crearInscripcion(Inscripcion::ESTADO_APROBADO);

        DB::table('taller.curso_estado')->insert([
            'id_curso' => $this->curso->id_curso,
            'id_estado' => EstadoCurso::FINALIZADO->value,
            'created_at' => now()->addMinute(),
            'updated_at' => now()->addMinute(),
        ]);

        $this->actingAs($this->facilitador)
            ->withSession(['profile_id' => Perfil::FACILITADOR])
            ->postJson(route('taller.calificaciones.store', [
                'curso' => $this->curso->id_curso,
                'contenido' => $this->evaluacion->id_contenido_curso,
            ]), [
                'calificaciones' => [
                    $this->participante->personalData->id_persona => ['nota' => 50],
                ],
            ])
            ->assertStatus(403);
    }

    public function test_no_se_reasigna_a_persona_no_elegible_como_facilitador(): void
    {
        $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR])
            ->putJson(route('taller.cursos.reasignarFacilitador', $this->curso->id_curso), [
                'id_persona' => $this->participante->personalData->id_persona,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'La persona seleccionada no es elegible como facilitador.');
    }

    private function crearContenido(string $titulo, bool $esEvaluacion): ContenidoCurso
    {
        return ContenidoCurso::create([
            'id_curso' => $this->curso->id_curso,
            'titulo' => $titulo,
            'descripcion' => 'Descripción de prueba',
            'descripcion_breve' => 'Breve',
            'fecha_contenido' => now(),
            'hora_inicio' => '09:00',
            'hora_fin' => '11:00',
            'es_evaluacion' => $esEvaluacion,
            'creado_por' => 1,
            'orden' => $esEvaluacion ? 2 : 1,
        ]);
    }

    private function crearInscripcion(string $estado): Inscripcion
    {
        return Inscripcion::create([
            'id_curso' => $this->curso->id_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'user_id' => $this->participante->id,
            'estado' => $estado,
            'fecha_inscripcion' => now(),
        ]);
    }
}
