@extends('layouts.kaiadmin-menu')

@section('content')

    {{-- Alertas --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="card-head-row">
                    <h4 class="card-title">
                        <i class="fas fa-magic me-2 text-primary"></i>{{__('New Profile')}}
                    </h4>
                    <div class="card-tools">
                        <a href="{{ route('profiles') }}" class="btn btn-icon btn-link btn-primary btn-xs">
                            <span class="fa fa-undo"></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                {{
                    html()->form("post", route('profiles.create'))
                            ->autocomplete("off")
                            ->id("frm1")
                            ->open()
                }}
                <div class="row">
                    <div class="col-12 offset-md-2 col-md-8 offset-lg-3 col-lg-6">
                        <div class="form-floating form-floating-custom mb-2">
                            {{
                                html()->input("text", 'name', old('name'))
                                        ->class('form-control')
                                        ->placeholder(__(''))
                                        ->maxlength(50)
                                        ->required(true)
                                        ->autofocus(true)
                            }}
                            <label for="name">{{__('Name')}} <span class="text-danger">*</span></label>
                        </div>
                        @error('name')
                            <label id="name-error" class="error" for="name">{{__('This field is required')}}</label>
                        @enderror

                        <div class="form-floating form-floating-custom mb-2">
                            {{
                                html()->textarea('description', old('description'))
                                        ->class('form-control')
                                        ->rows(4)
                                        ->maxlength(250)
                                        ->required(true)
                            }}
                            <label for="description">{{__('Description')}} <span class="text-danger">*</span></label>
                        </div>
                        @error('description')
                            <label id="description-error" class="error" for="description">{{__('This field is required')}}</label>
                        @enderror

                        <div class="col-12 offset-md-4 col-md-4 offset-lg-4 col-lg-4 text-center mt-4">
                            <button id="save" type="button" class="btn btn-large btn-primary">
                                <i class="fa fa-save"></i>
                                {{__('Save')}}
                            </button>
                        </div>
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
                } else {
                    showNotify('danger', "{{__('Missing Required Fields')}}");
                }
            });
        });
    </script>
@endsection