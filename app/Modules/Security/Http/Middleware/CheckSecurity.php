<?php

namespace Modules\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App;


class CheckSecurity {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {

        if (session()->get('language') != null) {
            /*
             * Establece el locale de Laravel
             */
            App::setLocale(session()->get('language'));
        }

        // Verificar acceso a rutas administrativas 'users.*'
        // Se usa el sistema RBAC en lugar de un ID de usuario hardcodeado.
        //
        // Lógica de resolución:
        // 1. Intentar con la ruta completa (ej. 'users.asignar_perfil' → Process 6)
        // 2. Si no hay match, usar solo la base 'users' (ej. 'users.list' → Process 2)
        $routeName = $request->route()?->getName();
        if ($routeName) {
            $ruta = explode('.', $routeName);
            if ($ruta[0] === 'users') {
                $tienePermiso = hasPermissionRoute($routeName, \App\Constants\SecurityAction::VER);
                
                // Fallback: si la ruta exacta no tiene process, buscar por la base
                if (!$tienePermiso && count($ruta) > 1) {
                    $tienePermiso = hasPermissionRoute($ruta[0], \App\Constants\SecurityAction::VER);
                }

                if (!$tienePermiso) {
                    abort(403, 'No tiene permisos para acceder a esta sección.');
                }
            }
        }

        return $next($request);
    }
}
