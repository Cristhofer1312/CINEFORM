<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * RF-01: el código de verificación del registro caduca a los 15 minutos
 * y las rutas de registro tienen límite de intentos.
 */
class RegistroCodigoSeguridadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_codigo_de_registro_expirado_es_rechazado(): void
    {
        $email = 'expirado_'.uniqid().'@example.com';

        DB::table('security.codes')->insert([
            'email' => $email,
            'code' => '123456',
            'date' => now()->subMinutes(20),
            'processed' => false,
        ]);

        $response = $this->post(route('registro.usuario.store'), [
            'username' => 'usuario_'.uniqid(),
            'email' => $email,
            'code' => '123456',
            'password' => 'Clave-Segura-2026',
            'password_confirmation' => 'Clave-Segura-2026',
            'tipo_dni' => 'V',
            'dni' => random_int(10000000, 29999999),
            'genero' => 'M',
            'primer_nombre' => 'Prueba',
            'primer_apellido' => 'Expirado',
            'telefono' => '04141234567',
        ]);

        $response->assertSessionHas('error', 'El código de verificación ha expirado. Solicita uno nuevo.');
        $this->assertDatabaseMissing('security.users', ['email' => $email]);
    }

    public function test_ruta_de_envio_de_codigo_tiene_limite_de_intentos(): void
    {
        Mail::fake();

        for ($i = 0; $i < 10; $i++) {
            $this->put(route('registro.usuario.enviar_codigo'), [
                'email' => 'limite_'.uniqid().'@example.com',
            ]);
        }

        $this->put(route('registro.usuario.enviar_codigo'), [
            'email' => 'limite_'.uniqid().'@example.com',
        ])->assertStatus(429);
    }
}
