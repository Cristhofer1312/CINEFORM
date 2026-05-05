@extends('layouts.kaiadmin-menu')

@section('content')

<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <div class="card-head-row">
                <h4 class="card-title">{{__('Users')}}</h4>
                <div class="card-tools">
                    <a href="{{route('users')}}" class="btn btn-icon btn-link btn-primary btn-xs">
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
                html()->form("post", route('users.password', $id))
                        ->autocomplete("off")

                        ->id("frm1")
                        ->open()
            }}
            <input type="hidden" value="" name="country_iso2" id="country_iso2" />
            <div class="row">
                <div class="col-12 offset-md-2 col-md-4 offset-lg-4  col-lg-4 border border-info"">
                    <h1>{{__('Personal Data')}}</h1>

                    <div class="form-floating form-floating-custom mb-2">
                        {{
                        html()->input("text", 'document_type_id', $user->getDocumentType->name )
                                    ->class('form-control')
                                    ->placeholder('')
                                    ->disabled(true)
                        }}
                        <label for="type_document">{{__('Document Type')}}</label>
                    </div>
                    <div class="form-floating form-floating-custom mb-2">
                        {{
                            html()->input("text", 'document', $user->document )
                                    ->class('form-control')
                                    ->placeholder(__('Document'))
                                    ->disabled(true)
                        }}
                        <label for="document">{{__('Document')}}</label>
                    </div>
                    <div class="form-floating form-floating-custom mb-2">
                        {{
                            html()->input("text", 'full_name', $user->full_name )
                                    ->class('form-control')
                                    ->placeholder(__('Full Name'))
                                    ->disabled(true)
                        }}
                        <label for="full_name">{{__('Full Name')}}</label>
                    </div>


                    <div class="form-floating form-floating-custom mb-2">
                        <input
                            id="new_password"
                            name="new_password"
                            type="password"
                            maxlength="16"
                            class="form-control"
                            placeholder=""
                            autofocus="true"
                            required
                            />
                        <label for="new_password">{{__('New Password')}}</label>

                        <div class="show-password icon-right">
                            <i class="icon-eye"></i>
                        </div>

                    </div>
                    @error('password')
                    <label id="new_password-error" class="error" for="new_password">{{__('This field is required')}}</label>
                    @enderror
                    <div class="form-floating form-floating-custom mb-2">
                        <input
                            id="re_password"
                            name="re_password"
                            type="password"
                            maxlength="16"
                            class="form-control"
                            placeholder=""
                            required
                            />
                        <label for="re_password">{{__('Repeat your Password')}}</label>

                        <div class="show-password icon-right">
                            <i class="icon-eye"></i>
                        </div>

                    </div>
                    @error('password')
                    <label id="re_password-error" class="error" for="re_password">{{__('This field is required')}}</label>
                    @enderror


                </div>
                <div class="col-12 offset-md-4 col-md-4 offset-lg-4  col-lg-4 text-center mt-4">

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
    var iti = null;
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
