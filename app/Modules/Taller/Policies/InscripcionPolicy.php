<?php

namespace Modules\Taller\Policies;

use App\Constants\SecurityAction;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Policies\Traits\ResuelveRolesTaller;

/**
 * Reglas de autorización y pertenencia sobre la entidad Inscripcion.
 */
class InscripcionPolicy
{
    use ResuelveRolesTaller;

    /**
     * Comprueba si el usuario autenticado es el participante titular de la inscripción.
     */
    public function esDuenio(User $user, Inscripcion $inscripcion): bool
    {
        $idPersona = $this->personaId($user);

        return $idPersona !== null && (int) $inscripcion->id_persona === (int) $idPersona;
    }

    /**
     * Comprueba si el usuario es gestor o facilitador del curso asociado a la inscripción.
     */
    protected function puedeGestionarCurso(User $user, Inscripcion $inscripcion): bool
    {
        $curso = $inscripcion->curso;
        if (!$curso) {
            return $this->esGestor($user);
        }

        return $this->esGestor($user) || ($this->esFacilitador($user) && $this->esFacilitadorDe($user, $curso));
    }

    /**
     * Ver detalle de la inscripción y respuestas de requisitos.
     */
    public function ver(User $user, Inscripcion $inscripcion): bool
    {
        return $this->esDuenio($user, $inscripcion) || $this->puedeGestionarCurso($user, $inscripcion);
    }

    /**
     * Postularse / inscribirse en un curso.
     */
    public function inscribirse(User $user, Curso $curso): bool
    {
        return $this->autorizacion()->puede('taller.cursos.index', SecurityAction::INSCRIBIRSE_CURSO, $user);
    }

    /**
     * Cancelar o retirar inscripción.
     * Permitido al participante titular o a un Gestor con permiso explícito.
     */
    public function cancelar(User $user, Inscripcion $inscripcion): bool
    {
        return $this->esDuenio($user, $inscripcion)
            || ($this->esGestor($user) && $this->autorizacion()->puede('taller.cursos.index', SecurityAction::CANCELAR_INSCRIPCION, $user));
    }

    /**
     * Corregir postulación observada / rechazada.
     * Exclusivo del participante titular.
     */
    public function corregir(User $user, Inscripcion $inscripcion): bool
    {
        return $this->esDuenio($user, $inscripcion);
    }

    /**
     * Aprobar, rechazar o denegar la postulación.
     * Gestores o el Facilitador asignado al curso.
     */
    public function gestionar(User $user, Inscripcion $inscripcion): bool
    {
        return $this->puedeGestionarCurso($user, $inscripcion);
    }

    /**
     * Rehabilitar una postulación previamente rechazada o cancelada.
     * Exclusivo de Gestores (Coordinador / Admin) con permiso para gestionar/cancelar.
     */
    public function rehabilitar(User $user, Inscripcion $inscripcion): bool
    {
        return $this->esGestor($user) && (
            $this->autorizacion()->puede('taller.cursos.index', SecurityAction::CANCELAR_INSCRIPCION, $user)
            || $this->autorizacion()->puede('taller.cursos.index', SecurityAction::GESTIONAR_CURSO, $user)
        );
    }

    /**
     * Ver o descargar documentos adjuntos a las respuestas de requisitos.
     * Resuelve C3: Unifica para titular, gestores y facilitador del curso.
     */
    public function verDocumento(User $user, Inscripcion $inscripcion): bool
    {
        return $this->esDuenio($user, $inscripcion) || $this->puedeGestionarCurso($user, $inscripcion);
    }
}
