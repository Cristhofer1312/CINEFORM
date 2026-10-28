<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Taller\Entities\Curso;

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

        $user = $this->getUsuarioAutenticado();
        $persona = $user->personalData;
        // Sin perfil activo: quien puede facilitar ve sus cursos asignados y,
        // con ?vista=inscritos, aquellos en los que participa.
        $puedeFacilitar = app(\App\Services\Security\Autorizacion::class)
            ->puede(\App\Services\Security\Autorizacion::PROCESO_CURSOS, \App\Constants\SecurityAction::EDITAR_CURSO, $user);
        $esFacilitador = $puedeFacilitar && $request->query('vista') !== 'inscritos';

        if ($esFacilitador) {
            // Cursos asignados donde es el facilitador responsable
            $query = \Modules\Taller\Entities\Curso::with(['modalidad', 'estados', 'inscripciones'])
                ->withCount('contenidos')
                ->where('id_persona', $persona->id_persona);

            $estados = \Modules\Taller\Entities\Estado::whereIn('id_estado', [1, 3, 4, 5, 6, 7, 8, 9])->get();
        } else {
            // Cursos donde el usuario está formalmente inscrito como estudiante/participante
            $query = \Modules\Taller\Entities\Curso::with(['modalidad', 'estados', 'inscripciones'])
                ->withCount('contenidos')
                ->withExists(['resenasSatisfaccion as ya_respondio' => function ($q) use ($persona) {
                    $q->where('taller.resenas_satisfaccion.id_persona', $persona->id_persona);
                }])
                ->selectSub(
                    \Modules\Taller\Entities\Inscripcion::select('certificado_aprobado')
                        ->whereColumn('taller.inscripciones.id_curso', 'taller.cursos.id_curso')
                        ->where('taller.inscripciones.id_persona', $persona->id_persona)
                        ->where('taller.inscripciones.estado', \Modules\Taller\Entities\Inscripcion::ESTADO_APROBADO)
                        ->limit(1),
                    'certificado_aprobado'
                )
                ->whereHas('inscripciones', function ($q) use ($persona) {
                    $q->where('id_persona', $persona->id_persona)
                        ->where('estado', \Modules\Taller\Entities\Inscripcion::ESTADO_APROBADO);
                });

            // Solo los estados que el usuario puede tener como inscrito (activos y cerrados)
            $estados = \Modules\Taller\Entities\Estado::whereIn('id_estado', [6, 7, 8, 9])->get();
        }

        // Filtro por nombre o descripción
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%'.$request->search.'%')
                    ->orWhere('descripcion', 'like', '%'.$request->search.'%');
            });
        }

        // Filtro por estado (usa la tabla pivote curso_estado para determinar el estado actual)
        if ($request->filled('id_estado')) {
            $estado = \Modules\Taller\Entities\Estado::find($request->id_estado);

            if ($estado) {
                $query->where(
                    DB::raw('(SELECT ce.id_estado FROM taller.curso_estado ce WHERE ce.id_curso = "taller"."cursos"."id_curso" ORDER BY ce.created_at DESC, ce.id DESC LIMIT 1)'),
                    $estado->id_estado
                );
            }
        }

        $cursosInscritos = $query->orderBy('fecha_inicio', 'desc')->paginate(12)->withQueryString();

        // Procesar para agregar propiedades de badge/etiqueta
        $this->procesarCursosParaVista($cursosInscritos);

        return view('taller::a.CursosInscritos', compact('persona', 'cursosInscritos', 'estados', 'esFacilitador', 'puedeFacilitar'));
    }

    /**
     * Procesa cada curso para agregar propiedades calculadas necesarias en la vista
     *
     * @param  \Illuminate\Support\Collection  $cursos
     * @return void
     */
    private function procesarCursosParaVista($cursos)
    {
        foreach ($cursos as $curso) {
            $estadoActual = $curso->estado_actual;
            $estadoId = $estadoActual ? (int) $estadoActual->id_estado : 0;
            $estadoEnum = \App\Enums\EstadoCurso::tryFrom($estadoId);

            $curso->estadoNombre = $estadoEnum ? $estadoEnum->label() : ($estadoActual ? str_replace('_', ' ', $estadoActual->nombre) : 'Sin estado');

            $cursor_modalidad = $curso->modalidad->nombre_modalidad ?? 'No especificada';
            $curso->modalidad = $cursor_modalidad;
            $curso->modalidadNombre = $cursor_modalidad;
            $curso->modalidadIcon = str_contains(strtolower($cursor_modalidad), 'presencial') ? 'fa-building' : 'fa-laptop';

            $curso->badgeClass = $estadoEnum ? 'bg-'.$estadoEnum->color() : 'bg-secondary';
        }
    }
}
