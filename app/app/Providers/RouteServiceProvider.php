<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Toda la web: por usuario si hay sesión (varios funcionarios pueden salir
        // por la misma IP pública de la oficina) y por IP si no la hay.
        RateLimiter::for('web', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(300)->by('usuario:'.$request->user()->id)
                : Limit::perMinute(120)->by('ip:'.$request->ip());
        });

        // Pantallas de entrada (login, recuperación, registro, captcha) por IP.
        // Los intentos fallidos por cuenta los cuenta SecurityController::login,
        // para no bloquear a quien entra bien.
        RateLimiter::for('entrada', function (Request $request) {
            if ($request->isMethod('get')) {
                return Limit::perMinute(60)->by('entrada-ver:'.$request->ip());
            }

            $limites = [Limit::perMinute(10)->by('entrada-ip:'.$request->ip())];

            // Pedir el enlace de recuperación envía un correo: tope por destinatario.
            if ($request->routeIs('recovery') && $request->isMethod('post')) {
                $correo = mb_strtolower(trim((string) $request->input('email')));
                $limites[] = Limit::perMinutes(15, 3)->by('recuperacion:'.sha1($correo));
            }

            return $limites;
        });

        // Envío de códigos por correo: por destinatario, para que nadie use el
        // sistema para llenar de correos el buzón de otra persona.
        RateLimiter::for('codigo-correo', function (Request $request) {
            $destino = mb_strtolower(trim((string) $request->input('email', $request->user()?->email)));

            return [
                Limit::perMinute(5)->by('codigo-ip:'.$request->ip()),
                Limit::perHour(5)->by('codigo-correo:'.sha1($destino)),
            ];
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
