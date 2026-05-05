@extends('layouts.kaiadmin-menu')

@section('content')
@auth
    @php
        // Inicializar el servicio que resuelve qué vista mostrar
        $condicional = new \Modules\Taller\Services\CondicionalEditarCurso();

        // Resolver la vista parcial apropiada según estado y rol
        $vistaParcial = $condicional->resolverVista(
            $curso->estado_actual->id_estado,
            $esCoordinador,
            $esFacilitador
        );
    @endphp

    {{-- Incluir la vista parcial correspondiente si existe --}}
    @if($vistaParcial)
        @include("taller::a.$vistaParcial", [
            'curso' => $curso,
        ])
    @endif
@endauth

@endsection
