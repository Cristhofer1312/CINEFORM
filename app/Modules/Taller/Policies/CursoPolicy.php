<?php

namespace Modules\Taller\Policies;

use App\Constants\SecurityAction;
use Modules\Security\Entities\User;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Policies\Traits\ResuelveRolesTaller;

/**
 * Reglas de autorización y pertenencia sobre la entidad Curso.
 */
class CursoPolicy
{
    use ResuelveRolesTaller;

    /**
     * Acceso al contenido formativo (temario, clases, materiales) de un curso.
     * Permitido a Gestores, al Facilitador asignado o a participantes inscritos y aprobados.
     */
    public function verContenido(User $user, Curso $curso): bool
    {
        if ($this->esGestor($user)) {
            return true;
        }

        if ($this->esFacilitador($user) && $this->esFacilitadorDe($user, $curso)) {
            return true;
        }

        $idPersona = $this->personaId($user);
        if (! $idPersona) {
            return false;
        }

        return $curso->inscripciones()
            ->where('id_persona', $idPersona)
            ->where('estado', Inscripcion::ESTADO_APROBADO)
            ->exists();
    }

    /**
     * Editar información o contenido del curso.
     * Permitido a Gestores (Coordinador) o al Facilitador asignado a este curso.
     */
    public function editar(User $user, Curso $curso): bool
    {
        return $this->esGestor($user) || ($this->esFacilitador($user) && $this->esFacilitadorDe($user, $curso));
    }

    /**
     * Responder a una asignación de curso (Aceptar o declinar).
     * Requiere permiso formal RESPONDER_CURSO y pertenencia si es facilitador.
     */
    public function responderAsignacion(User $user, Curso $curso): bool
    {
        if (! $this->autorizacion()->puede('taller.cursos.index', SecurityAction::RESPONDER_CURSO, $user)) {
            return false;
        }

        return $this->esGestor($user) || ($this->esFacilitador($user) && $this->esFacilitadorDe($user, $curso));
    }

    /**
     * Aprobar el curso y abrir la fase de inscripciones (Estado 5 → 6).
     * Requiere permiso APROBAR_CURSO.
     */
    public function aprobar(User $user, Curso $curso): bool
    {
        return $this->autorizacion()->puede('taller.cursos.index', SecurityAction::APROBAR_CURSO, $user);
    }

    /**
     * Transiciones de estado del ciclo de vida del curso.
     * Evalúa permisos según el par (origen, destino) para prevenir IDOR y transiciones indebidas.
     */
    public function cambiarEstado(User $user, Curso $curso, ?int $nuevoEstado = null): bool
    {
        if ($nuevoEstado === null) {
            return $this->esGestor($user) || ($this->esFacilitador($user) && $this->esFacilitadorDe($user, $curso));
        }

        $origen = (int) ($curso->estado_actual?->id_estado ?? 0);
        $destino = (int) $nuevoEstado;

        // 1. Destino EDICIÓN (4)
        if ($destino === \App\Enums\EstadoCurso::EDICION->value) {
            if ($origen === \App\Enums\EstadoCurso::POR_ACEPTAR->value) {
                return $this->responderAsignacion($user, $curso);
            }
            if ($origen === \App\Enums\EstadoCurso::APROBACION->value) {
                return $this->esGestor($user);
            }

            return $this->esGestor($user) || ($this->esFacilitador($user) && $this->esFacilitadorDe($user, $curso));
        }

        // 2. Destino DECLINADO (3)
        if ($destino === \App\Enums\EstadoCurso::DECLINADO->value) {
            if ($origen === \App\Enums\EstadoCurso::APROBACION->value) {
                return $this->esGestor($user);
            }

            if ($origen === \App\Enums\EstadoCurso::POR_ACEPTAR->value) {
                return $this->esFacilitador($user)
                    && $this->esFacilitadorDe($user, $curso)
                    && $this->autorizacion()->puede('taller.cursos.index', SecurityAction::RESPONDER_CURSO, $user);
            }

            return false;
        }

        // 3. Destino APROBACIÓN (5)
        if ($destino === \App\Enums\EstadoCurso::APROBACION->value) {
            return $this->esGestor($user) || ($this->esFacilitador($user) && $this->esFacilitadorDe($user, $curso));
        }

        // 4. Destino INSCRIPCIÓN (6)
        if ($destino === \App\Enums\EstadoCurso::INSCRIPCION->value) {
            return $this->aprobar($user, $curso);
        }

        // 5. Destino RECHAZADO (2)
        if ($destino === \App\Enums\EstadoCurso::RECHAZADO->value) {
            if ($this->esGestor($user)) {
                return true;
            }

            if ($origen === \App\Enums\EstadoCurso::POR_ACEPTAR->value) {
                return $this->esFacilitador($user)
                    && $this->esFacilitadorDe($user, $curso)
                    && $this->autorizacion()->puede('taller.cursos.index', SecurityAction::RESPONDER_CURSO, $user);
            }

            return false;
        }

        // 6. Otros destinos administrativos: EN_CURSO (7), FINALIZADO (8), CERRADO (9), POR_ACEPTAR (1), BORRADOR (10)
        return $this->esGestor($user);
    }

