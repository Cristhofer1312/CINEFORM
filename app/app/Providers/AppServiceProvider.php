<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Sanctum::ignoreMigrations();

        $this->app->singleton(\App\Services\Security\Autorizacion::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Modules\Taller\Entities\Curso::observe(\App\Observers\CursoObserver::class);

        // RNF-01: política única de contraseñas para registro, alta de usuarios,
        // cambio de contraseña y recuperación. Cambiarla aquí la cambia en todo el sistema.
        Password::defaults(fn () => Password::min(10)->mixedCase()->numbers()->symbols()->max(72));
    }
}
