<?php

namespace Tests\Feature\Security;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

/**
 * RNF-01: política única de contraseñas (Password::defaults()).
 */
class PoliticaContrasenaTest extends TestCase
{
    private function pasa(string $clave): bool
    {
        return Validator::make(['password' => $clave], ['password' => ['required', Password::defaults()]])->passes();
    }

    public function test_acepta_contrasena_que_cumple_la_politica(): void
    {
        $this->assertTrue($this->pasa('Abcdefg123!'));
    }

    public function test_rechaza_contrasenas_cortas(): void
    {
        $this->assertFalse($this->pasa('Abc12345!'));  // 9 caracteres
        $this->assertFalse($this->pasa('abc123'));      // la antigua regla de registro (min 6)
    }

    public function test_rechaza_contrasenas_sin_complejidad(): void
    {
        $this->assertFalse($this->pasa('abcdefghij1!')); // sin mayúscula
        $this->assertFalse($this->pasa('ABCDEFGHIJ1!')); // sin minúscula
        $this->assertFalse($this->pasa('Abcdefghijk!')); // sin número
        $this->assertFalse($this->pasa('Abcdefghij12')); // sin símbolo
    }

    public function test_rechaza_contrasenas_mayores_a_72_caracteres(): void
    {
        $this->assertFalse($this->pasa('Aa1!'.str_repeat('x', 69))); // 73 caracteres
        $this->assertTrue($this->pasa('Aa1!'.str_repeat('x', 68)));  // 72 caracteres
    }
}
