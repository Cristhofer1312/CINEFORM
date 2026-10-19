<?php

namespace Modules\Taller\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

/**
 * Guarda plantilla y firma del certificado normalizadas.
 *
 * Reencodea con intervention/image y guarda siempre con la extensión del
 * formato real (antes un JPEG quedaba como ".png" falso). Si la plantilla
 * no respeta la proporción A4 horizontal, la ajusta añadiendo márgenes
 * blancos (nunca se deforma ni se recorta) y las coordenadas guardadas
 * deben recorrerse con coordsAjustadas() para seguir calibradas.
 */
final class ImagenCertificadoService
{
    /** @var array{origenW:int,origenH:int,lienzoW:int,lienzoH:int}|null */
    private ?array $ultimoAjuste = null;

    /**
     * @return string Ruta final en disco.
     *
     * @throws \RuntimeException Con mensaje en español accionable.
     */
    public function guardarPlantilla(UploadedFile $archivo, int|string $idCurso): string
    {
        $destinoDir = CertificadoConfig::dirCursos();
        $this->asegurarDirectorio($destinoDir);

        $mime = (string) $archivo->getMimeType();
        $extension = $this->extensionPara($mime, $archivo);

        [$ancho, $alto] = $this->dimensiones($archivo);
        if ($alto > $ancho) {
            throw new \RuntimeException(
                "La plantilla mide {$ancho} x {$alto} px y es más alta que ancha. ".
                'El certificado es horizontal (A4, 297 x 210 mm), así que necesitas una imagen apaisada.'
            );
        }

        $this->ultimoAjuste = $this->calcularAjusteA4($ancho, $alto);

        $destino = $destinoDir.'/'.$idCurso.'.'.$extension;
        $this->reencodear($archivo->getRealPath(), $destino, $mime, $this->ultimoAjuste);
        $this->limpiarVariantes($destinoDir.'/'.$idCurso.'.', $extension);

        return $destino;
    }

    /**
     * Indica si la última plantilla guardada necesitó ajuste de proporción.
     */
    public function fueAjustada(): bool
    {
        return $this->ultimoAjuste !== null;
    }

    /**
     * Descripción del ajuste aplicado, para informar al usuario. Null si no hubo.
     */
    public function detalleAjuste(): ?string
    {
        if ($this->ultimoAjuste === null) {
            return null;
        }

        $a = $this->ultimoAjuste;

        return "La plantilla medía {$a['origenW']} x {$a['origenH']} px y se ajustó ".
            'a la proporción A4 horizontal (297 x 210 mm) añadiendo márgenes blancos, sin deformarla.';
    }

    /**
     * Recorre coordenadas calibradas sobre la vista previa estirada para que
     * apunten al mismo punto del diseño tras añadir los márgenes del ajuste.
     */
    public function coordsAjustadas(array $coords): array
    {
        if ($this->ultimoAjuste === null) {
            return $coords;
        }

        return self::transformarCoords($coords, $this->ultimoAjuste);
    }

    /**
     * Transformación pura (sin E/S): escala cada bloque al lienzo nuevo y le
     * suma el desplazamiento de los márgenes centrados.
     */
    public static function transformarCoords(array $coords, array $ajuste): array
    {
        $escalaX = $ajuste['origenW'] / $ajuste['lienzoW'];
        $escalaY = $ajuste['origenH'] / $ajuste['lienzoH'];
        $despX = (($ajuste['lienzoW'] - $ajuste['origenW']) / 2) / $ajuste['lienzoW'] * 297;
        $despY = (($ajuste['lienzoH'] - $ajuste['origenH']) / 2) / $ajuste['lienzoH'] * 210;

        foreach (['nombre', 'dni', 'qr', 'code', 'firma'] as $clave) {
            if (! isset($coords[$clave]) || ! is_array($coords[$clave])) {
                continue;
            }
            $bloque = $coords[$clave];
            foreach (['x' => [$escalaX, $despX], 'w' => [$escalaX, 0], 'y' => [$escalaY, $despY], 'h' => [$escalaY, 0]] as $campo => [$escala, $desp]) {
                if (isset($bloque[$campo]) && is_numeric($bloque[$campo])) {
                    $bloque[$campo] = round($bloque[$campo] * $escala + $desp, 3);
                }
            }
            $coords[$clave] = $bloque;
        }

        return $coords;
    }

