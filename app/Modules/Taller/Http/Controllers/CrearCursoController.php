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

            return redirect()->route('taller.cursos.plantilla.create', ['curso' => $curso->id_curso])
                ->with('success', 'Curso "' . $curso->nombre . '" planificado exitosamente. Ahora configure la plantilla del certificado.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear curso: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ocurrió un error al crear el curso: ' . $e->getMessage()]);
        }
    }

    /**
     * Muestra el formulario para cargar la plantilla del certificado (Paso 2)
     *
     * @param int|string $id_curso
     * @return \Illuminate\View\View
     */
    public function plantillaCreate($id_curso)
    {
        $curso = Curso::findOrFail($id_curso);

        // --- MANEJO DE IMAGEN DE PLANTILLA CON PREVENCIÓN DE CACHÉ ---
        $plantillaPath = storage_path('app/public/Certificados/cursos/' . $curso->id_curso . '.png');
        $plantillaGlobalPath = storage_path('app/public/Certificados/plantilla.png');

        $plantillaUrl = null;
        if (file_exists($plantillaPath)) {
            // Agregamos ?v=timestamp para obligar al navegador a refrescar la imagen
            $plantillaUrl = asset('storage/Certificados/cursos/' . $curso->id_curso . '.png') . '?v=' . filemtime($plantillaPath);
        } elseif (file_exists($plantillaGlobalPath)) {
            $plantillaUrl = asset('storage/Certificados/plantilla.png') . '?v=' . filemtime($plantillaGlobalPath);
        }

        // --- MANEJO DE FIRMA CON PREVENCIÓN DE CACHÉ ---
        $firmaPath = storage_path('app/public/Certificados/cursos/' . $curso->id_curso . '_firma.png');
        $firmaUrl = file_exists($firmaPath)
            ? asset('storage/Certificados/cursos/' . $curso->id_curso . '_firma.png') . '?v=' . filemtime($firmaPath)
            : null;

        // Cargar coordenadas: hardcoded → defaults.json → {id_curso}.json
        $hardcoded = [
            'nombre' => ['x' => 40.9, 'y' => 78.2, 'size' => 24, 'w' => 220],
            'dni'    => ['x' => 159.4, 'y' => 92.6, 'size' => 12, 'w' => 25],
            'qr'     => ['x' => 9.8, 'y' => 154.6, 'w' => 20, 'h' => 20],
            'code'   => ['x' => 0.0, 'y' => 177.4, 'size' => 8, 'w' => 50],
            'firma'  => ['x' => 178.1, 'y' => 130.2, 'w' => 50, 'h' => 20],
        ];

        $coords = $hardcoded;

        // 1. Cargar defaults globales si existen
        $defaultsPath = storage_path('app/public/Certificados/defaults.json');
        if (file_exists($defaultsPath)) {
            $defaults = json_decode(file_get_contents($defaultsPath), true);
            if ($defaults) {
                $coords = array_merge($coords, $defaults);
            }
        }

        // 2. Cargar coordenadas específicas del curso si existen
        $coordsPath = storage_path('app/public/Certificados/cursos/' . $curso->id_curso . '.json');
        if (file_exists($coordsPath)) {
            $savedCoords = json_decode(file_get_contents($coordsPath), true);
            if ($savedCoords) {
                $coords = array_merge($coords, $savedCoords);
            }
        }

        // Verificar si ya existe firma guardada con cualquier extensión válida (png, jpg, jpeg)
        $firmaUrl = null;
        foreach (['png', 'jpg', 'jpeg'] as $ext) {
            $firmaPath = storage_path('app/public/Certificados/cursos/' . $curso->id_curso . '_firma.' . $ext);
            if (file_exists($firmaPath)) {
                $firmaUrl = asset('storage/Certificados/cursos/' . $curso->id_curso . '_firma.' . $ext);
                break;
            }
        }

        return view('taller::a.CursoPlantillaCrear', compact('curso', 'plantillaUrl', 'coords', 'firmaUrl'));
    }

    /**
     * Almacena la plantilla del certificado y continúa a Requisitos (Paso 3)
     *
     * @param Request $request
     * @param int|string $id_curso
     * @return \Illuminate\Http\RedirectResponse
     */
    public function plantillaStore(Request $request, $id_curso)
    {
        $curso = Curso::findOrFail($id_curso);

        $request->validate([
            'plantilla' => 'nullable|image|mimes:png,jpg,jpeg|max:4096',
            'firma'     => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'coords'    => 'nullable|json',
            'guardar_default' => 'nullable'
        ]);

        $destinationPath = storage_path('app/public/Certificados/cursos');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // Guardar plantilla si se subió una
        if ($request->hasFile('plantilla')) {
            $request->file('plantilla')->move($destinationPath, $curso->id_curso . '.png');
        }

        // Guardar firma si se subió una
        if ($request->hasFile('firma')) {
            $extension = $request->file('firma')->getClientOriginalExtension();
            // Eliminar firmas previas con otras extensiones para evitar duplicidad de firmas
            foreach (['png', 'jpg', 'jpeg'] as $ext) {
                $oldFile = $destinationPath . '/' . $curso->id_curso . '_firma.' . $ext;
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }
            $request->file('firma')->move($destinationPath, $curso->id_curso . '_firma.' . $extension);
        }

        // Guardar coordenadas si se enviaron
        if ($request->filled('coords')) {
            file_put_contents($destinationPath . '/' . $curso->id_curso . '.json', $request->coords);

            // Guardar como predeterminado si se solicitó
            if ($request->has('guardar_default')) {
                $defaultsDir = storage_path('app/public/Certificados');
                if (!file_exists($defaultsDir)) {
                    mkdir($defaultsDir, 0755, true);
                }
                file_put_contents($defaultsDir . '/defaults.json', $request->coords);
            }
        }

        // Redirección condicional: si es creación inicial (POR_ACEPTAR), ir a Requisitos. 
        // De lo contrario (edición posterior), regresar al detalle del curso.
        if ($curso->estado_actual && $curso->estado_actual->id_estado == EstadoCurso::POR_ACEPTAR->value) {
            return redirect()->route('taller.cursos.requisitos.create', ['curso' => $curso->id_curso])
                ->with('success', 'Configuración de certificado guardada. Ahora define los requisitos de la actividad.');
        }

        return redirect()->route('taller.cursos.show', $curso->id_curso)
            ->with('success', 'Certificado actualizado exitosamente.');
    }
}
