<?php

namespace Tests\Feature\Security;

use App\Services\Security\RegistroAccesos;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Security\Entities\User;
use Tests\TestCase;

/**
 * Registro de accesos (security.accesos): cada intento de login y cada logout
 * deja una fila con el usuario, la IP real y el navegador.
 */
class RegistroAccesosTest extends TestCase
{
    use DatabaseTransactions;

    private const CAPTCHA = 'ABCDE';

    private const CLAVE = 'Correcta#2026';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);

        $this->user = User::findOrFail(7); // Participante de prueba
        $this->user->forceFill(['password' => Hash::make(self::CLAVE), 'active' => 1])->save();
    }

    public function test_login_correcto_registra_usuario_ip_y_navegador(): void
    {
        $this->intentar($this->user->username, self::CLAVE, '10.9.0.1')->assertRedirect();

        $this->assertAcceso(RegistroAccesos::LOGIN, '10.9.0.1', $this->user->id);
        $fila = $this->ultimo('10.9.0.1');
        $this->assertSame('PruebaNavegador/1.0', $fila->user_agent);
        $this->assertSame($this->user->username, $fila->usuario);
    }

    public function test_contrasena_incorrecta_registra_fallido(): void
    {
        $this->intentar($this->user->username, 'clave-incorrecta', '10.9.0.2');

        $this->assertAcceso(RegistroAccesos::FALLIDO, '10.9.0.2', $this->user->id);
    }

    public function test_usuario_inexistente_guarda_lo_que_se_escribio(): void
    {
        $this->intentar('No_Existe_XYZ', 'loquesea', '10.9.0.3');

        $this->assertAcceso(RegistroAccesos::USUARIO_INEXISTENTE, '10.9.0.3', null);
        $this->assertSame('no_existe_xyz', $this->ultimo('10.9.0.3')->usuario);
    }

    public function test_cuenta_inactiva_con_clave_correcta(): void
    {
        $this->user->forceFill(['active' => 0])->save();

        $this->intentar($this->user->username, self::CLAVE, '10.9.0.4');

        $this->assertAcceso(RegistroAccesos::USUARIO_INACTIVO, '10.9.0.4', $this->user->id);
    }

    public function test_intentos_rechazados_por_bloqueo(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->intentar($this->user->username, 'clave-incorrecta', '10.9.0.5');
        }

        $this->assertAcceso(RegistroAccesos::BLOQUEADO, '10.9.0.5', null);
    }

    public function test_logout_queda_registrado(): void
    {
        $this->actingAs($this->user)
            ->withServerVariables(['REMOTE_ADDR' => '10.9.0.6'])
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertAcceso(RegistroAccesos::LOGOUT, '10.9.0.6', $this->user->id);
    }

    public function test_la_ip_real_llega_a_traves_del_proxy(): void
    {
        // nginx del host -> nginx del contenedor -> PHP: la IP del visitante viaja en X-Forwarded-For.
        $this->withServerVariables(['REMOTE_ADDR' => '172.18.0.5'])
            ->withHeader('X-Forwarded-For', '203.0.113.7')
            ->withHeader('User-Agent', 'PruebaNavegador/1.0')
            ->withSession(['SET_CAPTCHA' => self::CAPTCHA])
            ->post(route('login'), ['username' => $this->user->username, 'password' => 'x', 'captcha' => self::CAPTCHA]);

        $this->assertAcceso(RegistroAccesos::FALLIDO, '203.0.113.7', $this->user->id);
    }

    public function test_el_registro_no_se_puede_modificar_ni_borrar(): void
    {
        $this->intentar($this->user->username, 'clave-incorrecta', '10.9.0.8');
        $id = $this->ultimo('10.9.0.8')->id;

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('security.accesos')->where('id', $id)->delete();
    }

    private function intentar(string $usuario, string $clave, string $ip)
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->withHeader('User-Agent', 'PruebaNavegador/1.0')
            ->withSession(['SET_CAPTCHA' => self::CAPTCHA])
            ->post(route('login'), ['username' => $usuario, 'password' => $clave, 'captcha' => self::CAPTCHA]);
    }

    private function ultimo(string $ip): object
    {
        return DB::table('security.accesos')->where('ip', $ip)->orderByDesc('id')->firstOrFail();
    }

    private function assertAcceso(string $evento, string $ip, ?int $userId): void
    {
        $fila = $this->ultimo($ip);
        $this->assertSame($evento, $fila->evento);
        $this->assertSame($userId, $fila->user_id === null ? null : (int) $fila->user_id);
    }
}
