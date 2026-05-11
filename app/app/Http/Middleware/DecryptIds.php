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

    private function isEncrypted($value)
    {
        // Un ID encriptado debe ser string, tener longitud razonable y no ser puramente numérico
        return $value && is_string($value) && strlen($value) > 10 && !is_numeric($value);
    }

    private function tryDecrypt($value)
    {
        try {
            $decrypted = Encryptor::decrypt($value);
            // Si el resultado es false, openssl_decrypt falló
            return $decrypted === false ? null : $decrypted;
        } catch (\Exception $e) {
            return null;
        }
    }
}
