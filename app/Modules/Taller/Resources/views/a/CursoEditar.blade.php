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
    .bg-primary-soft { background-color: rgba(30, 58, 138, 0.1); }
    .border-light-2 { border: 1.5px solid #d1d5db !important; }
    .shadow-xs { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
    
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #999; }
</style>
@endpush
