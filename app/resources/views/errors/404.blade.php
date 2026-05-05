@extends('layouts.kaiadmin-login')
@section('content')

<div class="container d-flex justify-content-center align-items-center">
    <div class="page-inner">
        <div class="d-flex flex-column align-items-center">
            <img src="{{url('img/lost.png')}}" width="250" alt="Page Not Found">
            <h2 class="h1 mt-4 mb-4 fw-bold">{{__('Sorry! page not found')}}</h2>
            
            <div>
                
                <a href="{{route('main')}}" class="btn btn-success">{{__('GO TO HOME PAGE')}}</a>
            </div>
        </div>
    </div>
</div>


@endsection
