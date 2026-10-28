<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\CursoRequisito;
use Modules\Taller\Entities\Inscripcion;
use Tests\TestCase;

/**
 * Valida el flujo P2 de postulación e inscripción:
 * - UX-10: Carga sin error 500 para nuevos postulantes ($inscripcionExistente).
 * - UX-11: Permite corrección y reenvío de postulaciones en estado rechazado.
 */
class InscripcionFlujoTest extends TestCase
{
    use DatabaseTransactions;

    private User $participante;

    private Curso $curso;

    private CursoRequisito $requisitoPregunta;

    protected function setUp(): void
    {
        parent::setUp();

        // Participante (ID 7)
        $this->participante = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->where('id', '>', 1)->first() ?? User::findOrFail(7);

        $this->assertNotNull($this->participante->personalData, 'Participante debe tener datos personales.');

        // Crear o tomar un curso en estado INSCRIPCION (6)
        $idPersonaFacilitador = optional(User::findOrFail(1)->personalData)->id_persona ?? 1;

        $this->curso = Curso::withoutEvents(function () use ($idPersonaFacilitador) {
            $c = new Curso;
            $c->nombre = 'Curso Test Flujo Inscripcion';
            $c->codigo = 'LAB-TEST-INS-2026-'.random_int(1000, 9999);
            $c->id_persona = $idPersonaFacilitador;
            $c->id_actividad_formativa = 1;
            $c->id_aspecto = 1;
            $c->id_modalidad = 1;
            $c->id_modalidad_especial = 1;
            $c->fecha_inicio = now();
            $c->fecha_fin = now()->addMonths(1);
            $c->cantidad_cupos = 20;
            $c->save();

            return $c;
        });

        // Registrar estado INSCRIPCION (6) en taller.curso_estado
        DB::table('taller.curso_estado')->insert([
            'id_curso' => $this->curso->id_curso,
            'id_estado' => EstadoCurso::INSCRIPCION->value,
            'motivo' => 'Apertura de inscripciones para pruebas',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Crear un requisito de tipo pregunta obligatoria
        $this->requisitoPregunta = CursoRequisito::create([
            'id_curso' => $this->curso->id_curso,
            'titulo' => '¿Cuál es tu motivación?',
            'descripcion' => 'Explica brevemente tu interés.',
            'tipo' => 'pregunta',
            'obligatorio' => true,
        ]);
    }

    public function test_pantalla_inscribirse_retorna_200_para_participante_sin_postulacion_previa(): void
    {
        // UX-10: Antes fallaba con 500 por Undefined variable $inscripcionExistente
        $response = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->get(route('taller.inscripciones.create', $this->curso->id_curso));

        $response->assertStatus(200);
        $response->assertSee('Completar Postulación');
        $response->assertSee('¿Cuál es tu motivación?');
    }

    public function test_participante_puede_enviar_postulacion_y_queda_en_estado_postulado(): void
    {
        $response = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->post(route('taller.inscripciones.procesar', $this->curso->id_curso), [
                'req_'.$this->requisitoPregunta->id_requisito => 'Mi motivación es aprender producción audiovisual.',
            ]);

        $response->assertRedirect(route('taller.cursos.show', $this->curso->id_curso));
        $response->assertSessionHas('success');

        $inscripcion = Inscripcion::where('id_curso', $this->curso->id_curso)
            ->where('id_persona', $this->participante->personalData->id_persona)
            ->first();

        $this->assertNotNull($inscripcion);
        $this->assertSame(Inscripcion::ESTADO_POSTULADO, $inscripcion->estado);

        $respuesta = $inscripcion->respuestas()->where('id_requisito', $this->requisitoPregunta->id_requisito)->first();
        $this->assertNotNull($respuesta);
        $this->assertSame('Mi motivación es aprender producción audiovisual.', $respuesta->respuesta_texto);
    }

    public function test_participante_con_postulacion_rechazada_puede_acceder_a_corregir(): void
    {
        // UX-11: Crear inscripción rechazada con observaciones
        $inscripcion = Inscripcion::create([
            'id_curso' => $this->curso->id_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'fecha_inscripcion' => Carbon::now()->subDays(2),
            'estado' => Inscripcion::ESTADO_RECHAZADO,
            'motivo_estado' => 'La respuesta es muy breve, amplía tu explicación.',
        ]);

        $inscripcion->respuestas()->create([
            'id_requisito' => $this->requisitoPregunta->id_requisito,
            'respuesta_texto' => 'Aprender.',
        ]);

        // Antes redirigía hacia atrás con mensaje de error impidiendo corregir
        $response = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->get(route('taller.inscripciones.create', $this->curso->id_curso));

        $response->assertStatus(200);
        $response->assertSee('Corregir Postulación');
        $response->assertSee('Observaciones en tu postulación anterior');
        $response->assertSee('La respuesta es muy breve, amplía tu explicación.');
        $response->assertSee('Aprender.'); // Precarga de respuesta previa
    }

    public function test_participante_puede_actualizar_postulacion_rechazada_y_vuelve_a_postulado(): void
    {
        $inscripcion = Inscripcion::create([
            'id_curso' => $this->curso->id_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'fecha_inscripcion' => Carbon::now()->subDays(2),
            'estado' => Inscripcion::ESTADO_RECHAZADO,
            'motivo_estado' => 'Amplía tu explicación.',
        ]);

        $inscripcion->respuestas()->create([
            'id_requisito' => $this->requisitoPregunta->id_requisito,
            'respuesta_texto' => 'Respuesta inicial.',
        ]);

        $response = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->post(route('taller.inscripciones.procesar', $this->curso->id_curso), [
                'req_'.$this->requisitoPregunta->id_requisito => 'Respuesta corregida y detallada.',
            ]);

        $response->assertRedirect(route('taller.cursos.show', $this->curso->id_curso));
        $response->assertSessionHas('success', 'Tu postulación ha sido corregida y enviada nuevamente para revisión.');

        // Debe ser la misma fila de inscripción pero actualizada
        $inscripcionFresca = $inscripcion->fresh();
        $this->assertSame(Inscripcion::ESTADO_POSTULADO, $inscripcionFresca->estado);
        $this->assertNull($inscripcionFresca->motivo_estado);

        $respuesta = $inscripcionFresca->respuestas()->where('id_requisito', $this->requisitoPregunta->id_requisito)->first();
        $this->assertSame('Respuesta corregida y detallada.', $respuesta->respuesta_texto);
    }

    public function test_participante_aprobado_es_redirigido_sin_permitir_reinscribirse(): void
    {
        Inscripcion::create([
            'id_curso' => $this->curso->id_curso,
            'id_persona' => $this->participante->personalData->id_persona,
            'fecha_inscripcion' => Carbon::now()->subDays(5),
            'estado' => Inscripcion::ESTADO_APROBADO,
        ]);

        $response = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->get(route('taller.inscripciones.create', $this->curso->id_curso));

        $response->assertRedirect(route('taller.cursos.show', $this->curso->id_curso));
        $response->assertSessionHas('info');
    }

    /**
     * RNF-03 / RF-30: Rechazar extensiones no permitidas y archivos que exceden 5MB.
     */
    public function test_subida_documento_rechaza_archivo_peligroso_y_archivo_pesado(): void
    {
        Storage::fake('public');

        $reqDoc = CursoRequisito::create([
            'id_curso' => $this->curso->id_curso,
            'titulo' => 'Documento de Identidad',
            'tipo' => 'documento',
            'obligatorio' => true,
        ]);

        // 1. Archivo con extensión ejecutable/peligrosa
        $archivoPhp = UploadedFile::fake()->create('script.php', 10, 'application/x-php');

        $respPeligroso = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->post(route('taller.inscripciones.procesar', $this->curso->id_curso), [
                'req_'.$this->requisitoPregunta->id_requisito => 'Mi respuesta',
                'req_'.$reqDoc->id_requisito => $archivoPhp,
            ]);

        // Validación del requisito (RNF-03): error en el campo, sin guardar nada.
        $respPeligroso->assertSessionHasErrors('req_'.$reqDoc->id_requisito);
        $this->assertDatabaseMissing('taller.inscripcion_respuestas', [
            'id_requisito' => $reqDoc->id_requisito,
        ]);

        // 2. Archivo que supera los 5MB (5120 KB)
        $archivoPesado = UploadedFile::fake()->create('documento_gigante.pdf', 6000, 'application/pdf');

        $respPesado = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->post(route('taller.inscripciones.procesar', $this->curso->id_curso), [
                'req_'.$this->requisitoPregunta->id_requisito => 'Mi respuesta',
                'req_'.$reqDoc->id_requisito => $archivoPesado,
            ]);

        $respPesado->assertSessionHasErrors('req_'.$reqDoc->id_requisito);
    }

    /**
     * RNF-03 / RF-30: Archivo válido se almacena con UUID y limpia el previo al actualizar.
     */
    public function test_subida_documento_valido_guarda_con_nombre_uuid_y_limpia_previo(): void
    {
        Storage::fake('public');

        $reqDoc = CursoRequisito::create([
            'id_curso' => $this->curso->id_curso,
            'titulo' => 'Copia de DNI',
            'tipo' => 'documento',
            'obligatorio' => true,
        ]);

        $pdfValido = UploadedFile::fake()->create('mi_dni.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->post(route('taller.inscripciones.procesar', $this->curso->id_curso), [
                'req_'.$this->requisitoPregunta->id_requisito => 'Respuesta inicial',
                'req_'.$reqDoc->id_requisito => $pdfValido,
            ]);

        $response->assertSessionHas('success');

        $respuesta = \Modules\Taller\Entities\InscripcionRespuesta::where('id_requisito', $reqDoc->id_requisito)->first();
        $this->assertNotNull($respuesta);
        $this->assertNotNull($respuesta->ruta_archivo);

        // El nombre debe terminar en .pdf y no contener el nombre original del cliente
        $this->assertStringEndsWith('.pdf', $respuesta->ruta_archivo);
        $this->assertStringNotContainsString('mi_dni', $respuesta->ruta_archivo);
        Storage::disk('public')->assertExists($respuesta->ruta_archivo);

        $primerRuta = $respuesta->ruta_archivo;

        // Rechazar postulación para permitir corrección
        $inscripcion = $respuesta->inscripcion;
        $inscripcion->update(['estado' => Inscripcion::ESTADO_RECHAZADO]);

        // Reenviar corrección con un nuevo PDF
        $nuevoPdf = UploadedFile::fake()->create('mi_dni_corregido.pdf', 600, 'application/pdf');

        $respCorregida = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->post(route('taller.inscripciones.procesar', $this->curso->id_curso), [
                'req_'.$this->requisitoPregunta->id_requisito => 'Respuesta corregida',
                'req_'.$reqDoc->id_requisito => $nuevoPdf,
            ]);

        $respCorregida->assertSessionHas('success');

        $respuestaFresca = $respuesta->fresh();
        $this->assertNotSame($primerRuta, $respuestaFresca->ruta_archivo);

        // El archivo anterior debe haber sido eliminado del disco
        Storage::disk('public')->assertMissing($primerRuta);
        Storage::disk('public')->assertExists($respuestaFresca->ruta_archivo);
    }

    /**
     * RF-26: El participante postulado puede ver su estado en el detalle del curso
     * y retirar (cancelar) su postulación, limpiando registros y archivos.
     */
    public function test_participante_postulado_puede_ver_estado_y_retirar_postulacion(): void
    {
        Storage::fake('public');

        $reqDoc = CursoRequisito::create([
            'id_curso' => $this->curso->id_curso,
            'titulo' => 'Comprobante',
            'tipo' => 'documento',
            'obligatorio' => false,
        ]);

        $pdf = UploadedFile::fake()->create('comprobante.pdf', 200, 'application/pdf');

        // Postularse
        $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->post(route('taller.inscripciones.procesar', $this->curso->id_curso), [
                'req_'.$this->requisitoPregunta->id_requisito => 'Mi respuesta',
                'req_'.$reqDoc->id_requisito => $pdf,
            ])
            ->assertSessionHas('success');

        $inscripcion = Inscripcion::where('id_curso', $this->curso->id_curso)
            ->where('id_persona', $this->participante->personalData->id_persona)
            ->first();
        $this->assertNotNull($inscripcion);
        $this->assertTrue($inscripcion->esPostulado());

        $respuestaDoc = $inscripcion->respuestas->where('id_requisito', $reqDoc->id_requisito)->first();
        $this->assertNotNull($respuestaDoc);
        $rutaDoc = $respuestaDoc->ruta_archivo;
        Storage::disk('public')->assertExists($rutaDoc);

        // Ver detalle del curso: debe mostrar estado en revisión y botón para retirar
        $showResponse = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->get(route('taller.cursos.show', $this->curso->id_curso));

        $showResponse->assertStatus(200);
        $showResponse->assertSee('Postulación en Revisión');
        $showResponse->assertSee('Retirar Postulación');
        $showResponse->assertDontSee('Ver contenidos del programa');

        // Cancelar postulación
        $deleteResponse = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->deleteJson(route('taller.inscripciones.destroy', $inscripcion->id_inscripcion));

        $deleteResponse->assertStatus(200);
        $deleteResponse->assertJson(['success' => true]);

        // La inscripción y sus respuestas deben haber sido eliminadas
        $this->assertDatabaseMissing('taller.inscripciones', [
            'id_inscripcion' => $inscripcion->id_inscripcion,
        ]);
        $this->assertDatabaseMissing('taller.inscripcion_respuestas', [
            'id_inscripcion' => $inscripcion->id_inscripcion,
        ]);

        // El archivo debe haber sido eliminado del disco
        Storage::disk('public')->assertMissing($rutaDoc);

        // Al visitar nuevamente el curso, ya no está en revisión y puede volver a postularse
        $showAfter = $this->actingAs($this->participante)
            ->withSession([
                'profile_id' => Perfil::PARTICIPANTE,
                'session_token' => $this->participante->session_token,
            ])
            ->get(route('taller.cursos.show', $this->curso->id_curso));

        $showAfter->assertStatus(200);
        $showAfter->assertDontSee('Postulación en Revisión');
        $showAfter->assertSee('Postularme al Programa');
    }
}
