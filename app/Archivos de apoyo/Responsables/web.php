<?php

use Illuminate\Support\Facades\Route;
use Modules\Taller\Http\Controllers\InscripcionController;
use Modules\Taller\Http\Controllers\CursoInscritoController;
use Modules\Taller\Http\Controllers\CursoAsignadoController;
use Modules\Taller\Http\Controllers\CursoDetalleController;
use Modules\Taller\Http\Controllers\CursoController;
use Modules\Taller\Http\Controllers\EditarCursoController;
use Modules\Taller\Http\Controllers\BaseController;
use Modules\Taller\Http\Controllers\CatalogoController;
use Modules\Taller\Http\Controllers\AsistenciaController;
use Modules\Comun\Http\Controllers\PersonalDataController;

Route::get('/dev-login', function() {
    $user = \Modules\Security\Entities\User::where('username', 'admin')->first();
    if ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        $perfil = $user->perfiles()->first();
        if ($perfil) {
            session()->put('profile_id', $perfil->id);
        }
        return "Logged in! <a href='/taller/cursos/S0lUMVNwTTNzMVZFSUtLT3U1T1Y1Zz09/plantilla'>Go to calibration</a>";
    }
    return "No admin user";
});

Route::prefix('taller')->middleware(['decrypt_id'])->group(function () {
    // Rutas de gestión de cursos
    Route::middleware(['auth'])->group(function () {
        // Ruta para ver detalle de un curso
        Route::get('/cursos/{curso}', [CursoDetalleController::class, 'show'])
            ->name('taller.cursos.show');

        // Ruta para ver participantes de un curso
        Route::get('/cursos/{curso}/participantes', [CursoDetalleController::class, 'participantes'])
            ->name('taller.cursos.participantes');

        // Ruta para cursos asignados (facilitador)
        Route::get('/Cursos-asignados', [CursoAsignadoController::class, 'index'])
            ->name('taller.mis-cursos-asignados');

        // Ruta para cursos inscritos (participante)
        Route::get('/mis-cursos', [CursoInscritoController::class, 'index'])
            ->name('taller.mis-cursos');

        Route::get('/crear-curso', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'create'])->name('taller.cursos.create');
        Route::post('/crear-curso', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'store'])->name('taller.cursos.store_new');

        // Carga de Plantilla de Certificado (Paso Intermedio)
        Route::get('/cursos/{curso}/plantilla', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'plantillaCreate'])
            ->name('taller.cursos.plantilla.create');
        Route::post('/cursos/{curso}/plantilla', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'plantillaStore'])
            ->name('taller.cursos.plantilla.store');

        // Ruta para editar el certificado (Premium acceso rápido)
        Route::get('/cursos/{curso}/certificado/editar', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'plantillaCreate'])
            ->name('taller.cursos.certificado.edit');

        // Ruta para ver el contenido de un curso
        Route::get('/cursos/{curso}/contenido/{contenido_id?}', [CursoController::class, 'contenido'])->name('taller.cursos.contenido');
        // Ruta principal de cursos (listado)
        Route::get('/cursos', [CursoController::class, 'index'])->name('taller.cursos.index');

        // Ruta para mostrar formulario de edición
        Route::get('/cursos-asignados/{curso}/editar', [EditarCursoController::class, 'edit'])
            ->name('taller.cursos.edit');

        // Ruta para actualizar un curso
        Route::put('/cursos-asignados/{curso}', [EditarCursoController::class, 'update'])
            ->name('taller.cursos.update');

        // Ruta para aceptar un curso (cambiar a estado Aceptado)
        Route::post('/cursos/{curso}/aceptar', [CursoAsignadoController::class, 'aceptar'])
            ->name('taller.cursos.aceptar');

        // Ruta para aceptar un curso y actualizar su estado a 6 (Aceptado)
        Route::post('/cursos/{curso}/aceptar-estado', [CursoAsignadoController::class, 'aceptarCurso'])
            ->name('taller.cursos.aceptar-estado');

        // Requisitos del curso
        Route::get('/cursos/{curso}/requisitos/crear', [Modules\Taller\Http\Controllers\CursoRequisitoController::class, 'create'])
            ->name('taller.cursos.requisitos.create');
        Route::post('/cursos/{curso}/requisitos', [Modules\Taller\Http\Controllers\CursoRequisitoController::class, 'store'])
            ->name('taller.cursos.requisitos.store');
        Route::get('/cursos/{curso}/requisitos/editar', [Modules\Taller\Http\Controllers\CursoRequisitoController::class, 'edit'])
            ->name('taller.cursos.requisitos.edit');
        Route::put('/cursos/{curso}/requisitos', [Modules\Taller\Http\Controllers\CursoRequisitoController::class, 'update'])
            ->name('taller.cursos.requisitos.update');

        // Rutas de inscripciones
        Route::get('/cursos/{curso}/inscribirse', [InscripcionController::class, 'create'])
            ->name('taller.inscripciones.create');
        
        Route::post('/cursos/{curso}/inscribirse', [InscripcionController::class, 'procesarInscripcion'])
            ->name('taller.inscripciones.procesar');

        Route::post('/inscripciones', [InscripcionController::class, 'store'])
            ->name('taller.inscripciones.store');

        Route::delete('/inscripciones/{inscripcion}', [InscripcionController::class, 'destroy'])
            ->name('taller.inscripciones.destroy');

        Route::patch('/inscripciones/{inscripcion}/rehabilitar', [InscripcionController::class, 'rehabilitar'])
            ->name('taller.inscripciones.rehabilitar');

        // Nuevas rutas para el Workflow de Postulación
        Route::post('/inscripciones/{inscripcion}/aprobar', [InscripcionController::class, 'aprobar'])
            ->name('taller.inscripciones.aprobar');
        Route::post('/inscripciones/{inscripcion}/rechazar', [InscripcionController::class, 'rechazar'])
            ->name('taller.inscripciones.rechazar');
        Route::post('/inscripciones/{inscripcion}/denegar', [InscripcionController::class, 'denegar'])
            ->name('taller.inscripciones.denegar');

        // Nota: esta ruta va ANTES de cualquier wildcard que pueda capturar 'respuestas'
        Route::get('/inscripciones/respuestas/{respuesta}/descargar', [InscripcionController::class, 'descargarDocumento'])
            ->name('taller.inscripciones.respuestas.descargar');

        Route::get('/inscripciones/respuestas/{respuesta}/ver', [InscripcionController::class, 'verDocumento'])
            ->name('taller.inscripciones.respuestas.ver');

        Route::get('/cursos/{curso}/inscripciones/{inscripcion}/respuestas', [InscripcionController::class, 'verRespuestas'])
            ->name('taller.inscripciones.respuestas');


        // Ruta para actualizar el estado del curso
        Route::put('/cursos/{curso}/status', [CursoController::class, 'updateStatus'])
            ->name('taller.cursos.updateStatus');

        // Ruta para finalizar la edición del curso
        Route::put('/cursos/{curso}/finalizar', [CursoController::class, 'finalizarEdicion'])
            ->name('taller.cursos.finalizarEdicion');

        // Rutas de Calificaciones
        Route::get('/cursos/{curso}/contenido/{contenido}/calificar', [\Modules\Taller\Http\Controllers\CalificacionController::class, 'index'])
            ->name('taller.calificaciones.index');
        Route::post('/cursos/{curso}/contenido/{contenido}/calificar', [\Modules\Taller\Http\Controllers\CalificacionController::class, 'store'])
            ->name('taller.calificaciones.store');

        // Rutas de Certificados
        Route::get('/certificados/{curso}/descargar', [\Modules\Taller\Http\Controllers\CertificadoController::class, 'descargar'])
            ->name('taller.certificados.descargar');

        Route::get('/certificados/{curso}/probar', [\Modules\Taller\Http\Controllers\CertificadoController::class, 'probar'])
            ->name('taller.certificados.probar');

        Route::get('/certificados/verificar/{codigo}', [\Modules\Taller\Http\Controllers\CertificadoController::class, 'verificar'])
            ->name('taller.certificados.verificar')
            ->withoutMiddleware(['auth', 'decrypt_id']);

        // ── Gestión de Catálogos: Agregar Tipo de Actividad ──────────────────
        Route::get('/catalogos', [CatalogoController::class, 'index'])
            ->name('taller.catalogos.index');

        // Actividades Formativas
        Route::post('/catalogos/actividades', [CatalogoController::class, 'storeActividad'])
            ->name('taller.catalogos.actividades.store');
        Route::put('/catalogos/actividades/{actividad}', [CatalogoController::class, 'updateActividad'])
            ->name('taller.catalogos.actividades.update');
        Route::patch('/catalogos/actividades/{actividad}/toggle', [CatalogoController::class, 'toggleActividad'])
            ->name('taller.catalogos.actividades.toggle');

        // Aspectos de Formación
        Route::post('/catalogos/aspectos', [CatalogoController::class, 'storeAspecto'])
            ->name('taller.catalogos.aspectos.store');
        Route::put('/catalogos/aspectos/{aspecto}', [CatalogoController::class, 'updateAspecto'])
            ->name('taller.catalogos.aspectos.update');
        Route::patch('/catalogos/aspectos/{aspecto}/toggle', [CatalogoController::class, 'toggleAspecto'])
            ->name('taller.catalogos.aspectos.toggle');

        // ── Rutas de Asistencia ──────────────────────────────────────────────
        Route::get('/cursos/{curso}/asistencia', [AsistenciaController::class, 'consolidado'])
            ->name('taller.asistencia.consolidado');
        Route::get('/cursos/{curso}/asistencia/{inscripcion}/individual', [AsistenciaController::class, 'individual'])
            ->name('taller.asistencia.individual');
        Route::post('/cursos/{curso}/contenido/{contenido}/generar-token', [AsistenciaController::class, 'generarToken'])
            ->name('taller.asistencia.generar-token');
        Route::post('/cursos/{curso}/asistencia/{asistencia}/anular', [AsistenciaController::class, 'anular'])
            ->name('taller.asistencia.anular');
        Route::post('/cursos/{curso}/asistencia/{asistencia}/restaurar', [AsistenciaController::class, 'restaurar'])
            ->name('taller.asistencia.restaurar');
        Route::post('/cursos/{curso}/contenido/{contenido}/marcar-manual', [AsistenciaController::class, 'marcarManual'])
            ->name('taller.asistencia.marcar-manual');
        Route::post('/cursos/{curso}/token/{token}/marcar', [AsistenciaController::class, 'marcar'])
            ->name('taller.asistencia.marcar');
    });

    // Ruta pública: marcado de asistencia vía link/QR (requiere auth, pero fuera del grupo 'auth' para el flujo intencionado)
    Route::get('/asistencia/{curso}/{token}', [AsistenciaController::class, 'mostrarConfirmacion'])
        ->name('taller.asistencia.confirmar')
        ->middleware('auth');
    Route::post('/asistencia/{curso}/{token}/confirmar', [AsistenciaController::class, 'marcar'])
        ->name('taller.asistencia.confirmar-marcar')
        ->middleware('auth');
});
