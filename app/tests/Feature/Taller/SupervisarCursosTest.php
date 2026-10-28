<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use App\Services\Security\Autorizacion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Tests\TestCase;

/**
 * D7 (26/10): el alcance "todos los cursos" sale del permiso explícito
 * `supervise_courses`, que extiende a cualquier curso las acciones que el
 * usuario ya tiene, y `manage_course` deja de servir de comodín.
 */
class SupervisarCursosTest extends TestCase
{
    use DatabaseTransactions;

    private User $coordinador;

    private Curso $cursoAjeno;

    protected function setUp(): void
    {
        parent::setUp();

        $facilitador = User::whereHas('perfiles', fn ($q) => $q->where('security.profiles.id', Perfil::FACILITADOR))
            ->whereDoesntHave('perfiles', fn ($q) => $q->whereIn('security.profiles.id', [Perfil::ADMINISTRADOR, Perfil::COORDINADOR]))
            ->where('id', '>', 1)->first() ?? User::findOrFail(5);

        $this->coordinador = User::whereHas('perfiles', fn ($q) => $q->where('security.profiles.id', Perfil::COORDINADOR))
            ->where('id', '>', 1)
            ->whereKeyNot($facilitador->id)
            ->first() ?? User::findOrFail(2);
        // Los permisos suman perfiles: se aísla el de Coordinador.
        $this->soloConPerfiles($this->coordinador, Perfil::COORDINADOR);

        $this->cursoAjeno = Curso::withoutEvents(function () use ($facilitador) {
            $c = new Curso;
            $c->nombre = 'Taller de Sonido Directo';
            $c->codigo = 'TSD-'.random_int(1000, 9999);
            $c->id_persona = $facilitador->personalData->id_persona;
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
            'id_curso' => $this->cursoAjeno->id_curso,
            'id_estado' => EstadoCurso::EN_CURSO->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function quitarAlCoordinador(array $slugs): void
    {
        $ids = DB::table('security.permissions as p')
            ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
            ->where('pr.codigo', 'taller.cursos.index')->whereIn('p.slug', $slugs)
            ->pluck('p.id');

        DB::table('security.profile_permissions')->where('profile_id', Perfil::COORDINADOR)->whereIn('permission_id', $ids)->delete();
        app(Autorizacion::class)->invalidar();
    }

    private function puede(string $habilidad): bool
    {
        return Gate::forUser($this->coordinador)->allows($habilidad, $this->cursoAjeno);
    }

    private function verParticipantes()
    {
        return $this->actingAs($this->coordinador)
            ->withSession(['profile_id' => Perfil::COORDINADOR, 'session_token' => $this->coordinador->session_token])
            ->get(route('taller.cursos.participantes', $this->cursoAjeno->id_curso));
    }

    public function test_el_coordinador_sembrado_conserva_el_alcance_sobre_cursos_ajenos(): void
    {
        $this->assertTrue($this->puede('editar'));
        $this->assertTrue($this->puede('verParticipantes'));
        $this->assertTrue($this->puede('gestionarAsistencia'));
        $this->assertTrue($this->puede('reasignarFacilitador'));
        $this->verParticipantes()->assertOk();
    }

    public function test_sin_supervisar_no_alcanza_cursos_ajenos_aunque_tenga_gestionar(): void
    {
        $this->quitarAlCoordinador(['supervise_courses']);

        $this->assertFalse($this->puede('editar'));
        $this->assertFalse($this->puede('verParticipantes'));
        $this->assertFalse($this->puede('gestionarAsistencia'));
        $this->assertFalse($this->puede('reasignarFacilitador'));
        $this->assertFalse($this->puede('verSatisfaccion'));
        $this->verParticipantes()->assertForbidden();
    }

    public function test_supervisar_no_concede_acciones_que_el_usuario_no_tiene(): void
    {
        $this->quitarAlCoordinador(['manage_attendance']);

        $this->assertTrue($this->puede('verParticipantes'));
        $this->assertFalse($this->puede('gestionarAsistencia'));
        $this->assertFalse($this->puede('certificar'));
    }

    public function test_gestionar_curso_ya_no_es_comodin_de_otras_acciones(): void
    {
        $this->quitarAlCoordinador(['view_participants']);

        // Antes, manage_course bastaba para ver participantes de cualquier curso.
        $this->assertFalse($this->puede('verParticipantes'));
        $this->verParticipantes()->assertForbidden();
    }

    public function test_sin_gestionar_curso_supervisa_pero_no_gestiona(): void
    {
        $this->quitarAlCoordinador(['manage_course']);

        $this->assertTrue($this->puede('verParticipantes'));
        $this->assertTrue($this->puede('verSatisfaccion'));
        $this->assertFalse($this->puede('editar'));
        $this->assertFalse($this->puede('reasignarFacilitador'));
    }
}
