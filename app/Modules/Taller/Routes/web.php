<?php

use App\Constants\SecurityAction;
use Illuminate\Support\Facades\Route;
use Modules\Taller\Http\Controllers\AsistenciaController;
use Modules\Taller\Http\Controllers\CatalogoController;
use Modules\Taller\Http\Controllers\CertificacionPanelController;
use Modules\Taller\Http\Controllers\CursoAsignadoController;
use Modules\Taller\Http\Controllers\CursoController;
use Modules\Taller\Http\Controllers\CursoDetalleController;
use Modules\Taller\Http\Controllers\CursoInscritoController;
use Modules\Taller\Http\Controllers\EditarCursoController;
use Modules\Taller\Http\Controllers\EstadisticasController;
use Modules\Taller\Http\Controllers\InscripcionController;
use Modules\Taller\Http\Controllers\PostulacionFacilitadorController;
use Modules\Taller\Http\Controllers\SatisfaccionCursoController;

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

        // Creación de curso: requiere CREAR_CURSO sobre el proceso de cursos.
        Route::middleware('permiso:taller.cursos.index,'.SecurityAction::CREAR_CURSO)->group(function () {
            Route::get('/crear-curso', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'create'])->name('taller.cursos.create');
            Route::post('/crear-curso', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'store'])->name('taller.cursos.store_new');

            // Wizard de Creación de Curso (5 pasos, sin curso en BD hasta finalizar)
            // Paso 1: Identidad · Paso 2: Logística · Paso 3: Temario ·
            // Paso 4: Certificado (plantilla) · Paso 5: Requisitos + revisión.
            Route::get('/crear-curso/nuevo', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'reiniciar'])
                ->name('taller.cursos.wizard.nuevo');
            Route::get('/crear-curso/paso-1', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'createPaso1'])
                ->name('taller.cursos.wizard.paso1.create');
            Route::post('/crear-curso/paso-1', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'storePaso1'])
                ->name('taller.cursos.wizard.paso1.store');
            Route::get('/crear-curso/paso-2', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'createPaso2'])
                ->name('taller.cursos.wizard.paso2.create');
            Route::post('/crear-curso/paso-2', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'storePaso2'])
                ->name('taller.cursos.wizard.paso2.store');
            Route::get('/crear-curso/paso-3', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'createPaso3'])
                ->name('taller.cursos.wizard.paso3.create');
            Route::post('/crear-curso/paso-3', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'storePaso3'])
                ->name('taller.cursos.wizard.paso3.store');
            Route::get('/crear-curso/plantilla', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'wizardPlantillaCreate'])
                ->name('taller.cursos.wizard.plantilla.create');
            Route::post('/crear-curso/plantilla', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'wizardPlantillaStore'])
                ->name('taller.cursos.wizard.plantilla.store');
            Route::get('/crear-curso/requisitos', [Modules\Taller\Http\Controllers\CursoRequisitoController::class, 'wizardCreate'])
                ->name('taller.cursos.wizard.requisitos.create');
            Route::post('/crear-curso/requisitos', [Modules\Taller\Http\Controllers\CursoRequisitoController::class, 'wizardStore'])
                ->name('taller.cursos.wizard.requisitos.store');
            Route::get('/crear-curso/revision', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'revision'])
                ->name('taller.cursos.wizard.revision');
            // Buscador paginado de facilitadores (modal paso 2): evita
            // renderizar los N usuarios con avatar en el HTML del paso.
            Route::get('/crear-curso/facilitadores', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'buscarFacilitadores'])
                ->name('taller.cursos.wizard.facilitadores.buscar');
            Route::post('/crear-curso/finalizar', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'finalizar'])
                ->name('taller.cursos.wizard.finalizar');
            Route::get('/crear-curso/foto/{token}', [Modules\Taller\Http\Controllers\CrearCursoController::class, 'verFotoWizard'])
                ->name('taller.cursos.wizard.foto')
                ->where('token', '[A-Za-z0-9]+')
                ->withoutMiddleware(['decrypt_id']);
        });

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

        Route::get('/cursos/{curso}/satisfaccion', [SatisfaccionCursoController::class, 'create'])
            ->name('taller.cursos.satisfaccion.create');

        Route::post('/cursos/{curso}/satisfaccion', [SatisfaccionCursoController::class, 'store'])
            ->name('taller.cursos.satisfaccion.store');

        Route::get('/cursos/{curso}/satisfaccion/resultados', [SatisfaccionCursoController::class, 'resultados'])
            ->name('taller.cursos.satisfaccion.resultados');

        // Ruta para actualizar el estado del curso
        Route::put('/cursos/{curso}/status', [CursoController::class, 'updateStatus'])
            ->name('taller.cursos.updateStatus');

        // Ruta para finalizar la edición del curso
        Route::put('/cursos/{curso}/finalizar', [CursoController::class, 'finalizarEdicion'])
            ->name('taller.cursos.finalizarEdicion');

        // Ruta para reasignar el facilitador de un curso
        Route::put('/cursos/{curso}/reasignar-facilitador', [CursoController::class, 'reasignarFacilitador'])
            ->name('taller.cursos.reasignarFacilitador');

        // Rutas de Calificaciones
        Route::get('/cursos/{curso}/contenido/{contenido}/calificar', [\Modules\Taller\Http\Controllers\CalificacionController::class, 'index'])
            ->name('taller.calificaciones.index');
        Route::post('/cursos/{curso}/contenido/{contenido}/calificar', [\Modules\Taller\Http\Controllers\CalificacionController::class, 'store'])
            ->name('taller.calificaciones.store');

        // Rutas de Certificados

        // Sirven las imágenes de plantilla y firma del certificado.
        //
        // Reemplazan a asset('storage/...'), que dependía del enlace simbólico
        // public/storage: con el bind mount de docker-compose (./app:/var/www/html)
        // el enlace creado en el host no se resuelve dentro del contenedor y la
        // imagen salía rota en el editor de calibración.
        //
        // La URL lleva un identificador y NO la extensión a propósito: el bloque
        // de estáticos de nginx (location ~* \.(png|jpg|...)$) tiene prioridad
        // sobre 'location /', así que cualquier URL terminada en .png se resuelve
        // como archivo estático y nunca llega a PHP. Es el mismo motivo por el que
        // el avatar se sirve en security/show-avatar/{img}.
        Route::get('/certificados/plantilla/global', [\Modules\Taller\Http\Controllers\CertificadoController::class, 'verPlantillaGlobal'])
            ->name('taller.certificados.plantilla.global')
            ->withoutMiddleware(['decrypt_id']);

        Route::get('/certificados/plantilla/{tipo}/{ref}', [\Modules\Taller\Http\Controllers\CertificadoController::class, 'verPlantilla'])
            ->name('taller.certificados.plantilla.ver')
            ->where('tipo', 'curso|wizard')
            ->where('ref', '[A-Za-z0-9]+')
            ->withoutMiddleware(['decrypt_id']);

        Route::get('/certificados/firma/{tipo}/{ref}', [\Modules\Taller\Http\Controllers\CertificadoController::class, 'verFirma'])
            ->name('taller.certificados.firma.ver')
            ->where('tipo', 'curso|wizard')
            ->where('ref', '[A-Za-z0-9]+')
            ->withoutMiddleware(['decrypt_id']);

        // Prueba en vivo del editor del wizard (FPDF+QR por clic: con throttle).
        Route::post('/certificados/prueba-wizard', [\Modules\Taller\Http\Controllers\CertificadoController::class, 'pruebaWizard'])
            ->name('taller.certificados.prueba.wizard')
            ->middleware('throttle:10,1')
            ->withoutMiddleware(['decrypt_id']);

        Route::get('/certificados/{curso}/descargar', [\Modules\Taller\Http\Controllers\CertificadoController::class, 'descargar'])
            ->name('taller.certificados.descargar');

        Route::get('/certificados/{curso}/probar', [\Modules\Taller\Http\Controllers\CertificadoController::class, 'probar'])
            ->name('taller.certificados.probar');

        Route::get('/certificados/verificar/{codigo}', [\Modules\Taller\Http\Controllers\CertificadoController::class, 'verificar'])
            ->name('taller.certificados.verificar')
            ->withoutMiddleware(['auth', 'decrypt_id']);

        // ── Gestión de Catálogos: Agregar Tipo de Actividad ──────────────────
        Route::middleware('permiso:taller.catalogos.index,'.SecurityAction::VER)->group(function () {
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

        // ── Rutas de Asistencia ──────────────────────────────────────────────
        Route::get('/cursos/{curso}/asistencia', [AsistenciaController::class, 'consolidado'])
            ->name('taller.asistencia.consolidado');
        Route::get('/cursos/{curso}/asistencia/{inscripcion}/individual', [AsistenciaController::class, 'individual'])
            ->name('taller.asistencia.individual');
        Route::post('/cursos/{curso}/generar-token', [AsistenciaController::class, 'generarToken'])
            ->name('taller.asistencia.generar-token');
        Route::post('/cursos/{curso}/desactivar-token', [AsistenciaController::class, 'desactivarToken'])
            ->name('taller.asistencia.desactivar-token');
        Route::get('/cursos/{curso}/tokens-activos', [AsistenciaController::class, 'todosTokensActivos'])
            ->name('taller.asistencia.tokens-activos');
        Route::post('/cursos/{curso}/asistencia/{asistencia}/anular', [AsistenciaController::class, 'anular'])
            ->name('taller.asistencia.anular');
        Route::post('/cursos/{curso}/asistencia/{asistencia}/restaurar', [AsistenciaController::class, 'restaurar'])
            ->name('taller.asistencia.restaurar');
        Route::post('/cursos/{curso}/contenido/{contenido}/marcar-manual', [AsistenciaController::class, 'marcarManual'])
            ->name('taller.asistencia.marcar-manual');
        Route::post('/cursos/{curso}/token/{token}/marcar', [AsistenciaController::class, 'marcar'])
            ->name('taller.asistencia.marcar');

        // ── Postulación a Facilitador ──
        // Landing (participantes)
        Route::get('/postulacion-facilitador', [PostulacionFacilitadorController::class, 'landing'])
            ->name('taller.postulacion-facilitador.landing');
        Route::get('/postulacion-facilitador/postularse', [PostulacionFacilitadorController::class, 'formulario'])
            ->name('taller.postulacion-facilitador.formulario');
        Route::post('/postulacion-facilitador/postularse', [PostulacionFacilitadorController::class, 'postular'])
            ->name('taller.postulacion-facilitador.postular');

        // Admin (coordinadores): requiere GESTIONAR_POSTULACIONES_FACILITADOR.
        // Documentos/descarga quedan fuera: el propio postulante también puede verlos
        // (la comprobación de dueño/gestor vive en el controlador).
        Route::middleware('permiso:taller.postulacion-facilitador.admin,'.SecurityAction::GESTIONAR_POSTULACIONES_FACILITADOR)->group(function () {
            Route::get('/postulacion-facilitador/admin', [PostulacionFacilitadorController::class, 'adminIndex'])
                ->name('taller.postulacion-facilitador.admin');
            Route::get('/postulacion-facilitador/admin/preview', [PostulacionFacilitadorController::class, 'previewLanding'])
                ->name('taller.postulacion-facilitador.admin.preview');
            Route::post('/postulacion-facilitador/requisitos', [PostulacionFacilitadorController::class, 'storeRequisito'])
                ->name('taller.postulacion-facilitador.requisitos.store');
            Route::put('/postulacion-facilitador/requisitos/{requisito}', [PostulacionFacilitadorController::class, 'updateRequisito'])
                ->name('taller.postulacion-facilitador.requisitos.update');
            Route::patch('/postulacion-facilitador/requisitos/{requisito}/toggle', [PostulacionFacilitadorController::class, 'toggleRequisito'])
                ->name('taller.postulacion-facilitador.requisitos.toggle');
            Route::post('/postulacion-facilitador/{postulacion}/aprobar', [PostulacionFacilitadorController::class, 'aprobar'])
                ->name('taller.postulacion-facilitador.aprobar');
            Route::post('/postulacion-facilitador/{postulacion}/rechazar', [PostulacionFacilitadorController::class, 'rechazar'])
                ->name('taller.postulacion-facilitador.rechazar');
        });
        Route::get('/postulacion-facilitador/{postulacion}/documentos', [PostulacionFacilitadorController::class, 'verDocumentos'])
            ->name('taller.postulacion-facilitador.documentos');
        Route::get('/postulacion-facilitador/documento/{respuesta}/descargar', [PostulacionFacilitadorController::class, 'descargarDocumento'])
            ->name('taller.postulacion-facilitador.documento.descargar');

        // ── Panel de Certificación (Aprobación por Facilitador) ──
        Route::get('/cursos/{curso}/certificacion', [CertificacionPanelController::class, 'index'])
            ->name('taller.certificacion.panel');
        Route::post('/cursos/{curso}/certificacion/{inscripcion}/aprobar', [CertificacionPanelController::class, 'aprobar'])
            ->name('taller.certificacion.aprobar');
        Route::post('/cursos/{curso}/certificacion/{inscripcion}/denegar', [CertificacionPanelController::class, 'denegar'])
            ->name('taller.certificacion.denegar');

        // ── Estadísticas de Cursos ──
        Route::middleware('permiso:taller.estadisticas.index,'.SecurityAction::VER_ESTADISTICAS)->group(function () {
            Route::get('/estadisticas', [EstadisticasController::class, 'index'])
                ->name('taller.estadisticas.index');
            Route::get('/estadisticas/datos', [EstadisticasController::class, 'datos'])
                ->name('taller.estadisticas.datos');
        });
    });

    // Ruta pública: QR de asistencia (fuera del grupo 'auth', sin decrypt_id).
    // Se define PRIMERO porque el segmento "qr" es literal; de lo contrario la
    // ruta genérica /asistencia/{curso}/{token} capturaría la URL con {curso}='qr'.
    Route::get('/asistencia/qr/{token}', [AsistenciaController::class, 'qrImage'])
        ->name('taller.asistencia.qr-image')
        ->withoutMiddleware('decrypt_id');
    Route::get('/asistencia/{curso}/{token}', [AsistenciaController::class, 'mostrarConfirmacion'])
        ->name('taller.asistencia.confirmar')
        ->middleware('auth');
    Route::post('/asistencia/{curso}/{token}/confirmar', [AsistenciaController::class, 'marcar'])
        ->name('taller.asistencia.confirmar-marcar')
        ->middleware('auth');
});
