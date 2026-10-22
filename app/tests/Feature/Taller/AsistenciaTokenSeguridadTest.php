<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\AsistenciaToken;
use Modules\Taller\Entities\ContenidoCurso;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Tests\TestCase;

/**
 * Valida la seguridad e integridad del marcado de asistencia (UX-13 / RF-32):
 * - El token debe coincidir estrictamente con el curso de la URL.
 * - Solo participantes con inscripción formalmente APROBADA pueden marcar.
 */
class AsistenciaTokenSeguridadTest extends TestCase
{
    use DatabaseTransactions;

    private User $participante;
    private Curso $cursoA;
    private Curso $cursoB;
    private ContenidoCurso $contenidoA;
    private AsistenciaToken $tokenA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->participante = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->where('id', '>', 1)->first() ?? User::findOrFail(7);

        $idPersonaFacilitador = optional(User::findOrFail(1)->personalData)->id_persona ?? 1;

        // Curso A
        $this->cursoA = Curso::withoutEvents(function () use ($idPersonaFacilitador) {
            $c = new Curso();
            $c->nombre = 'Curso A Asistencia';
            $c->codigo = 'LAB-TEST-AS-A-' . random_int(1000, 9999);
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

        // Contenido en Curso A
        $this->contenidoA = ContenidoCurso::create([
            'id_curso' => $this->cursoA->id_curso,
            'titulo' => 'Clase 1 Curso A',
            'descripcion_breve' => 'Descripción breve',
            'descripcion' => 'Descripción completa de la clase 1',
            'fecha_contenido' => now(),
            'hora_inicio' => '09:00',
            'hora_fin' => '12:00',
            'creado_por' => 1,
        ]);

        // Token para Contenido en Curso A
        $this->tokenA = AsistenciaToken::create([
            'id_contenido_curso' => $this->contenidoA->id_contenido_curso,
            'token' => 'TOKEN_TEST_CURSO_A_' . random_int(10000, 99999),
            'activo' => true,
            'fecha_expiracion' => now()->addHours(2),
            'creado_por' => 1,
        ]);

        // Curso B
        $this->cursoB = Curso::withoutEvents(function () use ($idPersonaFacilitador) {
            $c = new Curso();
            $c->nombre = 'Curso B Asistencia';
            $c->codigo = 'LAB-TEST-AS-B-' . random_int(1000, 9999);
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

    public function test_rechaza_token_de_otro_curso_al_mostrar_confirmacion(): void
    {
        // El participante está aprobado en Curso B pero accede con el token de Curso A
        Inscripcion::create([
            'id_curso' => $this->cursoB->id_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'fecha_inscripcion' => Carbon::now(),
            'estado' => Inscripcion::ESTADO_APROBADO,
        ]);

        // Intentar ver confirmación en Curso B con token de Curso A
        $response = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->get(route('taller.asistencia.confirmar', [
                'curso' => $this->cursoB->id_curso,
                'token' => $this->tokenA->token,
            ]));

        $response->assertStatus(200);
        $response->assertSee('El enlace de asistencia no corresponde a este curso.');
    }

    public function test_rechaza_marcado_de_asistencia_si_inscripcion_no_esta_aprobada(): void
    {
        // Participante solo en estado POSTULADO en Curso A
        Inscripcion::create([
            'id_curso' => $this->cursoA->id_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'fecha_inscripcion' => Carbon::now(),
            'estado' => Inscripcion::ESTADO_POSTULADO,
        ]);

        $response = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->get(route('taller.asistencia.confirmar', [
                'curso' => $this->cursoA->id_curso,
                'token' => $this->tokenA->token,
            ]));

        $response->assertStatus(200);
        $response->assertSee('Debes estar formalmente inscrito y admitido en este curso para registrar asistencia.');
    }

    public function test_permite_marcar_asistencia_a_participante_aprobado_en_su_curso(): void
    {
        // Participante formalmente APROBADO en Curso A
        $inscripcion = Inscripcion::create([
            'id_curso' => $this->cursoA->id_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'fecha_inscripcion' => Carbon::now(),
            'estado' => Inscripcion::ESTADO_APROBADO,
        ]);

        $response = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->post(route('taller.asistencia.confirmar-marcar', [
                'curso' => $this->cursoA->id_curso,
                'token' => $this->tokenA->token,
            ]));

        $this->assertDatabaseHas('taller.asistencias', [
            'id_contenido_curso' => $this->contenidoA->id_contenido_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'id_inscripcion' => $inscripcion->id_inscripcion,
            'activa' => true,
        ]);
    }
}
