<?php

namespace Modules\Taller\Services;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Modules\Taller\Services\Contracts\QrGeneratorInterface;

/**
 * Generador QR local basado en bacon/bacon-qr-code (Imagick).
 *
 * No realiza llamadas de red: el DNI del participante nunca sale del servidor.
 */
final class BaconQrGenerator implements QrGeneratorInterface
{
    public function generar(string $contenido, string $destino, int $tamPx = 300): void
    {
        if (trim($contenido) === '') {
            throw new \RuntimeException('No se puede generar un QR con contenido vacío.');
        }

        $tamPx = max(100, min(1000, $tamPx));

        if (! extension_loaded('imagick')) {
            throw new \RuntimeException(
                'Falta la extensión imagick de PHP para generar el QR local. Instálala en el contenedor (pecl install imagick).'
            );
        }

        $directorio = dirname($destino);
        if (! is_dir($directorio) && ! @mkdir($directorio, 0755, true) && ! is_dir($directorio)) {
            throw new \RuntimeException('No se pudo crear el directorio temporal del QR.');
        }

        try {
            $renderer = new ImageRenderer(new RendererStyle($tamPx), new ImagickImageBackEnd);
            (new Writer($renderer))->writeFile($contenido, $destino);
            $this->forzar8Bits($destino);
        } catch (\Throwable $e) {
            throw new \RuntimeException('No se pudo generar el QR del certificado: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Imagick escribe PNG de 16 bits por canal y FPDF solo admite 8 bits.
     * Sin esta conversión el certificado fallaría al incrustar el QR.
     */
    private function forzar8Bits(string $destino): void
    {
        $imagen = new \Imagick($destino);
        $imagen->setImageDepth(8);
        $imagen->stripImage();
        $imagen->writeImage($destino);
        $imagen->clear();
    }
}
