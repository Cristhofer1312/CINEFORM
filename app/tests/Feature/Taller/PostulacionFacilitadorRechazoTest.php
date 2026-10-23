<?php

namespace Tests\Feature\Taller;

use App\Constants\Perfil;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\PostulacionFacilitador;
use Modules\Taller\Entities\PostulacionRespuesta;
use Modules\Taller\Entities\RequisitoFacilitador;
use Tests\TestCase;

/**
 * RF-56: al rechazar una postulación de facilitador, los documentos adjuntos
 * se conservan como evidencia (no se borran del disco ni de la BD) y el
 * motivo queda registrado.
 */
class PostulacionFacilitadorRechazoTest extends TestCase
{
    use DatabaseTransactions;

    private User $coordinador;

    private User $postulante;

    private string $directorio;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $this->coordinador = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::COORDINADOR);
        })->where('id', '>', 1)->first() ?? User::findOrFail(2);

        $this->postulante = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->where('id', '>', 1)
            ->whereDoesntHave('perfiles', function ($q) {
                $q->whereIn('security.profiles.id', [Perfil::ADMINISTRADOR, Perfil::FACILITADOR, Perfil::COORDINADOR]);
            })
            ->whereHas('personalData')
            ->first() ?? User::findOrFail(7);

        $this->directorio = storage_path('app/public/postulaciones_facilitador/test-rf56-'.uniqid());
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directorio);

        parent::tearDown();
    }

    public function test_rechazar_postulacion_conserva_los_documentos_adjuntos(): void
    {
        $persona = $this->postulante->personalData;

        $postulacion = PostulacionFacilitador::create([
            'id_persona' => $persona->id_persona,
            'estado' => PostulacionFacilitador::ESTADO_PENDIENTE,
        ]);

        // Documento real en disco con la misma ruta relativa que genera postular()
        File::ensureDirectoryExists($this->directorio);
        $rutaRelativa = str_replace(storage_path('app/public/'), '', $this->directorio).'/hoja-de-vida.pdf';
        $rutaAbsoluta = storage_path('app/public/'.$rutaRelativa);
        file_put_contents($rutaAbsoluta, '%PDF-1.4 contenido de prueba');

        $requisito = RequisitoFacilitador::create([
            'titulo' => 'Hoja de vida (prueba RF-56)',
            'tipo' => 'documento',
            'descripcion' => 'Requisito de prueba',
            'obligatorio' => true,
            'orden' => 999,
            'activo' => true,
            'creado_por' => $this->coordinador->id,
        ]);

        $respuesta = PostulacionRespuesta::create([
            'id_postulacion' => $postulacion->id_postulacion,
            'id_requisito_facilitador' => $requisito->id_requisito_facilitador,
            'ruta_archivo' => $rutaRelativa,
        ]);

        $motivo = 'Faltan certificados de experiencia en dirección.';

        $response = $this->actingAs($this->coordinador)
            ->withSession([
                'profile_id' => Perfil::COORDINADOR,
                'session_token' => $this->coordinador->session_token,
            ])
            ->post(route('taller.postulacion-facilitador.rechazar', ['postulacion' => $postulacion->crypt_id]), [
                'motivo_rechazo' => $motivo,
            ]);

        $response->assertSessionHas('success', 'Postulación rechazada. El participante puede corregir y volver a postularse.');

        // La postulación queda rechazada con su motivo
        $postulacion->refresh();
        $this->assertSame(PostulacionFacilitador::ESTADO_RECHAZADA, $postulacion->estado);
        $this->assertSame($motivo, $postulacion->motivo_rechazo);

        // La respuesta documental sigue en BD (no queda huérfana)
        $this->assertDatabaseHas('taller.postulacion_respuestas', [
            'id_respuesta' => $respuesta->id_respuesta,
            'id_postulacion' => $postulacion->id_postulacion,
            'ruta_archivo' => $rutaRelativa,
        ]);

        // El archivo sigue en disco como evidencia
        $this->assertFileExists($rutaAbsoluta);
    }
}
