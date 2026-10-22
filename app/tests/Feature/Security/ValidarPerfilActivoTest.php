<?php

namespace Tests\Feature\Security;

use App\Constants\Perfil;
use Modules\Security\Entities\User;
use Tests\TestCase;

class ValidarPerfilActivoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_superadmin_pasa_sin_restriccion_de_perfil(): void
    {
        $superadmin = User::find(1);
        if (!$superadmin) {
            $this->markTestSkipped('No existe superadmin con ID 1 en la BD de pruebas.');
        }

        $sessionData = ['profile_id' => 999999];
        if (!empty($superadmin->session_token)) {
            $sessionData['session_token'] = $superadmin->session_token;
        }

        $response = $this->actingAs($superadmin)
            ->withSession($sessionData)
            ->get(route('home'));

        // Superadmin bypasses validation and can view home
        $response->assertStatus(200);
    }

    public function test_usuario_con_perfil_activo_valido_en_sesion_accede_normalmente(): void
    {
        $user = User::where('id', '>', 1)->where('active', true)->whereHas('perfiles', function ($q) {
            $q->where('security.profiles.active', true);
        })->first();

        if (!$user) {
            $this->markTestSkipped('No hay usuario de prueba disponible.');
        }

        $perfilActivo = $user->perfiles()->where('security.profiles.active', true)->first();

        $sessionData = ['profile_id' => $perfilActivo->id];
        if (!empty($user->session_token)) {
            $sessionData['session_token'] = $user->session_token;
        }

        $response = $this->actingAs($user)
            ->withSession($sessionData)
            ->get(route('home'));

        $response->assertStatus(200);
    }

    public function test_usuario_con_perfil_revocado_es_redirigido_o_reasignado(): void
    {
        $user = User::where('id', '>', 1)->where('active', true)->first();
        if (!$user) {
            $this->markTestSkipped('No hay usuario de prueba disponible.');
        }

        $sessionData = ['profile_id' => 999999]; // Perfil inválido
        if (!empty($user->session_token)) {
            $sessionData['session_token'] = $user->session_token;
        }

        $response = $this->actingAs($user)
            ->withSession($sessionData)
            ->get(route('home'));

        $perfilesActivos = $user->perfiles()->where('security.profiles.active', true)->count();

        if ($perfilesActivos === 1) {
            // Auto-asigna el único perfil y pasa a 200
            $response->assertStatus(200);
        } elseif ($perfilesActivos > 1) {
            // Redirige a selección de perfil
            $response->assertRedirect(route('usuario.cambiar_perfil'));
        } else {
            // Sin perfiles: desautentica a login
            $response->assertRedirect(route('login'));
        }
    }

    public function test_usuario_desactivado_es_desautenticado_y_redirigido(): void
    {
        $user = User::where('id', '>', 1)->first();
        if (!$user) {
            $this->markTestSkipped('No hay usuario de prueba disponible.');
        }

        // Marcar usuario como inactivo
        $user->active = 0;

        $response = $this->actingAs($user)
            ->withSession(['profile_id' => 3])
            ->get(route('home'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_rutas_exentas_no_son_bloqueadas_por_validador_de_perfil(): void
    {
        // Ruta pública que no requiere perfil activo
        $response = $this->get(route('main'));
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }
}
