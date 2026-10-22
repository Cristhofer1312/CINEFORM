<?php

namespace Tests\Unit\Taller;

use Illuminate\Validation\ValidationException;
use Modules\Taller\Http\Requests\StorePaso3Request;
use Tests\TestCase;

class WizardPreflightTest extends TestCase
{
    public function test_rango_acepta_bloque_dentro_del_curso(): void
    {
        StorePaso3Request::validarRangoContenidos(
            [[
                'titulo' => 'Clase 1',
                'fecha_contenido' => '2026-03-10',
                'hora_inicio' => '09:00',
                'hora_fin' => '11:00',
            ]],
            '2026-03-01',
            '2026-03-31'
        );

        $this->assertTrue(true);
    }

    public function test_rango_rechaza_fecha_fuera_y_horas_invertidas(): void
    {
        try {
            StorePaso3Request::validarRangoContenidos(
                [[
                    'titulo' => 'Clase 1',
                    'fecha_contenido' => '2026-04-10',
                    'hora_inicio' => '11:00',
                    'hora_fin' => '09:00',
                ]],
                '2026-03-01',
                '2026-03-31'
            );
            $this->fail('Debió lanzar ValidationException.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('contenidos.0.fecha_contenido', $e->errors());
            $this->assertArrayHasKey('contenidos.0.hora_fin', $e->errors());
        }
    }

    public function test_rango_sin_fechas_del_curso_no_exige(): void
    {
        StorePaso3Request::validarRangoContenidos(
            [[
                'titulo' => 'Clase 1',
                'fecha_contenido' => '2026-04-10',
                'hora_inicio' => '09:00',
                'hora_fin' => '11:00',
            ]],
            null,
            null
        );

        $this->assertTrue(true);
    }

    public function test_rango_rechaza_bloque_sin_fecha_cuando_hay_rango_definido(): void
    {
        try {
            StorePaso3Request::validarRangoContenidos(
                [[
                    'titulo' => 'Clase 1',
                    'fecha_contenido' => '',
                    'hora_inicio' => '09:00',
                    'hora_fin' => '11:00',
                ]],
                '2026-03-01',
                '2026-03-31'
            );
            $this->fail('Debió lanzar ValidationException por fecha_contenido vacía.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('contenidos.0.fecha_contenido', $e->errors());
        }
    }
}
