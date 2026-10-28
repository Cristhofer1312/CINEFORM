<?php

namespace Tests\Feature\Taller;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\CursoBorrador;
use Modules\Taller\Entities\CursoRequisito;
use Modules\Taller\Entities\Inscripcion;
use Tests\TestCase;

/**
 * Regla de negocio: la carta de permiso (autorización de menores) solo
 * aplica a cursos dirigidos a niños y adolescentes.
 *
 * Usa la base de datos real de desarrollo y DatabaseTransactions para
 * revertir todo lo creado por cada prueba.
 */
class CartaPermisoMenoresTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private int $menorId;

    private int $adultoId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::findOrFail(1);

        $this->menorId = (int) DB::table('taller.modalidades_especiales')
            ->where('es_menor', true)
            ->value('id_modalidad_especial');

        $this->adultoId = (int) DB::table('taller.modalidades_especiales')
            ->where('es_menor', false)
            ->value('id_modalidad_especial');

        $this->assertGreaterThan(0, $this->menorId, 'Falta el público objetivo para menores.');
        $this->assertGreaterThan(0, $this->adultoId, 'Falta el público objetivo para adultos.');
    }

    public function test_wizard_store_fuerza_la_carta_como_obligatoria_en_cursos_para_menores(): void
    {
        $borrador = $this->crearBorrador($this->menorId);

        $response = $this->actingAs($this->admin)
            ->withSession($this->sesionWizard($borrador))
            ->post(route('taller.cursos.wizard.requisitos.store'), [
                'requisitos' => [
                    ['tipo' => 'pregunta', 'titulo' => '¿Por qué te interesa el curso?', 'obligatorio' => true],
                ],
            ]);

        $response->assertRedirect(route('taller.cursos.wizard.revision'));

        $borrador->refresh();
        $carta = $this->buscarCartaEnArreglo($borrador->payload['requisitos'] ?? []);

        $this->assertNotNull($carta);
        $this->assertTrue((bool) $carta['obligatorio']);
    }

    public function test_wizard_store_descarta_la_carta_en_cursos_para_adultos(): void
    {
        $borrador = $this->crearBorrador($this->adultoId);
        $carta = CursoRequisito::requisitoCartaPermiso();
        $carta['obligatorio'] = false;

        $response = $this->actingAs($this->admin)
            ->withSession($this->sesionWizard($borrador))
            ->post(route('taller.cursos.wizard.requisitos.store'), [
                'requisitos' => [
                    $carta,
                    ['tipo' => 'pregunta', 'titulo' => '¿Por qué te interesa el curso?', 'obligatorio' => true],
                ],
            ]);

        $response->assertRedirect(route('taller.cursos.wizard.revision'));

        $borrador->refresh();
        $requisitos = $borrador->payload['requisitos'] ?? [];

        $this->assertNull($this->buscarCartaEnArreglo($requisitos));
        $this->assertCount(1, $requisitos);
        $this->assertSame('¿Por qué te interesa el curso?', $requisitos[0]['titulo']);
    }

    public function test_finalizar_crea_la_carta_obligatoria_para_cursos_de_menores(): void
    {
        $borrador = $this->crearBorrador($this->menorId);
        $carta = CursoRequisito::requisitoCartaPermiso();
        $carta['obligatorio'] = false;

        $this->guardarRequisitosEnBorrador($borrador, [
            $carta,
            ['tipo' => 'pregunta', 'titulo' => '¿Motivación?', 'obligatorio' => true],
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession($this->sesionWizard($borrador))
            ->post(route('taller.cursos.wizard.finalizar'));

        $response->assertStatus(302);

        $curso = $this->cursoPorNombre($borrador->payload['paso1']['nombre']);
        $this->assertNotNull($curso, 'El wizard no creó el curso.');

        $cartaDb = $this->cartaDe((int) $curso->id_curso);
        $this->assertNotNull($cartaDb);
        $this->assertTrue((bool) $cartaDb->obligatorio);

        $this->assertDatabaseHas('taller.curso_requisitos', [
            'id_curso' => $curso->id_curso,
            'tipo' => 'pregunta',
            'titulo' => '¿Motivación?',
        ]);
    }

    public function test_finalizar_no_crea_la_carta_para_cursos_de_adultos(): void
    {
        $borrador = $this->crearBorrador($this->adultoId);

        $this->guardarRequisitosEnBorrador($borrador, [
            CursoRequisito::requisitoCartaPermiso(),
            ['tipo' => 'pregunta', 'titulo' => '¿Motivación?', 'obligatorio' => true],
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession($this->sesionWizard($borrador))
            ->post(route('taller.cursos.wizard.finalizar'));

        $response->assertStatus(302);

        $curso = $this->cursoPorNombre($borrador->payload['paso1']['nombre']);
        $this->assertNotNull($curso, 'El wizard no creó el curso.');

        $this->assertNull($this->cartaDe((int) $curso->id_curso));

        $this->assertDatabaseHas('taller.curso_requisitos', [
            'id_curso' => $curso->id_curso,
            'tipo' => 'pregunta',
            'titulo' => '¿Motivación?',
        ]);
    }

    public function test_inscripcion_bloquea_curso_de_menores_sin_carta(): void
    {
        $cursoId = $this->crearCurso($this->menorId);
        $cartaId = $this->crearCarta($cursoId, true);

        $response = $this->actingAs($this->admin)
            ->withSession($this->sesionBase())
            ->post(route('taller.inscripciones.procesar', $cursoId));

        $response->assertRedirect();
        $response->assertSessionHasErrors('req_'.$cartaId);
        $this->assertStringContainsString('Carta de permiso', session('errors')->first('req_'.$cartaId));

        $this->assertDatabaseMissing('taller.inscripciones', [
            'id_curso' => $cursoId,
            'id_persona' => 1,
        ]);
    }

    public function test_inscripcion_ignora_la_carta_historica_en_curso_de_adultos(): void
    {
        $cursoId = $this->crearCurso($this->adultoId);
        $this->crearCarta($cursoId, true);

        $response = $this->actingAs($this->admin)
            ->withSession($this->sesionBase())
            ->post(route('taller.inscripciones.procesar', $cursoId));

        $response->assertRedirect(route('taller.cursos.show', $cursoId));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('taller.inscripciones', [
            'id_curso' => $cursoId,
            'id_persona' => 1,
            'estado' => Inscripcion::ESTADO_POSTULADO,
        ]);
    }

    public function test_coordinador_al_cambiar_a_adulto_elimina_la_carta_sin_evidencia(): void
    {
        $cursoId = $this->crearCurso($this->menorId);
        $this->crearCarta($cursoId, true);

        $response = $this->actingAs($this->admin)
            ->withSession($this->sesionBase())
            ->put(route('taller.cursos.update', $cursoId), [
                'id_modalidad_especial' => $this->adultoId,
            ]);

        $response->assertRedirect(route('taller.cursos.show', $cursoId));

        $this->assertNull($this->cartaDe($cursoId));
        $this->assertDatabaseHas('taller.cursos', [
            'id_curso' => $cursoId,
            'id_modalidad_especial' => $this->adultoId,
        ]);
    }

    public function test_coordinador_conserva_la_evidencia_al_pasar_a_adulto_y_reactiva_la_carta_al_volver_a_menores(): void
    {
        $cursoId = $this->crearCurso($this->menorId);
        $cartaId = $this->crearCarta($cursoId, true);
        $this->crearRespuestaCarta($cursoId, $cartaId);

        $response = $this->actingAs($this->admin)
            ->withSession($this->sesionBase())
            ->put(route('taller.cursos.update', $cursoId), [
                'id_modalidad_especial' => $this->adultoId,
            ]);

        $response->assertRedirect(route('taller.cursos.show', $cursoId));

        $carta = $this->cartaDe($cursoId);
        $this->assertNotNull($carta);
        $this->assertSame($cartaId, (int) $carta->id_requisito);
        $this->assertFalse((bool) $carta->obligatorio);

        $response = $this->actingAs($this->admin)
            ->withSession($this->sesionBase())
            ->put(route('taller.cursos.update', $cursoId), [
                'id_modalidad_especial' => $this->menorId,
            ]);

        $response->assertRedirect(route('taller.cursos.show', $cursoId));

        $carta = $this->cartaDe($cursoId);
        $this->assertNotNull($carta);
        $this->assertSame($cartaId, (int) $carta->id_requisito);
        $this->assertTrue((bool) $carta->obligatorio);
    }

    private function sesionBase(): array
    {
        return [
            'profile_id' => 1,
            'session_token' => $this->admin->session_token,
        ];
    }

    private function sesionWizard(CursoBorrador $borrador): array
    {
        return array_merge($this->sesionBase(), [
            'wizard_curso' => ['token' => $borrador->token],
        ]);
    }

    private function crearBorrador(int $modalidadEspecialId): CursoBorrador
    {
        return CursoBorrador::create([
            'user_id' => 1,
            'token' => Str::random(24),
            'paso_actual' => 5,
            'payload' => [
                'paso1' => [
                    'nombre' => 'Curso borrador '.Str::random(8),
                    'descripcion' => 'Curso creado por la prueba de carta de permiso.',
                    'id_modalidad' => 1,
                    'id_actividad_formativa' => 1,
                    'id_aspecto' => 1,
                    'id_modalidad_especial' => $modalidadEspecialId,
                ],
                'paso2' => [
                    'id_persona' => 1,
                    'fecha_inicio' => '2026-11-02',
                    'fecha_fin' => '2026-11-06',
                    'correlativo' => random_int(100000, 999999),
                    'anio' => 2026,
                    'duracion' => 1,
                    'horas' => 4,
                    'cantidad_cupos' => 10,
                    'direccion_corta' => 'Sede de prueba',
                    'es_nacional' => 1,
                ],
                'contenidos' => [],
                'requisitos' => [],
            ],
        ]);
    }

    private function guardarRequisitosEnBorrador(CursoBorrador $borrador, array $requisitos): void
    {
        $payload = $borrador->payload;
        $payload['requisitos'] = $requisitos;
        $borrador->update(['payload' => $payload]);
    }

    private function crearCurso(int $modalidadEspecialId): int
    {
        $cursoId = (int) DB::table('taller.cursos')->insertGetId([
            'codigo' => 'TST-'.Str::upper(Str::random(10)),
            'nombre' => 'Curso de prueba '.Str::random(8),
            'id_modalidad' => 1,
            'id_actividad_formativa' => 1,
            'id_aspecto' => 1,
            'id_modalidad_especial' => $modalidadEspecialId,
            'id_persona' => 1,
            'descripcion' => 'Curso creado por la prueba de carta de permiso.',
            'trimestre' => 4,
            'correlativo' => 9999,
            'anio' => 2026,
            'duracion' => 1,
            'horas' => 4,
            'cantidad_cupos' => '10',
            'fecha_inicio' => '2026-11-02',
            'fecha_fin' => '2026-11-06',
            'es_nacional' => true,
            'direccion_corta' => 'Sede de prueba',
            'creado_por' => 1,
            'creado_en' => now(),
            'actualizado_en' => now(),
        ], 'id_curso');

        DB::table('taller.curso_estado')->insert([
            'id_curso' => $cursoId,
            'id_estado' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $cursoId;
    }

    private function crearCarta(int $cursoId, bool $obligatorio): int
    {
        $definicion = CursoRequisito::requisitoCartaPermiso();

        return (int) DB::table('taller.curso_requisitos')->insertGetId([
            'id_curso' => $cursoId,
            'tipo' => $definicion['tipo'],
            'titulo' => $definicion['titulo'],
            'descripcion' => $definicion['descripcion'],
            'obligatorio' => $obligatorio,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_requisito');
    }

    private function crearRespuestaCarta(int $cursoId, int $cartaId): void
    {
        $inscripcionId = (int) DB::table('taller.inscripciones')->insertGetId([
            'id_curso' => $cursoId,
            'id_persona' => 1,
            'fecha_inscripcion' => now()->toDateString(),
            'estado' => Inscripcion::ESTADO_APROBADO,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_inscripcion');

        DB::table('taller.inscripcion_respuestas')->insert([
            'id_inscripcion' => $inscripcionId,
            'id_requisito' => $cartaId,
            'ruta_archivo' => 'test/carta.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function cartaDe(int $cursoId): ?object
    {
        return DB::table('taller.curso_requisitos')
            ->where('id_curso', $cursoId)
            ->where('tipo', 'documento')
            ->where('titulo', CursoRequisito::CARTA_PERMISO_TITULO)
            ->first();
    }

    private function cursoPorNombre(string $nombre): ?object
    {
        return DB::table('taller.cursos')->where('nombre', $nombre)->first();
    }

    private function buscarCartaEnArreglo(array $requisitos): ?array
    {
        foreach ($requisitos as $requisito) {
            if (is_array($requisito) && CursoRequisito::esCartaPermisoDocumento($requisito)) {
                return $requisito;
            }
        }

        return null;
    }
}
