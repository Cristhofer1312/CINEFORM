<?php

namespace Modules\Taller\Policies;

use App\Constants\SecurityAction;
use App\Enums\EstadoCurso;
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

    /** Estados en los que el facilitador asignado puede editar el curso. */
    public const ESTADOS_EDITABLES_FACILITADOR = [
        EstadoCurso::DECLINADO->value,
        EstadoCurso::EDICION->value,
    ];

    /** Estados en los que coordinación (gestor) puede editar el curso. */
    public const ESTADOS_EDITABLES_COORDINADOR = [
        EstadoCurso::RECHAZADO->value,
        EstadoCurso::DECLINADO->value,
        EstadoCurso::EDICION->value,
        EstadoCurso::EN_CURSO->value,
    ];

    /**
     * Visibilidad de la ficha de detalle de un curso.
     *
     * Aplica la misma regla que el listado de cursos:
     * - Quien supervisa todos los cursos ve cualquiera en cualquier estado.
     * - El facilitador asignado y los participantes con inscripción ven el curso en cualquier estado.
     * - El resto solo ve cursos públicos en estado INSCRIPCION acordes a su ámbito geográfico.
     * - Excepción de recarga post-rechazo: el facilitador que rechazó la asignación de un curso
     *   aún sin reasignar (RECHAZADO con id_persona nulo) mantiene lectura básica sin acciones.
     */
    public function ver(User $user, Curso $curso): bool
    {
        if ($this->supervisaCursos($user)) {
            return true;
        }

        if ($this->esFacilitadorDe($user, $curso)) {
            return true;
        }

        $persona = $user->personalData;
        $idPersona = $persona?->id_persona;

        if ($idPersona !== null) {
            $tieneInscripcion = $curso->relationLoaded('inscripciones')
                ? $curso->inscripciones->contains(fn ($inscripcion) => (int) $inscripcion->id_persona === (int) $idPersona)
                : $curso->inscripciones()->where('id_persona', $idPersona)->exists();

            if ($tieneInscripcion) {
                return true;
            }
        }

        $estadoActual = (int) ($curso->estado_actual?->id_estado ?? 0);

        if ($estadoActual === EstadoCurso::INSCRIPCION->value) {
            if ((bool) $curso->es_nacional) {
                return true;
            }

            $tieneLocalidades = $curso->relationLoaded('localidades')
                ? $curso->localidades->isNotEmpty()
                : $curso->localidades()->exists();

            if (! $tieneLocalidades) {
                return true;
            }

            $idEstadoUsuario = $persona?->id_estado;
            if ($idEstadoUsuario === null) {
                return false;
            }

            return $curso->relationLoaded('localidades')
                ? $curso->localidades->contains(fn ($loc) => (int) $loc->id === (int) $idEstadoUsuario)
                : $curso->localidades()->where('comun.estados.id', $idEstadoUsuario)->exists();
        }

        if ($estadoActual === EstadoCurso::RECHAZADO->value
            && $curso->id_persona === null
            && $curso->observaciones()->where('creado_por', $user->id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Acceso al contenido formativo (temario, clases, materiales) de un curso.
     * Permitido a Gestores, al Facilitador asignado o a participantes inscritos y aprobados.
     */
    public function verContenido(User $user, Curso $curso): bool
    {
        if ($this->supervisaCursos($user)) {
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
     * Estados del ciclo de vida en los que el usuario puede editar un curso según su rol.
     *
     * @return int[]
     */
    public function estadosEditablesPara(User $user): array
    {
        return $this->esGestor($user)
            ? self::ESTADOS_EDITABLES_COORDINADOR
            : self::ESTADOS_EDITABLES_FACILITADOR;
    }

    /**
     * Comprueba que el usuario puede editar el curso y que este se encuentra
     * en un estado editable para su rol.
     */
    public function editarEnEstadoActual(User $user, Curso $curso): bool
    {
        if (! $this->editar($user, $curso)) {
            return false;
        }

        $estadoActual = (int) ($curso->estado_actual?->id_estado ?? 0);

        return in_array($estadoActual, $this->estadosEditablesPara($user), true);
    }

    /**
     * Acceder a los recursos del certificado (plantilla, firma) y a su vista previa.
     * Permitido a gestores de cursos/certificados o al facilitador asignado; nunca a inscritos.
     */
    public function previsualizarCertificado(User $user, Curso $curso): bool
    {
        return $this->esGestor($user)
            || $this->certificar($user, $curso)
            || ($this->esFacilitador($user) && $this->esFacilitadorDe($user, $curso));
    }

    /**
     * Responder a una asignación de curso (aceptar o declinar).
     * Requiere permiso RESPONDER_CURSO y ser el facilitador asignado a ESTE curso.
     * Un gestor no responde por otro facilitador: solo puede aceptar lo que le corresponde.
     */
    public function responderAsignacion(User $user, Curso $curso): bool
    {
        if (! $this->autorizacion()->puede('taller.cursos.index', SecurityAction::RESPONDER_CURSO, $user)) {
            return false;
        }

        return $this->esFacilitador($user) && $this->esFacilitadorDe($user, $curso);
    }

    /**
     * Aprobar el curso y abrir la fase de inscripciones (Estado 5 → 6).
     * Requiere permiso APROBAR_CURSO.
     */
    public function aprobar(User $user, Curso $curso): bool
    {
        // Conflicto de interés: nadie aprueba su propia propuesta.
        return $this->autorizacion()->puede('taller.cursos.index', SecurityAction::APROBAR_CURSO, $user)
            && ! $this->esFacilitadorDe($user, $curso);
    }

    /**
     * Revisión de coordinación sobre un curso: gestión global y sin conflicto de
     * interés (quien es el facilitador asignado no revisa ni reasigna su curso).
     */
    public function revisarComoCoordinacion(User $user, Curso $curso): bool
    {
        return $this->esGestor($user) && ! $this->esFacilitadorDe($user, $curso);
    }

    /**
     * Gestión de coordinación que no decide sobre el propio trabajo (datos del
     * curso, foto, público objetivo): basta con ser gestor.
     */
    public function gestionar(User $user, Curso $curso): bool
    {
        return $this->esGestor($user);
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
                return $this->revisarComoCoordinacion($user, $curso);
            }

            return $this->esGestor($user) || ($this->esFacilitador($user) && $this->esFacilitadorDe($user, $curso));
        }

        // 2. Destino DECLINADO (3)
        if ($destino === \App\Enums\EstadoCurso::DECLINADO->value) {
            if ($origen === \App\Enums\EstadoCurso::APROBACION->value) {
                return $this->revisarComoCoordinacion($user, $curso);
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
            if ($this->revisarComoCoordinacion($user, $curso)) {
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
     * Reasignar facilitador a un curso. Exclusivo de Gestores que no son el
     * facilitador asignado: nadie reasigna su propio curso.
     */
    public function reasignarFacilitador(User $user, Curso $curso): bool
    {
        return $this->revisarComoCoordinacion($user, $curso);
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
     * Requiere VER_PARTICIPANTES y alcance sobre el curso (D7: sin GESTIONAR_CURSO como comodín).
     */
    public function verParticipantes(User $user, Curso $curso): bool
    {
        return $this->autorizacion()->puede('taller.cursos.index', SecurityAction::VER_PARTICIPANTES, $user)
            && $this->alcanzaCurso($user, $curso);
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
     * Requiere GESTIONAR_ASISTENCIA y alcance sobre el curso (D7).
     */
    public function gestionarAsistencia(User $user, Curso $curso): bool
    {
        return $this->autorizacion()->puede('taller.cursos.index', SecurityAction::GESTIONAR_ASISTENCIA, $user)
            && $this->alcanzaCurso($user, $curso);
    }

    /**
     * Aprobar o denegar emisión de certificados a participantes.
     * Requiere GESTIONAR_ASISTENCIA y alcance sobre el curso (D7).
     */
    public function certificar(User $user, Curso $curso): bool
    {
        return $this->autorizacion()->puede('taller.cursos.index', SecurityAction::GESTIONAR_ASISTENCIA, $user)
            && $this->alcanzaCurso($user, $curso);
    }

    /**
     * Consultar métricas y resultados de la encuesta de satisfacción del curso.
     * Gestores o el Facilitador asignado a este curso (Resuelve C4).
     */
    public function verSatisfaccion(User $user, Curso $curso): bool
    {
        return $this->supervisaCursos($user) || ($this->esFacilitador($user) && $this->esFacilitadorDe($user, $curso));
    }

    /**
     * Descargar el reporte de satisfacción (D2): quien puede consultar los
     * resultados del curso y además tiene REPORTE_SATISFACCION. El facilitador
     * solo consulta.
     */
    public function reporteSatisfaccion(User $user, Curso $curso): bool
    {
        return $this->verSatisfaccion($user, $curso)
            && $this->autorizacion()->puede('taller.cursos.index', SecurityAction::REPORTE_SATISFACCION, $user);
    }
}
