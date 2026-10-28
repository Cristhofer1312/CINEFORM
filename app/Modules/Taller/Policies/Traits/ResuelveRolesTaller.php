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
     * D7: alcance global. SUPERVISAR_CURSOS extiende a todos los cursos las
     * acciones que el usuario ya tiene; por sí solo no concede ninguna acción.
     */
    public function supervisaCursos(User $user): bool
    {
        return $this->autorizacion()->puede('taller.cursos.index', SecurityAction::SUPERVISAR_CURSOS, $user);
    }

    /**
     * El curso está al alcance del usuario: lo supervisa todo o es su facilitador.
     */
    public function alcanzaCurso(User $user, Curso $curso): bool
    {
        return $this->supervisaCursos($user) || $this->esFacilitadorDe($user, $curso);
    }

    /**
     * Gestión de cualquier curso: la acción GESTIONAR_CURSO con alcance global
     * (SUPERVISAR_CURSOS). No es un perfil: se decide por esos dos permisos.
     */
    public function esGestor(User $user): bool
    {
        return $this->autorizacion()->puede('taller.cursos.index', SecurityAction::GESTIONAR_CURSO, $user)
            && $this->supervisaCursos($user);
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
