<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use App\Enums\EstadoCurso;
use App\Services\Security\Autorizacion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\CursoBorrador;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Entities\ObservacionCurso;
use Modules\Taller\Services\CertificadoCodigo;
use Modules\Taller\Services\CertificadoConfig;
use Modules\Taller\Services\CertificadoService;
use Modules\Taller\Services\Contracts\QrGeneratorInterface;
use Smalot\PdfParser\Parser;
use Tests\TestCase;

/**
 * Cobertura de autorización e IDOR en el módulo Taller.
 */
class AutorizacionTallerSeguridadTest extends TestCase
{
    use DatabaseTransactions;

    private User $coordinador;

    private User $otroCoordinador;

    private User $facilitadorDuenio;

    private User $facilitadorAjeno;

    private User $participante;

    protected function setUp(): void
    {
        parent::setUp();

        $usuarios = User::where('id', '>', 1)
            ->where('active', 1)
            ->whereHas('personalData')
            ->orderBy('id')
            ->take(5)
            ->get();

        $this->assertGreaterThanOrEqual(5, $usuarios->count(), 'Se necesitan al menos 5 usuarios activos con datos personales.');

        $this->coordinador = $usuarios[0];
        $this->otroCoordinador = $usuarios[1];
        $this->facilitadorDuenio = $usuarios[2];
        $this->facilitadorAjeno = $usuarios[3];
        $this->participante = $usuarios[4];

        $this->soloConPerfiles($this->coordinador, Perfil::COORDINADOR);
        $this->soloConPerfiles($this->otroCoordinador, Perfil::COORDINADOR);
        $this->soloConPerfiles($this->facilitadorDuenio, Perfil::FACILITADOR);
        $this->soloConPerfiles($this->facilitadorAjeno, Perfil::FACILITADOR);
        $this->soloConPerfiles($this->participante, Perfil::PARTICIPANTE);
    }

    public function test_ver_plantilla_y_firma_de_curso_exigen_gestor_o_facilitador_asignado(): void
    {
        $curso = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::EDICION);
        $this->inscribir($curso, $this->participante, Inscripcion::ESTADO_APROBADO);

        $dirCursos = CertificadoConfig::dirCursos();
        if (! is_dir($dirCursos)) {
            mkdir($dirCursos, 0755, true);
        }

        $pngBytes = $this->pngDePrueba();
        $plantillaPath = $dirCursos.'/'.$curso->id_curso.'.png';
        $firmaPath = $dirCursos.'/'.$curso->id_curso.'_firma.png';
        file_put_contents($plantillaPath, $pngBytes);
        file_put_contents($firmaPath, $pngBytes);

