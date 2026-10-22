<?php

namespace Modules\Taller\Policies\Traits;

use App\Constants\SecurityAction;
use App\Services\Security\Autorizacion;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;

/**
 * Trait compartido por las Policies del módulo Taller.
 *
 * Centraliza la definición de "Gestor" y "Facilitador" contra el servicio
 * de Autorización y las comprobaciones de pertenencia de cursos y personas.
 */
trait ResuelveRolesTaller
{
    protected function autorizacion(): Autorizacion
    {
        return app(Autorizacion::class);
    }

    /**
     * Un "Gestor" en Taller es quien posee la capacidad GESTIONAR_CURSO
     * sobre el proceso principal de cursos (típicamente Coordinador / Administrador).
     */
    public function esGestor(User $user): bool
    {
        return $this->autorizacion()->puede('taller.cursos.index', SecurityAction::GESTIONAR_CURSO, $user);
    }

    /**
     * Un usuario con capacidad de "Facilitador" es quien posee EDITAR_CURSO
     * sobre el proceso principal de cursos.
     */
    public function esFacilitador(User $user): bool
    {
        return $this->autorizacion()->puede('taller.cursos.index', SecurityAction::EDITAR_CURSO, $user);
    }

    /**
     * Comprueba si el usuario es formalmente el facilitador asignado al curso específico.
     */
    public function esFacilitadorDe(User $user, Curso $curso): bool
    {
        $idPersona = $this->personaId($user);

        return $idPersona !== null && (int) $curso->id_persona === (int) $idPersona;
    }

    /**
     * Retorna el id_persona asociado al usuario, si existe.
     */
    public function personaId(User $user): ?int
    {
        return optional($user->personalData)->id_persona;
    }
}
