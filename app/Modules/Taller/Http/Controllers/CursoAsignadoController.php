<?php

namespace Modules\Taller\Http\Controllers;

use Modules\Taller\Http\Controllers\BaseController;
use Modules\Taller\Entities\Curso;

class CursoAsignadoController extends BaseController
{
    /**
     * Muestra los cursos asignados al facilitador (donde es responsable)
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = $this->getUsuarioAutenticado();

        if ($this->usuarioSinDatosPersonales()) {
            return view('taller::a.CursosAsignados', ['cursos' => collect()]);
        }

        // Cargamos los cursos con sus relaciones
        $cursos = Curso::with([
            'modalidad',
            'estados' => function ($query) {
                $query->orderBy('taller.curso_estado.created_at', 'desc')
                    ->withPivot('motivo');
            }
        ])
            ->where('id_persona', $user->personalData->id_persona)
            ->withCount(['contenidos as total_contenidos', 'inscripciones'])
            ->orderBy('fecha_inicio', 'desc')
            ->paginate(10);

        // Agregamos el estado actual a cada curso
        $cursos->getCollection()->each(function ($curso) {
            $curso->estado_actual = $curso->estados->first();
            // También podemos agregar el ID del estado directamente al modelo para facilitar el acceso
            if ($curso->estado_actual) {
                $curso->estado_id = $curso->estado_actual->id_estado;
            }
        });

        return view('taller::a.CursosAsignados', compact('cursos'));
    }
}
