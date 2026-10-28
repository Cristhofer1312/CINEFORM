<?php

namespace Modules\Taller\Services;

use App\Enums\EstadoCurso;

/**
 * Servicio: Condicional de Estados del Curso (Basado en Capacidades)
 *
 * Resuelve qué ACCIONES puede realizar un usuario en función del estado del curso
 * y sus múltiples contextos (Participante, Dueño del Servicio, Gestor).
 *
 * @author Sistema de Gestión de Cursos
 *
 * @version 2.0
 */
class CondicionalEstadoCurso
{
    /**
     * Mapa de Capacidades por Estado
     *
     * Define qué capacidades están disponibles teóricamente en cada estado.
     */
    private const MAPA_CAPACIDADES = [
        1 => [ // Por Aceptar
            'aceptar_asignacion' => 'operativo',
            'rechazar_asignacion' => 'operativo',
            'reasignar_facilitador' => 'gestion',
            'gestionar' => 'gestion',
        ],
        2 => [ // Rechazado
            'reasignar_facilitador' => 'gestion',
            'ver_motivo' => 'gestion',
            'gestionar' => 'gestion',
        ],
        3 => [ // Declinado
            'ver_motivo' => 'operativo',
            'editar' => 'operativo',
            'enviar_aprobacion' => 'operativo',
            'reasignar_facilitador' => 'gestion',
            'gestionar' => 'gestion',
        ],
        4 => [ // En Edición
            'editar' => 'operativo',
            'enviar_aprobacion' => 'operativo',
            'reasignar_facilitador' => 'gestion',
            'gestionar' => 'gestion',
        ],
        5 => [ // En Aprobación
            'aprobar' => 'gestion',
            'rechazar' => 'gestion',
            'reasignar_facilitador' => 'gestion',
            'en_revision' => 'operativo',
        ],
        6 => [ // Inscripciones
            'inscribirse' => 'publico',
            'cancelar_inscripcion' => 'participante',
            'acceder_contenido' => 'participante',
            'reasignar_facilitador' => 'gestion',
            'finalizar_inscripciones' => 'gestion',
            'gestionar' => 'gestion',
        ],
        7 => [ // En Progreso
            'acceder_contenido' => 'participante',
            'reasignar_facilitador' => 'gestion',
            'gestionar' => 'gestion',
            'finalizar_curso' => 'gestion',
            'gestionar_asistencia' => 'operativo',
            'marcar_asistencia' => 'participante',
        ],
        8 => [ // Finalizado
            'acceder_contenido' => 'participante',
            'emitir_certificado' => 'participante',
            'gestionar' => 'gestion',
            'cerrar_curso' => 'gestion',
            'consultar_asistencia' => 'operativo',
        ],
        9 => [ // Cerrado
            'ver_archivo' => 'publico',
            'emitir_certificado' => 'participante',
            'gestionar' => 'gestion',
        ],
    ];

    /**
     * Estados en los que se permite ajustar la plantilla del certificado.
     *
     * Empieza en Por Aceptar (recién creado, todavía en revisión) y termina en
     * Finalizado. Quedan fuera Rechazado (2), Cerrado (9) y Borrador (10).
     */
    public const ESTADOS_AJUSTE_CERTIFICADO = [
        EstadoCurso::POR_ACEPTAR->value,
        EstadoCurso::DECLINADO->value,
        EstadoCurso::EDICION->value,
        EstadoCurso::APROBACION->value,
        EstadoCurso::INSCRIPCION->value,
        EstadoCurso::EN_CURSO->value,
        EstadoCurso::FINALIZADO->value,
    ];

    /**
     * Indica si el usuario puede entrar al editor de plantilla del certificado.
     *
     * La plantilla y la firma son documentos de la institución, no del curso, así
     * que sólo quien tiene GESTIONAR_CURSO llega hasta aquí. El facilitador es
     * dueño del curso y edita sus datos y contenidos, pero no interviene en el
     * diseño del certificado que emite la institución.
     *
     * Es la única fuente de verdad: la consumen tanto la capacidad que dibuja el
     * botón como la autorización del controlador, para que no puedan divergir.
     *
     * @param  int|string  $estadoId
     * @param  bool  $esGestor
     * @return bool
     */
    public function puedeAjustarCertificado($estadoId, $esGestor)
    {
        return (bool) $esGestor
            && in_array((int) $estadoId, self::ESTADOS_AJUSTE_CERTIFICADO, true);
    }

