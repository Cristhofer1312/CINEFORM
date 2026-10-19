<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    /**
     * Handle an incoming request.
     *
     * Tras autenticar, valida el session_token: si el token guardado en la sesión
     * no coincide con el del usuario (por ejemplo, porque cambió el status de un
     * perfil asignado), se termina la sesión y se redirige al login con un aviso.
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        if (Auth::check()) {
            $user = Auth::user();
            if (!empty($user->session_token) && $request->session()->get('session_token') !== $user->session_token) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['message' => __('Your session was terminated. Please log in again.')], 401);
                }

                return redirect(route('login'))->withErrors(['error-message' => __('Your session was terminated because the status of one of your profiles changed. Please log in again.')]);
            }
        }

        return $next($request);
    }
}