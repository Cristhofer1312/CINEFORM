<?php

namespace App\Constants;

class SecurityAction
{
    // IDs numéricos de la acción (Lo que usarás en tu código)
    const VER = 1;
    const CREAR = 2;
    const CREAR_CURSO = 3;
    const GESTIONAR_CURSO = 4;
    const EDITAR_CURSO = 5;
    const EDITAR_CURSO_E = 6;
    const CALIFICAR_CURSO = 7;
    const RESPONDER_CURSO = 8; // Para que el facilitador acepte o rechace la asignación
    const APROBAR_CURSO = 9; // Para aprobar un curso y abrir las inscripciones (Estado 5 → 6)
    const INSCRIBIRSE_CURSO = 18; // Para que un participante pueda inscribirse en un curso

    // ── Administración de Seguridad ──────────────────────────────────────────
    const EDITAR = 10; // Editar registros en módulos de administración (perfiles, usuarios)
    const GESTIONAR_PERMISOS = 11; // Asignar / revocar acciones sobre los perfiles del sistema
    const SEGURIDAD_USUARIO = 12; // Cambiar credenciales de seguridad (contraseña) de otro usuario

    /**
     * Nombres amigables para mostrar en la interfaz (Checkboxes).
     */
    public static function labels(): array
    {
        return [
            self::VER => 'Acceso a la Sección',
            self::CREAR => 'Crear Registros Nuevos',
            self::CREAR_CURSO => 'Planificar Nuevo Curso',
            self::GESTIONAR_CURSO => 'Rol Coordinador / Supervisor',
            self::EDITAR_CURSO => 'Rol Facilitador',
            self::EDITAR_CURSO_E => 'Edición Excepcional',
            self::CALIFICAR_CURSO => 'Calificar Curso',
            self::RESPONDER_CURSO => 'Aceptar / Rechazar Asignación',
            self::APROBAR_CURSO => 'Abrir Inscripciones Oficialmente',
            self::EDITAR => 'Modificar Registros',
            self::GESTIONAR_PERMISOS => 'Modificar Permisos del Sistema',
            self::SEGURIDAD_USUARIO => 'Restablecer Contraseñas',
            self::INSCRIBIRSE_CURSO => 'Inscribirse en Cursos',
        ];
    }

    /**
     * Descripciones detalladas de lo que permite hacer cada acción.
     */
    public static function descriptions(): array
    {
        return [
            self::VER => 'Es el permiso base requerido. Permite ver esta sección en el menú lateral y hace visible el listado de elementos en pantalla.',
            self::CREAR => 'Otorga la capacidad de crear nuevos perfiles en el sistema.',
            self::CREAR_CURSO => 'Permite diseñar planes de curso desde cero.',
            self::GESTIONAR_CURSO => 'Permite gestionar cursos sin ser el facilitador directo, reasignar personal y alterar cualquier estado del ciclo general de cursos.',
            self::EDITAR_CURSO => 'Hace que un usuario sea seleccionable como "Facilitador". Permite al facilitador designado poder editar, agregar material y solicitar la aprobación final de su curso.',
            self::EDITAR_CURSO_E => 'Poder exclusivo de excepciones: Permite alterar información fundamental de un curso que ya está en progreso o que cerró inscripciones.',
            self::CALIFICAR_CURSO => 'Autoriza formalmente a un usuario a registrar calificaciones definitivas.',
            self::RESPONDER_CURSO => 'Habilita al usuario asignado a aceptar el compromiso de dictar la clase o rechazar la responsabilidad.',
            self::APROBAR_CURSO => 'Autoridad final: Cambia el curso a estado "Activo" para abrir la fase de inscripciones y hacerlo público a los participantes.',
            self::EDITAR => 'Permite modificar datos basicos de usuarios, Cambiar perfiles y activar/desactivar cuentas.',
            self::GESTIONAR_PERMISOS => 'Permite alterar el nivel de acceso de cualquier perfil de la institución. Solo para administradores principales.',
            self::SEGURIDAD_USUARIO => 'Permite forzar o restaurar contraseñas de las cuentas de otros empleados del sistema.',
            self::INSCRIBIRSE_CURSO => 'Permite que el perfil pueda realizar inscripciones formales en los cursos disponibles.',
        ];
    }

    /**
     * Mapea el ID numérico con el texto real que se guarda en la Base de Datos.
     */
    public static function dbString(int $actionId): string
    {
        return match ($actionId) {
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
            default => '',
        };
    }

    /**
     * Convierte un texto de la DB a nuestro ID numérico
     */
    public static function stringToId(string $actionString): int
    {
        return match ($actionString) {
            'view' => self::VER,
            'create' => self::CREAR,
            'create_course' => self::CREAR_CURSO,
            'manage_course' => self::GESTIONAR_CURSO,
            'edit_course' => self::EDITAR_CURSO,
            'edit_course_e' => self::EDITAR_CURSO_E,
            'grade_course' => self::CALIFICAR_CURSO,
            'accept_course' => self::RESPONDER_CURSO,
            'approve_course' => self::APROBAR_CURSO,
            'edit' => self::EDITAR,
            'permissions' => self::GESTIONAR_PERMISOS,
            'security' => self::SEGURIDAD_USUARIO,
            'enroll' => self::INSCRIBIRSE_CURSO,
            default => 0, // Desconocido
        };
    }
}
