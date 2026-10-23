<?php

namespace Modules\Taller\Http\Controllers;

use App\Constants\SecurityAction;
use App\Enums\EstadoCurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Comun\Entities\PersonalData;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Entities\ObservacionCurso;

class CursoController extends BaseController
{
    /**
     * Display the course content for enrolled students.
     *
     * @param  int  $id
     * @param  int|null  $contenido_id
     * @return \Illuminate\View\View
     */
    public function contenido($id, $contenido_id = null)
    {
        $curso = Curso::with([
            'modalidad',
            'contenidos' => function ($query) {
                $query->orderBy('fecha_contenido', 'asc')->orderBy('id_contenido_curso', 'asc')->with('tipoEvaluacion');
            },
            'persona',
            'estados',
        ])->findOrFail($id);

        // Autorización centralizada via Policy
        $this->authorize('verContenido', $curso);

        $personalData = $this->getUsuarioAutenticado()->personalData;
        $esFacilitador = $personalData && $curso->id_persona == $personalData->id_persona;
        $esGestor = auth()->user()->can('reasignarFacilitador', $curso);

        $contenidoActual = null;
        if ($contenido_id) {
            $contenidoActual = $curso->contenidos->where('id_contenido_curso', $contenido_id)->first();
        } elseif ($curso->contenidos->count() > 0) {
            $contenidoActual = $curso->contenidos->first();
        }

        // Verificar si es facilitador
        $personalData = $this->getUsuarioAutenticado()->personalData;
        $esFacilitador = $curso->id_persona == $personalData->id_persona;

        // Si es estudiante y es una evaluación, buscar calificación
        $calificacion = null;
        if (! $esFacilitador && $contenidoActual && $contenidoActual->es_evaluacion) {
            $calificacion = DB::table('taller.calificaciones')
                ->where('id_contenido_curso', $contenidoActual->id_contenido_curso)
                ->where('id_persona', $personalData->id_persona)
                ->first();
        }

        return view('taller::a.CursoContenido', compact('curso', 'contenidoActual', 'esFacilitador', 'calificacion', 'esGestor'));
    }

