<?php

namespace App\Helpers;

use Config;
use Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

/**
 * Encryptor — Capa de cifrado de IDs para CINEFORM.
 *
 * MIGRACIÓN PROGRESIVA (2026):
 * - encrypt() usa Crypt::encryptString() de Laravel: AES-256-CBC con IV aleatorio
 *   por operación + firma HMAC. Criptográficamente seguro.
 * - decrypt() intenta primero el método Laravel; si falla, cae al método legacy
 *   (openssl con IV fijo derivado del APP_KEY) para compatibilidad con IDs
 *   ya cifrados en base de datos o sesiones activas.
 *
 * Una vez confirmado que no quedan tokens legacy en producción, se puede
 * eliminar el bloque "Legacy fallback" de decrypt().
 */
class Encryptor
{
    // ─── Método nuevo (Laravel Crypt) ────────────────────────────────────────

    /**
     * Cifra un valor usando Crypt::encryptString() de Laravel.
     * Produce un ciphertext diferente cada vez (IV aleatorio).
     */
    public static function encrypt($value): string
    {
        return Crypt::encryptString((string)$value);
    }

    /**
     * Descifra un valor.
     * Intenta primero con Crypt::decryptString() (método nuevo).
     * Si falla, intenta con el método legacy (IV fijo) para compatibilidad.
     */
    public static function decrypt($value): mixed
    {
        if (empty($value)) {
            return null;
        }

        // Intento 1: método nuevo (Laravel Crypt)
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // No es un ciphertext Laravel — probar método legacy
        }

        // Intento 2: método legacy (openssl IV fijo — DEPRECADO)
        // TODO: Eliminar este bloque cuando se confirme que no hay tokens legacy en producción
        try {
            $key = hash('sha256', Str::substr(Config::get('app.key'), 7));
            $iv  = substr(hash('sha256', Str::substr(Config::get('app.key'), 7)), 0, 16);
            $raw = base64_decode($value);
            if ($raw === false) {
                return null;
            }
            $result = openssl_decrypt($raw, Config::get('app.cipher'), $key, 0, $iv);
            return ($result === false) ? null : $result;
        } catch (\Exception $e) {
            return null;
        }
    }

    // ─── Métodos auxiliares (compatibilidad interna) ─────────────────────────

    /** @deprecated Solo usado por el fallback legacy. No usar en código nuevo. */
    public static function method(): string
    {
        return Config::get('app.cipher');
    }

    /** @deprecated Solo usado por el fallback legacy. No usar en código nuevo. */
    public static function hash_key(): string
    {
        return hash('sha256', Str::substr(Config::get('app.key'), 7));
    }

    /** @deprecated Solo usado por el fallback legacy. No usar en código nuevo. */
    public static function iv(): string
    {
        $secret_iv = Str::substr(Config::get('app.key'), 7);
        return substr(hash('sha256', $secret_iv), 0, 16);
    }
}
