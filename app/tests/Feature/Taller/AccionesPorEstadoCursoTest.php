<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Services\CondicionalEstadoCurso;
use Tests\TestCase;

/**
 * Acciones del panel del curso por estado y rol (26/10).
 *
 * Antes, quien gestiona todos los cursos perdía las acciones de facilitador
 * aunque fuera el facilitador asignado: no veía "Aceptar asignación".
 */
class AccionesPorEstadoCursoTest extends TestCase
{
    use DatabaseTransactions;

    /** Capacidades esperadas: [estado => [rol => capacidades que deben aparecer]] */
    private const MATRIZ = [
        1 => ['facilitador' => ['aceptar_asignacion', 'rechazar_asignacion'], 'gestor' => ['reasignar_facilitador']],
        2 => ['facilitador' => ['ver_motivo'], 'gestor' => ['reasignar_facilitador', 'ver_motivo']],
        3 => ['facilitador' => ['ver_motivo', 'editar', 'enviar_aprobacion'], 'gestor' => ['reasignar_facilitador']],
        4 => ['facilitador' => ['editar', 'enviar_aprobacion'], 'gestor' => ['reasignar_facilitador']],
        5 => ['facilitador' => ['en_revision'], 'gestor' => ['aprobar', 'rechazar', 'reasignar_facilitador']],
        6 => ['facilitador' => ['acceder_contenido'], 'gestor' => ['finalizar_inscripciones', 'reasignar_facilitador']],
        7 => ['facilitador' => ['acceder_contenido'], 'gestor' => ['finalizar_curso', 'gestionar_asistencia']],
        8 => ['facilitador' => ['gestionar_certificacion', 'ver_satisfaccion'], 'gestor' => ['cerrar_curso', 'consultar_asistencia', 'gestionar_certificacion']],
        9 => ['facilitador' => ['gestionar_certificacion'], 'gestor' => ['gestionar_certificacion']],
    ];

    public function test_cada_estado_ofrece_las_acciones_de_cada_rol(): void
    {
        $servicio = new CondicionalEstadoCurso;

        foreach (self::MATRIZ as $estado => $roles) {
            $facilitador = $servicio->obtenerCapacidades($estado, false, true, false, 10);
            $gestor = $servicio->obtenerCapacidades($estado, false, false, true, 10);

            foreach ($roles['facilitador'] as $cap) {
                $this->assertContains($cap, $facilitador, "Estado {$estado}: el facilitador debe tener {$cap}.");
            }
            foreach ($roles['gestor'] as $cap) {
                $this->assertContains($cap, $gestor, "Estado {$estado}: el gestor debe tener {$cap}.");
            }
        }

        // Aceptar o rechazar la asignación es solo del facilitador.
        $this->assertNotContains('aceptar_asignacion', $servicio->obtenerCapacidades(1, false, false, true, 10));
    }

    private function cursoEnEstado(int $idPersona, EstadoCurso $estado): Curso
    {
        $curso = Curso::withoutEvents(function () use ($idPersona) {
            $c = new Curso;
            $c->nombre = 'Taller de Montaje';
            $c->codigo = 'TMJ-'.random_int(1000, 9999);
            $c->id_persona = $idPersona;
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

    private function verCurso(User $user, Curso $curso, int $perfil)
    {
        return $this->actingAs($user)
            ->withSession(['profile_id' => $perfil, 'session_token' => $user->session_token])
            ->get('/taller/cursos/'.$curso->id_curso);
    }

    public function test_el_administrador_asignado_como_facilitador_ve_aceptar_asignacion(): void
    {
        $admin = User::findOrFail(1);
        $curso = $this->cursoEnEstado($admin->personalData->id_persona, EstadoCurso::POR_ACEPTAR);

        $this->verCurso($admin, $curso, Perfil::ADMINISTRADOR)
            ->assertOk()
            ->assertSee('Aceptar Asignación')
            ->assertSee('Rechazar Asignación')
            ->assertSee('Reasignar Facilitador');
    }

    public function test_el_coordinador_no_asignado_no_ve_aceptar_asignacion(): void
    {
        $coordinador = User::whereHas('perfiles', fn ($q) => $q->where('security.profiles.id', Perfil::COORDINADOR))
            ->where('id', '>', 1)->first() ?? User::findOrFail(2);
        $curso = $this->cursoEnEstado(User::findOrFail(1)->personalData->id_persona, EstadoCurso::POR_ACEPTAR);

        $this->verCurso($coordinador, $curso, Perfil::COORDINADOR)
            ->assertOk()
            ->assertDontSee('Aceptar Asignación')
            ->assertSee('Reasignar Facilitador');
    }

    public function test_la_coordinacion_ve_gestionar_asistencia_en_curso(): void
    {
        $coordinador = User::whereHas('perfiles', fn ($q) => $q->where('security.profiles.id', Perfil::COORDINADOR))
            ->where('id', '>', 1)->first() ?? User::findOrFail(2);
        $curso = $this->cursoEnEstado(User::findOrFail(1)->personalData->id_persona, EstadoCurso::EN_CURSO);

        $this->verCurso($coordinador, $curso, Perfil::COORDINADOR)
            ->assertOk()
            ->assertSee('Gestionar Asistencia')
            ->assertSee('Finalizar Curso');
    }
}
