<?php

namespace Tests\Unit\Taller;

use Modules\Taller\Services\ImagenCertificadoService;
use PHPUnit\Framework\TestCase;

class ImagenCertificadoServiceTest extends TestCase
{
    public function test_transformar_coords_compensa_margenes_laterales(): void
    {
        // Plantilla 897x670 → lienzo 948x670 (margen de 25.5 px por lado).
        $ajuste = ['origenW' => 897, 'origenH' => 670, 'lienzoW' => 948, 'lienzoH' => 670];
        $coords = [
            'nombre' => ['x' => 40.9, 'y' => 78.2, 'size' => 24, 'w' => 220],
            'qr' => ['x' => 9.8, 'y' => 154.6, 'w' => 20, 'h' => 20],
        ];

        $resultado = ImagenCertificadoService::transformarCoords($coords, $ajuste);

        $this->assertEqualsWithDelta(46.688, $resultado['nombre']['x'], 0.01);
        $this->assertEqualsWithDelta(78.2, $resultado['nombre']['y'], 0.01);
        $this->assertEqualsWithDelta(208.165, $resultado['nombre']['w'], 0.01);
        $this->assertSame(24, $resultado['nombre']['size']);
        $this->assertEqualsWithDelta(17.262, $resultado['qr']['x'], 0.01);
    }

    public function test_transformar_coords_sin_ajuste_devuelve_identidad(): void
    {
        $ajuste = ['origenW' => 897, 'origenH' => 670, 'lienzoW' => 897, 'lienzoH' => 670];
        $coords = ['nombre' => ['x' => 40.9, 'y' => 78.2, 'size' => 24, 'w' => 220]];

        $this->assertEquals($coords, ImagenCertificadoService::transformarCoords($coords, $ajuste));
    }

    public function test_transformar_coords_ignora_bloques_desconocidos(): void
    {
        $ajuste = ['origenW' => 897, 'origenH' => 670, 'lienzoW' => 948, 'lienzoH' => 670];

        $resultado = ImagenCertificadoService::transformarCoords(['invento' => ['x' => 1]], $ajuste);

        $this->assertSame(['invento' => ['x' => 1]], $resultado);
    }
}
