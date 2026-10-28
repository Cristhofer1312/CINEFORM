<?php

namespace Tests\Feature\Security;

use App\Constants\Perfil;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Tests\TestCase;

class InscripcionPolicyTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private User $coordinador;

    private User $facilitadorDuenio;

    private User $facilitadorAjeno;

    private User $participanteTitular;

    private User $participanteAjeno;

    private Curso $curso;

    private Inscripcion $inscripcion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::findOrFail(1);

        $this->coordinador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::COORDINADOR);
        })->first() ?? User::findOrFail(1);

        // Facilitadores "puros": sin perfil Administrador ni Coordinador, para
        // que la jerarquía no los convierta en gestores.
        $facilitadores = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::FACILITADOR);
        })->whereDoesntHave('perfiles', function ($q) {
            $q->whereIn('security.profiles.id', [Perfil::ADMINISTRADOR, Perfil::COORDINADOR]);
        })->where('id', '>', 1)->orderBy('id')->get();

        $this->facilitadorDuenio = $facilitadores->first() ?? User::findOrFail(5);
        $this->facilitadorAjeno = $facilitadores->skip(1)->first() ?? User::findOrFail(6);

        // Participantes "puros": sin perfiles de mayor jerarquía.
        $participantes = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->whereDoesntHave('perfiles', function ($q) {
            $q->whereIn('security.profiles.id', [Perfil::ADMINISTRADOR, Perfil::FACILITADOR, Perfil::COORDINADOR]);
        })->where('id', '>', 1)->orderBy('id')->get();

        $this->participanteTitular = $participantes->first() ?? User::findOrFail(3);
        $this->participanteAjeno = $participantes->skip(1)->first() ?? User::findOrFail(7);

        $idPersonaFacilitador = optional($this->facilitadorDuenio->personalData)->id_persona;
        $idPersonaTitular = optional($this->participanteTitular->personalData)->id_persona;

        $this->assertNotNull($idPersonaFacilitador, 'facilitadorDuenio debe tener personalData.');
        $this->assertNotNull($idPersonaTitular, 'participanteTitular debe tener personalData.');

        // Curso propio: reutilizar Curso::first() chocaba con inscripciones reales
        // del titular en ese curso (unique id_curso + id_persona).
        $this->curso = Curso::withoutEvents(function () use ($idPersonaFacilitador) {
            $c = new Curso;
            $c->nombre = 'Curso Inscripcion Policy';
            $c->codigo = 'LAB-I'.random_int(1000, 9999).'-A-O-G-2026';
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

        $this->inscripcion = new Inscripcion;
        $this->inscripcion->id_curso = $this->curso->id_curso;
        $this->inscripcion->id_persona = $idPersonaTitular;
        $this->inscripcion->estado = 'pendiente';
        $this->inscripcion->fecha_inscripcion = now();
        $this->inscripcion->save();
    }

    public function test_participante_titular_puede_ver_y_descargar_documentos_de_su_inscripcion(): void
    {
        session(['profile_id' => Perfil::PARTICIPANTE]);

        $this->assertTrue(
            Gate::forUser($this->participanteTitular)->allows('ver', $this->inscripcion)
        );

        $this->assertTrue(
            Gate::forUser($this->participanteTitular)->allows('verDocumento', $this->inscripcion)
        );

        $this->assertTrue(
            Gate::forUser($this->participanteTitular)->allows('cancelar', $this->inscripcion)
        );
    }

    public function test_participante_ajeno_no_puede_ver_ni_descargar_inscripcion_ajena(): void
    {
        session(['profile_id' => Perfil::PARTICIPANTE]);

        $this->assertFalse(
            Gate::forUser($this->participanteAjeno)->allows('ver', $this->inscripcion)
        );

        $this->assertFalse(
            Gate::forUser($this->participanteAjeno)->allows('verDocumento', $this->inscripcion)
        );

        $this->assertFalse(
            Gate::forUser($this->participanteAjeno)->allows('cancelar', $this->inscripcion)
        );
    }

    public function test_facilitador_del_curso_puede_gestionar_postulaciones_y_ver_documentos(): void
    {
        session(['profile_id' => Perfil::FACILITADOR]);

        $this->assertTrue(
            Gate::forUser($this->facilitadorDuenio)->allows('gestionar', $this->inscripcion),
            'El facilitador del curso debe poder aprobar/rechazar postulaciones de su curso (Q3).'
        );

        $this->assertTrue(
            Gate::forUser($this->facilitadorDuenio)->allows('verDocumento', $this->inscripcion),
            'El facilitador del curso debe poder ver y descargar documentos de postulantes (C3).'
        );
    }

    public function test_facilitador_ajeno_no_puede_gestionar_postulaciones_de_otro_curso(): void
    {
        session(['profile_id' => Perfil::FACILITADOR]);

        $this->assertFalse(
            Gate::forUser($this->facilitadorAjeno)->allows('gestionar', $this->inscripcion)
        );

        $this->assertFalse(
            Gate::forUser($this->facilitadorAjeno)->allows('verDocumento', $this->inscripcion)
        );
    }

    public function test_coordinador_puede_gestionar_y_rehabilitar_inscripciones(): void
    {
        session(['profile_id' => Perfil::COORDINADOR]);

        $this->assertTrue(
            Gate::forUser($this->coordinador)->allows('gestionar', $this->inscripcion)
        );

        $this->assertTrue(
            Gate::forUser($this->coordinador)->allows('rehabilitar', $this->inscripcion)
        );

        $this->assertTrue(
            Gate::forUser($this->coordinador)->allows('verDocumento', $this->inscripcion)
        );
    }

    public function test_admin_raiz_tiene_bypass_total_en_inscripcion(): void
    {
        $this->assertTrue(
            Gate::forUser($this->admin)->allows('gestionar', $this->inscripcion)
        );
    }
}
