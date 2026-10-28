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
        
        {{-- Inyectar Scripts y Modales de Edición --}}
        @include('taller::a.partials.editar-actions.Editar-Coordinador-JS', [
            'tiposEvaluacionJson' => json_encode($tiposEvaluacion),
            'facilitadores' => $facilitadores,
            'especializaciones' => $especializaciones,
            'curso' => $curso
        ])
    @endif
@endauth

@endsection

@push('styles')
<style>
    
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: var(--cf-muted); }
</style>
@endpush
