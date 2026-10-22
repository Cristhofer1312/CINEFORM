<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
    }
}
