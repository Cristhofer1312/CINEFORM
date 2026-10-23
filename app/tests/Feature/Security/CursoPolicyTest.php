<?php

namespace Tests\Feature\Security;

use App\Constants\Perfil;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Tests\TestCase;

class CursoPolicyTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private User $coordinador;

    private User $facilitadorDuenio;

    private User $facilitadorAjeno;

    private User $participante;

    private Curso $curso;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::findOrFail(1);

        // Coordinador (no superadmin, sin perfil Administrador)
        $this->coordinador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::COORDINADOR);
        })->whereDoesntHave('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::ADMINISTRADOR);
        })->where('id', '>', 1)->orderBy('id')->first() ?? User::findOrFail(2);

        // Facilitadores "puros": sin perfil Administrador ni Coordinador, para
        // que la resolución por jerarquía no los convierta en gestores.
        $facilitadores = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::FACILITADOR);
        })->whereDoesntHave('perfiles', function ($q) {
            $q->whereIn('security.profiles.id', [Perfil::ADMINISTRADOR, Perfil::COORDINADOR]);
        })->where('id', '>', 1)->orderBy('id')->get();

        $this->facilitadorDuenio = $facilitadores->first() ?? User::findOrFail(5);
        $this->facilitadorAjeno = $facilitadores->skip(1)->first() ?? User::findOrFail(6);

        // Participante "puro" (user 7): sin perfiles de mayor jerarquía.
        $this->participante = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->whereDoesntHave('perfiles', function ($q) {
            $q->whereIn('security.profiles.id', [Perfil::ADMINISTRADOR, Perfil::FACILITADOR, Perfil::COORDINADOR]);
        })->where('id', '>', 1)->first() ?? User::findOrFail(7);

        // Obtener o crear curso de prueba sin disparar observer de PROCINEC
        $idPersonaFacilitador = optional($this->facilitadorDuenio->personalData)->id_persona;
        $this->assertNotNull($idPersonaFacilitador, 'facilitadorDuenio debe tener personalData.');

        $cursoExistente = Curso::first();
        if ($cursoExistente) {
            $this->curso = $cursoExistente;
            $this->curso->id_persona = $idPersonaFacilitador;
            $this->curso->saveQuietly();
        } else {
            $this->curso = Curso::withoutEvents(function () use ($idPersonaFacilitador) {
                $c = new Curso;
                $c->nombre = 'Curso Test Policy';
                $c->codigo = 'LAB-F1001-A-O-G-2026';
                $c->id_persona = $idPersonaFacilitador;
                $c->id_actividad_formativa = 1;
                $c->id_aspecto = 1;
                $c->id_modalidad = 1;
                $c->id_modalidad_especial = 1;
                $c->fecha_inicio = now();
                $c->fecha_fin = now()->addMonths(1);
                $c->save();

                return $c;
            });
        }
    }

    public function test_facilitador_duenio_puede_calificar_su_curso(): void
    {
        $this->assertTrue(
            Gate::forUser($this->facilitadorDuenio)->allows('calificar', $this->curso),
            'El facilitador asignado debe poder calificar su curso.'
        );
    }

    public function test_facilitador_ajeno_no_puede_calificar_ni_gestionar_curso_ajeno(): void
    {
        $this->assertFalse(
            Gate::forUser($this->facilitadorAjeno)->allows('calificar', $this->curso),
            'Un facilitador no asignado no puede calificar un curso ajeno.'
        );

        $this->assertFalse(
            Gate::forUser($this->facilitadorAjeno)->allows('gestionarAsistencia', $this->curso),
            'Un facilitador no asignado no puede gestionar asistencia de curso ajeno.'
        );

        $this->assertFalse(
            Gate::forUser($this->facilitadorAjeno)->allows('certificar', $this->curso),
            'Un facilitador no asignado no puede certificar curso ajeno.'
        );
    }

    public function test_coordinador_no_puede_calificar_cursos(): void
    {
        $this->assertFalse(
            Gate::forUser($this->coordinador)->allows('calificar', $this->curso),
            'El coordinador no debe calificar cursos (regla de negocio B3/Q1).'
        );
    }

    public function test_coordinador_puede_gestionar_asistencia_y_certificar(): void
    {
        $this->assertTrue(
            Gate::forUser($this->coordinador)->allows('gestionarAsistencia', $this->curso),
            'El coordinador debe poder gestionar asistencia.'
        );

        $this->assertTrue(
            Gate::forUser($this->coordinador)->allows('certificar', $this->curso),
            'El coordinador debe poder certificar cursos.'
        );

        $this->assertTrue(
            Gate::forUser($this->coordinador)->allows('verParticipantes', $this->curso),
            'El coordinador debe poder ver participantes.'
        );
    }

    public function test_facilitador_duenio_puede_ver_participantes_de_su_curso(): void
    {
        $this->assertTrue(
            Gate::forUser($this->facilitadorDuenio)->allows('verParticipantes', $this->curso),
            'El facilitador asignado debe poder ver los participantes de su curso (C2).'
        );
    }

    public function test_participante_no_puede_calificar_ni_certificar(): void
    {
        $this->assertFalse(
            Gate::forUser($this->participante)->allows('calificar', $this->curso)
        );

        $this->assertFalse(
            Gate::forUser($this->participante)->allows('certificar', $this->curso)
        );
    }

    public function test_transiciones_de_estado_cambiar_estado(): void
    {
        // 1. Facilitador duenio intentando aprobar curso (5 -> 6): denegado (requiere APROBAR_CURSO / Gestor)
        $this->assertFalse(
            Gate::forUser($this->facilitadorDuenio)->allows('cambiarEstado', [$this->curso, \App\Enums\EstadoCurso::INSCRIPCION->value]),
            'El facilitador no puede aprobar el curso para abrir inscripciones.'
        );

        // 2. Coordinador intentando declinar asignación (1 -> 3): denegado (reservado al facilitador asignado)
        $this->assertFalse(
            Gate::forUser($this->coordinador)->allows('cambiarEstado', [$this->curso, \App\Enums\EstadoCurso::DECLINADO->value]),
            'El coordinador no puede declinar la asignación de un facilitador.'
        );

        // 3. Coordinador puede rechazar propuesta de curso (5 -> 2)
        $this->assertTrue(
            Gate::forUser($this->coordinador)->allows('cambiarEstado', [$this->curso, \App\Enums\EstadoCurso::RECHAZADO->value]),
            'El coordinador puede rechazar una propuesta de curso.'
        );

        // 4. Coordinador puede aprobar curso para abrir inscripciones (5 -> 6)
        $this->assertTrue(
            Gate::forUser($this->coordinador)->allows('cambiarEstado', [$this->curso, \App\Enums\EstadoCurso::INSCRIPCION->value]),
            'El coordinador puede aprobar un curso.'
        );

        // 5. Facilitador ajeno no puede cambiar estado de curso ajeno
        $this->assertFalse(
            Gate::forUser($this->facilitadorAjeno)->allows('cambiarEstado', [$this->curso, \App\Enums\EstadoCurso::APROBACION->value]),
            'Un facilitador no asignado no puede cambiar el estado de un curso ajeno.'
        );
    }

    /**
     * Paso 6: al resolver el perfil de OTRO usuario no debe filtrarse el perfil
     * de la sesión del actor, y la elección entre varios perfiles debe ser
     * determinista por jerarquía (no por id).
     */
    public function test_perfil_activo_para_otro_usuario_ignora_la_sesion_del_actor(): void
    {
        $this->actingAs($this->admin)->withSession(['profile_id' => Perfil::ADMINISTRADOR]);

        $autorizacion = app(\App\Services\Security\Autorizacion::class);

        $this->assertSame(
            Perfil::COORDINADOR,
            (int) $autorizacion->perfilActivoPara($this->coordinador),
            'El coordinador (perfiles 3 y 4) debe resolverse como Coordinador, no como el perfil de la sesión del actor.'
        );

        $this->assertSame(
            Perfil::FACILITADOR,
            (int) $autorizacion->perfilActivoPara($this->facilitadorDuenio),
            'Un facilitador que también es participante debe resolverse como Facilitador.'
        );

        $this->assertSame(
            Perfil::PARTICIPANTE,
            (int) $autorizacion->perfilActivoPara($this->participante)
        );

        // Para el propio actor sigue mandando la sesión.
        $this->assertSame(
            Perfil::ADMINISTRADOR,
            (int) $autorizacion->perfilActivoPara($this->admin)
        );

        // Sin sesión activa (e.g. background job, CLI), debe resolver el perfil
        // determinista del usuario consultando directamente la base de datos.
        session()->flush();
        $this->assertSame(
            Perfil::COORDINADOR,
            (int) $autorizacion->perfilActivoPara($this->coordinador),
            'Sin sesión, el perfil debe resolverse deterministamente de la BD.'
        );
        $this->assertSame(
            Perfil::FACILITADOR,
            (int) $autorizacion->perfilActivoPara($this->facilitadorDuenio)
        );

        // Con sesión de PARTICIPANTE (perfil 3), evaluar al coordinador NO debe devolver 3.
        session(['profile_id' => Perfil::PARTICIPANTE]);
        $this->assertSame(
            Perfil::COORDINADOR,
            (int) $autorizacion->perfilActivoPara($this->coordinador),
            'La sesión activa del actor no debe contaminar el perfil evaluado de otro usuario.'
        );
    }

    public function test_facilitador_ajeno_recibe_403_al_intentar_editar_curso(): void
    {
        $session = ['profile_id' => Perfil::FACILITADOR];
        if (!empty($this->facilitadorAjeno->session_token)) {
            $session['session_token'] = $this->facilitadorAjeno->session_token;
        }

        $response = $this->actingAs($this->facilitadorAjeno)
            ->withSession($session)
            ->put(route('taller.cursos.update', ['curso' => $this->curso->id_curso]), [
                'nombre' => 'Intento de edicion no autorizada',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_raiz_tiene_bypass_total(): void
    {
        $this->assertTrue(
            Gate::forUser($this->admin)->allows('calificar', $this->curso),
            'El admin raíz tiene bypass total vía Gate::before.'
        );
    }
}
