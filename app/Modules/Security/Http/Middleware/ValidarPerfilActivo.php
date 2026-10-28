<?php

namespace Modules\Security\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidarPerfilActivo
{
    /**
     * Rutas exentas de validación de perfil activo.
     */
    protected array $except = [
        'login',
        'logout',
        'usuario.cambiar_perfil',
        'usuario.set_perfil',
        'registro.home',
        'show_avatar',
        'captcha',
        'language',
        'recovery',
        'register',
        'main',
        'registro.*',
        'taller.certificados.verificar',
        'taller.asistencia.qr-image',
    ];

    public function handle(Request $request, Closure $next)
    {
        // El servicio es singleton: en workers de larga vida (y en pruebas) no
        // debe arrastrar los perfiles memorizados de la petición anterior.
        app(\App\Services\Security\Autorizacion::class)->olvidarPerfiles();

        if (!Auth::check() || app(\App\Services\Security\Autorizacion::class)->esRaiz()) {
            return $next($request);
        }

        foreach ($this->except as $route) {
            if ($request->routeIs($route)) {
                return $next($request);
            }
        }

        $user = Auth::user();

        // Validar que el usuario no esté desactivado en el sistema
        if ((int) ($user->active ?? 0) !== 1) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tu usuario se encuentra inactivo.'], 403);
            }

            return redirect()->route('login')->withErrors([
                'error-message' => 'Tu usuario se encuentra inactivo. Contacta al administrador.'
            ]);
        }

        // Los permisos son la suma de los perfiles activos: no hay perfil que
        // elegir. Basta con tener al menos uno; el principal queda en sesión
        // solo como dato informativo.
        $principal = app(\App\Services\Security\Autorizacion::class)->perfilPrincipalPara($user);

        if ($principal === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tu usuario no tiene perfiles activos asignados.'], 403);
            }

            return redirect()->route('login')->withErrors([
                'error-message' => 'Tu usuario no tiene perfiles activos asignados. Contacta al administrador.'
            ]);
        }

        if ((int) session('profile_id') !== $principal) {
            session()->put('profile_id', $principal);
        }

        return $next($request);
    }
}
