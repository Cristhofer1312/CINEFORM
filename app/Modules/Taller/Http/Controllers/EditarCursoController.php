<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Comun\Entities\PersonalData;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\ContenidoCurso;
use App\Constants\SecurityAction;

class EditarCursoController extends BaseController
{
    /**
     * Muestra el formulario de edición del curso
     *
     * @param  int  $id
     * @return \Illuminate\View\View|\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        Log::info('Iniciando edición de curso', ['curso_id' => $id, 'user_id' => Auth::id()]);

        $curso = Curso::with(['estados', 'observaciones.autor'])->findOrFail($id);

        // Obtener los datos de la persona autenticada
        $persona = PersonalData::where('user_id', Auth::id())->first();
        $esCoordinador = hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO);

        if (!$persona && !$esCoordinador) {
            Log::error('No se encontraron datos personales para el usuario (No Coordinador)', [
                'user_id' => Auth::id(),
            ]);
            abort(403, 'No se encontraron tus datos de perfil. Por favor, contacta al administrador.');
        }

        $idPersona = $persona ? $persona->id_persona : null;

        Log::info('Datos del curso', [
            'curso_id' => $curso->id_curso,
            'curso_id_persona' => $curso->id_persona,
            'usuario_actual_id' => $idPersona,
            'son_iguales' => ($idPersona && $curso->id_persona == $idPersona) ? 'Sí' : 'No',
            'es_coordinador' => $esCoordinador ? 'Sí' : 'No'
        ]);

        // Verificar que el usuario autenticado es el Facilitador del curso o un Coordinador
        if ($esCoordinador) {
            Log::info('Usuario autenticado es el Coordinador de la institucion', [
                'usuario' => Auth::user()
            ]);
        } elseif (!$idPersona || $curso->id_persona != $idPersona) {
            Log::warning('Intento de edición no autorizado', [
                'curso_id' => $curso->id_curso,
                'usuario_esperado' => $curso->id_persona,
                'usuario_actual' => $idPersona,
                'usuario' => Auth::user()
            ]);
            abort(403, 'No tienes permiso para editar este curso.');
        }

        // ✅ VALIDACIÓN DE ESTADOS EDITABLES
        // Estados permitidos para edición:
        // 1 = Por Aceptar, 3 = Declinado, 4 = En Edición, 7 = En Progreso (solo coordinador)
        $estadoActual = $curso->estado_actual->id_estado ?? null;

        $estadosEditablesFacilitador = [3, 4]; // Declinado, En Edición
        $estadosEditablesCoordinador = [3, 4, 7]; // Declinado, En Edición, En Progreso

        $estadosPermitidos = $esCoordinador ? $estadosEditablesCoordinador : $estadosEditablesFacilitador;

        // Si el estado no permite edición, redirigir a vista de detalle con mensaje
        if (!in_array($estadoActual, $estadosPermitidos)) {
            $nombreEstado = $this->obtenerNombreEstado($estadoActual);

            Log::warning('Intento de edición en estado no permitido', [
                'curso_id' => $id,
                'estado_actual' => $estadoActual,
                'estados_permitidos' => $estadosPermitidos,
                'es_coordinador' => $esCoordinador
            ]);

            return redirect()
                ->route('taller.cursos.show', $curso->id_curso)
                ->with('warning', "No se puede editar el curso en estado '$nombreEstado'. Solo se puede editar cuando está Declinado o En Edición.");
        }

        // Obtener las modalidades para el select
        $modalidades = \Modules\Taller\Entities\Modalidad::all();

        // Obtener tipos de evaluación
        $tiposEvaluacion = \Modules\Taller\Entities\TipoEvaluacion::all();

        $actividades = \Modules\Taller\Entities\ActividadFormativa::where('status', 'Activo')
            ->orWhere('id_actividad_formativa', $curso->id_actividad_formativa)
            ->orderBy('nombre')
            ->get();
        $aspectos = \Modules\Taller\Entities\Aspecto::where('status', 'Activo')
            ->orWhere('id_aspecto', $curso->id_aspecto)
            ->orderBy('nombre')
            ->get();
        $modalidadesEspeciales = \Modules\Taller\Entities\ModalidadEspecial::all();
        $regiones = \Modules\Parametros\Entities\Estados::all();

        // Cargar la relación de modalidad
        $curso->load('modalidad');

        // Cargar los contenidos del curso ordenados
        $contenidos = $curso->contenidos()->orderBy('fecha_contenido', 'asc')->orderBy('id_contenido_curso', 'asc')->get();

        // Determinar si el usuario es facilitador del curso
        $esFacilitador = $idPersona && $curso->id_persona == $idPersona;

        // Obtener Facilitadores (Cualquier persona con el permiso de Dictar/Editar curso) para que el coordinador pueda reasignar
        $facilitadores = \Modules\Security\Entities\User::whereHas('getPerfiles.permissions', function ($q) {
            $q->where('security.permissions.slug', SecurityAction::dbString(SecurityAction::EDITAR_CURSO));
        })
            ->with(['personalData.especializaciones'])
            ->get()
            ->filter(fn($u) => $u->personalData != null);

        $especializaciones = \Modules\Comun\Entities\Especializacion::where('status', 'Activo')->get();

        Log::info('Permiso de edición concedido');
        return view('taller::a.CursoEditar', compact(
            'curso',
            'modalidades',
            'contenidos',
            'tiposEvaluacion',
            'esCoordinador',
            'esFacilitador',
            'facilitadores',
            'especializaciones',
            'actividades',
            'aspectos',
            'modalidadesEspeciales',
            'regiones'
        ));
    }

    /**
     * Obtiene el nombre legible del estado
     *
     * @param int|null $idEstado ID del estado
     * @return string Nombre del estado
     */
    private function obtenerNombreEstado(?int $idEstado): string
    {
        $estados = [
            1 => 'Por Aceptar',
            2 => 'Aceptado',
            3 => 'Declinado',
            4 => 'En Edición',
            5 => 'En Aprobación',
            6 => 'Inscripciones',
            7 => 'En Progreso',
            8 => 'Finalizado',
            9 => 'Cerrado'
        ];

        return $estados[$idEstado] ?? 'Desconocido';
    }