    /**
     * Reasignar facilitador a un curso. Exclusivo de Gestores.
     */
    public function reasignarFacilitador(User $user, Curso $curso): bool
    {
        return $this->esGestor($user);
    }

    /**
     * Gestionar requisitos de postulación del curso.
     */
    public function gestionarRequisitos(User $user, Curso $curso): bool
    {
        return $this->esGestor($user) || ($this->esFacilitador($user) && $this->esFacilitadorDe($user, $curso));
    }

    /**
     * Ver listado de participantes postulados/inscritos.
     * Requiere permiso VER_PARTICIPANTES (o GESTIONAR_CURSO) y ser Gestor o el Facilitador asignado al curso.
     */
    public function verParticipantes(User $user, Curso $curso): bool
    {
        $puedeVer = $this->autorizacion()->puede('taller.cursos.index', SecurityAction::VER_PARTICIPANTES, $user)
            || $this->autorizacion()->puede('taller.cursos.index', SecurityAction::GESTIONAR_CURSO, $user);

        return $puedeVer && ($this->esGestor($user) || $this->esFacilitadorDe($user, $curso));
    }

    /**
     * Calificar evaluaciones del curso.
     * Exclusivo del Facilitador asignado que posea el permiso CALIFICAR_CURSO (grade_course).
     */
    public function calificar(User $user, Curso $curso): bool
    {
        return $this->autorizacion()->puede('taller.cursos.index', SecurityAction::CALIFICAR_CURSO, $user)
            && $this->esFacilitadorDe($user, $curso);
    }

    /**
     * Gestión de asistencias (generar token QR, marcar manual, anular, restaurar).
     * Requiere GESTIONAR_ASISTENCIA (o GESTIONAR_CURSO) y pertenencia.
     */
    public function gestionarAsistencia(User $user, Curso $curso): bool
    {
        $tienePermiso = $this->autorizacion()->puede('taller.cursos.index', SecurityAction::GESTIONAR_ASISTENCIA, $user)
            || $this->autorizacion()->puede('taller.cursos.index', SecurityAction::GESTIONAR_CURSO, $user);

        return $tienePermiso && ($this->esGestor($user) || $this->esFacilitadorDe($user, $curso));
    }

    /**
     * Aprobar o denegar emisión de certificados a participantes.
     * Requiere GESTIONAR_ASISTENCIA (o GESTIONAR_CURSO) y pertenencia.
     */
    public function certificar(User $user, Curso $curso): bool
    {
        $tienePermiso = $this->autorizacion()->puede('taller.cursos.index', SecurityAction::GESTIONAR_ASISTENCIA, $user)
            || $this->autorizacion()->puede('taller.cursos.index', SecurityAction::GESTIONAR_CURSO, $user);

        return $tienePermiso && ($this->esGestor($user) || $this->esFacilitadorDe($user, $curso));
    }

    /**
     * Consultar métricas y resultados de la encuesta de satisfacción del curso.
     * Gestores o el Facilitador asignado a este curso (Resuelve C4).
     */
    public function verSatisfaccion(User $user, Curso $curso): bool
    {
        return $this->esGestor($user) || ($this->esFacilitador($user) && $this->esFacilitadorDe($user, $curso));
    }
}
