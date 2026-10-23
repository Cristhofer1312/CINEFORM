<?php

namespace Tests\Feature\Security;

use App\Constants\Perfil;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Security\Entities\User;
use Tests\TestCase;

/**
 * RF-08: el cierre de sesión solo se acepta por POST (protegido por CSRF)
 * y destruye la sesión.
 */
class LogoutSeguridadTest extends TestCase
{
    use DatabaseTransactions;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::whereHas('perfiles', function ($q) {
            $q->where('security.profiles.id', Perfil::PARTICIPANTE);
        })->where('id', '>', 1)->first() ?? User::findOrFail(7);
    }

    public function test_logout_por_get_no_cierra_sesion(): void
    {
        $this->actingAs($this->usuario)
            ->withSession(['profile_id' => Perfil::PARTICIPANTE])
            ->get(route('logout'))
            ->assertStatus(405);

        $this->assertAuthenticated();
    }

    public function test_logout_por_post_cierra_sesion_e_invalida(): void
    {
        $this->actingAs($this->usuario)
            ->withSession(['profile_id' => Perfil::PARTICIPANTE])
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
