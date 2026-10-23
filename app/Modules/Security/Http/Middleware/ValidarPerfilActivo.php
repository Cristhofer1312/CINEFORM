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

        $perfilEnSesion = session('profile_id');

        if ($perfilEnSesion) {
            $esValido = $user->perfiles()
                ->where('security.profiles.id', (int) $perfilEnSesion)
                ->where('security.profiles.active', true)
                ->where('security.profiles_users.status', 1)
                ->exists();

            if ($esValido) {
                return $next($request);
            }

            // Perfil revocado o desactivado
            session()->forget('profile_id');
        }

        // Si no tiene perfil en sesión o el anterior fue invalidado, consultar perfiles activos del usuario
        $perfilesActivos = $user->perfiles()
            ->where('security.profiles.active', true)
            ->where('security.profiles_users.status', 1)
            ->pluck('security.profiles.id');

        if ($perfilesActivos->isEmpty()) {
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

        if ($perfilesActivos->count() === 1) {
            session()->put('profile_id', (int) $perfilesActivos->first());
            return $next($request);
        }

        // Múltiples perfiles: exigir selección
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Por favor selecciona un perfil activo para continuar.'], 403);
        }

        return redirect()->route('usuario.cambiar_perfil')->withErrors([
            'error-message' => 'Tu perfil anterior ya no está disponible. Selecciona un perfil activo para continuar.'
        ]);
    }
}
