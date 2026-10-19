<?php

namespace Tests\Unit\Taller;

use Modules\Comun\Entities\PersonalData;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Services\CertificadoCodigo;
use Tests\TestCase;

class CertificadoCodigoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    private function curso(): Curso
    {
        return new Curso(['codigo' => 'LAB-C19LAB-2026']);
    }

    private function persona(): PersonalData
    {
        return new PersonalData(['dni' => '12345678']);
    }

    public function test_emitir_genera_formato_con_firma(): void
    {
        $codigo = CertificadoCodigo::emitir($this->curso(), $this->persona());

        $this->assertMatchesRegularExpression('/^LAB-C19LAB-2026-12345678-[a-f0-9]{6}$/', $codigo);
    }

    public function test_parse_acepta_formato_nuevo(): void
    {
        $codigo = CertificadoCodigo::emitir($this->curso(), $this->persona());
        $parsed = CertificadoCodigo::parse($codigo);

        $this->assertNotNull($parsed);
        $this->assertSame('LAB-C19LAB-2026', $parsed['codigo']);
        $this->assertSame('12345678', $parsed['dni']);
        $this->assertNotNull($parsed['hmac']);
        $this->assertSame('hmac', $parsed['formato']);
        $this->assertTrue(CertificadoCodigo::firmaValida($parsed['codigo'], $parsed['dni'], $parsed['hmac']));
    }

    public function test_parse_acepta_formato_legado(): void
    {
        $parsed = CertificadoCodigo::parse('LAB-C19LAB-2026-12345678');

        $this->assertNotNull($parsed);
        $this->assertSame('legacy', $parsed['formato']);
        $this->assertNull($parsed['hmac']);
    }

    public function test_parse_rechaza_codigos_malformados(): void
    {
        $this->assertNull(CertificadoCodigo::parse('invalido'));
        $this->assertNull(CertificadoCodigo::parse('LAB-C19LAB-2026'));
        $this->assertNull(CertificadoCodigo::parse('LAB-C19LAB-2026-ABC'));
    }

    public function test_firma_manipulada_no_valida(): void
    {
        $this->assertFalse(CertificadoCodigo::firmaValida('LAB-C19LAB-2026', '12345678', '000000'));
    }
}
