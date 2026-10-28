<?php

namespace App\Constants;

/**
 * Identificadores de las acciones de permiso.
 *
 * Solo contiene la correspondencia número ↔ slug. El nombre y la descripción de
 * cada permiso viven en `security.permissions` (name, description), que es la
 * única fuente que muestra la pantalla de permisos (RF-83).
 */
class SecurityAction
{
    const VER = 1;
    const CREAR = 2;
    const CREAR_CURSO = 3;
    const GESTIONAR_CURSO = 4;
    const EDITAR_CURSO = 5;
    const EDITAR_CURSO_E = 6;
    const CALIFICAR_CURSO = 7;
    const RESPONDER_CURSO = 8;
    const APROBAR_CURSO = 9;
    const EDITAR = 10;
    const GESTIONAR_PERMISOS = 11;
    const SEGURIDAD_USUARIO = 12;
    const INSCRIBIRSE_CURSO = 18;
    const CANCELAR_INSCRIPCION = 19;
    const ASIGNAR_PERFIL = 20;
    const VER_PARTICIPANTES = 21;
    const MARCAR_ASISTENCIA = 22;
    const GESTIONAR_ASISTENCIA = 23;
    const POSTULARSE_FACILITADOR = 24;
    const GESTIONAR_POSTULACIONES_FACILITADOR = 25;
    const VER_ESTADISTICAS = 26;
    const REPORTE_SATISFACCION = 27;
    const SUPERVISAR_CURSOS = 28;

    /** Slug con el que se guarda cada acción en `security.permissions.slug`. */
    private const SLUGS = [
        self::VER => 'view',
        self::CREAR => 'create',
        self::CREAR_CURSO => 'create_course',
        self::GESTIONAR_CURSO => 'manage_course',
        self::EDITAR_CURSO => 'edit_course',
        self::EDITAR_CURSO_E => 'edit_course_e',
        self::CALIFICAR_CURSO => 'grade_course',
        self::RESPONDER_CURSO => 'accept_course',
        self::APROBAR_CURSO => 'approve_course',
        self::EDITAR => 'edit',
        self::GESTIONAR_PERMISOS => 'permissions',
        self::SEGURIDAD_USUARIO => 'security',
        self::INSCRIBIRSE_CURSO => 'enroll',
        self::CANCELAR_INSCRIPCION => 'cancel_enrollment',
        self::ASIGNAR_PERFIL => 'assign',
        self::VER_PARTICIPANTES => 'view_participants',
        self::MARCAR_ASISTENCIA => 'mark_attendance',
        self::GESTIONAR_ASISTENCIA => 'manage_attendance',
        self::POSTULARSE_FACILITADOR => 'apply_facilitator',
        self::GESTIONAR_POSTULACIONES_FACILITADOR => 'manage_facilitator_applications',
        self::VER_ESTADISTICAS => 'view_statistics',
        self::REPORTE_SATISFACCION => 'export_satisfaction',
        self::SUPERVISAR_CURSOS => 'supervise_courses',
    ];

    /** Slug de la acción, o '' si no existe. */
    public static function dbString(int $actionId): string
    {
        return self::SLUGS[$actionId] ?? '';
    }

    /** Identificador de la acción a partir de su slug, o 0 si no existe. */
    public static function stringToId(string $actionString): int
    {
        $id = array_search($actionString, self::SLUGS, true);

        return $id === false ? 0 : $id;
    }
}
