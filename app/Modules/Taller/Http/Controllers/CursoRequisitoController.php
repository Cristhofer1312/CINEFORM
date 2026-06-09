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

        return view('taller::a.CursoRequisitosCrear', compact('curso'));
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

        if ($request->has('requisitos') && is_array($request->requisitos)) {
            foreach ($request->requisitos as $req) {
                CursoRequisito::create([
                    'id_curso' => $curso->id_curso,
                    'tipo' => $req['tipo'], // 'pregunta', 'recurso', 'documento'
                    'titulo' => $req['titulo'],
                    'descripcion' => $req['descripcion'] ?? null,
                    'obligatorio' => isset($req['obligatorio']) ? (bool) $req['obligatorio'] : true,
                ]);
            }
        }

        return redirect()->route('taller.cursos.index')
            ->with('success', 'Curso "' . $curso->nombre . '" y sus requisitos configurados exitosamente.');
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
