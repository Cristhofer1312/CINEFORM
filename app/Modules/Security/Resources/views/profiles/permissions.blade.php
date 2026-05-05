@extends('layouts.kaiadmin-menu')

@section('content')

<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <div class="card-head-row">
                <h4 class="card-title">{{__('Profiles')}}</h4>
                <div class="card-tools">
                    <a href="{{route('profiles')}}" class="btn btn-icon btn-link btn-primary btn-xs">
                        <span class="fa fa-undo"></span>
                    </a>
                    <a href="javascript:void(0)" onClick="closeCard(this)" class="btn btn-icon btn-link btn-primary btn-xs">
                        <span class="fa fa-times"></span>
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            {{
                html()->form("post", route('profiles.permissions', $id))
                        ->autocomplete("off")
                        ->id("frm1")
                        ->open()
            }}
            <div class="row">
                <div class="col-12 col-md-4 ">
                    <div class="form-floating form-floating-custom mb-2">
                        {{
                                html()->input("text", 'name', $profile->name)
                                        ->class('form-control ')
                                        ->placeholder('')
                                        
                                        ->disabled(true)
                        }}
                        <label for="name">{{__('Name')}}</label>
                    </div>

                </div>
                <div class="col-12 col-md-4 ">
                    <div class="form-floating form-floating-custom mb-2">
                        {{
                                html()->input("text", 'description', $profile->description)
                                        ->class('form-control ')
                                        
                                        ->placeholder('')
                                        ->disabled(true)
                        }}
                        <label for="description">{{__('Description')}}</label>
                    </div>
                </div>
                <div class="col-12 col-md-4 ">

                </div>

                <div class="col-5 col-md-1">

                    <div class="nav flex-column nav-pills nav-secondary nav-pills-no-bd nav-pills-icons" role="tablist" aria-orientation="vertical">
                        @foreach($MODULES as $key=>$value)
                        <a class="nav-link {{$key==0 ? 'active':''}}"  data-bs-toggle="pill" href="#v-{{$value->crypt_id}}" role="tab" aria-controls="v-pills-profile-icons" aria-selected="false" tabindex="-1">
                            <i class="fa fa-{{$value->icon}}"></i>
                            {{$value->name}}
                        </a>
                        @endforeach
                    </div>


                </div>

                <div class="col-7 col-md-11">
                    <div class="tab-content" >
                        @foreach($MODULES as $key=>$value)
                        <div class="tab-pane  {{$key==0 ? 'active':'fade'}}" id="v-{{$value->crypt_id}}" role="tabpanel" aria-labelledby="v-pills-home-tab-icons">
                            <div class="row mt-3">
                                <div class="col-12 col-md-12 text-center">
                                    <h1>{{$value->name}}</h1>

                                </div>
                                <div class="col-12 col-md-12 ">


                                    <ul class="nav nav-pills nav-secondary nav-pills-no-bd" id="pills-tab" role="tablist">
                                        @foreach($value->getMenus as $key2=>$value2)
                                        <li class="nav-item" role="presentation">
                                            {{-- Fix #7: ID combinado módulo+menú para evitar colisiones --}}
                                            <a class="nav-link {{$key2==0? 'active':''}}" id="pills-tab-{{$value->crypt_id}}-{{$value2->crypt_id}}" data-bs-toggle="pill" href="#pills-{{$value->crypt_id}}-{{$value2->crypt_id}}" role="tab" aria-controls="pills-{{$value->crypt_id}}-{{$value2->crypt_id}}" aria-selected="true">
                                                {{$value2->name}}
                                            </a>
                                        </li>
                                        @endforeach
                                    </ul>


                                    <div class="tab-content mt-2 mb-3" id="pills-tabContent">
                                        @foreach($value->getMenus as $key2=>$value2)
                                        {{-- Fix #7: ID combinado módulo+menú para que coincida con el tab link --}}
                                        <div class="tab-pane {{$key2==0? 'active':'fade'}}" id="pills-{{$value->crypt_id}}-{{$value2->crypt_id}}" role="tabpanel" aria-labelledby="pills-tab-{{$value->crypt_id}}-{{$value2->crypt_id}}">
                                            <div class="list-group list-group-flush">
                                                @foreach($value2->getProcess as $key3=>$value3)
                                                    @php
                                                    $check ='';
                                                    $actions = [];
                                                    foreach($profile->permissions as $key4=>$value4){
                                                        if ($value4->process_id == $value3->id){
                                                            $check ='checked="checked"';
                                                            $actions[] = $value4->slug;
                                                        }
                                                    }
                                                    @endphp
                                                    
                                                    <div class="list-group-item d-block px-1 py-4 border-0 border-bottom mb-1" style="background: transparent;">
                                                        
                                                        <!-- Encabezado de Lista: Solo título y el icono -->
                                                        <div class="d-flex align-items-center mb-3">
                                                            <div class="d-flex align-items-center">
                                                                <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.25rem; letter-spacing: -0.01em;">
                                                                    <i class="fas fa-layer-group text-primary me-2 opacity-75"></i>{{$value3->name}}
                                                                </h5>
                                                            </div>
                                                        </div>

                                                        <!-- Detalle de Lista: Sub-permisos -->
                                                        <div class="ps-0 ps-md-4 ms-md-4">
                                                            {!!showActions($value3->actions, $value3->crypt_id, $actions)!!}
                                                        </div>

                                                    </div>
                                                @endforeach
                                            </div>


                                        </div>

                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-12 col-md-12 text-center">

                    <button id="save" type="button" class="btn btn-large btn-info">
                        <i class="fa fa-save"></i>
                        {{__('Save')}}
                    </button>

                </div>
            </div>
            {{ html()->form()->close() }}
        </div>
    </div>
</div>


<script type="text/javascript">

    document.addEventListener("DOMContentLoaded", function (event) {

        $("#frm1").validate({
            errorPlacement: function (error, element) {
                element.parents('.form-floating').after(error);
            }
        });

        $('#save').on('click', function () {
            if ($("#frm1").valid() == true) {
                showLoading({"icon": "info", "title": "{{__('Processing...')}}", "html": '<i class="fas fa-spinner fa-spin"></i>'});
                $("#frm1").submit();
            }
        });


    });

</script>
@endsection
