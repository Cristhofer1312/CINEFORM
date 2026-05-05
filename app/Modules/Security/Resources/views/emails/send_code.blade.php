@extends('layouts.email')

@section('content')

<div class="card card-pricing">
    <div class="card-header">
        <h4 class="card-title">CNAC</h4>
        <div class="card-price">
            <span class="price">{{$code}}</span>
            
        </div>
    </div>
    <div class="card-body">
        <ul class="specification-list">
            <li>
                <span class="name-specification">Customizer</span>
                <span class="status-specification">14 days trial</span>
            </li>
            
        </ul>
    </div>
    <div class="card-footer">
        <button class="btn btn-primary w-100">
            <b>Get Started</b>
        </button>
    </div>
</div>


@endsection