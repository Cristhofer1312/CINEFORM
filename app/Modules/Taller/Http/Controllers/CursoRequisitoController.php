<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\CursoRequisito;
use App\Constants\SecurityAction;

class CursoRequisitoController extends BaseController
{
    /**
     * Muestra el formulario para crear requisitos para un curso nuevo
     */
    public function create($id_curso)
    {
        $curso = Curso::findOrFail($id_curso);
        
        // Validar permisos
        if (!hasPermissionRoute('taller.cursos.index', SecurityAction::CREAR_CURSO)) {
            abort(403, 'No tienes permiso para planificar cursos.');
        }

        return view('taller::a.CursoRequisitosCrear', compact('curso'))
            ->with('esWizard', false);
    }

    /**
     * Almacena los requisitos del curso y finaliza la creación
     */
    public function store(Request $request, $id_curso)
    {
        $curso = Curso::findOrFail($id_curso);

        // Validar permisos
        if (!hasPermissionRoute('taller.cursos.index', SecurityAction::CREAR_CURSO)) {
            abort(403, 'No tienes permiso para planificar cursos.');
        }

        $validados = $request->validate([
            'requisitos' => 'nullable|array',
            'requisitos.*.tipo' => 'required|in:pregunta,recurso,documento',
            'requisitos.*.titulo' => 'required|string|max:255',
            'requisitos.*.descripcion' => 'nullable|string|max:1000',
            'requisitos.*.obligatorio' => 'nullable|boolean',
        ]);

        foreach ($validados['requisitos'] ?? [] as $req) {
            CursoRequisito::create([
                'id_curso' => $curso->id_curso,
                'tipo' => $req['tipo'],
                'titulo' => $req['titulo'],
                'descripcion' => $req['descripcion'] ?? null,
                'obligatorio' => isset($req['obligatorio']) ? (bool) $req['obligatorio'] : true,
            ]);
        }

        return redirect()->route('taller.cursos.index')
            ->with('success', 'Curso "' . $curso->nombre . '" y sus requisitos configurados exitosamente.');
    }

    /**
     * Paso 3: Muestra el formulario de requisitos del wizard (sin curso en BD aún).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function wizardCreate()
    {
        if (!hasPermissionRoute('taller.cursos.index', SecurityAction::CREAR_CURSO)) {
            abort(403, 'No tienes permiso para planificar cursos.');
        }

        $wizard = session('wizard_curso');
        if (!$wizard || empty($wizard['token'])) {
            return redirect()->route('taller.cursos.create')
                ->withErrors(['error' => 'El proceso de creación expiró. Comienza nuevamente desde el Paso 1.']);
        }

        $curso = (object) [
            'nombre' => $wizard['datos']['nombre'] ?? 'Curso',
        ];

        $borrador = app(\Modules\Taller\Services\BorradorWizardService::class)
            ->porToken(\Illuminate\Support\Facades\Auth::id(), (string) ($wizard['token'] ?? ''));
        $payload = $borrador && is_array($borrador->payload) ? $borrador->payload : [];

        return view('taller::a.CursoRequisitosCrear', compact('curso'))
            ->with('esWizard', true)
            ->with('pasoActual', 5)
            ->with('requisitosPrevios', $payload['requisitos'] ?? [])
            ->with('completados', array_keys(array_filter([
                1 => ! empty($payload['paso1'] ?? null),
                2 => ! empty($payload['paso2'] ?? null),
                3 => ! empty($payload['contenidos'] ?? null),
            ])));
    }

    /**
     * Paso 3: Acumula los requisitos en sesión y finaliza la creación del curso.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function wizardStore(Request $request)
    {
        if (!hasPermissionRoute('taller.cursos.index', SecurityAction::CREAR_CURSO)) {
            abort(403, 'No tienes permiso para planificar cursos.');
        }

        $wizard = session('wizard_curso');
        if (!$wizard || empty($wizard['token'])) {
            return redirect()->route('taller.cursos.create')
                ->withErrors(['error' => 'El proceso de creación expiró. Comienza nuevamente desde el Paso 1.']);
        }

        $validados = $request->validate(
            [
                'requisitos' => 'nullable|array',
                'requisitos.*.tipo' => 'required|in:pregunta,recurso,documento',
                'requisitos.*.titulo' => 'required|string|max:255',
                'requisitos.*.descripcion' => 'nullable|string|max:1000',
                'requisitos.*.obligatorio' => 'nullable|boolean',
            ],
            [
                'requisitos.*.tipo.in' => 'El tipo de requisito debe ser pregunta, recurso o documento.',
                'requisitos.*.titulo.required' => 'Cada requisito debe tener un título.',
            ]
        );

        $requisitos = [];
        foreach ($validados['requisitos'] ?? [] as $req) {
            $requisitos[] = [
                'tipo' => $req['tipo'],
                'titulo' => $req['titulo'],
                'descripcion' => $req['descripcion'] ?? null,
                'obligatorio' => isset($req['obligatorio']) ? (bool) $req['obligatorio'] : true,
            ];
        }

        app(\Modules\Taller\Services\BorradorWizardService::class)
            ->guardarSlice(\Illuminate\Support\Facades\Auth::id(), 'requisitos', $requisitos, 5);

        // Paso 5a guardado: avanza a la revisión pre-flight (el curso se
        // crea solo en finalizar(), nunca aquí).
        return redirect()->route('taller.cursos.wizard.revision')
            ->with('success', 'Requisitos guardados. Revisa el resumen antes de crear el curso.');
    }

    /**
     * Muestra el formulario para editar los requisitos de un curso existente
     */
    public function edit($id_curso)
    {
        $curso = Curso::with('requisitos')->findOrFail($id_curso);
        
        // Validar permisos
        if (!hasPermissionRoute('taller.cursos.index', SecurityAction::EDITAR_CURSO) && !hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO)) {
            abort(403, 'No tienes permiso para editar este curso.');
        }

        return view('taller::a.CursoRequisitosEditar', compact('curso'));
    }

    /**
     * Actualiza los requisitos del curso
     */
    public function update(Request $request, $id_curso)
    {
        $curso = Curso::findOrFail($id_curso);

        // Validar permisos
        if (!hasPermissionRoute('taller.cursos.index', SecurityAction::EDITAR_CURSO) && !hasPermissionRoute('taller.cursos.index', SecurityAction::GESTIONAR_CURSO)) {
            abort(403, 'No tienes permiso para editar este curso.');
        }

        // Eliminar requisitos existentes
        CursoRequisito::where('id_curso', $curso->id_curso)->delete();

        // Crear los nuevos
        if ($request->has('requisitos') && is_array($request->requisitos)) {
            foreach ($request->requisitos as $req) {
                CursoRequisito::create([
                    'id_curso' => $curso->id_curso,
                    'tipo' => $req['tipo'], 
                    'titulo' => $req['titulo'],
                    'descripcion' => $req['descripcion'] ?? null,
                    'obligatorio' => isset($req['obligatorio']) ? (bool) $req['obligatorio'] : true,
                ]);
            }
        }

        return redirect()->route('taller.cursos.show', $curso->id_curso)
            ->with('success', 'Requisitos del curso actualizados correctamente.');
    }
}
