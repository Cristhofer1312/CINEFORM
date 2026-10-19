<?php

namespace Modules\Taller\Services\Contracts;

/**
 * Genera un código QR como imagen PNG en disco.
 *
 * Existe para que ningún servicio tenga que llamar a APIs externas
 * (privacidad + disponibilidad offline). La única implementación
 * productiva usa bacon/bacon-qr-code con Imagick (instalado en Docker).
 */
interface QrGeneratorInterface
{
    /**
     * @param  string  $contenido  Texto codificado en el QR (UTF-8, no vacío).
     * @param  string  $destino  Ruta del PNG a escribir (se crea el directorio).
     * @param  int  $tamPx  Lado en píxeles (100-1000).
     *
     * @throws \RuntimeException Si el contenido es inválido o no se puede renderizar.
     */
    public function generar(string $contenido, string $destino, int $tamPx = 300): void;
}
