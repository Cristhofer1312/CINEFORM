<?php

namespace Tests\Feature\Security;

use App\Constants\Perfil;
use App\Helpers\Encryptor;
use App\Services\Security\Autorizacion;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Security\Entities\User;
use Tests\TestCase;

/**
 * Revisión de seguridad (27/10), checklist de security-and-profile-manager:
 * ciclo de sesión al cambiar credenciales, reautenticación para cambiar el
 * correo, escalada por asignación de perfiles (D8), PII en serialización y
 * parámetros de archivo.
 */
class SeguridadCuentaTest extends TestCase
{
    use DatabaseTransactions;

    /** Valores de `option` que envía el formulario de "Configuración de cuenta". */
    private const OPCION_DATOS = 'dTBMWEdVQ1VucWw0dWVrSW9PWWxsZz09';

    private const OPCION_CREDENCIALES = 'b1VOTnVXeEp1KzZOOXl5bGhkZUk3dz09';

    private const CLAVE = 'Clave-Prueba-2026!';

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        // Usuario de prueba creado dentro de la transacción: no se tocan cuentas reales.
        $this->usuario = new User;
        $this->usuario->username = 'qa_seguridad_'.random_int(1000, 9999);
        $this->usuario->email = $this->usuario->username.'@example.test';
        $this->usuario->password = Hash::make(self::CLAVE);
        $this->usuario->active = true;
        $this->usuario->register_date = now();
        $this->usuario->ip = '127.0.0.1';
        $this->usuario->user_id = 1;
        $this->usuario->session_token = Str::random(40);
        $this->usuario->save();
        $this->soloConPerfiles($this->usuario, Perfil::PARTICIPANTE);
    }

    public function test_cambiar_la_contrasena_cierra_las_otras_sesiones(): void
    {
        $tokenAnterior = $this->usuario->session_token;

        $this->como($this->usuario)->post(route('update_profile'), [
            'option' => self::OPCION_CREDENCIALES,
            'current_password' => self::CLAVE,
            'new_password' => 'Otra-Clave-2026!x',
            're_password' => 'Otra-Clave-2026!x',
        ])->assertSessionHasNoErrors()->assertRedirect(route('update_profile'));

        $tokenNuevo = DB::table('security.users')->where('id', $this->usuario->id)->value('session_token');
        $this->assertNotSame($tokenAnterior, $tokenNuevo);

        // Otra sesión con el token anterior queda cerrada.
        $this->app['auth']->forgetGuards();
        $this->actingAs(User::find($this->usuario->id))
            ->withSession(['session_token' => $tokenAnterior])
            ->getJson(route('update_profile'))
            ->assertStatus(401);
    }

    public function test_cambiar_el_correo_exige_la_contrasena_actual(): void
    {
        $nuevo = 'nuevo_'.$this->usuario->email;
        $this->codigoPara($nuevo, now());

        $this->como($this->usuario)->post(route('update_profile'), $this->datos($nuevo, 'contrasena-equivocada'))
            ->assertSessionHasErrors('current_password');
        $this->assertSame($this->usuario->email, User::find($this->usuario->id)->email);

        $this->como($this->usuario)->post(route('update_profile'), $this->datos($nuevo, self::CLAVE))
            ->assertSessionHasNoErrors();
        $this->assertSame($nuevo, User::find($this->usuario->id)->email);
    }

    public function test_un_codigo_de_verificacion_vencido_no_sirve(): void
    {
        $nuevo = 'vencido_'.$this->usuario->email;
        $this->codigoPara($nuevo, now()->subMinutes(16));

        $this->como($this->usuario)->post(route('update_profile'), $this->datos($nuevo, self::CLAVE))
            ->assertSessionHasErrors(['error-message' => __('Your verification code is wrong')]);
        $this->assertSame($this->usuario->email, User::find($this->usuario->id)->email);
    }

    public function test_no_se_crea_un_usuario_con_un_perfil_que_el_actor_no_tiene(): void
    {
        $coordinador = $this->coordinadorQueGestionaUsuarios();

        $this->como($coordinador)->post(route('users.create'), [
            'username' => 'qa_escalada',
            'email' => 'qa_escalada@example.test',
            'password' => 'Clave-Escalada-2026!',
            'profile_id' => Encryptor::encrypt(Perfil::ADMINISTRADOR),
        ])->assertForbidden();

        $this->assertDatabaseMissing('security.users', ['username' => 'qa_escalada']);
    }

    public function test_no_se_edita_el_usuario_propio_desde_administracion(): void
    {
        $coordinador = $this->coordinadorQueGestionaUsuarios();

        $this->como($coordinador)->get(route('users.update', ['id' => Encryptor::encrypt($coordinador->id)]))
            ->assertRedirect(route('users'))
            ->assertSessionHasErrors('error-message');
    }

    public function test_restablecer_la_contrasena_de_otro_cierra_sus_sesiones(): void
    {
        $admin = User::findOrFail(1);
        $tokenAnterior = $this->usuario->session_token;

        $this->como($admin)->post(route('users.password', ['id' => Encryptor::encrypt($this->usuario->id)]), [
            'new_password' => 'Restablecida-2026!x',
            're_password' => 'Restablecida-2026!x',
        ])->assertRedirect(route('users'));

        $this->assertNotSame($tokenAnterior, DB::table('security.users')->where('id', $this->usuario->id)->value('session_token'));
    }

    public function test_el_usuario_serializado_no_expone_secretos(): void
    {
        $datos = User::find($this->usuario->id)->toArray();

        foreach (['password', 'remember_token', 'token', 'session_token'] as $campo) {
            $this->assertArrayNotHasKey($campo, $datos);
        }
    }

    public function test_el_visor_pdf_rechaza_nombres_de_archivo_inseguros(): void
    {
        $this->como($this->usuario)->get('/security/show-pdf/..%2F..%2F.env')->assertNotFound();
        $this->como($this->usuario)->get('/security/show-pdf/no_existe/0')->assertNotFound();
        $this->como($this->usuario)->get('/security/show-pdf/archivo/1%5D')->assertNotFound();
    }

    // ── Utilidades ─────────────────────────────────────────────────────────

    private function como(User $user)
    {
        return $this->actingAs($user)->withSession(['session_token' => $user->session_token]);
    }

    private function datos(string $email, string $clave): array
    {
        return [
            'option' => self::OPCION_DATOS,
            'phone' => '04120000000',
            'email' => $email,
            'code' => '123456',
            'current_password' => $clave,
        ];
    }

    private function codigoPara(string $email, $fecha): void
    {
        DB::table('security.codes')->where('email', $email)->delete();
        DB::table('security.codes')->insert(['email' => $email, 'code' => '123456', 'date' => $fecha, 'processed' => false]);
    }

    private function coordinadorQueGestionaUsuarios(): User
    {
        $coordinador = User::findOrFail(2);
        $this->soloConPerfiles($coordinador, Perfil::COORDINADOR);

        $permisos = DB::table('security.permissions as p')
            ->join('security.processes as pr', 'pr.id', '=', 'p.process_id')
            ->where('pr.codigo', 'users')->whereIn('p.slug', ['view', 'create', 'edit'])
            ->pluck('p.id');
        foreach ($permisos as $id) {
            DB::table('security.profile_permissions')->insertOrIgnore([
                'profile_id' => Perfil::COORDINADOR, 'permission_id' => $id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        app(Autorizacion::class)->invalidar();

        return $coordinador;
    }
}
