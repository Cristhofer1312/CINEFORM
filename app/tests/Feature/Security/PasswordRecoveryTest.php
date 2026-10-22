<?php

namespace Tests\Feature\Security;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Security\Entities\User;
use Tests\TestCase;

/**
 * Valida el flujo de recuperación de contraseña (RF-03):
 * - Solicitud de reset genera token SHA-256 de 64 caracteres.
 * - La base de datos acepta el token sin error de truncamiento (varchar 64).
 * - El enlace con el token plano valida el hash y permite cambiar la clave.
 */
class PasswordRecoveryTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->user = User::findOrFail(7); // Participante de prueba
    }

    public function test_solicitud_de_recuperacion_guarda_hash_de_64_caracteres_en_bd(): void
    {
        // Simular captcha en sesión
        $captchaValue = 'TESTCAP';

        $response = $this->withSession(['SET_CAPTCHA' => $captchaValue])
            ->post(route('recovery'), [
                'email' => $this->user->email,
                'captcha' => $captchaValue,
            ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $userFresco = $this->user->fresh();
        $this->assertTrue((bool) $userFresco->change_password);
        $this->assertNotEmpty($userFresco->token);
        $this->assertSame(64, strlen($userFresco->token), 'El token guardado debe tener 64 caracteres (SHA-256).');
    }

    public function test_enlace_con_token_valido_permite_acceder_al_formulario_de_cambio_de_clave(): void
    {
        $plainToken = Str::random(64);
        $hashedToken = hash('sha256', $plainToken);

        $this->user->forceFill([
            'change_password' => true,
            'token' => $hashedToken,
            'date_change_password' => Carbon::now(),
        ])->save();

        $response = $this->get(route('recovery', ['token' => $plainToken]));

        $response->assertStatus(200);
        $response->assertViewIs('security::users.recovery_password');
        $response->assertSee('Repita su Contraseña');
        $this->assertTrue(session()->has('SET_ID_ALTER_PASSWORD'));

        // El token debe haberse consumido (quedar vacío) para evitar re-uso
        $this->assertSame('', $this->user->fresh()->token);
    }
}
