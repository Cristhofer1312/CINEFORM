<?php

namespace Modules\Taller\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    protected function getUsuarioAutenticado()
    {
        return Auth::user()->load('personalData');
    }

    protected function usuarioSinDatosPersonales()
    {
        $usuario = $this->getUsuarioAutenticado();
        return !$usuario->personalData;
    }

    public function prueba()
    {
        $user = $this->getUsuarioAutenticado();
        return view('taller::a.prueba', compact('user'));
    }
}
