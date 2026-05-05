<?php

namespace Modules\Taller\Http\Controllers;
use Modules\Comun\Http\Controllers\PersonalDataController;
use Illuminate\Http\Request;
use Modules\Taller\Entities\Inscripcion;
use Modules\Taller\Entities\Curso;
use Illuminate\Support\Facades\Log;

class CursoInscritoController extends BaseController
{
    /**
     * Muestra los cursos en los que el usuario está inscrito
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        if ($this->usuarioSinDatosPersonales()) {
            return redirect()->back()->with('error', 'No se encontraron datos personales.');
        }

        $persona = $this->getUsuarioAutenticado()->personalData;

        // Query base: cursos donde el usuario está inscrito
        $query = \Modules\Taller\Entities\Curso::with(['modalidad', 'estados', 'inscripciones'])
            ->withCount('contenidos')
            ->whereHas('inscripciones', function ($q) use ($persona) {
                $q->where('id_persona', $persona->id_persona);
            });

        // Filtro por nombre o descripción
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%')
                  ->orWhere('descripcion', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por estado
        if ($request->filled('id_estado')) {
            $query->whereHas('estados', function ($q) use ($request) {
                $q->where('taller.estados_curso.id_estado', $request->id_estado);
            });
        }

        $cursosInscritos = $query->orderBy('fecha_inicio', 'desc')->paginate(12)->withQueryString();

        // Procesar para agregar propiedades de badge/etiqueta
        $this->procesarCursosParaVista($cursosInscritos);

        // Solo los estados que el usuario puede tener como inscrito (activos y cerrados)
        $estados = \Modules\Taller\Entities\Estado::whereIn('id_estado', [6, 7, 8, 9])->get();

        return view('taller::a.CursosInscritos', compact('persona', 'cursosInscritos', 'estados'));
    }

    /**
     * Procesa cada curso para agregar propiedades calculadas necesarias en la vista
     *
     * @param \Illuminate\Support\Collection $cursos
     * @return void
     */
    private function procesarCursosParaVista($cursos)
    {
        foreach ($cursos as $curso) {
            $estadoActual = $curso->estado_actual;
            $estadoId = $estadoActual ? $estadoActual->id_estado : 0;

            // Usar los mismos alias que usa CursoController para compatibilidad
            $curso->estadoNombre = match ($estadoId) {
                6  => 'Inscripciones abiertas',
                7  => 'En progreso',
                8  => 'Finalizado',
                9  => 'Cerrado',
                default => $estadoActual ? str_replace('_', ' ', $estadoActual->nombre) : 'Sin estado',
            };

            $cursor_modalidad = $curso->modalidad->nombre_modalidad ?? 'No especificada';
            $curso->modalidad      = $cursor_modalidad;
            $curso->modalidadNombre = $cursor_modalidad;
            $curso->modalidadIcon  = str_contains(strtolower($cursor_modalidad), 'presencial') ? 'fa-building' : 'fa-laptop';

            $curso->badgeClass = match ($estadoId) {
                6, 7   => 'bg-success',
                8, 9   => 'bg-danger',
                default => 'bg-secondary',
            };
        }
    }
}
