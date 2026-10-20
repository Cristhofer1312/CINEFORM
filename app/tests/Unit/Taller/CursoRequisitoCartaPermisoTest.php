<?php

namespace Tests\Unit\Taller;

use Modules\Taller\Entities\CursoRequisito;
use PHPUnit\Framework\TestCase;

class CursoRequisitoCartaPermisoTest extends TestCase
{
    public function test_definicion_es_documento_obligatorio(): void
    {
        $requisito = CursoRequisito::requisitoCartaPermiso();

        $this->assertSame('documento', $requisito['tipo']);
        $this->assertSame(CursoRequisito::CARTA_PERMISO_TITULO, $requisito['titulo']);
        $this->assertTrue($requisito['obligatorio']);
        $this->assertNotEmpty($requisito['descripcion']);
    }

    public function test_la_definicion_indica_que_aplica_solo_a_cursos_para_menores(): void
    {
        $requisito = CursoRequisito::requisitoCartaPermiso();

        $this->assertStringContainsString('niños y adolescentes', $requisito['descripcion']);
    }

    public function test_detecta_titulo_canonico(): void
    {
        $this->assertTrue(CursoRequisito::esCartaPermiso(CursoRequisito::CARTA_PERMISO_TITULO));
        $this->assertTrue(CursoRequisito::esCartaPermiso(['titulo' => 'CARTA DE PERMISO']));
    }

    public function test_detecta_autorizacion_de_menores_con_y_sin_acento(): void
    {
        $this->assertTrue(CursoRequisito::esCartaPermiso('Autorización de menores'));
        $this->assertTrue(CursoRequisito::esCartaPermiso('AUTORIZACION DE MENORES'));
    }

    public function test_no_confunde_otros_documentos(): void
    {
        $this->assertFalse(CursoRequisito::esCartaPermiso('Foto de cédula'));
        $this->assertFalse(CursoRequisito::esCartaPermiso(['titulo' => 'Comprobante de pago']));
    }

    public function test_solo_el_tipo_documento_cuenta_como_requisito_de_subida(): void
    {
        $this->assertTrue(CursoRequisito::esCartaPermisoDocumento([
            'tipo' => 'documento',
            'titulo' => 'Carta de permiso (Autorización de menores)',
        ]));

        $this->assertFalse(CursoRequisito::esCartaPermisoDocumento([
            'tipo' => 'recurso',
            'titulo' => 'Carta de permiso (Autorización de menores)',
        ]));

        $this->assertFalse(CursoRequisito::esCartaPermisoDocumento([
            'tipo' => 'pregunta',
            'titulo' => '¿Ya tienes carta de permiso?',
        ]));
    }
}
