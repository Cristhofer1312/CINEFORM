<?php

namespace Tests\Unit\Taller;

use Modules\Taller\Services\CertificadoConfig;
use PHPUnit\Framework\TestCase;

class CertificadoConfigTest extends TestCase
{
    public function test_coords_base_trae_los_cinco_bloques(): void
    {
        $coords = CertificadoConfig::coordsBase();

        $this->assertSame(['nombre', 'dni', 'qr', 'code', 'firma'], array_keys($coords));
    }

    public function test_validar_coords_acepta_bloque_valido(): void
    {
        $this->assertNull(CertificadoConfig::validarCoords([
            'nombre' => ['x' => 40.9, 'y' => 78.2, 'size' => 24, 'w' => 220],
        ]));
    }

    public function test_validar_coords_rechaza_clave_desconocida(): void
    {
        $this->assertNotNull(CertificadoConfig::validarCoords(['invento' => ['x' => 1, 'y' => 1]]));
    }

    public function test_validar_coords_rechaza_fuera_de_hoja(): void
    {
        $this->assertNotNull(CertificadoConfig::validarCoords(['nombre' => ['x' => 400, 'y' => 10]]));
        $this->assertNotNull(CertificadoConfig::validarCoords(['nombre' => ['x' => 10, 'y' => 10, 'size' => 99]]));
        $this->assertNotNull(CertificadoConfig::validarCoords('no-es-un-array'));
    }

    public function test_buscar_primero_ignora_mayusculas(): void
    {
        $dir = sys_get_temp_dir().'/cert_cfg_'.uniqid();
        mkdir($dir, 0755, true);
        file_put_contents($dir.'/7_firma.JPG', 'x');
        file_put_contents($dir.'/7.PNG', 'x');
        file_put_contents($dir.'/7.json', '{}');
        file_put_contents($dir.'/7_firma.txt', 'x');

        try {
            $this->assertSame($dir.'/7_firma.JPG', CertificadoConfig::buscarPrimero($dir, '7_firma'));
            $this->assertSame($dir.'/7.PNG', CertificadoConfig::buscarPrimero($dir, '7'));
            $this->assertNull(CertificadoConfig::buscarPrimero($dir, 'inexistente'));
            $this->assertNull(CertificadoConfig::buscarPrimero($dir, '../trampa'));
        } finally {
            array_map('unlink', glob($dir.'/*'));
            rmdir($dir);
        }
    }
}
