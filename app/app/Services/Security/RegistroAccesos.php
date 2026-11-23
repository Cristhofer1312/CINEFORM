<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Security\Entities\User;
use Throwable;

/**
 * Escribe en security.accesos (tabla de solo inserción) cada intento de inicio
 * de sesión y cada cierre, con la IP real del visitante (TrustProxies) y su
 * navegador.
 *
 * Un fallo al registrar nunca debe impedir que alguien entre o salga: el error
 * se manda al log y el login sigue su curso.
 */
class RegistroAccesos
{
    public const LOGIN = 'login';                       // entró correctamente
    public const FALLIDO = 'fallido';                   // contraseña incorrecta
    public const USUARIO_INEXISTENTE = 'usuario_inexistente';
    public const USUARIO_INACTIVO = 'usuario_inactivo'; // contraseña correcta, cuenta bloqueada
    public const BLOQUEADO = 'bloqueado';               // rechazado por demasiados intentos
    public const LOGOUT = 'logout';

    public function registrar(string $evento, ?User $user = null, ?string $usuario = null): void
    {
        try {
            $request = request();
            DB::table('security.accesos')->insert([
                'user_id' => $user?->id,
                'usuario' => $usuario === null ? $user?->username : mb_substr($usuario, 0, 100),
                'evento' => $evento,
                'ip' => $request?->ip(),
                'user_agent' => $request ? mb_substr((string) $request->userAgent(), 0, 300) : null,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('No se pudo registrar el acceso', ['evento' => $evento, 'error' => $e->getMessage()]);
        }
    }
}
