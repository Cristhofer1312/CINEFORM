<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\Encryptor;

class DecryptIds
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$parameters): Response
    {
        // 1. Desencriptar parámetros de RUTA
        $route = $request->route();
        if ($route) {
            $paramsToDecrypt = count($parameters) > 0 ? $parameters : array_keys($route->parameters());

            foreach ($paramsToDecrypt as $paramName) {
                $value = $route->parameter($paramName);
                if ($this->isEncrypted($value)) {
                    $decrypted = $this->tryDecrypt($value);
                    if ($decrypted !== null && $decrypted !== false) {
                        $route->setParameter($paramName, $decrypted);
                    }
                }
            }
        }

        // 2. Desencriptar parámetros de ENTRADA (Query/Post/JSON)
        $allInputs = $request->all();
        $changed = false;

        foreach ($allInputs as $key => $value) {
            if ($this->isEncrypted($value)) {
                $decrypted = $this->tryDecrypt($value);
                if ($decrypted !== null && $decrypted !== false) {
                    $allInputs[$key] = $decrypted;
                    $changed = true;
                }
            } elseif (is_array($value)) {
                array_walk_recursive($value, function(&$item) use (&$changed) {
                    if ($this->isEncrypted($item)) {
                        $decrypted = $this->tryDecrypt($item);
                        if ($decrypted !== null && $decrypted !== false) {
                            $item = $decrypted;
                            $changed = true;
                        }
                    }
                });
                $allInputs[$key] = $value;
            }
        }

        if ($changed) {
            $request->replace($allInputs);
        }

        return $next($request);
    }

    /**
     * Determina si un valor podría ser un ID cifrado.
     * Criterios estrictos para minimizar falsos positivos:
     * - Debe ser string de longitud razonable (no muy corto)
     * - No debe ser puramente numérico (sería un ID en claro)
     * - Debe ser base64 válido (los IDs cifrados siempre lo son)
     */
    private function isEncrypted($value): bool
    {
        if (!$value || !is_string($value) || strlen($value) < 16 || is_numeric($value)) {
            return false;
        }
        // Verificar que sea base64 válido estricto
        return base64_decode($value, true) !== false;
    }

    /**
     * Intenta descifrar un valor. Solo acepta el resultado si es un entero positivo,
     * lo que corresponde a un ID de base de datos real.
     */
    private function tryDecrypt($value): mixed
    {
        try {
            $decrypted = Encryptor::decrypt($value);
            // Solo aceptar si el resultado descifrado es un entero positivo válido (ID de DB)
            if ($decrypted !== false && $decrypted !== null && is_numeric($decrypted) && (int)$decrypted > 0) {
                return (int)$decrypted;
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