    public function updateStatus(Request $request, $id)
    {
        $id = $this->decryptId($id);
        $curso = Curso::findOrFail($id);
        $nuevoEstado = (int) $request->id_estado;

        $origen = EstadoCurso::tryFrom((int) ($curso->estado_actual->id_estado ?? 0));
        $destino = EstadoCurso::tryFrom($nuevoEstado);

        // Si el curso está en POR_ACEPTAR y no se especificó un destino explícito entre rechazar/declinar,
        // por defecto se asigna RECHAZADO (2).
        if ($origen === EstadoCurso::POR_ACEPTAR && !in_array($destino, [EstadoCurso::RECHAZADO, EstadoCurso::DECLINADO], true)) {
            $nuevoEstado = EstadoCurso::RECHAZADO->value;
            $destino = EstadoCurso::RECHAZADO;
        }

        // 1. Autorización estricta por par (origen, destino) via Policy
        $this->authorize('cambiarEstado', [$curso, $nuevoEstado]);

        if ($destino === null) {
            return response()->json([
                'success' => false,
                'message' => 'Estado de destino inválido.',
            ], 422);
        }

        if ($origen !== null && ! $origen->puedeTransicionarA($destino)) {
            return response()->json([
                'success' => false,
                'message' => 'El curso no puede pasar de "'.$origen->label().'" a "'.$destino->label().'".',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $curso->agregarEstado($nuevoEstado);

            // Si el facilitador rechaza/declina la asignación desde POR_ACEPTAR,
            // se le quita la asignación al curso (id_persona = null) y se registra la observación
            if ($origen === EstadoCurso::POR_ACEPTAR && in_array($destino, [EstadoCurso::RECHAZADO, EstadoCurso::DECLINADO], true)) {
                $nombreFacilitador = $curso->persona
                    ? ($curso->persona->primer_nombre.' '.$curso->persona->primer_apellido)
                    : 'Facilitador';

                $motivoTexto = $request->filled('motivo') ? $request->motivo : 'Sin motivo especificado.';

                $curso->update([
                    'id_persona' => null,
                    'actualizado_por' => auth()->id(),
                ]);

                ObservacionCurso::create([
                    'id_curso' => $curso->id_curso,
                    'observacion' => "El facilitador {$nombreFacilitador} rechazó la asignación del curso. Motivo: {$motivoTexto}",
                    'creado_por' => auth()->id(),
                ]);
            } elseif (in_array($nuevoEstado, [EstadoCurso::DECLINADO->value, EstadoCurso::RECHAZADO->value], true)
                && $request->filled('motivo')) {
                ObservacionCurso::create([
                    'id_curso' => $curso->id_curso,
                    'observacion' => $request->motivo,
                    'creado_por' => auth()->id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estado del curso actualizado correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Muestra la lista de cursos
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($this->usuarioSinDatosPersonales()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron datos personales para el usuario actual',
            ], 404);
        }

        // Resolver vista en base al permiso en lugar del viejo servicio
        $vistaParcial = hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::GESTIONAR_CURSO)
            ? 'partials.Buscador-actions.CursosCoordinador'
            : 'partials.Buscador-actions.Cursos';

        $esFacilitador = false; // base para vistas

        $query = Curso::with(['modalidad', 'inscripciones', 'estados', 'persona'])
            ->withCount(['contenidos', 'inscripciones']);

        // Si NO es gestor, ni facilitador, ni coordinador (Participante Base)
        if (! hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::GESTIONAR_CURSO) &&
            ! hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::EDITAR_CURSO)) {

            $personalData = Auth::user()->personalData;
            $idEstadoUsuario = $personalData->id_estado ?? null;
            $idPersonaActual = $personalData->id_persona ?? null;

            $query->where(function ($q) use ($idEstadoUsuario, $idPersonaActual) {
                // Bloque 1: Cursos públicos cuyo estado ACTUAL es Inscripción (6)
                // El estado actual se determina por el último registro en taller.curso_estado
                $q->where(function ($qPublico) use ($idEstadoUsuario) {
                    // Filtrar por estado actual = Inscripción usando la tabla pivote curso_estado
                    $qPublico->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('taller.curso_estado as ce_actual')
                            ->whereColumn('ce_actual.id_curso', 'taller.cursos.id_curso')
                            ->where('ce_actual.id_estado', \App\Enums\EstadoCurso::INSCRIPCION->value)
                            // El estado actual es el último registro (mismo criterio que
                            // Curso::estados(): desempate por id si created_at coincide).
                            ->whereRaw('ce_actual.id = (
                                SELECT ce_max.id
                                FROM taller.curso_estado AS ce_max
                                WHERE ce_max.id_curso = taller.cursos.id_curso
                                ORDER BY ce_max.created_at DESC, ce_max.id DESC
                                LIMIT 1
                            )');
                    });

                    // REGLA DE ALCANCE: Nacional o Localidad Coincidente
                    $qPublico->where(function ($qAlcance) use ($idEstadoUsuario) {
                        $qAlcance->where('es_nacional', true); // Opción 1: Es de alcance nacional

                        if ($idEstadoUsuario) {
                            $qAlcance->orWhereHas('localidades', function ($qLoc) use ($idEstadoUsuario) {
                                $qLoc->where('comun.estados.id', $idEstadoUsuario);
                            }); // Opción 2: El estado del usuario está permitido
                        }
                    });
                });

                // REGLA: Si el participante ya está inscrito en un curso de otra localidad (antes del cambio o por excepción), permitir que lo siga viendo
                if ($idPersonaActual) {
                    $q->orWhereHas('inscripciones', function ($qIns) use ($idPersonaActual) {
                        $qIns->where('id_persona', $idPersonaActual);
                    });
                }
            });
        }

