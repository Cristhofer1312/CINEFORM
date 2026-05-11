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
use Modules\Security\Entities\User;
use App\Constants\SecurityAction;

use Modules\Taller\Http\Requests\StoreCursoRequest;

class CrearCursoController extends BaseController
{
    /**
     * Muestra el formulario para crear un nuevo curso
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $modalidades = \Modules\Taller\Entities\Modalidad::all();
        $tiposEvaluacion = \Modules\Taller\Entities\TipoEvaluacion::all();
        
        $actividades = \Modules\Taller\Entities\ActividadFormativa::where('status', 'Activo')->orderBy('nombre')->get();
        $aspectos = \Modules\Taller\Entities\Aspecto::where('status', 'Activo')->orderBy('nombre')->get();
        $modalidadesEspeciales = \Modules\Taller\Entities\ModalidadEspecial::all();
        $regiones = \Modules\Parametros\Entities\Estados::all();

        // Obtener el próximo correlativo para el año actual
        $anioActual = date('Y');
        $ultimoCorrelativo = Curso::where('anio', $anioActual)->max('correlativo') ?? 0;
        $proximoCorrelativo = $ultimoCorrelativo + 1;

        // Obtener facilitadores (Usuarios con permiso de editar cursos)
        $facilitadores = User::whereHas('perfiles.permissions', function($q) {
            $q->where('security.permissions.slug', SecurityAction::dbString(SecurityAction::EDITAR_CURSO));
        })->with(['personalData.especializaciones'])->get()->filter(fn($u) => $u->personalData != null);

        $especializaciones = \Modules\Comun\Entities\Especializacion::where('status', 'Activo')->get();

        return view('taller::a.CursoCrear', compact(
            'modalidades', 'tiposEvaluacion', 'facilitadores', 
            'especializaciones', 'actividades', 'aspectos', 
            'modalidadesEspeciales', 'regiones', 'proximoCorrelativo'
        ));
    }

    /**
     * Almacena un nuevo curso
     *
     * @param  StoreCursoRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCursoRequest $request)
    {
        try {
            $validatedData = $request->validated();

            DB::beginTransaction();

            // Crear el curso (El código PROCINEC se genera automáticamente vía Observer)
            $cursoData = [
                'nombre' => $validatedData['nombre'],
                'id_modalidad' => $validatedData['id_modalidad'],
                'id_actividad_formativa' => $validatedData['id_actividad_formativa'],
                'id_aspecto' => $validatedData['id_aspecto'] ?? null,
                'id_modalidad_especial' => $validatedData['id_modalidad_especial'] ?? null,
                'id_persona' => $validatedData['id_persona'], 
                'nivel' => $request->nivel ?? null,
                'trimestre' => $validatedData['trimestre'],
                'correlativo' => $validatedData['correlativo'],
                'anio' => $validatedData['anio'],
                'descripcion' => $validatedData['descripcion'] ?? null,
                'duracion' => $validatedData['duracion'] ?? null,
                'horas' => $validatedData['horas'] ?? null,
                'cantidad_cupos' => $validatedData['cantidad_cupos'] ?? null,
                'telegram' => $validatedData['telegram'] ?? null,
                'es_nacional' => $request->has('es_nacional'),
                'fecha_inicio' => $validatedData['fecha_inicio'],
                'fecha_fin' => $validatedData['fecha_fin'],
                'creado_por' => Auth::id(),
                'creado_en' => now(),
            ];

            $curso = Curso::create($cursoData);

            // Sincronizar múltiples localidades (Estados)
            if (isset($validatedData['localidades'])) {
                $curso->localidades()->sync($validatedData['localidades']);
            }

            // Asignar estado inicial: Por Aceptar
            // Se usa la tabla pivote curso_estado
            DB::table('taller.curso_estado')->insert([
                'id_curso' => $curso->id_curso,
                'id_estado' => EstadoCurso::POR_ACEPTAR->value,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('Curso creado', ['curso_id' => $curso->id_curso, 'creado_por' => Auth::id()]);

            // Guardar contenidos si existen
            if (isset($validatedData['contenidos']) && is_array($validatedData['contenidos'])) {
                foreach ($validatedData['contenidos'] as $index => $contenidoData) {
                    $esEvaluacion = isset($contenidoData['es_evaluacion']) ? (bool) $contenidoData['es_evaluacion'] : false;

                    $dataToSave = [
                        'titulo' => $contenidoData['titulo'],
                        'descripcion' => $contenidoData['descripcion_breve'] ?? '',
                        'descripcion_breve' => $contenidoData['descripcion_breve'] ?? '',
                        'url_contenido' => $contenidoData['url_contenido'] ?? null,
                        'fecha_contenido' => $contenidoData['fecha_contenido'] ?? null,
                        'es_evaluacion' => $esEvaluacion,
                        'id_tipo_evaluacion' => $esEvaluacion ? ($contenidoData['id_tipo_evaluacion'] ?? null) : null,
                        'ponderacion' => $esEvaluacion ? ($contenidoData['ponderacion'] ?? 0) : 0,
                        'id_curso' => $curso->id_curso,
                        'creado_por' => Auth::id(),
                    ];

                    ContenidoCurso::create($dataToSave);
                }
            }

            DB::commit();

            return redirect()->route('taller.cursos.index')
                ->with('success', 'Curso "' . $curso->nombre . '" planificado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear curso: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error al crear el curso: ' . $e->getMessage()]);
        }
    }
}
