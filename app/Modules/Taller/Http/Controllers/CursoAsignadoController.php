<?php

namespace Modules\Taller\Http\Controllers;

class CursoAsignadoController extends BaseController
{
    /**
     * Muestra los cursos asignados al facilitador (donde es responsable)
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return redirect()->route('taller.mis-cursos');
    }
}
