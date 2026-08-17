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

        // Verificar acceso a rutas administrativas 'users.*' según el sistema RBAC
        $routeName = $request->route()?->getName();
        if ($routeName) {
            $ruta = explode('.', $routeName);
            if ($ruta[0] === 'users') {
                // 1. Probar la ruta exacta de la petición (ej. 'users.asignar_perfil' o 'users.list')
                $tienePermiso = hasPermissionRoute($routeName, \App\Constants\SecurityAction::VER);

                // 2. Mapeo para sub-rutas AJAX del módulo "Asignar Perfil":
                // Valida contra el permiso RBAC asignado al proceso 'users.asignar_perfil' para cualquier perfil autorizador (Coordinador, Administrador, etc.)
                if (!$tienePermiso && in_array($routeName, ['users.search_persona', 'users.assign_profiles'])) {
                    $tienePermiso = hasPermissionRoute('users.asignar_perfil', \App\Constants\SecurityAction::VER);
                }

                // 3. Fallback: si la ruta no tiene un proceso específico registrado, probar por el módulo base 'users'
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
