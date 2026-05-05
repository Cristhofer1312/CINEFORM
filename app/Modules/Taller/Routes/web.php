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
use Modules\Comun\Http\Controllers\PersonalDataController;

Route::prefix('taller')->group(function () {
    // Ruta para cursos asignados (facilitador)
    Route::get('/Cursos-asignados', [CursoAsignadoController::class, 'index'])
        ->name('taller.mis-cursos-asignados');

    // Ruta para ver detalle de un curso
    Route::get('/cursos/{curso}', [CursoDetalleController::class, 'show'])
        ->name('taller.cursos.show');

    // Ruta para cursos inscritos (participante)
    Route::get('/mis-cursos', [CursoInscritoController::class, 'index'])
        ->name('taller.mis-cursos');

    // Rutas de gestión de cursos
    Route::middleware(['auth'])->group(function () {
        Route::get('/crear-curso', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'create'])->name('taller.cursos.create');
        Route::post('/crear-curso', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'store'])->name('taller.cursos.store_new');

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

        // Rutas de inscripciones
        Route::post('/inscripciones', [InscripcionController::class, 'store'])
            ->name('taller.inscripciones.store');


        Route::delete('/inscripciones/{inscripcion}', [InscripcionController::class, 'destroy'])
            ->name('taller.inscripciones.destroy');


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

    });
});
