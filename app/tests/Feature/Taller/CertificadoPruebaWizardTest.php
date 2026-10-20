<?php

namespace Tests\Feature\Taller;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Modules\Security\Entities\User;
use Modules\Taller\Services\CertificadoConfig;
use Modules\Taller\Services\CertificadoService;
use Tests\TestCase;

/**
 * La vista previa del paso 4 debe usar las imágenes recién elegidas en el
 * editor, no solo las que ya están guardadas en disco.
 */
class CertificadoPruebaWizardTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::findOrFail(1);
        $this->assertNotNull(
            $this->admin->personalData,
            'El usuario de prueba necesita datos personales para generar el certificado.'
        );
    }

    public function test_la_prueba_usa_la_plantilla_y_la_firma_recien_elegidas(): void
    {
        $capturado = [];

        $servicio = \Mockery::mock(CertificadoService::class)->makePartial();
        $servicio->shouldReceive('generarPdfCon')
            ->once()
            ->andReturnUsing(function (...$argumentos) use (&$capturado) {
                $capturado = $argumentos;

                return '%PDF-1.4 prueba';
            });

        $this->app->instance(CertificadoService::class, $servicio);

        $response = $this->actingAs($this->admin)
            ->withSession($this->sesionWizard())
            ->post(route('taller.certificados.prueba.wizard'), [
                'coords' => json_encode(CertificadoConfig::coordsBase()),
                'plantilla' => UploadedFile::fake()->image('plantilla.png', 1600, 1131),
                'firma' => UploadedFile::fake()->image('firma.png', 600, 200),
            ]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');

        [, , , , $plantillaPath, $firmaPath, $hayPlantilla] = $capturado;

        $this->assertTrue($hayPlantilla, 'La plantilla elegida no llegó como imagen al PDF.');
        $this->assertNotNull($plantillaPath);
        $this->assertFileExists($plantillaPath);
        $this->assertNotNull($firmaPath);
        $this->assertFileExists($firmaPath);
    }

    public function test_la_prueba_rechaza_archivos_que_no_son_imagen(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession($this->sesionWizard())
            ->post(route('taller.certificados.prueba.wizard'), [
                'coords' => json_encode(CertificadoConfig::coordsBase()),
                'plantilla' => UploadedFile::fake()->create('plantilla.txt', 10, 'text/plain'),
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
        $this->assertNotEmpty($response->json('error'));
    }

    public function test_la_prueba_avisa_si_la_sesion_del_wizard_expiro(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession([
                'profile_id' => 1,
                'session_token' => $this->admin->session_token,
            ])
            ->post(route('taller.certificados.prueba.wizard'), [
                'coords' => json_encode(CertificadoConfig::coordsBase()),
            ]);

        $response->assertStatus(419);
        $response->assertJsonPath('ok', false);
        $this->assertStringContainsString('expi', (string) $response->json('error'));
    }

    private function sesionWizard(): array
    {
        return [
            'profile_id' => 1,
            'session_token' => $this->admin->session_token,
            'wizard_curso' => ['token' => Str::random(24)],
        ];
    }
}
