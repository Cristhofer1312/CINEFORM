<?php

/**
 * Descripción de cada permiso por slug (RF-83).
 *
 * Fuente única que usan la migración `catalogo_unico_permisos` (BD existente) y
 * el seeder `PermisosDescripcionesSeeder` (instalación limpia). Solo rellena las
 * descripciones vacías: un texto editado a mano en la BD no se sobrescribe.
 */
return [
    'view' => 'Permite ver esta sección en el menú y su listado.',
    'create' => 'Permite crear registros nuevos en esta sección.',
    'edit' => 'Permite modificar, activar o desactivar registros de esta sección.',
    'permissions' => 'Permite asignar o quitar permisos a cualquier perfil.',
    'security' => 'Permite cambiar la contraseña de otros usuarios.',
    'assign' => 'Permite asignar o quitar a otros usuarios los perfiles que uno mismo posee.',
    'create_course' => 'Permite planificar cursos nuevos con el asistente.',
    'manage_course' => 'Acciones de gestión de un curso: editarlo, cambiar su estado, sus requisitos y reasignar el facilitador. Se ejerce sobre todos los cursos junto con «Supervisar todos los cursos».',
    'supervise_courses' => 'Extiende a todos los cursos, sin ser su facilitador, las acciones de cursos que el usuario ya tiene (ver participantes, asistencia, gestión, etc.).',
    'edit_course' => 'Hace al usuario seleccionable como facilitador y le permite editar sus cursos asignados.',
    'edit_course_e' => 'Permite alterar datos fundamentales de un curso en progreso o con inscripciones cerradas.',
    'grade_course' => 'Permite registrar calificaciones en los cursos asignados.',
    'accept_course' => 'Permite aceptar o rechazar la asignación como facilitador de un curso.',
    'approve_course' => 'Aprueba la propuesta de curso que está "En Aprobación" y abre la fase de inscripciones.',
    'enroll' => 'Permite postularse e inscribirse en los cursos disponibles.',
    'view_participants' => 'Permite ver el listado de participantes de un curso.',
    'cancel_enrollment' => 'Permite retirar participantes de un curso con inscripciones activas o en progreso.',
    'mark_attendance' => 'Permite registrar la propia asistencia mediante enlace o código QR.',
    'manage_attendance' => 'Permite ver listas de asistencia, generar enlaces o QR, anular, restaurar y marcar asistencia manual.',
    'apply_facilitator' => 'Permite postularse como facilitador entregando los recaudos.',
    'manage_facilitator_applications' => 'Permite gestionar los requisitos y aprobar o rechazar postulaciones de facilitador.',
    'view_statistics' => 'Permite consultar los indicadores y gráficos de cursos.',
    'export_satisfaction' => 'Permite descargar en Excel el reporte de la encuesta de satisfacción de los cursos que puede consultar.',
];
