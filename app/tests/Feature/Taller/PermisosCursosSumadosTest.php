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
 * Permisos de cursos con perfiles sumados: el alcance depende de los permisos y de
 * la relación con el curso (facilitador asignado), no del perfil elegido.
 */
class PermisosCursosSumadosTest extends TestCase
{
    use DatabaseTransactions;

    private User $facilitador;

    private User $otroFacilitador;

    private User $coordinador;

    private Curso $cursoPropio;

    private Curso $cursoAjeno;

    private Curso $cursoPublicoAjeno;

    protected function setUp(): void
    {
        parent::setUp();

        $this->facilitador = $this->usuarioCon(Perfil::FACILITADOR);
        $this->otroFacilitador = $this->usuarioCon(Perfil::FACILITADOR, $this->facilitador->id);
        $this->coordinador = $this->usuarioCon(Perfil::COORDINADOR, $this->facilitador->id);

        // Curso propio del facilitador, aún por aceptar (asignación pendiente).
        $this->cursoPropio = $this->curso($this->facilitador, EstadoCurso::POR_ACEPTAR);
        // Curso de otro facilitador en borrador: no debe aparecer para el primero.
        $this->cursoAjeno = $this->curso($this->otroFacilitador, EstadoCurso::BORRADOR);
        // Curso de otro facilitador en inscripción: público, debe aparecer.
        $this->cursoPublicoAjeno = $this->curso($this->otroFacilitador, EstadoCurso::INSCRIPCION);
    }

    public function test_facilitador_ve_sus_cursos_y_los_publicos_en_inscripcion(): void
    {
        $ids = $this->listadoPara($this->facilitador)->pluck('id_curso')->all();

        $this->assertContains($this->cursoPropio->id_curso, $ids);
        $this->assertContains($this->cursoPublicoAjeno->id_curso, $ids);
        $this->assertNotContains($this->cursoAjeno->id_curso, $ids);
    }

    public function test_solo_el_facilitador_asignado_puede_aceptar_la_asignacion(): void
    {
        $this->assertTrue(Gate::forUser($this->facilitador)->allows('responderAsignacion', $this->cursoPropio));
        $this->assertFalse(Gate::forUser($this->otroFacilitador)->allows('responderAsignacion', $this->cursoPropio));
        // Un gestor no acepta por otro facilitador.
        $this->assertFalse(Gate::forUser($this->coordinador)->allows('responderAsignacion', $this->cursoPropio));
    }

    /** Usuario con un perfil (id > 1 para no tocar al administrador raíz). */
    private function usuarioCon(int $perfil, ?int $excluirId = null): User
    {
        return User::whereHas('perfiles', fn ($q) => $q->where('security.profiles.id', $perfil))
            ->where('id', '>', 1)
            ->when($excluirId, fn ($q) => $q->where('id', '!=', $excluirId))
            ->firstOrFail();
    }

    private function curso(User $facilitador, EstadoCurso $estado): Curso
    {
        $curso = Curso::withoutEvents(function () use ($facilitador) {
            $c = new Curso;
            $c->nombre = 'Curso de permisos '.random_int(1000, 9999);
            $c->codigo = 'PRM-'.random_int(100000, 999999);
            $c->id_persona = $facilitador->personalData->id_persona;
            $c->id_actividad_formativa = 1;
            $c->id_aspecto = 1;
            $c->id_modalidad = 1;
            $c->id_modalidad_especial = 1;
            $c->cantidad_cupos = 20;
            $c->es_nacional = true;
            $c->fecha_inicio = now()->addMonth();
            $c->fecha_fin = now()->addMonths(2);
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

    private function listadoPara(User $user)
    {
        $respuesta = $this->actingAs($user)
            ->withSession(['profile_id' => Perfil::FACILITADOR, 'session_token' => $user->session_token])
            ->get(route('taller.cursos.index'));

        $respuesta->assertOk();

        return collect($respuesta->viewData('cursos')->items());
    }
}