    /**
     * Resuelve el conjunto de capacidades para el usuario actual
     *
     * @param  int  $estadoId  ID del estado actual
     * @param  bool  $esParticipante  Si el usuario está inscrito
     * @param  bool  $esOperativo  Si el usuario es el facilitador/dueño
     * @param  bool  $esGestor  Si el usuario tiene permisos administrativos
     * @param  int|null  $cuposDisponibles  Cantidad de cupos
     * @param  bool  $puedeInscribirse  Si el usuario tiene el permiso formal de inscripción (RBAC)
     * @return array Lista de capacidades únicas
     */
    public function obtenerCapacidades($estadoId, $esParticipante, $esOperativo, $esGestor, $cuposDisponibles, $puedeInscribirse = true, $puedeVerParticipantes = false)
    {
        $capacidadesTeoricas = self::MAPA_CAPACIDADES[$estadoId] ?? [];
        $misCapacidades = [];

        foreach ($capacidadesTeoricas as $capacidad => $contextoRequerido) {
            $cumpleContexto = false;

            switch ($contextoRequerido) {
                case 'gestion':
                    if ($esGestor) {
                        $cumpleContexto = true;
                    }
                    break;
                case 'operativo':
                    if ($esOperativo) {
                        $cumpleContexto = true;
                    }
                    break;
                case 'participante':
                    if ($esParticipante) {
                        $cumpleContexto = true;
                    }
                    break;
                case 'publico':
                    // Caso especial para inscripción: debe cumplir contexto público,
                    // tener el permiso RBAC, y NO ser el facilitador del curso
                    if ($capacidad === 'inscribirse') {
                        if (! $esParticipante && ! $esOperativo && ($cuposDisponibles === null || $cuposDisponibles > 0) && $puedeInscribirse) {
                            $cumpleContexto = true;
                        }
                    } else {
                        if (! $esParticipante && ($cuposDisponibles === null || $cuposDisponibles > 0)) {
                            $cumpleContexto = true;
                        }
                    }
                    if ($estadoId == 9) {
                        $cumpleContexto = true;
                    } // En cerrado todos ven el aviso
                    break;
            }

            if ($cumpleContexto) {
                $misCapacidades[] = $capacidad;
            }
        }

        // Casos especiales y herencias lógicas
        if ($esGestor) {
            // El gestor siempre puede ver contenidos en cualquier estado
            $misCapacidades[] = 'acceder_contenido';
        }

        if ($esOperativo) {
            // El operativo (facilitador) siempre puede ver contenidos en cualquier estado
            $misCapacidades[] = 'acceder_contenido';
        }

        // El botón de participantes solo se muestra en estados de Inscripción (6) y En Curso (7)
        if ($puedeVerParticipantes && in_array($estadoId, [6, 7])) {
            $misCapacidades[] = 'ver_participantes';
        }

        // Capacidad de ajustar la plantilla del certificado (solo Gestión)
        if ($this->puedeAjustarCertificado($estadoId, $esGestor)) {
            $misCapacidades[] = 'ajustar_certificado';
        }

        // La coordinación también gestiona la asistencia (En Curso) y la consulta (Finalizado).
        if ($esGestor && (int) $estadoId === 7) {
            $misCapacidades[] = 'gestionar_asistencia';
        }
        if ($esGestor && (int) $estadoId === 8) {
            $misCapacidades[] = 'consultar_asistencia';
        }

        // Gestor y operativo pueden ver observaciones/motivo en Rechazado (2) y Declinado (3)
        if (($esGestor || $esOperativo) && in_array((int) $estadoId, [2, 3])) {
            $misCapacidades[] = 'ver_motivo';
        }

        // Capacidad de gestionar certificación y ver satisfacción (Solo Gestor y Operativo, en Finalizado o Cerrado)
        if ($esGestor || $esOperativo) {
            if (in_array($estadoId, [8, 9])) {
                $misCapacidades[] = 'gestionar_certificacion';
                $misCapacidades[] = 'ver_satisfaccion';
            }
        }

        return array_unique($misCapacidades);
    }
}
