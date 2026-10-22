<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \Modules\Taller\Entities\Curso::class => \Modules\Taller\Policies\CursoPolicy::class,
        \Modules\Taller\Entities\Inscripcion::class => \Modules\Taller\Policies\InscripcionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Bypass total para el administrador raíz del sistema (ID 1) en cualquier Gate/Policy
        Gate::before(function ($user, $ability) {
            return app(\App\Services\Security\Autorizacion::class)->esRaiz($user) ? true : null;
        });
    }
}