        try {
            $urlPlantilla = route('taller.certificados.plantilla.ver', ['tipo' => 'curso', 'ref' => (string) $curso->id_curso]);
            $urlFirma = route('taller.certificados.firma.ver', ['tipo' => 'curso', 'ref' => (string) $curso->id_curso]);

            // Inscrito y facilitador ajeno: 403
            $this->como($this->participante)->get($urlPlantilla)->assertForbidden();
            $this->como($this->participante)->get($urlFirma)->assertForbidden();
            $this->como($this->facilitadorAjeno)->get($urlPlantilla)->assertForbidden();
            $this->como($this->facilitadorAjeno)->get($urlFirma)->assertForbidden();

            // Facilitador asignado y coordinador: 200
            $this->como($this->facilitadorDuenio)->get($urlPlantilla)->assertOk();
            $this->como($this->facilitadorDuenio)->get($urlFirma)->assertOk();
            $this->como($this->coordinador)->get($urlPlantilla)->assertOk();
            $this->como($this->coordinador)->get($urlFirma)->assertOk();
        } finally {
            @unlink($plantillaPath);
            @unlink($firmaPath);
        }
    }

    public function test_ver_plantilla_y_firma_de_wizard_exigen_ser_duenio_del_borrador(): void
    {
        $borrador = CursoBorrador::create([
            'user_id' => $this->coordinador->id,
            'token' => Str::random(24),
            'paso_actual' => 4,
            'payload' => [],
        ]);

        $dirWizard = CertificadoConfig::dirWizard($borrador->token);
        mkdir($dirWizard, 0755, true);

        $pngBytes = $this->pngDePrueba();
        $plantillaPath = $dirWizard.'/plantilla.png';
        $firmaPath = $dirWizard.'/firma.png';
        file_put_contents($plantillaPath, $pngBytes);
        file_put_contents($firmaPath, $pngBytes);

        try {
            $urlPlantilla = route('taller.certificados.plantilla.ver', ['tipo' => 'wizard', 'ref' => $borrador->token]);
            $urlFirma = route('taller.certificados.firma.ver', ['tipo' => 'wizard', 'ref' => $borrador->token]);

            // Otro coordinador con CREAR_CURSO pero que no es dueño del borrador: 403
            $this->como($this->otroCoordinador)->get($urlPlantilla)->assertForbidden();
            $this->como($this->otroCoordinador)->get($urlFirma)->assertForbidden();

            // Dueño del borrador: 200
            $this->como($this->coordinador)->get($urlPlantilla)->assertOk();
            $this->como($this->coordinador)->get($urlFirma)->assertOk();
        } finally {
            @unlink($plantillaPath);
            @unlink($firmaPath);
            @rmdir($dirWizard);
        }
    }

    public function test_probar_certificado_bloquea_inscritos_y_marca_vista_previa(): void
    {
        $curso = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::EN_CURSO);
        $this->inscribir($curso, $this->participante, Inscripcion::ESTADO_APROBADO);

        $pngBytes = $this->pngDePrueba();
        $fakeQr = new class($pngBytes) implements QrGeneratorInterface
        {
            public ?string $capturado = null;

            public function __construct(private string $pngBytes) {}

            public function generar(string $contenido, string $destino, int $tamPx = 300): void
            {
                $this->capturado = $contenido;
                file_put_contents($destino, $this->pngBytes);
            }
        };

        $this->app->instance(CertificadoService::class, new CertificadoService($fakeQr));

        // Un participante inscrito aprobado y un facilitador ajeno reciben 403
        $this->como($this->participante)
            ->get(route('taller.certificados.probar', $curso->id_curso))
            ->assertForbidden();

        $this->como($this->facilitadorAjeno)
            ->get(route('taller.certificados.probar', $curso->id_curso))
            ->assertForbidden();

        // El coordinador genera una vista previa distinguible y sin código/QR válidos
        $respuesta = $this->como($this->coordinador)
            ->get(route('taller.certificados.probar', $curso->id_curso));

        $respuesta->assertOk();
        $respuesta->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame('VISTA PREVIA - SIN VALIDEZ OFICIAL', $fakeQr->capturado);

        $textoPdf = (new Parser)->parseContent($respuesta->getContent())->getText();
        $codigoReal = CertificadoCodigo::emitir($curso, $this->coordinador->personalData);

        $this->assertStringContainsString('VISTA PREVIA', $textoPdf);
        $this->assertStringContainsString(CertificadoService::CODIGO_VISTA_PREVIA, $textoPdf);
        $this->assertStringNotContainsString($codigoReal, $textoPdf);

        // El facilitador asignado también puede previsualizar
        $this->como($this->facilitadorDuenio)
            ->get(route('taller.certificados.probar', $curso->id_curso))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_detalle_de_curso_aplica_visibilidad_y_oculta_datos_internos(): void
    {
        $cursoBorrador = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::BORRADOR);
        ObservacionCurso::create([
            'id_curso' => $cursoBorrador->id_curso,
            'observacion' => 'Nota interna confidencial de coordinacion',
            'creado_por' => $this->coordinador->id,
        ]);

        // Borrador: bloqueado para participante y facilitador ajeno; visible para dueño y coordinador
        $this->como($this->participante)->get(route('taller.cursos.show', $cursoBorrador->id_curso))->assertForbidden();
        $this->como($this->facilitadorAjeno)->get(route('taller.cursos.show', $cursoBorrador->id_curso))->assertForbidden();

        $respDuenio = $this->como($this->facilitadorDuenio)
            ->get(route('taller.cursos.show', $cursoBorrador->id_curso));
        $respDuenio->assertOk()
            ->assertSee('Nota interna confidencial de coordinacion', false);
        $this->assertCount(1, $respDuenio->viewData('curso')->observaciones);

        $respCoord = $this->como($this->coordinador)
            ->get(route('taller.cursos.show', $cursoBorrador->id_curso));
        $respCoord->assertOk()
            ->assertSee('Nota interna confidencial de coordinacion', false);
        $this->assertCount(1, $respCoord->viewData('curso')->observaciones);

        // Cerrado: bloqueado si no está inscrito, visible al inscribirse pero sin observaciones internas
        $cursoCerrado = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::CERRADO);
        ObservacionCurso::create([
            'id_curso' => $cursoCerrado->id_curso,
            'observacion' => 'Comentario interno del cierre',
            'creado_por' => $this->coordinador->id,
        ]);

        $this->como($this->participante)->get(route('taller.cursos.show', $cursoCerrado->id_curso))->assertForbidden();

        $this->inscribir($cursoCerrado, $this->participante, Inscripcion::ESTADO_APROBADO);
        $respInscrito = $this->como($this->participante)
            ->get(route('taller.cursos.show', $cursoCerrado->id_curso));
        $respInscrito->assertOk()
            ->assertDontSee('Comentario interno del cierre', false);
        $this->assertCount(0, $respInscrito->viewData('curso')->observaciones);

        // Público en inscripción con alcance nacional: visible para participante pero sin observaciones internas
        $cursoPublico = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::INSCRIPCION, true);
        ObservacionCurso::create([
            'id_curso' => $cursoPublico->id_curso,
            'observacion' => 'Observacion solo para gestion',
            'creado_por' => $this->coordinador->id,
        ]);

        $respPublico = $this->como($this->participante)
            ->get(route('taller.cursos.show', $cursoPublico->id_curso));
        $respPublico->assertOk()
            ->assertDontSee('Observacion solo para gestion', false);
        $this->assertCount(0, $respPublico->viewData('curso')->observaciones);

        // Curso regional en inscripción: bloqueado si el estado del participante no coincide
        $estadoOtro = DB::table('comun.estados')
            ->when($this->participante->personalData->id_estado, fn ($q, $est) => $q->where('id', '!=', $est))
            ->value('id');

        if ($estadoOtro !== null) {
            $cursoRegional = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::INSCRIPCION, false);
            DB::table('taller.curso_localidades')->insert([
                'id_curso' => $cursoRegional->id_curso,
                'id_estado' => $estadoOtro,
            ]);

            $this->como($this->participante)
                ->get(route('taller.cursos.show', $cursoRegional->id_curso))
                ->assertForbidden();
        }
    }

    public function test_crear_requisitos_exige_pertenencia_o_gestor_y_estado_editable(): void
    {
        $cursoFinalizado = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::FINALIZADO);
        $cursoEditable = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::EDICION);

        // Coordinador sobre curso en estado no editable (FINALIZADO): 403
        $this->como($this->coordinador)
            ->get(route('taller.cursos.requisitos.create', $cursoFinalizado->id_curso))
            ->assertForbidden();
        $this->como($this->coordinador)
            ->post(route('taller.cursos.requisitos.store', $cursoFinalizado->id_curso), [
                'requisitos' => [['tipo' => 'pregunta', 'titulo' => 'No permitido']],
            ])
            ->assertForbidden();

        // Conceder CREAR_CURSO al perfil Facilitador temporalmente para verificar pertenencia
        $permisoCrearId = DB::table('security.permissions as p')
            ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
            ->where('pr.codigo', 'taller.cursos.index')
            ->where('p.slug', 'create_course')
            ->value('p.id');

        $this->assertNotNull($permisoCrearId);
        DB::table('security.profile_permissions')->insertOrIgnore([
            'profile_id' => Perfil::FACILITADOR,
            'permission_id' => $permisoCrearId,
        ]);
        app(Autorizacion::class)->invalidar();

        // Facilitador ajeno con CREAR_CURSO pero sin pertenencia: 403
        $this->como($this->facilitadorAjeno)
            ->get(route('taller.cursos.requisitos.create', $cursoEditable->id_curso))
            ->assertForbidden();
        $this->como($this->facilitadorAjeno)
            ->post(route('taller.cursos.requisitos.store', $cursoEditable->id_curso), [
                'requisitos' => [['tipo' => 'pregunta', 'titulo' => 'Inyectado ajeno']],
            ])
            ->assertForbidden();

        // Facilitador dueño con CREAR_CURSO en estado editable: 200
        $this->como($this->facilitadorDuenio)
            ->get(route('taller.cursos.requisitos.create', $cursoEditable->id_curso))
            ->assertOk();

        // Coordinador en estado editable: puede crear requisitos
        $this->como($this->coordinador)
            ->post(route('taller.cursos.requisitos.store', $cursoEditable->id_curso), [
                'requisitos' => [['tipo' => 'pregunta', 'titulo' => '¿Experiencia previa?', 'obligatorio' => 1]],
            ])
            ->assertRedirect(route('taller.cursos.index'));

        $this->assertDatabaseHas('taller.curso_requisitos', [
            'id_curso' => $cursoEditable->id_curso,
            'titulo' => '¿Experiencia previa?',
        ]);
    }

    public function test_update_curso_valida_estado_editable_por_rol(): void
    {
        // En EN_CURSO: facilitador bloqueado (403), coordinador permitido
        $cursoEnCurso = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::EN_CURSO);
        $nombreOriginal = $cursoEnCurso->nombre;

        $this->como($this->facilitadorDuenio)
            ->put(route('taller.cursos.update', $cursoEnCurso->id_curso), [
                'nombre' => 'Nombre alterado por facilitador',
            ])
            ->assertForbidden();
        $this->assertSame($nombreOriginal, $cursoEnCurso->fresh()->nombre);

        $this->como($this->coordinador)
            ->put(route('taller.cursos.update', $cursoEnCurso->id_curso), [
                'nombre' => 'Nombre actualizado por coordinación',
            ])
            ->assertRedirect(route('taller.cursos.show', $cursoEnCurso->id_curso));
        $this->assertSame('Nombre actualizado por coordinación', $cursoEnCurso->fresh()->nombre);

        // En FINALIZADO: tanto coordinador como facilitador bloqueados (403)
        $cursoFinalizado = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::FINALIZADO);

        $this->como($this->coordinador)
            ->put(route('taller.cursos.update', $cursoFinalizado->id_curso), [
                'nombre' => 'Intento en finalizado',
            ])
            ->assertForbidden();
        $this->assertNotSame('Intento en finalizado', $cursoFinalizado->fresh()->nombre);

        // En EDICION: facilitador asignado permitido
        $cursoEdicion = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::EDICION);
        $this->como($this->facilitadorDuenio)
            ->put(route('taller.cursos.update', $cursoEdicion->id_curso), [
                'nombre' => 'Nombre editado por facilitador',
            ])
            ->assertRedirect(route('taller.cursos.show', $cursoEdicion->id_curso));
        $this->assertSame('Nombre editado por facilitador', $cursoEdicion->fresh()->nombre);
    }

    public function test_finalizar_edicion_exige_policy_cambiar_estado_ademas_de_id_persona(): void
    {
        $curso = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::EDICION);

        // Coordinador que no es el facilitador asignado (id_persona distinto): 403
        $this->como($this->coordinador)
            ->putJson(route('taller.cursos.finalizarEdicion', $curso->id_curso))
            ->assertForbidden();

        // Usuario que figura en id_persona pero perdió su perfil/permiso de Facilitador: 403
        $this->soloConPerfiles($this->facilitadorDuenio, Perfil::PARTICIPANTE);

        $this->como($this->facilitadorDuenio)
            ->putJson(route('taller.cursos.finalizarEdicion', $curso->id_curso))
            ->assertForbidden();
        $this->assertSame(EstadoCurso::EDICION->value, (int) $curso->fresh()->estado_actual->id_estado);

        // Con perfil de Facilitador vigente y siendo id_persona: 200 y avanza a APROBACION
        $this->soloConPerfiles($this->facilitadorDuenio, Perfil::FACILITADOR);

        $this->como($this->facilitadorDuenio)
            ->putJson(route('taller.cursos.finalizarEdicion', $curso->id_curso))
            ->assertOk()
            ->assertJson(['success' => true]);
        $this->assertSame(EstadoCurso::APROBACION->value, (int) $curso->fresh()->estado_actual->id_estado);
    }

    public function test_ver_respuestas_rechaza_inscripcion_de_otro_curso(): void
    {
        $cursoA = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::INSCRIPCION);
        $cursoB = $this->crearCurso($this->facilitadorDuenio, EstadoCurso::INSCRIPCION);
        $inscripcionB = $this->inscribir($cursoB, $this->participante, Inscripcion::ESTADO_POSTULADO);

        // Inscripción de curso B solicitada bajo la ruta de curso A: 404
        $this->como($this->coordinador)
            ->get(route('taller.inscripciones.respuestas', [
                'curso' => $cursoA->id_curso,
                'inscripcion' => $inscripcionB->id_inscripcion,
            ]))
            ->assertNotFound();

        // Con el curso correcto: 200
        $this->como($this->coordinador)
            ->get(route('taller.inscripciones.respuestas', [
                'curso' => $cursoB->id_curso,
                'inscripcion' => $inscripcionB->id_inscripcion,
            ]))
            ->assertOk();
    }

    public function test_ver_foto_wizard_exige_que_el_borrador_pertenezca_al_usuario(): void
    {
        $token = Str::random(24);
        $borrador = CursoBorrador::create([
            'user_id' => $this->coordinador->id,
            'token' => $token,
            'paso_actual' => 1,
            'payload' => [],
            'foto_path' => 'wizard/'.$token.'/foto.png',
        ]);

        $dirWizard = storage_path('app/public/Certificados/wizard/'.$token);
        mkdir($dirWizard, 0755, true);
        $fotoPath = $dirWizard.'/foto.png';
        file_put_contents($fotoPath, $this->pngDePrueba());

        try {
            $url = route('taller.cursos.wizard.foto', ['token' => $borrador->token]);

            // Otro usuario con CREAR_CURSO pero distinto dueño: 403
            $this->como($this->otroCoordinador)->get($url)->assertForbidden();

            // Dueño del borrador: 200
            $this->como($this->coordinador)
                ->get($url)
                ->assertOk()
                ->assertHeader('Content-Type', 'image/png');
        } finally {
            @unlink($fotoPath);
            @rmdir($dirWizard);
        }
    }

    public function test_ruta_publica_qr_asistencia_tiene_throttle_de_30_por_minuto(): void
    {
        $ruta = Route::getRoutes()->getByName('taller.asistencia.qr-image');
        $this->assertNotNull($ruta);

        $middlewares = array_map('strval', $ruta->gatherMiddleware());
        $tieneThrottle = collect($middlewares)->contains(
            fn (string $m) => str_contains($m, 'throttle:30,1') || str_contains($m, 'ThrottleRequests:30,1')
        );
        $this->assertTrue($tieneThrottle, 'La ruta pública de QR de asistencia debe incluir middleware throttle:30,1.');

        $ipPrueba = '10.99.99.'.random_int(10, 240);
        for ($i = 0; $i < 30; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => $ipPrueba])
                ->get(route('taller.asistencia.qr-image', 'token-inexistente-prueba'))
                ->assertNotFound();
        }

        $this->withServerVariables(['REMOTE_ADDR' => $ipPrueba])
            ->get(route('taller.asistencia.qr-image', 'token-inexistente-prueba'))
            ->assertStatus(429);
    }

    private function como(User $user)
    {
        return $this->actingAs($user)->withSession([
            'session_token' => $user->session_token,
        ]);
    }

    private function crearCurso(User $facilitador, EstadoCurso $estado, bool $esNacional = true): Curso
    {
        $curso = Curso::withoutEvents(function () use ($facilitador, $esNacional) {
            $c = new Curso;
            $c->nombre = 'Curso Seguridad '.Str::random(6);
            $c->codigo = 'LAB-SEG'.random_int(100, 999).'-2026';
            $c->id_persona = $facilitador->personalData->id_persona;
            $c->id_actividad_formativa = 1;
            $c->id_aspecto = 1;
            $c->id_modalidad = 1;
            $c->id_modalidad_especial = 1;
            $c->cantidad_cupos = 20;
            $c->es_nacional = $esNacional;
            $c->fecha_inicio = now()->addWeek();
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

    private function inscribir(Curso $curso, User $participante, string $estado): Inscripcion
    {
        return Inscripcion::create([
            'id_curso' => $curso->id_curso,
            'id_persona' => $participante->personalData->id_persona,
            'user_id' => $participante->id,
            'estado' => $estado,
            'fecha_inscripcion' => now(),
        ]);
    }

    private function pngDePrueba(): string
    {
        $archivo = UploadedFile::fake()->image('muestra.png', 20, 20);
        $contenido = (string) file_get_contents($archivo->getRealPath());

        return $contenido;
    }
}