    /**
     * Actualiza un curso existente
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Log de depuración
        Log::info('Iniciando actualización de curso', [
            'curso_id' => $id,
            'user_id' => Auth::id()
        ]);

        try {
            // Obtener el usuario autenticado
            $user = Auth::user();
            if (!$user) {
                Log::error('Usuario no autenticado');
                return redirect()->route('login');
            }

            // Obtener los datos de la persona asociada al usuario
            $persona = \Modules\Comun\Entities\PersonalData::where('user_id', $user->id)->first();
            $isCoordinator = hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO);

            if (!$persona && !$isCoordinator) {
                Log::error('No se encontraron datos de persona para el usuario (No Coordinador)', [
                    'user_id' => $user->id,
                ]);
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'No se encontró tu perfil de persona. Contacta al administrador.']);
            }

            $idPersona = $persona ? $persona->id_persona : null;

            // Buscar el curso con sus relaciones
            $curso = Curso::with('contenidos')->find($id);

            if (!$curso) {
                Log::error('Curso no encontrado', ['curso_id' => $id]);
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'El curso solicitado no existe.']);
            }

            // Verificar que el usuario es el propietario del curso o un Coordinador
            if (!$isCoordinator && (!$idPersona || $curso->id_persona != $idPersona)) {
                Log::warning('Intento de edición no autorizado', [
                    'curso_id' => $id,
                    'usuario_esperado' => $curso->id_persona,
                    'usuario_actual' => $idPersona,
                    'user' => $user->toArray()
                ]);

                return back()
                    ->withInput()
                    ->withErrors(['error' => 'No tienes permiso para editar este curso.']);
            }

            // Validación: El dueño solo puede editar si el curso está en estado válido (Por Aceptar, Borrador)
            // Se asume 1: Por Aceptar (recién creado), 4: Borrador, 6: Borrador (alternativo)

            // Obtener ID del estado actual
            $estadoActual = null;
            if ($curso->estado_actual) { // Accesor definido en modelo
                $estadoActual = $curso->estado_actual->id_estado;
            } elseif (isset($curso->estado_id)) {
                $estadoActual = $curso->estado_id;
            } else {
                // Try loading connection
                $estadoObj = $curso->estadoActual()->first();
                if ($estadoObj)
                    $estadoActual = $estadoObj->id_estado;
            }

            // Estados permitidos para edición por facilitador: 3 (Declinado), 4 (En Edición)
            $estadosPermitidos = [3, 4];

            if (!$isCoordinator && !in_array($estadoActual, $estadosPermitidos)) {
                Log::warning('Intento de edición en estado no permitido', [
                    'curso_id' => $id,
                    'estado_actual' => $estadoActual,
                    'estados_permitidos' => $estadosPermitidos
                ]);
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'Solo se puede editar el curso cuando está en estado pendiente o borrador. Estado actual: ' . $estadoActual]);
            }

            // Validación de campos del curso
            if ($isCoordinator) {
                $validatedData = $request->validate([
                    'nombre' => 'required|string|max:255',
                    'codigo' => 'nullable|string|max:100',
                    'id_modalidad' => 'required|exists:' . \Modules\Taller\Entities\Modalidad::class . ',id_modalidad',
                    'id_actividad_formativa' => 'nullable|exists:' . \Modules\Taller\Entities\ActividadFormativa::class . ',id_actividad_formativa',
                    'id_aspecto' => 'nullable|exists:' . \Modules\Taller\Entities\Aspecto::class . ',id_aspecto',
                    'id_modalidad_especial' => 'nullable|exists:' . \Modules\Taller\Entities\ModalidadEspecial::class . ',id_modalidad_especial',
                    'id_estado' => 'nullable|exists:' . \Modules\Parametros\Entities\Estados::class . ',id_estado',
                    'id_persona' => 'required|exists:' . \Modules\Comun\Entities\PersonalData::class . ',id_persona',
                    'nivel' => 'nullable|string|max:50',
                    'trimestre' => 'nullable|integer|min:1|max:4',
                    'correlativo' => 'nullable|integer',
                    'anio' => 'nullable|integer|min:2000',
                    'descripcion' => 'nullable|string',
                    'duracion' => 'nullable|integer|min:1',
                    'horas' => 'nullable|integer|min:1',
                    'cantidad_cupos' => 'nullable|integer|min:0',
                    'fecha_inicio' => 'nullable|date',
                    'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
                    
                    'contenidos' => 'nullable|array',
                    'contenidos.*.id' => 'nullable|integer',
                    'contenidos.*.titulo' => 'required|string|max:255',
                    'contenidos.*.url_contenido' => 'nullable|url',
                    'contenidos.*.fecha_contenido' => 'nullable|date',
                    'contenidos.*.descripcion' => 'nullable|string',
                    'contenidos.*.descripcion_breve' => 'nullable|string',
                    'contenidos.*.orden' => 'nullable|integer|min:0',
                    'contenidos.*.es_evaluacion' => 'nullable|boolean',
                    'contenidos.*.id_tipo_evaluacion' => 'nullable|exists:' . \Modules\Taller\Entities\TipoEvaluacion::class . ',id_tipo_evaluacion',
                    'contenidos.*.ponderacion' => 'nullable|numeric|min:0|max:100'
                ]);
            } else {
                $validatedData = $request->validate([

                    'descripcion' => 'nullable|string',
                    'contenidos' => 'nullable|array',
                    'contenidos.*.id' => 'nullable|integer',
                    'contenidos.*.titulo' => 'required|string|max:255',
                    // 'contenidos.*.tipo_contenido' => 'required|in:video,documento,enlace',
                    'contenidos.*.url_contenido' => 'nullable|url',
                    'contenidos.*.fecha_contenido' => 'nullable|date',
                    'contenidos.*.descripcion' => 'nullable|string',
                    'contenidos.*.descripcion_breve' => 'nullable|string',
                    'contenidos.*.orden' => 'nullable|integer|min:0',
                    'contenidos.*.es_evaluacion' => 'nullable|boolean',
                    'contenidos.*.id_tipo_evaluacion' => 'nullable|exists:' . \Modules\Taller\Entities\TipoEvaluacion::class . ',id_tipo_evaluacion',
                    'contenidos.*.ponderacion' => 'nullable|numeric|min:0|max:100'
                ]);
            }

            DB::beginTransaction();

            // Validar que la suma de las ponderaciones sea exactamente 100 si hay evaluaciones
            if (isset($validatedData['contenidos']) && is_array($validatedData['contenidos'])) {
                $totalPonderacion = 0;
                $hayEvaluaciones = false;
                foreach ($validatedData['contenidos'] as $contenidoData) {
                    if (isset($contenidoData['es_evaluacion']) && $contenidoData['es_evaluacion'] == 1) {
                        $totalPonderacion += (float) ($contenidoData['ponderacion'] ?? 0);
                        $hayEvaluaciones = true;
                    }
                }

                if ($hayEvaluaciones) {
                    if ($totalPonderacion > 100) {
                        DB::rollBack();
                        return back()
                            ->withInput()
                            ->withErrors(['error' => 'La suma de las ponderaciones de las evaluaciones no puede ser mayor al 100%. Total actual: ' . $totalPonderacion . '%']);
                    } else if ($totalPonderacion < 100) {
                        DB::rollBack();
                        return back()
                            ->withInput()
                            ->withErrors(['error' => 'La suma de las ponderaciones de las evaluaciones es menor al 100%. Total actual: ' . $totalPonderacion . '%']);
                    }
                }
            }
            if ($isCoordinator) {
                $curso->update([
                    'nombre' => $validatedData['nombre'],
                    'codigo' => $validatedData['codigo'] ?? null,
                    'id_modalidad' => $validatedData['id_modalidad'],
                    'id_actividad_formativa' => $validatedData['id_actividad_formativa'] ?? null,
                    'id_aspecto' => $validatedData['id_aspecto'] ?? null,
                    'id_modalidad_especial' => $validatedData['id_modalidad_especial'] ?? null,
                    'id_estado' => $validatedData['id_estado'] ?? null, // Región
                    'id_persona' => $validatedData['id_persona'] ?? null,
                    'nivel' => $validatedData['nivel'] ?? null,
                    'trimestre' => $validatedData['trimestre'] ?? null,
                    'correlativo' => $validatedData['correlativo'] ?? null,
                    'anio' => $validatedData['anio'] ?? null,
                    'descripcion' => $validatedData['descripcion'] ?? null,
                    'duracion' => $validatedData['duracion'] ?? null,
                    'horas' => $validatedData['horas'] ?? null,
                    'cantidad_cupos' => $validatedData['cantidad_cupos'] ?? null,
                    'fecha_inicio' => $validatedData['fecha_inicio'] ?? null,
                    'fecha_fin' => $validatedData['fecha_fin'] ?? null,
                ]);
            } else {
                $curso->update([
                    'descripcion' => $validatedData['descripcion'] ?? null,
                ]);
            }


            Log::info('Curso actualizado', ['curso_id' => $curso->id_curso]);

            // Sincronizar contenidos
            if (isset($validatedData['contenidos']) && is_array($validatedData['contenidos'])) {
                $contenidosData = collect($validatedData['contenidos']);
                Log::debug('Contenidos a procesar:', $contenidosData->toArray());

                // Obtener IDs de contenidos existentes
                $contenidosExistentes = $curso->contenidos()->pluck('id_contenido_curso')->toArray();
                $contenidosRecibidos = $contenidosData->pluck('id')->filter()->values()->toArray();

                Log::debug('Contenidos existentes:', $contenidosExistentes);
                Log::debug('Contenidos recibidos:', $contenidosRecibidos);

                // Eliminar contenidos que ya no están en la lista
                $contenidosAEliminar = array_diff($contenidosExistentes, $contenidosRecibidos);
                if (!empty($contenidosAEliminar)) {
                    Log::info('Eliminando contenidos:', $contenidosAEliminar);
                    ContenidoCurso::whereIn('id_contenido_curso', $contenidosAEliminar)->delete();
                }

                // Actualizar o crear contenidos
                foreach ($contenidosData as $index => $contenidoData) {
                    try {
                        $esEvaluacion = isset($contenidoData['es_evaluacion']) ? (bool) $contenidoData['es_evaluacion'] : false;

                        // Asegurar que descripcion no sea null (requerido por DB)
                        $descripcion = $contenidoData['descripcion'] ?? $contenidoData['descripcion_breve'] ?? $contenidoData['titulo'] ?? '';

                        $dataToSave = [
                            'titulo' => $contenidoData['titulo'],
                            'url_contenido' => $contenidoData['url_contenido'] ?? null,
                            'fecha_contenido' => $contenidoData['fecha_contenido'] ?? null,
                            'descripcion' => $descripcion,
                            'es_evaluacion' => $esEvaluacion,
                            'id_tipo_evaluacion' => $esEvaluacion ? ($contenidoData['id_tipo_evaluacion'] ?? null) : null,
                            'ponderacion' => $esEvaluacion ? ($contenidoData['ponderacion'] ?? null) : null
                        ];

                        // Asegurar que descripcion_breve no sea null
                        $dataToSave['descripcion_breve'] = $contenidoData['descripcion_breve'] ?? mb_substr($descripcion, 0, 100);
                        if (empty($dataToSave['descripcion_breve'])) {
                            $dataToSave['descripcion_breve'] = mb_substr($contenidoData['titulo'], 0, 100);
                        }

                        if (!empty($contenidoData['id'])) {
                            // Actualizar contenido existente
                            $contenido = ContenidoCurso::where('id_contenido_curso', $contenidoData['id'])
                                ->where('id_curso', $curso->id_curso)
                                ->first();

                            if ($contenido) {
                                $contenido->update($dataToSave);
                                Log::debug('Contenido actualizado:', $contenido->toArray());
                            }
                        } else {
                            // Crear nuevo contenido
                            $dataToSave['creado_por'] = Auth::id();
                            $dataToSave['actualizado_por'] = Auth::id();

                            $nuevoContenido = $curso->contenidos()->create($dataToSave);
                            Log::debug('Nuevo contenido creado:', $nuevoContenido->toArray());
                        }
                    } catch (\Exception $e) {
                        Log::error('Error al procesar contenido', [
                            'contenido_data' => $contenidoData,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        throw $e;
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('taller.cursos.show', $curso->id_curso)
                ->with('success', 'Curso y contenidos actualizados exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Error de validación al actualizar curso', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar el curso: ' . $e->getMessage(), [
                'curso_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error al actualizar el curso: ' . $e->getMessage()]);
        }
    }
}
