<?php

namespace Modules\Taller\Services;

/**
 * Única fuente de verdad para la configuración del certificado en disco.
 *
 * Antes las coordenadas base, las rutas de plantilla/firma/coords y las
 * extensiones admitidas estaban duplicadas entre CertificadoService y
 * CrearCursoController::coordsBase(). Cualquier cambio había que hacerlo
 * en dos sitios y divergían.
 */
final class CertificadoConfig
{
    public const EXTENSIONES_IMAGEN = ['png', 'jpg', 'jpeg'];

    /** Proporción A4 horizontal (297/210) y tolerancia aceptada. */
    public const PROPORCION_A4 = 1.4142857;

    public const TOLERANCIA_PROPORCION = 0.03;

    public static function coordsBase(): array
    {
        return [
            'nombre' => ['x' => 40.9, 'y' => 78.2, 'size' => 24, 'w' => 220],
            'dni' => ['x' => 159.4, 'y' => 92.6, 'size' => 12, 'w' => 25],
            'qr' => ['x' => 9.8, 'y' => 154.6, 'w' => 20, 'h' => 20],
            'code' => ['x' => 0.0, 'y' => 177.4, 'size' => 8, 'w' => 50],
            'firma' => ['x' => 178.1, 'y' => 130.2, 'w' => 50, 'h' => 20],
        ];
    }

    public static function dirBase(): string
    {
        return storage_path('app/public/Certificados');
    }

    public static function dirCursos(): string
    {
        return self::dirBase().'/cursos';
    }

    /**
     * Busca un archivo por nombre base probando las extensiones admitidas
     * sin distinguir mayúsculas (`firma.JPG` subido desde Windows vale igual).
     */
    public static function buscarPrimero(string $directorio, string $baseSinExtension): ?string
    {
        if (! is_dir($directorio) || str_contains($baseSinExtension, '/')) {
            return null;
        }

        $porExtension = [];
        foreach (glob($directorio.'/'.$baseSinExtension.'.*') ?: [] as $ruta) {
            if (is_file($ruta)) {
                $porExtension[strtolower((string) pathinfo($ruta, PATHINFO_EXTENSION))] = $ruta;
            }
        }

        foreach (self::EXTENSIONES_IMAGEN as $ext) {
            if (isset($porExtension[$ext])) {
                return $porExtension[$ext];
            }
        }

        return null;
    }

    public static function dirWizard(string $token): string
    {
        return self::dirBase().'/wizard/'.$token;
    }

    public static function rutaWizardPlantilla(string $token): ?string
    {
        return self::buscarPrimero(self::dirWizard($token), 'plantilla');
    }

    public static function rutaWizardFirma(string $token): ?string
    {
        return self::buscarPrimero(self::dirWizard($token), 'firma');
    }

    public static function rutaPlantilla(int|string $idCurso): ?string
    {
        $propia = self::buscarPrimero(self::dirCursos(), (string) $idCurso);
        if ($propia !== null) {
            return $propia;
        }

        return self::buscarPrimero(self::dirBase(), 'plantilla');
    }

    public static function rutaFirma(int|string $idCurso): ?string
    {
        return self::buscarPrimero(self::dirCursos(), $idCurso.'_firma');
    }

    public static function rutaCoords(int|string $idCurso): string
    {
        return self::dirCursos().'/'.$idCurso.'.json';
    }

    public static function rutaDefaults(): string
    {
        return self::dirBase().'/defaults.json';
    }

    /**
     * Carga coordenadas fusionadas: base → defaults.json → {id}.json
     */
    public static function coordsPara(int|string $idCurso): array
    {
        $coords = self::coordsBase();

        foreach ([self::rutaDefaults(), self::rutaCoords($idCurso)] as $ruta) {
            if (is_file($ruta)) {
                $datos = json_decode((string) @file_get_contents($ruta), true);
                if (is_array($datos)) {
                    $coords = array_merge($coords, $datos);
                }
            }
        }

        return $coords;
    }

    /**
     * Valida coordenadas enviadas desde el editor antes de persistirlas.
     *
     * @return string|null Mensaje de error en español, o null si son válidas.
     */
    public static function validarCoords(mixed $coords): ?string
    {
        if (! is_array($coords)) {
            return 'Las coordenadas del certificado no tienen un formato válido.';
        }

        $permitidas = ['nombre', 'dni', 'qr', 'code', 'firma'];
        foreach ($coords as $clave => $val) {
            if (! in_array($clave, $permitidas, true) || ! is_array($val)) {
                return 'Las coordenadas del certificado contienen un bloque no reconocido.';
            }

            foreach (['x', 'y'] as $eje) {
                if (isset($val[$eje]) && (! is_numeric($val[$eje]) || $val[$eje] < 0 || $val[$eje] > 297)) {
                    return 'Las coordenadas del certificado están fuera de la hoja A4 horizontal.';
                }
            }

            foreach (['w', 'h'] as $dim) {
                if (isset($val[$dim]) && (! is_numeric($val[$dim]) || $val[$dim] < 5 || $val[$dim] > 280)) {
                    return 'Las dimensiones de un bloque del certificado están fuera de rango.';
                }
            }

            if (isset($val['size']) && (! is_numeric($val['size']) || $val['size'] < 6 || $val['size'] > 40)) {
                return 'El tamaño de letra de un bloque del certificado está fuera de rango (6–40).';
            }
        }

        return null;
    }
}