        // Filtro por búsqueda (nombre o descripción)
        if ($request->has('search') && ! empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%'.$request->search.'%')
                    ->orWhere('descripcion', 'like', '%'.$request->search.'%');
            });
        }

        // Filtro por estado específico (usa la tabla pivote curso_estado para determinar el estado actual)
        if ($request->has('id_estado') && ! empty($request->id_estado)) {
            $idEstadoFiltro = $this->decryptId($request->id_estado);
            $query->whereExists(function ($sub) use ($idEstadoFiltro) {
                $sub->select(DB::raw(1))
                    ->from('taller.curso_estado as ce_filtro')
                    ->whereColumn('ce_filtro.id_curso', 'taller.cursos.id_curso')
                    ->where('ce_filtro.id_estado', $idEstadoFiltro)
                    ->whereRaw('ce_filtro.id = (
                        SELECT ce_m.id
                        FROM taller.curso_estado AS ce_m
                        WHERE ce_m.id_curso = taller.cursos.id_curso
                        ORDER BY ce_m.created_at DESC, ce_m.id DESC
                        LIMIT 1
                    )');
            });
        }

        $cursos = $query->orderBy('fecha_inicio', 'desc')
            ->paginate(12)
            ->withQueryString();

        $this->procesarCursosParaVista($cursos);

        // Si gestiona los cursos, trae todos los estados, sino solo los activos (>= 6)
        $estados = hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::GESTIONAR_CURSO)
            ? \Modules\Taller\Entities\Estado::all()
            : \Modules\Taller\Entities\Estado::where('id_estado', 6)->get();

        $esCoordinador = hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::GESTIONAR_CURSO);

        return view('taller::a.Cursos', compact('vistaParcial', 'cursos', 'estados', 'esCoordinador', 'esFacilitador'));
    }

    /**
     * Procesa cada curso para agregar propiedades calculadas necesarias en la vista
     *
     * @param  \Illuminate\Pagination\LengthAwarePaginator  $cursos
     * @return void
     */
    private function procesarCursosParaVista($cursos)
    {
        foreach ($cursos as $curso) {
            $estadoActual = $curso->estado_actual;
            $estadoId = $estadoActual ? $estadoActual->id_estado : 0;

            // Agregar propiedades calculadas al objeto curso
            $curso->estadoNombre = $estadoActual ? str_replace('_', ' ', $estadoActual->nombre) : 'Sin estado';
            $curso->modalidad = $curso->modalidad->nombre_modalidad ?? 'No especificada';
            $curso->modalidadIcon = $curso->modalidad === 'Presencial' ? 'fa-building' : 'fa-laptop';

            // Color del badge según EstadoCurso::color() (fuente única, sin mapa duplicado).
            $curso->badgeClass = 'bg-'.(EstadoCurso::tryFrom((int) $estadoId)?->color() ?? 'secondary');
        }
    }

    /**
     * Obtiene los cursos en los que el usuario está inscrito como participante
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function misCursosParticipante()
    {
        if ($this->usuarioSinDatosPersonales()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron datos personales para el usuario actual',
            ], 404);
        }

        $persona = $this->getUsuarioAutenticado()->personalData;

        // Obtener los cursos en los que la persona está inscrita
        $cursosParticipante = Curso::whereHas('inscripciones', function ($query) use ($persona) {
            $query->where('id_persona', $persona->id_persona);
        })
            ->with(['modalidad', 'inscripciones'])
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cursosParticipante,
        ]);
    }

    /**
     * Muestra los detalles de un curso específico
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if ($this->usuarioSinDatosPersonales()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron datos personales para el usuario actual',
            ], 404);
        }

        $persona = $this->getUsuarioAutenticado()->personalData;

        // Buscar el curso con sus relaciones
        $curso = Curso::with(['modalidad', 'contenidos', 'inscripciones.persona'])
            ->find($id);

        if (! $curso) {
            return response()->json([
                'success' => false,
                'message' => 'Curso no encontrado',
            ], 404);
        }

        // Verificar si el usuario es el facilitador o un participante
        $esFacilitador = $curso->id_persona == $persona->id_persona;
        $esParticipante = $curso->inscripciones->contains('id_persona', $persona->id_persona);
        $esCoordinador = hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::GESTIONAR_CURSO);

        if (! $esFacilitador && ! $esParticipante && ! $esCoordinador) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver este curso',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $curso,
            'es_facilitador' => $esFacilitador,
            'es_participante' => $esParticipante,
        ]);
    }

    /**
     * Almacena un nuevo curso en la base de datos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        /**
         * IMPORTANTE — Esquema PostgreSQL:
         * NO usar 'exists:taller.cursos,...' directamente en las reglas de validación.
         * Laravel interpreta el punto (.) como separador de conexión.tabla, NO como esquema.tabla,
         * lo cual causa errores de "tabla no encontrada" en PostgreSQL multi-esquema.
         * Usar la referencia a la clase del Modelo (Curso::class) resuelve el $table correcto
         * ('taller.cursos') desde la propiedad del modelo, evitando la ambigüedad.
         */
        $request->validate([
            'id_curso' => 'required|exists:'.Curso::class.',id_curso',
        ]);

        $curso = Curso::findOrFail($request->id_curso);

        if ($this->usuarioSinDatosPersonales()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron los datos personales del usuario.',
            ], 404);
        }

        $personalData = $this->getUsuarioAutenticado()->personalData;

        // Verificar si el usuario es el propietario del curso
        if ($curso->id_persona == $personalData->id_persona) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes inscribirte en tu propio curso.',
            ], 403);
        }

        // Verificar si el usuario ya está inscrito en el curso
        $inscripcionExistente = Inscripcion::where('id_curso', $request->id_curso)
            ->where('id_persona', $personalData->id_persona)
            ->first();

        if ($inscripcionExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya estás inscrito en este curso.',
            ], 409);
        }

        // Verificar si hay cupos disponibles
        $inscritos = Inscripcion::where('id_curso', $curso->id_curso)->count();
        if ($curso->cantidad_cupos !== null && $inscritos >= (int) $curso->cantidad_cupos) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cupos disponibles para este curso.',
            ], 400);
        }

        // Crear la inscripción
        $inscripcion = Inscripcion::create([
            'id_curso' => $request->id_curso,
            'id_persona' => $personalData->id_persona,
            'fecha_inscripcion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inscripción realizada con éxito.',
            'data' => $inscripcion,
            'cupos_restantes' => $curso->cantidad_cupos !== null
                ? max(0, (int) $curso->cantidad_cupos - Inscripcion::where('id_curso', $curso->id_curso)->count())
                : null,
        ], 201);
    }

    /**
     * Elimina un curso
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if ($this->usuarioSinDatosPersonales()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron los datos personales del usuario.',
            ], 404);
        }

        $personalData = $this->getUsuarioAutenticado()->personalData;

        $inscripcion = Inscripcion::where('id_inscripcion', $id)
            ->where('id_persona', $personalData->id_persona)
            ->first();

        if (! $inscripcion) {
            return response()->json([
                'success' => false,
                'message' => 'Inscripción no encontrada o no autorizada para cancelar.',
            ], 404);
        }

        $curso = $inscripcion->curso;
        $inscripcion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inscripción cancelada correctamente.',
            'cupos_restantes' => $curso->cantidad_cupos !== null
                ? max(0, (int) $curso->cantidad_cupos - Inscripcion::where('id_curso', $curso->id_curso)->count())
                : null,
        ]);
    }

    /**
     * Finaliza la edición de un curso cambiando su estado a 5 (Finalizado)
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function finalizarEdicion($id)
    {
        try {
            // Buscar el curso
            $curso = Curso::findOrFail($id);

            // Verificar que el usuario es el propietario del curso
            if ($this->usuarioSinDatosPersonales()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado para finalizar la edición de este curso',
                ], 403);
            }

            $personalData = $this->getUsuarioAutenticado()->personalData;

            if ($curso->id_persona != $personalData->id_persona) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado para finalizar la edición de este curso',
                ], 403);
            }

            $origen = EstadoCurso::tryFrom($curso->estado_actual->id_estado ?? 0);

            if (! $origen || ! $origen->puedeTransicionarA(EstadoCurso::APROBACION)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El curso no puede pasar a En Aprobación desde su estado actual.',
                ], 422);
            }

            // Agregar el nuevo estado (En Aprobación)
            $curso->agregarEstado(EstadoCurso::APROBACION->value);

            return response()->json([
                'success' => true,
                'message' => 'Propuesta de curso enviada a coordinación exitosamente.',
                'estado_actual' => EstadoCurso::APROBACION->value,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Curso no encontrado',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar la edición del curso: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reasigna el facilitador de un curso.
     * Solo coordinadores (GESTIONAR_CURSO) pueden ejecutar esta acción.
     * Solo permitido en estados 1-5 (antes de Inscripción).
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reasignarFacilitador(Request $request, $id)
    {
        try {
            // Verificar permiso de gestión
            if (! hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para reasignar facilitadores.',
                ], 403);
            }

            $id = $this->decryptId($id);
            $curso = Curso::with('estados')->findOrFail($id);

            // Validar estado del curso: se permite durante preparación o ejecución activa
            $estadoActualId = $curso->estado_actual?->id_estado ?? 0;
            $estadosPermitidos = [
                EstadoCurso::POR_ACEPTAR->value,
                EstadoCurso::RECHAZADO->value,
                EstadoCurso::DECLINADO->value,
                EstadoCurso::EDICION->value,
                EstadoCurso::APROBACION->value,
                EstadoCurso::INSCRIPCION->value,
                EstadoCurso::EN_CURSO->value,
            ];

            if (! in_array($estadoActualId, $estadosPermitidos, true)) {
                $estadoNombre = EstadoCurso::tryFrom($estadoActualId)?->label() ?? 'Desconocido';

                return response()->json([
                    'success' => false,
                    'message' => "No se puede reasignar el facilitador. El curso está en estado \"$estadoNombre\".",
                ], 422);
            }

            // Validar que se envió id_persona
            $nuevoIdPersona = $request->id_persona;
            if (! $nuevoIdPersona) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar un facilitador.',
                ], 422);
            }

            // Validar que no es el mismo facilitador si ya tenía uno asignado
            if ($curso->id_persona && (int) $nuevoIdPersona === (int) $curso->id_persona) {
                return response()->json([
                    'success' => false,
                    'message' => 'El facilitador seleccionado ya es el facilitador actual del curso.',
                ], 422);
            }

            // Validar que la persona existe
            $nuevaPersona = PersonalData::find($nuevoIdPersona);
            if (! $nuevaPersona) {
                return response()->json([
                    'success' => false,
                    'message' => 'La persona seleccionada no fue encontrada.',
                ], 404);
            }

            // El nuevo responsable debe ser elegible como facilitador (misma regla que el listado de candidatos)
            if (! app(\App\Services\Security\Autorizacion::class)->esElegibleFacilitador($nuevaPersona->user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La persona seleccionada no es elegible como facilitador.',
                ], 422);
            }

            // Guardar referencia al facilitador anterior para la observación
            $facilitadorAnterior = $curso->persona;
            $nombreAnterior = $facilitadorAnterior
                ? $facilitadorAnterior->primer_nombre.' '.$facilitadorAnterior->primer_apellido
                : 'No asignado';
            $nombreNuevo = $nuevaPersona->primer_nombre.' '.$nuevaPersona->primer_apellido;
            $estadoNombreActual = EstadoCurso::tryFrom($estadoActualId)?->label() ?? 'activo';

            DB::beginTransaction();

            // Actualizar el facilitador
            $curso->update([
                'id_persona' => $nuevoIdPersona,
                'actualizado_por' => Auth::id(),
            ]);

            // Comportamiento del ciclo de vida tras la reasignación:
            // 1. Fases de preparación (1-5): vuelve a POR_ACEPTAR para que el nuevo docente acepte.
            // 2. Fases operativas activas (6-7): se mantiene en la fase actual sin interrumpir inscripciones ni avance del curso.
            if (in_array($estadoActualId, [EstadoCurso::INSCRIPCION->value, EstadoCurso::EN_CURSO->value], true)) {
                $mensajeExito = "Facilitador reasignado exitosamente a $nombreNuevo. El curso se mantiene en estado \"$estadoNombreActual\".";
                $observacionTexto = "Facilitador reasignado de \"$nombreAnterior\" a \"$nombreNuevo\" durante el estado \"$estadoNombreActual\" por incumplimiento o cambio de asignación.";
            } else {
                $origen = EstadoCurso::tryFrom($estadoActualId);

                if (! $origen || ! $origen->puedeTransicionarA(EstadoCurso::POR_ACEPTAR)) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'El curso no puede reasignarse desde su estado actual.',
                    ], 422);
                }

                // Volver al estado POR_ACEPTAR para que el nuevo facilitador acepte
                $curso->agregarEstado(EstadoCurso::POR_ACEPTAR->value);
                $mensajeExito = "Facilitador reasignado exitosamente a $nombreNuevo. El curso ha vuelto al estado \"Por Aceptar\".";
                $observacionTexto = "Facilitador reasignado de \"$nombreAnterior\" a \"$nombreNuevo\".";
            }

            // Registrar observación automática del cambio
            ObservacionCurso::create([
                'id_curso' => $curso->id_curso,
                'observacion' => $observacionTexto,
                'creado_por' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $mensajeExito,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al reasignar el facilitador: '.$e->getMessage(),
            ], 500);
        }
    }
}
