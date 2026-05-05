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
                html()->form("post", route('profiles.update', $id))
                        ->autocomplete("off")
                        ->id("frm1")
                        ->open()
            }}
            <div class="row">
                <div class="col-7 offset-md-4 col-md-4 offset-lg-4  col-lg-4">

                    <div class="form-floating form-floating-custom mb-2">
                        {{
                                html()->input("text", 'name', $profile->name)
                                        ->class('form-control ')
                                        ->maxlength(50)
                                        ->placeholder('')
                                        ->autofocus('true')
                                        ->required(true)
                        }}
                        <label for="name">{{__('Name')}}</label>
                    </div>
                    @error('name')
                    <label id="name-error" class="error" for="name">{{__('This field is required')}}</label>
                    @enderror
                    <div class="form-floating form-floating-custom mb-2">
                        {{
                                html()->input("text", 'description', $profile->description)
                                        ->class('form-control ')
                                        ->maxlength(250)
                                        ->placeholder('')
                                        ->required(true)
                        }}
                        <label for="description">{{__('Description')}}</label>
                    </div>
                    @error('description')
                    <label id="description-error" class="error" for="description">{{__('This field is required')}}</label>
                    @enderror
                    
                    <div class="form-floating form-floating-custom mb-2">
                        {{html()->select("active", ["1"=>__('Yes'), "0"=>__('Not')], $profile->active )
                                                        ->class('form-select')
                                                        ->placeholder(__("Select"))
                                                        ->required(true)
                        }}
                        <label for="active">{{__('Active')}}</label>
                    </div>
                    @error('type_document')
                    <label id="active-error" class="error" for="active">{{__('This field is required')}}</label>
                    @enderror

                </div>
                <div class="col-7 offset-md-4 col-md-4 offset-lg-4  col-lg-4 text-center">

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
        
        $('#save').on('click', function (){
            if ($("#frm1").valid()==true){
                 showLoading({"icon":"info", "title":"{{__('Processing...')}}", "html":'<i class="fas fa-spinner fa-spin"></i>' });
                $("#frm1").submit();
            }
        });




    });

</script>
@endsection
