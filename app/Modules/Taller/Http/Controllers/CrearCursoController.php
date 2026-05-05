<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Comun\Entities\PersonalData;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\ContenidoCurso;
use Modules\Security\Entities\User;
use App\Constants\SecurityAction;

class CrearCursoController extends BaseController
{
    public function __construct()
    {
        // Protegemos a todo el controlador usando el nuevo Proceso dedicado "Planificar Curso"
        // Esto permite que el link aparezca en el sidebar solo a quienes tengan este permiso.
        $this->middleware('permiso:taller.cursos.create,' . \App\Constants\SecurityAction::VER)->only(['create', 'store']);
    }

    /**
     * Muestra el formulario de creación del curso
     *
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function create()
    {
        // Obtener las modalidades para el select
        $modalidades = \Modules\Taller\Entities\Modalidad::all();

        // Obtener tipos de evaluación 
        $tiposEvaluacion = \Modules\Taller\Entities\TipoEvaluacion::all();

        // Obtener Facilitadores (Cualquier persona con el permiso de Dictar/Editar curso)
        // Esto permite una asignación dinámica basada en RBAC y no solo en un ID de perfil rígido
        $facilitadores = User::whereHas('getPerfiles.permissions', function ($q) {
            $q->where('security.permissions.slug', SecurityAction::dbString(SecurityAction::EDITAR_CURSO));
        })
            ->with(['personalData.especializaciones'])
            ->get()
            ->filter(function ($user) {
                return $user->personalData != null;
            });

        $especializaciones = \Modules\Comun\Entities\Especializacion::where('status', 'Activo')->get();
        
        $actividades = \Modules\Taller\Entities\ActividadFormativa::activos()->orderBy('nombre')->get();
        $aspectos = \Modules\Taller\Entities\Aspecto::activos()->orderBy('nombre')->get();
        $modalidadesEspeciales = \Modules\Taller\Entities\ModalidadEspecial::all();
        $regiones = \Modules\Parametros\Entities\Estados::all();
        
        // Obtener el último correlativo para sugerir el siguiente
        $ultimoCorrelativo = \Modules\Taller\Entities\Curso::max('correlativo') ?? 0;
        $proximoCorrelativo = $ultimoCorrelativo + 1;

        return view('taller::a.CursoCrear', compact(
            'modalidades', 
            'tiposEvaluacion', 
            'facilitadores', 
            'especializaciones', 
            'actividades', 
            'aspectos', 
            'modalidadesEspeciales', 
            'regiones',
            'proximoCorrelativo'
        ));
    }

    /**
     * Almacena un nuevo curso
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // Validación
            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'codigo' => 'nullable|string|max:100',
                'id_modalidad' => 'required|exists:' . \Modules\Taller\Entities\Modalidad::class . ',id_modalidad',
                'id_actividad_formativa' => 'nullable|exists:' . \Modules\Taller\Entities\ActividadFormativa::class . ',id_actividad_formativa',
                'id_aspecto' => 'nullable|exists:' . \Modules\Taller\Entities\Aspecto::class . ',id_aspecto',
                'id_modalidad_especial' => 'nullable|exists:' . \Modules\Taller\Entities\ModalidadEspecial::class . ',id_modalidad_especial',
                'id_estado' => 'nullable|exists:' . \Modules\Parametros\Entities\Estados::class . ',id_estado',
                'id_persona' => 'required|exists:' . \Modules\Comun\Entities\PersonalData::class . ',id_persona', // Facilitador
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
                'contenidos.*.titulo' => 'required|string|max:255',
                'contenidos.*.url_contenido' => 'nullable|url',
                'contenidos.*.fecha_contenido' => 'nullable|date',
                'contenidos.*.descripcion' => 'nullable|string',
                'contenidos.*.descripcion_breve' => 'nullable|string',
                'contenidos.*.es_evaluacion' => 'nullable|boolean',
                'contenidos.*.id_tipo_evaluacion' => 'nullable|exists:' . \Modules\Taller\Entities\TipoEvaluacion::class . ',id_tipo_evaluacion',
                'contenidos.*.ponderacion' => 'nullable|numeric|min:0|max:100'
            ]);

            DB::beginTransaction();



            // Crear el curso
            // Nota: id_curso es autoincremental, no se pasa.
            // create() asignará automáticamente timestamps si el modelo lo permite.
            // 'status' inicial logic: 
            // Si el coordinador lo crea, ¿en qué estado nace? 
            // Asumiremos estado inicial o el que defina el negocio. Por ahora no pasamos status explícito si no es necesario,
            // o lo definimos. Entities/Curso tiene getStatuses() pero no un default claro en código visible.
            // Asumiremos que la BD tiene default o se maneja por lógica de negocio.
            // Revisando Curso model: "status" se cast a Enum.
            // Vamos a crearlo base.

            $cursoData = [
                'nombre' => $validatedData['nombre'],
                'codigo' => $validatedData['codigo'] ?? null,
                'id_modalidad' => $validatedData['id_modalidad'],
                'id_actividad_formativa' => $validatedData['id_actividad_formativa'] ?? null,
                'id_aspecto' => $validatedData['id_aspecto'] ?? null,
                'id_modalidad_especial' => $validatedData['id_modalidad_especial'] ?? null,
                'id_estado' => $validatedData['id_estado'] ?? null, // Región
                'id_persona' => $validatedData['id_persona'], // Facilitador asignado
                'nivel' => $validatedData['nivel'] ?? null,
                'trimestre' => $validatedData['trimestre'] ?? null,
                'correlativo' => $validatedData['correlativo'] ?? null,
                'anio' => $validatedData['anio'] ?? date('Y'),
                'descripcion' => $validatedData['descripcion'] ?? null,
                'duracion' => $validatedData['duracion'] ?? null,
                'horas' => $validatedData['horas'] ?? null,
                'cantidad_cupos' => $validatedData['cantidad_cupos'] ?? null,
                'fecha_inicio' => $validatedData['fecha_inicio'] ?? null,
                'fecha_fin' => $validatedData['fecha_fin'] ?? null,
                'creado_por' => Auth::id(),
                'creado_en' => now(),
            ];

            $curso = Curso::create($cursoData);

            // Asignar estado inicial: Por Aceptar (1)
            // Se usa la tabla pivote curso_estado
            DB::table('taller.curso_estado')->insert([
                'id_curso' => $curso->id_curso,
                'id_estado' => 1, // Por Aceptar
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
                        'url_contenido' => $contenidoData['url_contenido'] ?? null,
                        'fecha_contenido' => $contenidoData['fecha_contenido'] ?? null,
                        'descripcion' => $contenidoData['descripcion'] ?? $contenidoData['descripcion_breve'] ?? null,
                        'descripcion_breve' => $contenidoData['descripcion_breve'] ?? mb_substr($contenidoData['descripcion'] ?? '', 0, 100) . '...',
                        'es_evaluacion' => $esEvaluacion,
                        'id_tipo_evaluacion' => $esEvaluacion ? ($contenidoData['id_tipo_evaluacion'] ?? null) : null,
                        'ponderacion' => $esEvaluacion ? ($contenidoData['ponderacion'] ?? null) : null,
                        'creado_por' => Auth::id(),
                        'actualizado_por' => Auth::id() // Se usa create() de la relación hasMany
                    ];

                    $curso->contenidos()->create($dataToSave);
                }
            }

            DB::commit();

            return redirect()
                ->route('taller.cursos.index')
                ->with('success', 'Curso creado exitosamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear curso: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error al crear el curso: ' . $e->getMessage()]);
        }
    }
}
