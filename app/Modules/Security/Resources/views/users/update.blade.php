@extends('layouts.kaiadmin-menu')

@section('content')

    <div class="row justify-content-center">
        <div class="col-12 col-lg-11">

            {{-- Alertas --}}
            @if(session('success'))
                <div class="alert alert-success shadow-xs border-0 d-flex align-items-start gap-2 rounded-4 mb-4" role="alert">
                    <i class="fas fa-check-circle mt-1"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger shadow-xs border-0 d-flex align-items-start gap-2 rounded-4 mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle mt-1"></i>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Encabezado --}}
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4 px-2">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 p-0 bg-transparent" style="font-size: 0.8rem;">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">{{__('Administration')}}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('users') }}" class="text-decoration-none text-muted">{{__('Users')}}</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">{{__('Edit User')}}</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-dark mb-0">{{__('Edit User')}}</h2>
                    <p class="text-muted small mb-0"><i class="fas fa-user-edit me-1 text-primary"></i> {{__('Update the user information.')}}</p>
                </div>
                <div class="text-end">
                    <a href="{{ route('users') }}"
                        class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold transition-hover">
                        <i class="fas fa-arrow-left me-2 text-primary"></i> {{__('Cancel')}}
                    </a>
                </div>
            </div>

            <form action="{{ route('users.update', $id) }}" method="POST" autocomplete="off" id="frm1">
                @csrf
                <input type="hidden" value="" name="country_iso2" id="country_iso2" />

                {{-- Datos Personales --}}
                <div class="card border-0 shadow-card rounded-4 mb-4 overflow-hidden border-top border-4 border-primary">
                    <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h6 class="mb-0 fw-bold text-dark uppercase" style="font-size: 0.75rem;">
                            <i class="fas fa-id-badge me-2 text-primary"></i> {{__('Personal Data')}}
                        </h6>
                    </div>
                    <div class="card-body p-4 bg-gray-100 bg-opacity-25">
                        <div class="row g-4">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="document_type_id" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-file-alt me-2 opacity-50 text-primary"></i> {{__('Document Type')}} <span class="text-danger ms-1">*</span>
                                </label>
                                <select id="document_type_id" name="document_type_id" class="form-select shadow-none rounded-3 border-light-2 py-2" required>
                                    <option value="">{{__('Select')}}</option>
                                    @foreach($typeDoc as $cryptId => $code)
                                        <option value="{{ $cryptId }}" {{ old('document_type_id', $user->documentType?->crypt_id ?? '') == $cryptId ? 'selected' : '' }}>{{ $code }}</option>
                                    @endforeach
                                </select>
                                @error('type_document')
                                    <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="document" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-hashtag me-2 opacity-50 text-primary"></i> {{__('Document')}} <span class="text-danger ms-1">*</span>
                                </label>
                                <input id="document" name="document" type="text" class="form-control shadow-none rounded-3 border-light-2 py-2" maxlength="20" placeholder="{{__('Document')}}" value="{{ old('document', $user->document) }}" required>
                                @error('document')
                                    <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="full_name" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-user me-2 opacity-50 text-primary"></i> {{__('Full Name')}} <span class="text-danger ms-1">*</span>
                                </label>
                                <input id="full_name" name="full_name" type="text" class="form-control shadow-none rounded-3 border-light-2 py-2" maxlength="250" placeholder="{{__('Full Name')}}" value="{{ old('full_name', $user->full_name) }}" required>
                                @error('full_name')
                                    <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="phone" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-phone-alt me-2 opacity-50 text-primary"></i> {{__('Cell Phone')}} <span class="text-danger ms-1">*</span>
                                </label>
                                <input id="phone" name="phone" type="tel" class="form-control phone shadow-none rounded-3 border-light-2 py-2" maxlength="20" value="{{ old('phone', $user->phone) }}" required>
                                @error('phone')
                                    <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Datos de Cuenta --}}
                <div class="card border-0 shadow-card rounded-4 mb-4 overflow-hidden border-top border-4 border-primary">
                    <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h6 class="mb-0 fw-bold text-dark uppercase" style="font-size: 0.75rem;">
                            <i class="fas fa-user-lock me-2 text-primary"></i> {{__('User Data')}}
                        </h6>
                    </div>
                    <div class="card-body p-4 bg-gray-100 bg-opacity-25">
                        <div class="row g-4">
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="email" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-envelope me-2 opacity-50 text-primary"></i> {{__('Email')}} <span class="text-danger ms-1">*</span>
                                </label>
                                <input id="email" name="email" type="email" class="form-control email shadow-none rounded-3 border-light-2 py-2" maxlength="100" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="profile_id" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-user-shield me-2 opacity-50 text-primary"></i> {{__('Profile')}} <span class="text-danger ms-1">*</span>
                                </label>
                                <select id="profile_id" name="profile_id" class="form-select shadow-none rounded-3 border-light-2 py-2" required>
                                    <option value="">{{__('Select')}}</option>
                                    @foreach($profiles as $cryptId => $name)
                                        <option value="{{ $cryptId }}" {{ old('profile_id', $user->profile?->crypt_id ?? '') == $cryptId ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('profile_id')
                                    <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="active" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-toggle-on me-2 opacity-50 text-primary"></i> {{__('Active')}}
                                </label>
                                <select id="active" name="active" class="form-select shadow-none rounded-3 border-light-2 py-2" required>
                                    @php $selActive = old('active', $user->active ? '1' : '0'); @endphp
                                    <option value="1" {{ $selActive == '1' ? 'selected' : '' }}>{{__('Yes')}}</option>
                                    <option value="0" {{ $selActive == '0' ? 'selected' : '' }}>{{__('Not')}}</option>
                                </select>
                                @error('active')
                                    <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer de Acciones --}}
                <div class="row justify-content-center mt-4 pb-5">
                    <div class="col-12 col-lg-6">
                        <div class="card border shadow-xs rounded-pill overflow-hidden bg-white">
                            <div class="card-body p-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <a href="{{ route('users') }}"
                                    class="btn btn-link text-muted fw-bold text-decoration-none px-4 ms-2">
                                    {{__('Cancel')}}
                                </a>
                                <div class="d-flex align-items-center me-2">
                                    <button type="button" id="save" class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-sm">
                                        <i class="fas fa-save me-2"></i> {{__('Save')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('security::partials.style')

    <script type="text/javascript">
        var iti = null;
        document.addEventListener("DOMContentLoaded", function (event) {

            $("#frm1").validate({
                errorPlacement: function (error, element) {
                    element.parent().after(error);
                }
            });

            iti = window.intlTelInput(document.getElementById('phone'), {
                initialCountry: '{{Auth::user()->country?->iso2 ?? 'us'}}',
                strictMode: true,
                i18n: INTL_TEL_ES,
                utilsScript: "{{ url('/') }}/template/kaiadmin/assets/js/plugin/intl-tel/intl-tel-input-utils.js",
            });

            $("#country_iso2").val(iti.getSelectedCountryData().iso2);

            $("#phone").on('countrychange', function (e) {
                this.value = "";
                $("#country_iso2").val(iti.getSelectedCountryData().iso2);
            });

            $('#save').on('click', function () {
                if ($("#frm1").valid() == true) {
                    if (iti.isValidNumber() == true) {
                        var dial = (iti.getSelectedCountryData().dialCode || '').toString().replace(/[^0-9]/g, '');
                        var raw = $("#phone").val().replace(/[^0-9]/g, '');
                        if (dial && raw.indexOf(dial) === 0) {
                            raw = raw.substring(dial.length);
                        }
                        $("#phone").val(raw);
                        showLoading({"icon": "info", "title": "{{__('Processing...')}}", "html": '<i class="fas fa-spinner fa-spin"></i>'});
                        $("#frm1").submit();
                    } else {
                        showNotify('danger', "{{__('Wrong cell phone number')}}");
                    }
                } else {
                    showNotify('danger', "{{__('Missing Required Fields')}}");
                }
            });
        });
    </script>
@endsection