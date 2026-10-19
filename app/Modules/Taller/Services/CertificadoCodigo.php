<?php

namespace Modules\Taller\Services;

use Modules\Comun\Entities\PersonalData;
use Modules\Taller\Entities\Curso;

/**
 * Emisión y lectura del código impreso en el certificado.
 *
 * Formato nuevo:  {CODIGO}-{DNI}-{HMAC6}
 * Formato legado: {CODIGO}-{DNI}  (QR ya impresos, se siguen aceptando)
 *
 * El HMAC evita que el código sea enumerable: sin la firma no se puede
 * forjar un certificado válido aunque se conozca el patrón PROCINEC.
 */
final class CertificadoCodigo
{
    private const PATRON_CODIGO = '/^LAB-[A-Z0-9]+-\d{4}$/';

    private const PATRON_DNI = '/^\d{6,9}$/';

    private const PATRON_HMAC = '/^[a-f0-9]{6}$/';

    public static function emitir(Curso $curso, PersonalData $persona): string
    {
        $base = $curso->codigo.'-'.$persona->dni;

        return $base.'-'.substr(
            hash_hmac('sha256', $curso->codigo.'|'.$persona->dni, (string) config('app.key')),
            0,
            6
        );
    }

    /**
     * @return array{codigo:string, dni:string, hmac:?string, formato:string}|null
     */
    public static function parse(string $codigo): ?array
    {
        $partes = explode('-', trim($codigo));
        if (count($partes) < 3) {
            return null;
        }

        $candidatoHmac = end($partes);
        $conHmac = (bool) preg_match(self::PATRON_HMAC, (string) $candidatoHmac);

        if ($conHmac) {
            $hmac = array_pop($partes);
            $dni = array_pop($partes);
            $cursoCodigo = implode('-', $partes);

            if (! preg_match(self::PATRON_CODIGO, $cursoCodigo) || ! preg_match(self::PATRON_DNI, (string) $dni)) {
                return null;
            }

            return ['codigo' => $cursoCodigo, 'dni' => (string) $dni, 'hmac' => (string) $hmac, 'formato' => 'hmac'];
        }

        $dni = array_pop($partes);
        $cursoCodigo = implode('-', $partes);

        if (! preg_match(self::PATRON_CODIGO, $cursoCodigo) || ! preg_match(self::PATRON_DNI, (string) $dni)) {
            return null;
        }

        return ['codigo' => $cursoCodigo, 'dni' => (string) $dni, 'hmac' => null, 'formato' => 'legacy'];
    }

    public static function firmaValida(string $codigoCurso, string $dni, string $hmac): bool
    {
        $esperado = substr(
            hash_hmac('sha256', $codigoCurso.'|'.$dni, (string) config('app.key')),
            0,
            6
        );

        return hash_equals($esperado, strtolower($hmac));
    }
}
