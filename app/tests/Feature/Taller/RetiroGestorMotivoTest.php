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
 * RF-27: el retiro por gestor exige motivo y deja la inscripción no corregible.
 */
class RetiroGestorMotivoTest extends TestCase
{
    use DatabaseTransactions;

    private User $coordinador;

    private User $participante;

    private Curso $curso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::COORDINADOR);
        })->where('id', '>', 1)->first() ?? User::find(2);

        $this->participante = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->where('id', '>', 1)->first() ?? User::findOrFail(7);

        $facilitador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::FACILITADOR);
        })->where('id', '>', 1)->first() ?? User::findOrFail(5);

        $this->curso = Curso::withoutEvents(function () use ($facilitador) {
            $c = new Curso;
            $c->nombre = 'Curso Retiro Motivo';
            $c->codigo = 'LAB-RETIRO-'.random_int(1000, 9999);
            $c->id_persona = $facilitador->personalData->id_persona;
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

        DB::table('taller.curso_estado')->insert([
            'id_curso' => $this->curso->id_curso,
            'id_estado' => EstadoCurso::EN_CURSO->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_retiro_de_gestor_sin_motivo_devuelve_422_y_no_cambia_estado(): void
    {
        $inscripcion = Inscripcion::create([
            'id_curso' => $this->curso->id_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'user_id' => $this->participante->id,
            'estado' => Inscripcion::ESTADO_APROBADO,
            'fecha_inscripcion' => now(),
        ]);

        $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR])
            ->deleteJson(route('taller.inscripciones.destroy', $inscripcion->id_inscripcion))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['motivo']);

        $this->assertSame(Inscripcion::ESTADO_APROBADO, $inscripcion->fresh()->estado);
    }
}
