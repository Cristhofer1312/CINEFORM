<?php

namespace Modules\Taller\Http\Controllers;

use App\Enums\EstadoCurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Comun\Entities\PersonalData;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\ContenidoCurso;
use App\Constants\SecurityAction;
use Modules\Taller\Http\Requests\UpdateCursoRequest;

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

        // Verificar que el usuario autenticado es el Facilitador del curso o un Coordinador
        if (!$esCoordinador && (!$idPersona || $curso->id_persona != $idPersona)) {
            abort(403, 'No tienes permiso para editar este curso.');
        }

        // VALIDACIÓN DE ESTADOS EDITABLES
        $estadoActual = $curso->estado_actual->id_estado ?? null;
        $estadosEditablesFacilitador = [EstadoCurso::DECLINADO->value, EstadoCurso::EDICION->value]; 
        $estadosEditablesCoordinador = [EstadoCurso::DECLINADO->value, EstadoCurso::EDICION->value, EstadoCurso::EN_CURSO->value];

        $estadosPermitidos = $esCoordinador ? $estadosEditablesCoordinador : $estadosEditablesFacilitador;

        if (!in_array($estadoActual, $estadosPermitidos)) {
            $nombreEstado = $this->obtenerNombreEstado($estadoActual);
            return redirect()
                ->route('taller.cursos.show', $curso->id_curso)
                ->with('warning', "No se puede editar el curso en estado '$nombreEstado'.");
        }

        $modalidades = \Modules\Taller\Entities\Modalidad::all();
        $tiposEvaluacion = \Modules\Taller\Entities\TipoEvaluacion::all();
        $actividades = \Modules\Taller\Entities\ActividadFormativa::where('status', 'Activo')
            ->orWhere('id_actividad_formativa', $curso->id_actividad_formativa)->orderBy('nombre')->get();
        $aspectos = \Modules\Taller\Entities\Aspecto::where('status', 'Activo')
            ->orWhere('id_aspecto', $curso->id_aspecto)->orderBy('nombre')->get();
        $modalidadesEspeciales = \Modules\Taller\Entities\ModalidadEspecial::all();
        $regiones = \Modules\Parametros\Entities\Estados::all();

        $curso->load('modalidad');
        $contenidos = $curso->contenidos()->orderBy('fecha_contenido', 'asc')->orderBy('id_contenido_curso', 'asc')->get();
        $esFacilitador = $idPersona && $curso->id_persona == $idPersona;

        $facilitadores = \Modules\Security\Entities\User::whereHas('perfiles.permissions', function ($q) {
            $q->where('security.permissions.slug', SecurityAction::dbString(SecurityAction::EDITAR_CURSO));
        })->with(['personalData.especializaciones'])->get()->filter(fn($u) => $u->personalData != null);

        $especializaciones = \Modules\Comun\Entities\Especializacion::where('status', 'Activo')->get();

        return view('taller::a.CursoEditar', compact(
            'curso', 'modalidades', 'contenidos', 'tiposEvaluacion', 'esCoordinador', 
            'esFacilitador', 'facilitadores', 'especializaciones', 'actividades', 
            'aspectos', 'modalidadesEspeciales', 'regiones'
        ));
    }

    /**
     * Actualiza el curso existente
     *
     * @param  UpdateCursoRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateCursoRequest $request, $id)
    {
        try {
            $validatedData = $request->validated();
            $curso = Curso::findOrFail($id);

            // ── Validación server-side: ponderación total = 100% ──
            if (isset($validatedData['contenidos'])) {
                $totalPonderacion = 0;
                $tieneEvaluacion = false;

                foreach ($validatedData['contenidos'] as $cData) {
                    if (!empty($cData['es_evaluacion']) && (bool)$cData['es_evaluacion']) {
                        $tieneEvaluacion = true;
                        $totalPonderacion += floatval($cData['ponderacion'] ?? 0);
                    }
                }

                if ($tieneEvaluacion && abs($totalPonderacion - 100) > 0.01) {
                    return back()->withInput()->withErrors([
                        'ponderacion' => "La ponderación total de las evaluaciones debe ser exactamente 100%. Actualmente es {$totalPonderacion}%."
                    ]);
                }
            }

            DB::beginTransaction();

            // Solo actualizar los campos que vienen en la solicitud para evitar borrar datos 
            // cuando el formulario (ej. facilitador) no incluye todos los campos.
            $updateData = [];
            
            $camposPermitidos = [
                'nombre', 'id_modalidad', 'id_persona', 'duracion', 'horas', 
                'cantidad_cupos', 'fecha_inicio', 'fecha_fin', 'descripcion'
            ];

            foreach ($camposPermitidos as $campo) {
                if ($request->has($campo)) {
                    $updateData[$campo] = $validatedData[$campo];
                }
            }

            // Bloquear cambio de facilitador si el curso está en estado >= 6 (Inscripción en adelante)
            if (isset($updateData['id_persona']) && (int) $updateData['id_persona'] !== (int) $curso->id_persona) {
                $estadoActual = $curso->estado_actual->id_estado ?? 0;
                if ($estadoActual >= EstadoCurso::INSCRIPCION->value) {
                    return back()->withInput()->withErrors([
                        'id_persona' => 'No se puede cambiar el facilitador de un curso en estado "' . (EstadoCurso::tryFrom($estadoActual)?->label() ?? 'Desconocido') . '".'
                    ]);
                }
            }

            // El campo es_nacional es un checkbox, se maneja aparte
            if ($request->has('es_nacional_present')) { // Un campo oculto para saber que el checkbox existe en el form
                 $updateData['es_nacional'] = $request->has('es_nacional');
            } elseif ($request->has('es_nacional')) {
                 $updateData['es_nacional'] = true;
            }

            // Requisito: Solo el coordinador puede editar el campo Telegram
            if (hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO)) {
                if ($request->has('telegram')) {
                    $updateData['telegram'] = $validatedData['telegram'];
                }
            }

            $updateData['actualizado_por'] = Auth::id();

            $curso->update($updateData);

            // Sincronizar localidades (Solo si no es nacional)
            if ($curso->es_nacional) {
                $curso->localidades()->detach();
            } elseif ($request->has('localidades')) {
                $curso->localidades()->sync($validatedData['localidades'] ?? []);
            }

            // Gestión de Contenidos (Solo si vienen en el request)
            if ($request->has('contenidos')) {
                $curso->contenidos()->delete();

                foreach ($validatedData['contenidos'] as $index => $cData) {
                    $esEvaluacion = isset($cData['es_evaluacion']) ? (bool)$cData['es_evaluacion'] : false;

                    ContenidoCurso::create([
                        'id_curso'           => $curso->id_curso,
                        'titulo'             => $cData['titulo'],
                        'descripcion'        => $cData['descripcion_breve'] ?? '',
                        'descripcion_breve'  => $cData['descripcion_breve'] ?? '',
                        'url_contenido'      => $cData['url_contenido'] ?? null,
                        'fecha_contenido'    => $cData['fecha_contenido'] ?? null,
                        'es_evaluacion'      => $esEvaluacion,
                        'id_tipo_evaluacion' => $esEvaluacion ? ($cData['id_tipo_evaluacion'] ?? null) : null,
                        'ponderacion'        => $esEvaluacion ? ($cData['ponderacion'] ?? 0) : 0,
                        'creado_por'         => Auth::id(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('taller.cursos.show', $curso->id_curso)->with('success', 'Curso actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar curso: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Error al guardar cambios: ' . $e->getMessage()]);
        }
    }

    private function obtenerNombreEstado(?int $idEstado): string
    {
        $estado = \App\Enums\EstadoCurso::tryFrom($idEstado);
        return $estado ? $estado->label() : 'Desconocido';
    }
}
