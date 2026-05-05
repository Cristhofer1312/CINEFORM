<?php

namespace Modules\Taller\Http\Controllers;

use Modules\Taller\Entities\Curso;
use Modules\Comun\Entities\PersonalData;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Entities\ObservacionCurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Constants\SecurityAction;

class CursoController extends BaseController
{

    /**
     * Display the course content for enrolled students.
     *
     * @param  int  $id
     * @param  int|null $contenido_id
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
            'estados'
        ])->findOrFail($id);

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
        if (!$esFacilitador && $contenidoActual && $contenidoActual->es_evaluacion) {
            $calificacion = DB::table('taller.calificaciones')
                ->where('id_contenido_curso', $contenidoActual->id_contenido_curso)
                ->where('id_persona', $personalData->id_persona)
                ->first();
        }

        return view('taller::a.CursoContenido', compact('curso', 'contenidoActual', 'esFacilitador', 'calificacion'));
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $curso = Curso::findOrFail($id);
            $nuevoEstado = (int) $request->id_estado;

            // Validación RBAC: el cambio de estado 5 → 6 (Aprobar → Inscripciones)
            // requiere el permiso específico de APROBAR_CURSO
            if ($nuevoEstado === 6) {
                if (!hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::APROBAR_CURSO)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permiso para aprobar courses y abrir inscripciones.'
                    ], 403);
                }

                // Verificar que el curso esté en estado 5 (En Aprobación) para poder aprobar
                $estadoActual = $curso->estado_actual?->id_estado;
                if ($estadoActual !== 5) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Solo se pueden aprobar cursos que están en estado "En Aprobación".'
                    ], 422);
                }
            }

            // Para otros cambios de estado sensibles, verificar gestión general
            if (in_array($nuevoEstado, [7, 8, 9])) {
                if (!hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::GESTIONAR_CURSO)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No tienes permiso para realizar esta acción.'
                    ], 403);
                }
            }

            $curso->agregarEstado($nuevoEstado);

            // Si es un rechazo, guardar la observación en su propia tabla
            if ($nuevoEstado === 3 && $request->filled('motivo')) {
                ObservacionCurso::create([
                    'id_curso'    => $curso->id_curso,
                    'observacion' => $request->motivo,
                    'creado_por'  => auth()->id(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Estado del curso actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
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
                'message' => 'No se encontraron datos personales para el usuario actual'
            ], 404);
        }

        // Resolver vista en base al permiso en lugar del viejo servicio
        $vistaParcial = hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::GESTIONAR_CURSO)
            ? 'partials.Buscador-actions.CursosCoordinador'
            : 'partials.Buscador-actions.Cursos';

        $esFacilitador = false; // base para vistas

        $query = Curso::with(['modalidad', 'inscripciones', 'estados', 'persona'])
            ->withCount(['contenidos', 'inscripciones']);

        // Si NO tiene gestión total (no es coordinador)
        if (!hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::GESTIONAR_CURSO)) {
            $idPersonaActual = Auth::user()->personalData->id_persona ?? null;
            // Evaluamos si actúa como un facilitador (Posee permisos de edición)
            $puedeEditar = hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::EDITAR_CURSO);

            $query->where(function ($q) use ($idPersonaActual, $puedeEditar) {
                // El filtro base son cursos en estado 6 (Inscripción)
                $q->whereHas('estados', function ($q2) {
                    $q2->where('taller.estados_curso.id_estado', 6);
                });

                if ($idPersonaActual) {
                    if (!$puedeEditar) {
                        // REGLA: Si es participante base sin poderes, EXCLUIR sus propios cursos
                        $q->where('taller.cursos.id_persona', '!=', $idPersonaActual);
                    } else {
                        // REGLA: Si tiene permisos de edición (Facilitador), MOSTRAR sus propios cursos siempre
                        $q->orWhere('taller.cursos.id_persona', $idPersonaActual);
                    }
                }
            });
        }

        // Filtro por búsqueda (nombre o descripción)
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%')
                    ->orWhere('descripcion', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por estado específico
        if ($request->has('id_estado') && !empty($request->id_estado)) {
            $query->whereHas('estados', function ($q) use ($request) {
                $q->where('taller.estados_curso.id_estado', $request->id_estado);
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
     * @param \Illuminate\Pagination\LengthAwarePaginator $cursos
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

            // Determinar la clase CSS del badge según el estado
            $curso->badgeClass = match ($estadoId) {
                1 => 'bg-warning',      // Pendiente
                2 => 'bg-warning',      // Borrador
                3 => 'bg-warning',      // Declinado
                4 => 'bg-warning',      // En Edición
                5 => 'bg-warning',      // En Aprobación
                6 => 'bg-success',      // Inscripción
                7 => 'bg-success',      // En curso
                8 => 'bg-danger',       // Finalizado
                9 => 'bg-danger',       // Cerrado
                default => 'bg-secondary'
            };
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
                'message' => 'No se encontraron datos personales para el usuario actual'
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
            'data' => $cursosParticipante
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
                'message' => 'No se encontraron datos personales para el usuario actual'
            ], 404);
        }

        $persona = $this->getUsuarioAutenticado()->personalData;

        // Buscar el curso con sus relaciones
        $curso = Curso::with(['modalidad', 'contenidos', 'inscripciones.persona'])
            ->find($id);

        if (!$curso) {
            return response()->json([
                'success' => false,
                'message' => 'Curso no encontrado'
            ], 404);
        }

        // Verificar si el usuario es el facilitador o un participante
        $esFacilitador = $curso->id_persona == $persona->id_persona;
        $esParticipante = $curso->inscripciones->contains('id_persona', $persona->id_persona);
        $esCoordinador = hasPermissionRoute('taller.cursos.index', \App\Constants\SecurityAction::GESTIONAR_CURSO);

        if (!$esFacilitador && !$esParticipante && !$esCoordinador) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para ver este curso'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $curso,
            'es_facilitador' => $esFacilitador,
            'es_participante' => $esParticipante
        ]);
    }

    /**
     * Almacena un nuevo curso en la base de datos
     *
     * @param  \Illuminate\Http\Request  $request
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
            'id_curso' => 'required|exists:' . Curso::class . ',id_curso',
        ]);

        $curso = Curso::findOrFail($request->id_curso);

        if ($this->usuarioSinDatosPersonales()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron los datos personales del usuario.'
            ], 404);
        }

        $personalData = $this->getUsuarioAutenticado()->personalData;

        // Verificar si el usuario es el propietario del curso
        if ($curso->id_persona == $personalData->id_persona) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes inscribirte en tu propio curso.'
            ], 403);
        }

        // Verificar si el usuario ya está inscrito en el curso
        $inscripcionExistente = Inscripcion::where('id_curso', $request->id_curso)
            ->where('id_persona', $personalData->id_persona)
            ->first();

        if ($inscripcionExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya estás inscrito en este curso.'
            ], 409);
        }

        // Verificar si hay cupos disponibles
        $inscritos = Inscripcion::where('id_curso', $curso->id_curso)->count();
        if ($curso->cantidad_cupos !== null && $inscritos >= (int) $curso->cantidad_cupos) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cupos disponibles para este curso.'
            ], 400);
        }

        // Crear la inscripción
        $inscripcion = Inscripcion::create([
            'id_curso' => $request->id_curso,
            'id_persona' => $personalData->id_persona,
            'fecha_inscripcion' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inscripción realizada con éxito.',
            'data' => $inscripcion,
            'cupos_restantes' => $curso->cantidad_cupos !== null 
                ? max(0, (int)$curso->cantidad_cupos - Inscripcion::where('id_curso', $curso->id_curso)->count()) 
                : null
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
                'message' => 'No se encontraron los datos personales del usuario.'
            ], 404);
        }

        $personalData = $this->getUsuarioAutenticado()->personalData;

        $inscripcion = Inscripcion::where('id_inscripcion', $id)
            ->where('id_persona', $personalData->id_persona)
            ->first();

        if (!$inscripcion) {
            return response()->json([
                'success' => false,
                'message' => 'Inscripción no encontrada o no autorizada para cancelar.'
            ], 404);
        }

        $curso = $inscripcion->curso;
        $inscripcion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inscripción cancelada correctamente.',
            'cupos_restantes' => $curso->cantidad_cupos !== null 
                ? max(0, (int)$curso->cantidad_cupos - Inscripcion::where('id_curso', $curso->id_curso)->count()) 
                : null
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
                    'message' => 'No autorizado para finalizar la edición de este curso'
                ], 403);
            }

            $personalData = $this->getUsuarioAutenticado()->personalData;

            if ($curso->id_persona != $personalData->id_persona) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado para finalizar la edición de este curso'
                ], 403);
            }

            // Agregar el nuevo estado (5 = Finalizado)
            $curso->agregarEstado(5);

            return response()->json([
                'success' => true,
                'message' => 'Edición del curso finalizada correctamente',
                'estado_actual' => 5
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Curso no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar la edición del curso: ' . $e->getMessage()
            ], 500);
        }
    }
}
