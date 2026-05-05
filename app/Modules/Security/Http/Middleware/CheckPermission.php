<?php

namespace Modules\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        if (!hasPermissionRoute($processRoute, $requiredActionId)) {
            abort(403, 'Acceso denegado: No posee el permiso necesario para ejecutar esta acción en el módulo ' . $processRoute);
        }

        return $next($request);
    }
}
