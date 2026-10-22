@props(['actual' => 1, 'completados' => []])

@php
$pasos = [
    1 => ['titulo' => 'Identidad', 'ruta' => 'taller.cursos.wizard.paso1.create', 'icono' => 'fas fa-tag'],
    2 => ['titulo' => 'Logística', 'ruta' => 'taller.cursos.wizard.paso2.create', 'icono' => 'fas fa-calendar-alt'],
    3 => ['titulo' => 'Temario', 'ruta' => 'taller.cursos.wizard.paso3.create', 'icono' => 'fas fa-list-ul'],
    4 => ['titulo' => 'Certificado', 'ruta' => 'taller.cursos.wizard.plantilla.create', 'icono' => 'fas fa-award'],
    5 => ['titulo' => 'Revisión', 'ruta' => 'taller.cursos.wizard.revision', 'icono' => 'fas fa-check-circle'],
];
$completados = is_array($completados) ? $completados : [];
@endphp

<div class="card border-0 shadow-card rounded-4 mb-4 overflow-hidden">
    <div class="card-body p-3 p-md-4">
        <ol class="d-flex align-items-start justify-content-between list-unstyled mb-0 wizard-steps" style="gap:.5rem;">
            @foreach($pasos as $numero => $paso)
                @php
                    $esActual = ((int) $actual) === $numero;
                    $estaCompleto = in_array($numero, $completados, true);
                    $puedeNavegar = $estaCompleto || $numero < ((int) $actual);
                    $esClicable = $puedeNavegar && ! $esActual;
                @endphp
                <li class="flex-fill text-center wizard-step {{ $esActual ? 'active' : '' }} {{ $estaCompleto ? 'done' : '' }}">
                    @if($esClicable)
                        <a href="{{ route($paso['ruta']) }}" class="text-decoration-none d-block" title="Volver al paso {{ $numero }}: {{ $paso['titulo'] }}">
                    @else
                        <span class="d-block">
                    @endif
                        <span class="wizard-dot mx-auto d-flex align-items-center justify-content-center rounded-circle fw-bold {{ $esActual ? 'bg-primary text-white shadow' : ($estaCompleto ? 'bg-success text-white' : 'bg-light text-muted border') }}" style="width:38px;height:38px;">
                            @if($estaCompleto && ! $esActual)
                                <i class="fas fa-check"></i>
                            @else
                                {{ $numero }}
                            @endif
                        </span>
                        <span class="d-block mt-2 small fw-bold {{ $esActual ? 'text-primary' : ($estaCompleto ? 'text-success' : 'text-muted') }}">
                            <i class="{{ $paso['icono'] }} me-1"></i>{{ $paso['titulo'] }}
                        </span>
                    @if($esClicable)
                        </a>
                    @else
                        </span>
                    @endif
                </li>
                @if(! $loop->last)
                    <li class="flex-fill wizard-sep align-self-center" aria-hidden="true"><hr class="my-0"></li>
                @endif
            @endforeach
        </ol>
    </div>
</div>
