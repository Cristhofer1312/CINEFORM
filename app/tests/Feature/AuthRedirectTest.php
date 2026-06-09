<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthRedirectTest extends TestCase
{
    /**
     * Verify that unauthenticated users are redirected to the login page.
     */
    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        // We attempt to access a protected route
        $response = $this->get('/security/home');

        // It should redirect
        $response->assertStatus(302);

        // It should redirect to the login route
        $response->assertRedirect(route('login'));
        
        // Let's verify where route('login') points to
        $this->assertEquals(url('/security/login'), route('login'));
    }

    /**
     * Verify that the root route also redirects to login.
     */
    public function test_root_route_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * Verify behavior of potentially unprotected Taller route.
     */
    public function test_unauthenticated_user_access_taller_mis_cursos(): void
    {
        $response = $this->get('/taller/mis-cursos');
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * Verify behavior of Cursos-asignados route.
     */
    public function test_unauthenticated_user_access_taller_cursos_asignados(): void
    {
        $response = $this->get('/taller/Cursos-asignados');
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }
}
