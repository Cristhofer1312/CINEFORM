<?php

namespace Tests\Feature\Security;

use App\Exceptions\ReglaDeNegocioException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Modules\Security\Entities\User;
use Tests\TestCase;

/**
 * Auditoría de seguridad del 2026-10-28: captcha de un solo uso, bloqueo por
 * cuenta, enumeración de usuarios, recuperación de contraseña, validación del
 * registro público, rutas de API de plantilla, idioma y CORS.
 */
class AuditoriaSeguridadTest extends TestCase
{
    use DatabaseTransactions;

    private const CAPTCHA = 'ABCDE';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->user = User::findOrFail(7); // Participante de prueba
    }

    // ── Login ──────────────────────────────────────────────────────────────

    public function test_el_captcha_se_consume_en_cada_intento(): void
    {
        $this->withSession(['SET_CAPTCHA' => self::CAPTCHA])
            ->post(route('login'), $this->credenciales('clave-incorrecta'));

        $this->assertFalse(session()->has('SET_CAPTCHA'), 'El captcha debe borrarse tras compararlo.');

        // Reusar el mismo texto sin pedir otra imagen ya no sirve.
        $this->post(route('login'), $this->credenciales('clave-incorrecta'))
            ->assertSessionHasErrors(['error-message' => __('Captcha does not match')]);
    }

    public function test_5_fallos_bloquean_la_cuenta_solo_desde_esa_ip(): void
    {
        $this->user->forceFill(['password' => Hash::make('Correcta#2026'), 'active' => 1])->save();

        for ($i = 1; $i <= 5; $i++) {
            $this->desdeIp('10.0.0.1')
                ->withSession(['SET_CAPTCHA' => self::CAPTCHA])
                ->post(route('login'), $this->credenciales('clave-incorrecta'))
                ->assertSessionHasErrors(['error-message' => __('Wrong Data')]);
        }

        // Desde la IP atacante, ni con la contraseña correcta: espera 15 minutos.
        $this->desdeIp('10.0.0.1')
            ->withSession(['SET_CAPTCHA' => self::CAPTCHA])
            ->post(route('login'), $this->credenciales('Correcta#2026'));
        $this->assertGuest();
        $this->assertStringContainsString('Demasiados intentos', session('errors')->first('error-message'));

        // El dueño, desde su IP, sigue entrando: fallar a propósito no le niega el acceso.
        $this->desdeIp('10.0.0.2')
            ->withSession(['SET_CAPTCHA' => self::CAPTCHA])
            ->post(route('login'), $this->credenciales('Correcta#2026'));
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_una_ip_que_prueba_muchas_cuentas_se_bloquea_tras_20_fallos(): void
    {
        // Sin el límite por minuto de la ruta, para llegar a los 20 fallos en la prueba.
        $this->withoutMiddleware(ThrottleRequests::class);

        for ($i = 1; $i <= 20; $i++) {
            $this->desdeIp('10.0.3.1')
                ->withSession(['SET_CAPTCHA' => self::CAPTCHA])
                ->post(route('login'), ['username' => "cuenta_$i", 'password' => 'x', 'captcha' => self::CAPTCHA])
                ->assertSessionHasErrors(['error-message' => __('Wrong Data')]);
        }

        $this->desdeIp('10.0.3.1')
            ->withSession(['SET_CAPTCHA' => self::CAPTCHA])
            ->post(route('login'), $this->credenciales('clave-incorrecta'));
        $this->assertStringContainsString('Demasiados intentos', session('errors')->first('error-message'));
    }

    public function test_un_acceso_correcto_reinicia_el_conteo_de_fallos(): void
    {
        $this->user->forceFill(['password' => Hash::make('Correcta#2026'), 'active' => 1])->save();

        for ($i = 1; $i <= 4; $i++) {
            $this->desdeIp('10.0.1.1')->withSession(['SET_CAPTCHA' => self::CAPTCHA])
                ->post(route('login'), $this->credenciales('clave-incorrecta'));
        }
        $this->desdeIp('10.0.1.1')->withSession(['SET_CAPTCHA' => self::CAPTCHA])
            ->post(route('login'), $this->credenciales('Correcta#2026'));
        $this->assertAuthenticatedAs($this->user);

        $this->post(route('logout'));
        $this->desdeIp('10.0.1.1')->withSession(['SET_CAPTCHA' => self::CAPTCHA])
            ->post(route('login'), $this->credenciales('clave-incorrecta'))
            ->assertSessionHasErrors(['error-message' => __('Wrong Data')]);
    }

    public function test_cuenta_bloqueada_no_se_revela_sin_la_contrasena(): void
    {
        $this->user->forceFill(['active' => 0])->save();

        $this->withSession(['SET_CAPTCHA' => self::CAPTCHA])
            ->post(route('login'), $this->credenciales('clave-incorrecta'))
            ->assertSessionHasErrors(['error-message' => __('Wrong Data')]);
    }

    // ── Recuperación de contraseña ─────────────────────────────────────────

    public function test_recuperacion_responde_igual_exista_o_no_el_correo(): void
    {
        $existente = $this->withSession(['SET_CAPTCHA' => self::CAPTCHA])
            ->post(route('recovery'), ['email' => $this->user->email, 'captcha' => self::CAPTCHA]);
        $inexistente = $this->withSession(['SET_CAPTCHA' => self::CAPTCHA])
            ->post(route('recovery'), ['email' => 'nadie_'.uniqid().'@example.com', 'captcha' => self::CAPTCHA]);

        $existente->assertRedirect(route('login'))->assertSessionHas('success');
        $inexistente->assertRedirect(route('login'))->assertSessionHas('success', session('success'));
    }

    public function test_cambiar_la_clave_por_recuperacion_cierra_las_demas_sesiones(): void
    {
        $tokenAnterior = 'token-de-una-sesion-robada-0000000000000';
        $this->user->forceFill([
            'session_token' => $tokenAnterior,
            'change_password' => true,
            'date_change_password' => now(),
        ])->save();

        $this->withSession(['SET_CAPTCHA' => self::CAPTCHA, 'SET_ID_ALTER_PASSWORD' => $this->user->crypt_id])
            ->put(route('recovery'), [
                'password' => 'Nueva#Clave2026',
                're_password' => 'Nueva#Clave2026',
                'captcha' => self::CAPTCHA,
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success', __('User Updated Successfully'));

        $fresco = $this->user->fresh();
        $this->assertTrue(Hash::check('Nueva#Clave2026', $fresco->password));
        $this->assertNotSame($tokenAnterior, $fresco->session_token);
        $this->assertFalse(session()->has('SET_ID_ALTER_PASSWORD'));
    }

    public function test_el_permiso_de_cambio_en_sesion_caduca_con_el_enlace(): void
    {
        $this->user->forceFill(['change_password' => true, 'date_change_password' => now()->subMinutes(45)])->save();
        $hashAnterior = $this->user->password;

        $this->withSession(['SET_CAPTCHA' => self::CAPTCHA, 'SET_ID_ALTER_PASSWORD' => $this->user->crypt_id])
            ->put(route('recovery'), [
                'password' => 'Nueva#Clave2026',
                're_password' => 'Nueva#Clave2026',
                'captcha' => self::CAPTCHA,
            ])
            ->assertSessionHas('success', __('Security Token Expired'));

        $this->assertSame($hashAnterior, $this->user->fresh()->password);
    }

    // ── Registro público ───────────────────────────────────────────────────

    public function test_registro_rechaza_datos_fuera_de_formato_sin_llegar_a_la_bd(): void
    {
        $email = 'formato_'.uniqid().'@example.com';
        DB::table('security.codes')->insert(['email' => $email, 'code' => '123456', 'date' => now(), 'processed' => false]);

        $this->post(route('registro.usuario.store'), [
            'username' => 'usuario '.uniqid(),           // espacio
            'email' => $email,
            'code' => '123456',
            'password' => 'Clave-Segura-2026',
            'password_confirmation' => 'Clave-Segura-2026',
            'tipo_dni' => 999,                           // no existe
            'dni' => (string) random_int(10000000, 29999999),
            'genero' => 1,
            'primer_nombre' => 'Prueba',
            'primer_apellido' => 'Formato',
            'telefono' => '<script>',
            'id_estado' => 999999,
            'direccion' => str_repeat('x', 300),         // la columna es varchar(255)
        ])->assertSessionHasErrors(['username', 'tipo_dni', 'telefono', 'id_estado', 'direccion']);

        $this->assertDatabaseMissing('security.users', ['email' => $email]);
    }

    public function test_registro_solo_acepta_prefijos_moviles_venezolanos(): void
    {
        $email = 'prefijo_'.uniqid().'@example.com';
        DB::table('security.codes')->insert(['email' => $email, 'code' => '123456', 'date' => now(), 'processed' => false]);

        foreach (['04101234567', '0414123456', '(0414) 123-4567'] as $telefono) {
            $this->post(route('registro.usuario.store'), ['telefono' => $telefono] + $this->registro($email, '123456'))
                ->assertSessionHasErrors('telefono');
        }

        foreach (array_keys(\Modules\Registro\Http\Controllers\RegisterController::PREFIJOS_TELEFONO) as $prefijo) {
            $this->post(route('registro.usuario.store'), ['telefono' => $prefijo.'1234567'] + $this->registro($email, '000000'))
                ->assertSessionDoesntHaveErrors('telefono');
        }
    }

    public function test_codigo_de_registro_se_bloquea_tras_5_errores(): void
    {
        $email = 'fuerza_'.uniqid().'@example.com';
        DB::table('security.codes')->insert(['email' => $email, 'code' => '123456', 'date' => now(), 'processed' => false]);

        for ($i = 0; $i < 5; $i++) {
            $this->desdeIp("10.0.2.$i")->post(route('registro.usuario.store'), $this->registro($email, '00000'.$i))
                ->assertSessionHas('error', 'El código de verificación es inválido o ya ha sido procesado.');
        }

        // El código correcto ya no se acepta hasta que pase el bloqueo.
        $this->desdeIp('10.0.2.99')->post(route('registro.usuario.store'), $this->registro($email, '123456'))
            ->assertSessionHas('error', 'Demasiados códigos incorrectos. Espere 15 minutos y solicite un código nuevo.');
        $this->assertDatabaseMissing('security.users', ['email' => $email]);
    }

    // ── Rutas, idioma y CORS ───────────────────────────────────────────────

    public function test_rutas_de_plantilla_sin_guard_ya_no_existen(): void
    {
        foreach (['/api/comun', '/api/parametros', '/api/registro', '/api/taller', '/comun', '/parametros'] as $ruta) {
            $this->getJson($ruta)->assertNotFound();
        }
    }

    public function test_idioma_solo_acepta_valores_conocidos_y_no_redirige_fuera(): void
    {
        $this->get('/language/../../etc')->assertNotFound();
        $this->get('/language/xx')->assertNotFound();

        $this->withHeader('Referer', 'https://sitio-malicioso.example/')
            ->get('/language/es')
            ->assertRedirect(route('home'));

        $this->withHeader('Referer', url('/taller/cursos'))
            ->get('/language/es')
            ->assertRedirect(url('/taller/cursos'));
    }

    public function test_cors_no_autoriza_otros_origenes(): void
    {
        $respuesta = $this->withHeaders([
            'Origin' => 'https://sitio-malicioso.example',
            'Access-Control-Request-Method' => 'POST',
        ])->options('/api/cualquiera');

        $this->assertNotSame('*', $respuesta->headers->get('Access-Control-Allow-Origin'));
        $this->assertNotSame('https://sitio-malicioso.example', $respuesta->headers->get('Access-Control-Allow-Origin'));
    }

    public function test_registro_antiguo_solo_redirige_al_vigente(): void
    {
        $this->get('/security/register')->assertRedirect(route('registro.usuario'));
        $this->post('/security/register')->assertStatus(405);
        $this->put('/security/register', ['email' => 'x@example.com'])->assertStatus(405);
    }

    // ── Manejo de errores ──────────────────────────────────────────────────

    public function test_handler_muestra_el_mensaje_de_una_regla_de_negocio(): void
    {
        Route::middleware('web')->post('/_prueba/regla', function () {
            throw new ReglaDeNegocioException('Ya respondiste la encuesta de este curso.');
        });

        $this->from('/formulario')->post('/_prueba/regla', ['nombre' => 'Ana', 'password' => 'secreta'])
            ->assertRedirect('/formulario')
            ->assertSessionHas('error', 'Ya respondiste la encuesta de este curso.')
            ->assertSessionHasInput('nombre', 'Ana')
            ->assertSessionMissing('_old_input.password');

        $this->postJson('/_prueba/regla')
            ->assertStatus(422)
            ->assertJson(['message' => 'Ya respondiste la encuesta de este curso.']);
    }

    public function test_handler_no_muestra_otras_excepciones_de_dominio(): void
    {
        // Una DomainException cualquiera (p. ej. de una librería) es una falla:
        // página de error genérica, sin su mensaje técnico.
        Route::middleware('web')->post('/_prueba/libreria', function () {
            throw new \DomainException('SQLSTATE detalle interno');
        });
        config(['app.debug' => false]);

        $this->post('/_prueba/libreria')
            ->assertStatus(500)
            ->assertDontSee('SQLSTATE detalle interno');
    }

    // ── Utilidades ─────────────────────────────────────────────────────────

    private function credenciales(string $clave): array
    {
        return ['username' => $this->user->username, 'password' => $clave, 'captcha' => self::CAPTCHA];
    }

    private function desdeIp(string $ip): static
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip]);
    }

    private function registro(string $email, string $codigo): array
    {
        return [
            'username' => 'fuerza_'.uniqid(),
            'email' => $email,
            'code' => $codigo,
            'password' => 'Clave-Segura-2026',
            'password_confirmation' => 'Clave-Segura-2026',
            'tipo_dni' => 1,
            'dni' => (string) random_int(10000000, 29999999),
            'genero' => 1,
            'primer_nombre' => 'Prueba',
            'primer_apellido' => 'Fuerza',
            'telefono' => '04141234567',
        ];
    }
}
