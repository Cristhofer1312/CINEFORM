<?php

namespace Modules\Taller\Services;

use App\Constants\SecurityAction;

/**
 * Servicio: Condicional de Estados del Curso (Basado en Capacidades)
 * 
 * Resuelve qué ACCIONES puede realizar un usuario en función del estado del curso
 * y sus múltiples contextos (Participante, Dueño del Servicio, Gestor).
 * 
 * @author Sistema de Gestión de Cursos
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
            'gestionar' => 'gestion',
        ],
        3 => [ // Declinado
            'ver_motivo'       => 'operativo',
            'editar'           => 'operativo',
            'enviar_aprobacion' => 'operativo',
            'gestionar'        => 'gestion',
        ],
        4 => [ // En Edición
            'editar' => 'operativo',
            'enviar_aprobacion' => 'operativo',
            'gestionar' => 'gestion',
        ],
        5 => [ // En Aprobación
            'aprobar'     => 'gestion',
            'rechazar'    => 'gestion',
            'en_revision' => 'operativo',
        ],
        6 => [ // Inscripciones
            'inscribirse' => 'publico',
            'cancelar_inscripcion' => 'participante',
            'acceder_contenido' => 'participante',
            'finalizar_inscripciones' => 'gestion',
            'gestionar' => 'gestion',
        ],
        7 => [ // En Progreso
            'acceder_contenido' => 'participante',
            'gestionar' => 'gestion',
            'finalizar_curso' => 'gestion',
        ],
        8 => [ // Finalizado
            'acceder_contenido' => 'participante',
            'emitir_certificado' => 'participante',
            'gestionar' => 'gestion',
            'cerrar_curso' => 'gestion',
        ],
        9 => [ // Cerrado
            'ver_archivo' => 'publico',
            'gestionar' => 'gestion',
        ],
    ];

    /**
     * Resuelve el conjunto de capacidades para el usuario actual
     * 
     * @param int $estadoId ID del estado actual
     * @param bool $esParticipante Si el usuario está inscrito
     * @param bool $esOperativo Si el usuario es el facilitador/dueño
     * @param bool $esGestor Si el usuario tiene permisos administrativos
     * @param int|null $cuposDisponibles Cantidad de cupos
     * @param bool $puedeInscribirse Si el usuario tiene el permiso formal de inscripción (RBAC)
     * @return array Lista de capacidades únicas
     */
    public function obtenerCapacidades($estadoId, $esParticipante, $esOperativo, $esGestor, $cuposDisponibles, $puedeInscribirse = true)
    {
        $capacidadesTeoricas = self::MAPA_CAPACIDADES[$estadoId] ?? [];
        $misCapacidades = [];

        foreach ($capacidadesTeoricas as $capacidad => $contextoRequerido) {
            $cumpleContexto = false;

            switch ($contextoRequerido) {
                case 'gestion':
                    if ($esGestor) $cumpleContexto = true;
                    break;
                case 'operativo':
                    if ($esOperativo) $cumpleContexto = true;
                    break;
                case 'participante':
                    if ($esParticipante) $cumpleContexto = true;
                    break;
                case 'publico':
                    // Caso especial para inscripción: debe cumplir contexto público Y tener el permiso
                    if ($capacidad === 'inscribirse') {
                        if (!$esParticipante && ($cuposDisponibles === null || $cuposDisponibles > 0) && $puedeInscribirse) {
                            $cumpleContexto = true;
                        }
                    } else {
                        if (!$esParticipante && ($cuposDisponibles === null || $cuposDisponibles > 0)) {
                            $cumpleContexto = true;
                        }
                    }
                    if ($estadoId == 9) $cumpleContexto = true; // En cerrado todos ven el aviso
                    break;
            }

            if ($cumpleContexto) {
                $misCapacidades[] = $capacidad;
            }
        }

        // Casos especiales y herencias lógicas
        if ($esGestor) {
            // El gestor siempre puede ver contenidos si el curso ya está activo/finalizado
            if (in_array($estadoId, [6, 7, 8])) {
                $misCapacidades[] = 'acceder_contenido';
            }
        }

        if ($esOperativo) {
            // El operativo siempre puede ver contenidos si no está en borrador
            if (in_array($estadoId, [4, 5, 6, 7, 8])) {
                $misCapacidades[] = 'acceder_contenido';
            }
        }

        return array_unique($misCapacidades);
    }
}
