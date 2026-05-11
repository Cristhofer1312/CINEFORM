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
        $routeName = $request->route()?->getName();
        if ($routeName) {
            $ruta = explode('.', $routeName);
            if ($ruta[0] === 'users') {
                // Verificar si el perfil tiene al menos el permiso 'view' 
                // para la ruta completa registrada en security.processes
                if (!hasPermissionRoute($routeName, \App\Constants\SecurityAction::VER)) {
                    abort(403, 'No tiene permisos para acceder a esta sección.');
                }
            }
        }

        return $next($request);
    }
}
