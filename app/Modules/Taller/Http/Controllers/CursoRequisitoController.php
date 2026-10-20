<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Taller\Entities\Curso;
use Modules\Taller\Entities\CursoRequisito;
use Modules\Taller\Entities\ModalidadEspecial;
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
            ->with('esWizard', false)
            ->with('esParaMenores', $curso->esParaMenores());
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

        $requisitos = $validados['requisitos'] ?? [];
        $esParaMenores = $curso->esParaMenores();

        // La carta de permiso solo aplica a cursos para niños y adolescentes.
        // Si el curso no es para menores y llega en el formulario, se descarta.
        if (! $esParaMenores) {
            $requisitos = array_values(array_filter(
                $requisitos,
                fn ($req) => ! CursoRequisito::esCartaPermisoDocumento($req)
            ));
        }

        $yaTieneCartaPermiso = CursoRequisito::where('id_curso', $curso->id_curso)
            ->get()
            ->contains(fn ($req) => CursoRequisito::esCartaPermisoDocumento($req));

        if ($esParaMenores
            && ! $yaTieneCartaPermiso
            && ! collect($requisitos)->contains(fn ($req) => CursoRequisito::esCartaPermisoDocumento($req))) {
            $requisitos[] = CursoRequisito::requisitoCartaPermiso();
        }

        foreach ($requisitos as $req) {
            // Si la carta ya existe, no se duplica: solo se reafirma su
            // obligatoriedad cuando el curso es para menores.
            if ($yaTieneCartaPermiso && CursoRequisito::esCartaPermisoDocumento($req)) {
                continue;
            }

            $obligatorio = isset($req['obligatorio']) ? (bool) $req['obligatorio'] : true;

            // La carta de permiso no puede quedar como opcional en un curso
            // dirigido a menores.
            if ($esParaMenores && CursoRequisito::esCartaPermisoDocumento($req)) {
                $obligatorio = true;
            }

            CursoRequisito::create([
                'id_curso' => $curso->id_curso,
                'tipo' => $req['tipo'],
                'titulo' => $req['titulo'],
                'descripcion' => $req['descripcion'] ?? null,
                'obligatorio' => $obligatorio,
            ]);
        }

        if ($esParaMenores) {
            $carta = CursoRequisito::where('id_curso', $curso->id_curso)
                ->get()
                ->first(fn ($req) => CursoRequisito::esCartaPermisoDocumento($req));

            if ($carta && ! $carta->obligatorio) {
                $carta->update(['obligatorio' => true]);
            }
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

        $esParaMenores = ModalidadEspecial::esMenor(
            (int) ($payload['paso1']['id_modalidad_especial']
                ?? $wizard['datos']['id_modalidad_especial']
                ?? 0)
        );

        $requisitosPrevios = is_array($payload['requisitos'] ?? null) ? $payload['requisitos'] : [];
        if (! $esParaMenores) {
            $requisitosPrevios = array_values(array_filter(
                $requisitosPrevios,
                fn ($req) => ! CursoRequisito::esCartaPermisoDocumento($req)
            ));
        } else {
            if (! collect($requisitosPrevios)->contains(fn ($req) => CursoRequisito::esCartaPermisoDocumento($req))) {
                array_unshift($requisitosPrevios, CursoRequisito::requisitoCartaPermiso());
            }

            $requisitosPrevios = array_map(function ($req) {
                if (CursoRequisito::esCartaPermisoDocumento($req)) {
                    $req['obligatorio'] = true;
                }

                return $req;
            }, $requisitosPrevios);
        }

        return view('taller::a.CursoRequisitosCrear', compact('curso'))
            ->with('esWizard', true)
            ->with('esParaMenores', $esParaMenores)
            ->with('pasoActual', 5)
            ->with('requisitosPrevios', $requisitosPrevios)
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

        $borrador = app(\Modules\Taller\Services\BorradorWizardService::class)
            ->porToken(\Illuminate\Support\Facades\Auth::id(), (string) ($wizard['token'] ?? ''));
        $payload = $borrador && is_array($borrador->payload) ? $borrador->payload : [];
        $esParaMenores = ModalidadEspecial::esMenor(
            (int) ($payload['paso1']['id_modalidad_especial']
                ?? $wizard['datos']['id_modalidad_especial']
                ?? 0)
        );

        $requisitos = [];
        foreach ($validados['requisitos'] ?? [] as $req) {
            $obligatorio = isset($req['obligatorio']) ? (bool) $req['obligatorio'] : true;

            // La carta de permiso no puede quedar como opcional en un curso
            // dirigido a menores.
            if ($esParaMenores && CursoRequisito::esCartaPermisoDocumento($req)) {
                $obligatorio = true;
            }

            $requisitos[] = [
                'tipo' => $req['tipo'],
                'titulo' => $req['titulo'],
                'descripcion' => $req['descripcion'] ?? null,
                'obligatorio' => $obligatorio,
            ];
        }

        if (! $esParaMenores) {
            $requisitos = array_values(array_filter(
                $requisitos,
                fn ($req) => ! CursoRequisito::esCartaPermisoDocumento($req)
            ));
        } elseif (! collect($requisitos)->contains(fn ($req) => CursoRequisito::esCartaPermisoDocumento($req))) {
            $requisitos[] = CursoRequisito::requisitoCartaPermiso();
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

        return view('taller::a.CursoRequisitosEditar', compact('curso'))
            ->with('esParaMenores', $curso->esParaMenores());
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

        $validados = $request->validate([
            'requisitos' => 'nullable|array',
            'requisitos.*.tipo' => 'required|in:pregunta,recurso,documento',
            'requisitos.*.titulo' => 'required|string|max:255',
            'requisitos.*.descripcion' => 'nullable|string|max:1000',
            'requisitos.*.obligatorio' => 'nullable|boolean',
        ]);

        $esParaMenores = $curso->esParaMenores();
        $requisitos = $validados['requisitos'] ?? [];

        // Si el curso no es para menores, la carta de permiso no se exige.
        if (! $esParaMenores) {
            $requisitos = array_values(array_filter(
                $requisitos,
                fn ($req) => ! CursoRequisito::esCartaPermisoDocumento($req)
            ));
        }

        // Evidencia ya cargada: si la carta tiene respuestas, se conserva su
        // fila para no borrar los documentos en cascada. Si el curso dejó de
        // ser para menores, queda como no obligatoria.
        $cartaExistente = CursoRequisito::where('id_curso', $curso->id_curso)
            ->get()
            ->first(fn ($req) => CursoRequisito::esCartaPermisoDocumento($req));
        $cartaTieneRespuestas = $cartaExistente && $cartaExistente->respuestas()->exists();

        $consulta = CursoRequisito::where('id_curso', $curso->id_curso);
        if ($cartaTieneRespuestas) {
            $consulta->where('id_requisito', '!=', $cartaExistente->id_requisito);
        }
        $consulta->delete();

        // Crear los nuevos
        foreach ($requisitos as $req) {
            if ($cartaTieneRespuestas && CursoRequisito::esCartaPermisoDocumento($req)) {
                continue;
            }

            $obligatorio = isset($req['obligatorio']) ? (bool) $req['obligatorio'] : true;
            if ($esParaMenores && CursoRequisito::esCartaPermisoDocumento($req)) {
                $obligatorio = true;
            }

            CursoRequisito::create([
                'id_curso' => $curso->id_curso,
                'tipo' => $req['tipo'],
                'titulo' => $req['titulo'],
                'descripcion' => $req['descripcion'] ?? null,
                'obligatorio' => $obligatorio,
            ]);
        }

        if ($cartaTieneRespuestas) {
            $definicionCarta = CursoRequisito::requisitoCartaPermiso();
            $cartaExistente->update([
                'titulo' => $esParaMenores ? $definicionCarta['titulo'] : $cartaExistente->titulo,
                'descripcion' => $esParaMenores ? $definicionCarta['descripcion'] : $cartaExistente->descripcion,
                'obligatorio' => $esParaMenores,
            ]);
        }

        // La carta de permiso es obligatoria solo en cursos para menores:
        // se restituye si el formulario la omitió o si intentaron eliminarla,
        // y nunca queda como opcional.
        if ($esParaMenores) {
            $carta = CursoRequisito::where('id_curso', $curso->id_curso)
                ->get()
                ->first(fn ($req) => CursoRequisito::esCartaPermisoDocumento($req));

            if (! $carta) {
                CursoRequisito::create(array_merge(
                    ['id_curso' => $curso->id_curso],
                    CursoRequisito::requisitoCartaPermiso()
                ));
            } elseif (! $carta->obligatorio) {
                $carta->update(['obligatorio' => true]);
            }
        }

        return redirect()->route('taller.cursos.show', $curso->id_curso)
            ->with('success', 'Requisitos del curso actualizados correctamente.');
    }
}
