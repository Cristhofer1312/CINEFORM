<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Tests\TestCase;

/**
 * D6 (26/10): denegar y retirar son definitivos para el participante, pero
 * quien gestiona puede revertirlos. La denegación vuelve a revisión
 * (postulado); el retiro vuelve a inscrito (aprobado) si hay cupo.
 */
class RevertirInscripcionTest extends TestCase
{
    use DatabaseTransactions;

    private User $facilitador;

    private User $coordinador;

    private User $participante;

    private Curso $curso;

    protected function setUp(): void
    {
        parent::setUp();

        // Los permisos suman perfiles: actores distintos y cada uno solo con el suyo.
        $this->facilitador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::FACILITADOR);
        })->whereDoesntHave('perfiles', fn ($q) => $q->whereIn('security.profiles.id', [Perfil::ADMINISTRADOR, Perfil::COORDINADOR]))
            ->where('id', '>', 1)->first() ?? User::findOrFail(5);

        $this->coordinador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::COORDINADOR);
        })->where('id', '>', 1)->first() ?? User::find(2);
        $this->soloConPerfiles($this->coordinador, Perfil::COORDINADOR);

        $this->participante = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->whereDoesntHave('perfiles', fn ($q) => $q->where('security.profiles.id', '!=', Perfil::PARTICIPANTE))
            ->where('id', '>', 1)->first() ?? User::findOrFail(7);

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
            $c->fecha_inicio = now();
            $c->fecha_fin = now()->addMonth();
            $c->save();

            return $c;
        });

        $this->ponerEstado(EstadoCurso::INSCRIPCION, 0);
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

    private function inscripcion(string $estado): Inscripcion
    {
        return Inscripcion::create([
            'id_curso' => $this->curso->id_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'user_id' => $this->participante->id,
            'estado' => $estado,
            'motivo_estado' => in_array($estado, [Inscripcion::ESTADO_DENEGADO, Inscripcion::ESTADO_RETIRADO], true)
                ? 'Documento de identidad ilegible'
                : null,
            'rechazada_por' => $this->coordinador->id,
            'fecha_inscripcion' => now(),
        ]);
    }

    private function como(User $user, int $perfil): self
    {
        return $this->actingAs($user)->withSession([
            'profile_id' => $perfil,
            'session_token' => $user->session_token,
        ]);
    }

    private function revertir(User $user, int $perfil, Inscripcion $inscripcion, array $datos = ['motivo' => 'Se verificó el documento original'])
    {
        return $this->como($user, $perfil)
            ->postJson(route('taller.inscripciones.revertir', $inscripcion->id_inscripcion), $datos);
    }

    public function test_revertir_una_denegacion_la_devuelve_a_revision_y_queda_auditada(): void
    {
        $inscripcion = $this->inscripcion(Inscripcion::ESTADO_DENEGADO);

        $this->revertir($this->facilitador, Perfil::FACILITADOR, $inscripcion)
            ->assertOk()
            ->assertJsonPath('message', 'Rechazo definitivo revertido: la postulación vuelve a revisión.');

        $fresca = $inscripcion->fresh();
        $this->assertSame(Inscripcion::ESTADO_POSTULADO, $fresca->estado);
        $this->assertNull($fresca->motivo_estado);
        $this->assertNull($fresca->rechazada_por);

        $auditoria = DB::table('security.auditoria')
            ->where('accion', 'inscripcion.revertir')
            ->where('entidad_id', (string) $inscripcion->id_inscripcion)
            ->first();
        $this->assertNotNull($auditoria);
        $this->assertSame('Se verificó el documento original', $auditoria->motivo);
        $this->assertSame(Inscripcion::ESTADO_DENEGADO, json_decode($auditoria->antes, true)['estado']);
    }

    public function test_el_retiro_del_gestor_queda_retirado_y_se_revierte_a_inscrito(): void
    {
        $inscripcion = $this->inscripcion(Inscripcion::ESTADO_APROBADO);

        $this->como($this->coordinador, Perfil::COORDINADOR)
            ->deleteJson(route('taller.inscripciones.destroy', $inscripcion->id_inscripcion), [
                'motivo' => 'Inasistencias reiteradas',
            ])
            ->assertOk();
        $this->assertSame(Inscripcion::ESTADO_RETIRADO, $inscripcion->fresh()->estado);

        $this->revertir($this->coordinador, Perfil::COORDINADOR, $inscripcion)
            ->assertOk()
            ->assertJsonPath('message', 'Retiro revertido: el participante vuelve a estar inscrito.');
        $this->assertSame(Inscripcion::ESTADO_APROBADO, $inscripcion->fresh()->estado);
    }

    public function test_el_facilitador_no_revierte_un_retiro(): void
    {
        $inscripcion = $this->inscripcion(Inscripcion::ESTADO_RETIRADO);

        $this->revertir($this->facilitador, Perfil::FACILITADOR, $inscripcion)->assertForbidden();
        $this->assertSame(Inscripcion::ESTADO_RETIRADO, $inscripcion->fresh()->estado);
    }

    public function test_el_participante_no_revierte_su_denegacion(): void
    {
        $inscripcion = $this->inscripcion(Inscripcion::ESTADO_DENEGADO);

        $this->revertir($this->participante, Perfil::PARTICIPANTE, $inscripcion)->assertForbidden();
        $this->assertSame(Inscripcion::ESTADO_DENEGADO, $inscripcion->fresh()->estado);
    }

    public function test_sin_cupo_no_se_revierte_un_retiro(): void
    {
        $this->curso->update(['cantidad_cupos' => 0]);
        $inscripcion = $this->inscripcion(Inscripcion::ESTADO_RETIRADO);

        $this->revertir($this->coordinador, Perfil::COORDINADOR, $inscripcion)
            ->assertStatus(409)
            ->assertJsonPath('message', 'No hay cupos disponibles para reincorporar al participante.');
        $this->assertSame(Inscripcion::ESTADO_RETIRADO, $inscripcion->fresh()->estado);
    }

    public function test_revertir_exige_motivo(): void
    {
        $inscripcion = $this->inscripcion(Inscripcion::ESTADO_DENEGADO);

        $this->revertir($this->coordinador, Perfil::COORDINADOR, $inscripcion, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('motivo');
        $this->assertSame(Inscripcion::ESTADO_DENEGADO, $inscripcion->fresh()->estado);
    }

    public function test_solo_se_revierten_denegaciones_y_retiros(): void
    {
        $inscripcion = $this->inscripcion(Inscripcion::ESTADO_APROBADO);

        $this->revertir($this->coordinador, Perfil::COORDINADOR, $inscripcion)
            ->assertStatus(400)
            ->assertJsonPath('message', 'Solo se puede revertir una postulación denegada o una inscripción retirada.');
    }

    public function test_con_el_curso_finalizado_no_se_revierte(): void
    {
        $this->ponerEstado(EstadoCurso::EN_CURSO, 1);
        $this->ponerEstado(EstadoCurso::FINALIZADO, 2);
        $inscripcion = $this->inscripcion(Inscripcion::ESTADO_RETIRADO);

        $this->revertir($this->coordinador, Perfil::COORDINADOR, $inscripcion)
            ->assertStatus(400)
            ->assertJsonPath('message', 'Solo se puede revertir mientras el curso está en Inscripción o En Curso.');
    }

    public function test_rechazar_para_correccion_no_reabre_una_denegacion_ni_un_retiro(): void
    {
        foreach ([Inscripcion::ESTADO_DENEGADO, Inscripcion::ESTADO_RETIRADO] as $estado) {
            Inscripcion::where('id_curso', $this->curso->id_curso)->delete();
            $inscripcion = $this->inscripcion($estado);

            $this->como($this->coordinador, Perfil::COORDINADOR)
                ->postJson(route('taller.inscripciones.rechazar', $inscripcion->id_inscripcion), [
                    'motivo' => 'Intento de reabrir',
                ])
                ->assertStatus(400)
                ->assertJsonPath('message', 'La postulación está denegada o retirada; use la acción de revertir.');

            $this->assertSame($estado, $inscripcion->fresh()->estado);
        }
    }

    public function test_el_participante_retirado_no_puede_volver_a_postularse(): void
    {
        $this->inscripcion(Inscripcion::ESTADO_RETIRADO);

        $this->como($this->participante, Perfil::PARTICIPANTE)
            ->get(route('taller.inscripciones.create', $this->curso->id_curso))
            ->assertRedirect()
            ->assertSessionHas('error', 'Fuiste retirado de este curso por la coordinación.');

        $this->como($this->participante, Perfil::PARTICIPANTE)
            ->post(route('taller.inscripciones.procesar', $this->curso->id_curso))
            ->assertSessionHas('error', 'Ya cuentas con una postulación activa en este programa.');
    }

    public function test_la_lista_de_participantes_muestra_retirados_con_boton_de_revertir(): void
    {
        $this->inscripcion(Inscripcion::ESTADO_RETIRADO);

        $this->como($this->coordinador, Perfil::COORDINADOR)
            ->get(route('taller.cursos.participantes', $this->curso->id_curso))
            ->assertOk()
            ->assertViewHas('retirados', fn ($retirados) => $retirados->count() === 1)
            ->assertSee('Retirados')
            ->assertSee('¿Revertir el retiro?');
    }
}
