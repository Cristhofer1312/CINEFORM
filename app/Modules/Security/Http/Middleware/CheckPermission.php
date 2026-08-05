<?php

namespace Modules\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Security\Entities\Process;
use App\Constants\SecurityAction;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $processRoute  La ruta o código del proceso a proteger (ej: 'cursos')
     * @param  int  $requiredActionId  El ID de la constante SecurityAction que se requiere
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $processRoute, int $requiredActionId)
    {
        // El usuario administrador del sistema (ID 1) siempre tiene acceso total.
        // Se registra en log de auditoría para detectar uso indebido de la cuenta raíz.
        if (Auth::check() && Auth::id() === 1) {
            \Illuminate\Support\Facades\Log::channel('single')->info('[ADMIN-BYPASS] Acceso sin verificación RBAC', [
                'user_id'    => Auth::id(),
                'process'    => $processRoute,
                'action_id'  => $requiredActionId,
                'ip'         => $request->ip(),
                'url'        => $request->fullUrl(),
                'method'     => $request->method(),
            ]);
            return $next($request);
        }

        if (!hasPermissionRoute($processRoute, $requiredActionId)) {
            abort(403, 'Acceso denegado: No posee el permiso necesario para ejecutar esta acción en el módulo ' . $processRoute);
        }

        return $next($request);
    }
}
