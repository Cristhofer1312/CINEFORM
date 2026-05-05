@extends('layouts.kaiadmin-menu')

@section('content')
    @auth

        {{-- Incluir la vista parcial correspondiente si existe --}}
        @if($vistaParcial)
            @include("taller::a.$vistaParcial", [
                'cursos' => $cursos,
                'estados' => $estados,
            ])
        @endif
    @endauth    

@endsection