    /**
     * @return string Ruta final en disco.
     *
     * @throws \RuntimeException Con mensaje en español accionable.
     */
    public function guardarFirma(UploadedFile $archivo, int|string $idCurso): string
    {
        $destinoDir = CertificadoConfig::dirCursos();
        $this->asegurarDirectorio($destinoDir);

        $mime = (string) $archivo->getMimeType();
        $extension = $this->extensionPara($mime, $archivo);

        $destino = $destinoDir.'/'.$idCurso.'_firma.'.$extension;
        $this->reencodear($archivo->getRealPath(), $destino, $mime);
        $this->limpiarVariantes($destinoDir.'/'.$idCurso.'_firma.', $extension);

        return $destino;
    }

    private function extensionPara(string $mime, UploadedFile $archivo): string
    {
        return match ($mime) {
            'image/png' => 'png',
            'image/jpeg' => $this->esPngReal($archivo->getRealPath()) ? 'png' : 'jpg',
            default => throw new \RuntimeException('La imagen debe ser PNG o JPG.'),
        };
    }

    private function esPngReal(string $ruta): bool
    {
        $bytes = @file_get_contents($ruta, false, null, 0, 4);

        return is_string($bytes) && substr($bytes, 0, 4) === "\x89PNG";
    }

    /**
     * @return array{0:int,1:int} Ancho y alto reales en píxeles.
     */
    private function dimensiones(UploadedFile $archivo): array
    {
        $info = @getimagesize($archivo->getRealPath());
        if ($info === false) {
            throw new \RuntimeException('La plantilla no parece una imagen válida. Usa un archivo PNG o JPG.');
        }

        [$ancho, $alto] = $info;
        if ($ancho <= 0 || $alto <= 0) {
            throw new \RuntimeException('La plantilla no parece una imagen válida. Usa un archivo PNG o JPG.');
        }

        return [$ancho, $alto];
    }

    /**
     * Calcula el lienzo A4 exacto conservando la resolución: si la imagen ya
     * está en proporción, devuelve null (no hay nada que ajustar).
     *
     * @return array{origenW:int,origenH:int,lienzoW:int,lienzoH:int}|null
     */
    private function calcularAjusteA4(int $ancho, int $alto): ?array
    {
        $objetivo = CertificadoConfig::PROPORCION_A4;
        $prop = $ancho / $alto;

        if (abs($prop - $objetivo) <= $objetivo * CertificadoConfig::TOLERANCIA_PROPORCION) {
            return null;
        }

        if ($prop < $objetivo) {
            $lienzoW = (int) round($alto * $objetivo);
            $lienzoH = $alto;
        } else {
            $lienzoW = $ancho;
            $lienzoH = (int) round($ancho / $objetivo);
        }

        return ['origenW' => $ancho, 'origenH' => $alto, 'lienzoW' => $lienzoW, 'lienzoH' => $lienzoH];
    }

    private function reencodear(string $origen, string $destino, string $mime, ?array $ajuste = null): void
    {
        try {
            $manager = new ImageManager($this->resolverDriver());
            $imagen = $manager->read($origen);

            if ($ajuste !== null) {
                $imagen->pad($ajuste['lienzoW'], $ajuste['lienzoH'], 'ffffff');
            }

            if ($mime === 'image/png') {
                $imagen->toPng()->save($destino);
            } else {
                $imagen->toJpeg(85)->save($destino);
            }
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new \RuntimeException('No se pudo procesar la imagen del certificado: '.$e->getMessage(), 0, $e);
        }

        if (! is_file($destino)) {
            throw new \RuntimeException('No se pudo guardar la imagen del certificado. Revisa los permisos de escritura.');
        }
    }

    private function resolverDriver(): string|object
    {
        if (extension_loaded('imagick')) {
            return new ImagickDriver;
        }

        if (extension_loaded('gd')) {
            return new GdDriver;
        }

        throw new \RuntimeException('Falta GD o Imagick en PHP para procesar la imagen del certificado.');
    }

    private function asegurarDirectorio(string $dir): void
    {
        if (! is_dir($dir) && ! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new \RuntimeException('No se pudo crear la carpeta de certificados. Revisa los permisos de escritura.');
        }
    }

    private function limpiarVariantes(string $prefijo, ?string $conservar): void
    {
        // Glob en vez de lista fija: también elimina restos con mayúsculas
        // (`3_firma.JPG`) de subidas anteriores a la normalización.
        foreach (glob($prefijo.'*') ?: [] as $ruta) {
            if (! is_file($ruta)) {
                continue;
            }
            $extension = strtolower((string) pathinfo($ruta, PATHINFO_EXTENSION));
            if (! in_array($extension, CertificadoConfig::EXTENSIONES_IMAGEN, true)) {
                continue;
            }
            if ($conservar !== null && $ruta === $prefijo.$conservar) {
                continue;
            }
            @unlink($ruta);
        }
    }
}
