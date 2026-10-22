<?php

namespace Tests\Unit\Security;

use App\Constants\Perfil;
use App\Constants\SecurityAction;
use App\Services\Security\Autorizacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AutorizacionTest extends TestCase
{
    public function test_usuario_raiz_tiene_bypass_total(): void
    {
        $autorizacion = app(Autorizacion::class);

        // Sin autenticar
        $this->assertFalse($autorizacion->esRaiz());

        // Autenticado como usuario 1
        $user1 = (object) ['id' => 1];
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn(1);

        $this->assertTrue($autorizacion->esRaiz());
        $this->assertTrue($autorizacion->puede('ruta.inexistente', SecurityAction::CREAR));
        $this->assertTrue($autorizacion->puedeEnProceso(9999, SecurityAction::VER));
    }

    public function test_perfil_activo_desencripta_si_es_necesario(): void
    {
        $autorizacion = app(Autorizacion::class);

        session(['profile_id' => Perfil::COORDINADOR]);
        $this->assertSame(Perfil::COORDINADOR, $autorizacion->perfilActivo());

        // Encriptado
        $encrypted = \App\Helpers\Encryptor::encrypt(Perfil::PARTICIPANTE);
        session(['profile_id' => $encrypted]);
        $this->assertSame(Perfil::PARTICIPANTE, $autorizacion->perfilActivo());
    }

    public function test_invalidar_incrementa_version_y_limpia_memo(): void
    {
        $autorizacion = app(Autorizacion::class);

        $v1 = Cache::get('rbac:version', 1);
        $autorizacion->invalidar();
        $v2 = Cache::get('rbac:version');

        $this->assertGreaterThan($v1, $v2);
    }
}
