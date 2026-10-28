@foreach($caps as $cap)
    @include('taller::a.partials.curso-actions.boton-gestion', ['cap' => $cap, 'curso' => $curso])
@endforeach
