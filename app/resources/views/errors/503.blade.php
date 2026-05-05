@extends('layouts.kaiadmin-login')
@section('content')

<div class="container d-flex justify-content-center align-items-center">
    <div class="page-inner">
        <div class="d-flex flex-column align-items-center">
            <h1 class="mt-4 mb-4 fw-bold">{{__('Sorry, the page is under maintenance')}}</h1>
            <img src="{{url('img/mant.png')}}" class="img-fluid" alt="Sorry, the page is under maintenance">
            
            
            
        </div>
    </div>
</div>


@endsection