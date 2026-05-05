@extends('layouts.kaiadmin-menu')

@section('content')

<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <div class="card-head-row">
                <h4 class="card-title">{{__('Documents')}}</h4>
                <div class="card-tools">
                    <button onClick="closeCard(this)" class="btn btn-icon btn-link btn-primary btn-xs">
                        <span class="fa fa-times"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            {{
                html()->form("post", route('security.admin'))
                        ->autocomplete("off")
                        ->id("frm1")
                        ->attribute("enctype","multipart/form-data")
                        ->open()
            }}
            <div class="row">
                <div class="col-12 offset-md-2 col-md-4 offset-lg-3  col-lg-3 ">
                    los archivos a subir deben de tener una clasificacion:
                    ANEXOS
                    REGULACIONES
                    DOCUMENTOS
                    PANS
                    MANUALES
                    CIRCULARES
                    NORMAS COMPLEMENTARIAS
                    PROCEDIMIENTOS
                    
                    
                    
                    ----------------------------
                    ANS
                    AGA AERODROMO
                    OPERACIONES
                    AERONAVEGABILIDAD
                    PEL (LICENCIA DE PERSONAL)
                    
                    
                    <div class="form-floating form-floating-custom mb-2">
                        {{
                            html()->input("text", 'name', '' )
                                    ->class('form-control')
                                    ->placeholder(__(''))
                                    ->maxlength(20)
                                    ->required(true)
                                    ->autofocus(true)
                        }}
                        <label for="name">{{__('Name')}}</label>
                    </div>
                    @error('document')
                    <label id="name-error" class="error" for="name">{{__('This field is required')}}</label>
                    @enderror
                    <div class="form-floating form-floating-custom mb-2">
                        {{
                            html()->input("text", 'description', '' )
                                    ->class('form-control')
                                    ->placeholder(__(''))
                                    ->maxlength(20)
                                    ->required(true)
                                    
                        }}
                        <label for="description">{{__('Description')}}</label>
                    </div>
                    @error('document')
                    <label id="description-error" class="error" for="description">{{__('This field is required')}}</label>
                    @enderror
                    <div class="form-floating form-floating-custom mb-2">
                        {{
                            html()->file( 'attach')
                                    ->class('form-control')
                                   
                                    ->accept('application/pdf')
                                    ->required(true)
                                    
                        }}
                        <label for="attach">{{__('File')}}</label>
                    </div>
                    @error('document')
                    <label id="attach-error" class="error" for="attach">{{__('This field is required')}}</label>
                    @enderror

                    <div class="col-12 offset-md-4 col-md-4 offset-lg-4  col-lg-4 text-center mt-4">

                        <button id="save" type="button" class="btn btn-large btn-info">
                            <i class="fa fa-save"></i>
                            {{__('Save')}}
                        </button>

                    </div>
                </div>
            </div>
            {{ html()->form()->close() }}


            <div class="row">
                <div class="col-7 offset-md-1 col-md-10 offset-lg-1  col-lg-10">
                    <table id="table1" class="table table-bordered table-head-bg-info table-bordered-bd-info ">
                        <thead>
                            <tr>
                                <th>{{__('ID')}}</th>
                                <th>{{__('Document')}}</th>
                                <th>{{__('Actions')}}</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach($newFile as $key =>$value) 
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$value->name}}</td>
                                <td>
                                    {{
                                    html()->form("put", route('security.admin', $value->id))->open()
                                    }}
                                    <button  type="submit" class="btn btn-success">
                                        <i class="fa fa-file"></i>
                                        {{__('Ver')}}
                                    </button>
                                    {{ html()->form()->close() }}

                                </td>


                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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

            } else {
                showNotify('danger', "{{__('Missing Required Fields')}}");
            }
        });









    });

</script>
@endsection
