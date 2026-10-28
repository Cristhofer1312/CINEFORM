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
                            <li class="breadcrumb-item active text-primary fw-bold">{{__('Change Password')}}</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-dark mb-0">{{__('Change Password')}}</h2>
                    <p class="text-muted small mb-0"><i class="fas fa-key me-1 text-primary"></i> {{__('Set a new access password for the user')}} <strong>{{ $user->username }}</strong>.</p>
                </div>
                <div class="text-end">
                    <a href="{{ route('users') }}"
                        class="btn btn-white shadow-sm border rounded-pill px-4 btn-sm fw-bold transition-hover">
                        <i class="fas fa-arrow-left me-2 text-primary"></i> {{__('Cancel')}}
                    </a>
                </div>
            </div>

            <form action="{{ route('users.password', $id) }}" method="POST" autocomplete="off" id="frm1">
                @csrf

                {{-- Datos del usuario --}}
                <div class="card border-0 shadow-card rounded-4 mb-4 overflow-hidden border-top border-4 border-primary">
                    <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h6 class="mb-0 fw-bold text-dark uppercase" style="font-size: 0.75rem;">
                            <i class="fas fa-id-badge me-2 text-primary"></i> {{__('Personal Data')}}
                        </h6>
                    </div>
                    <div class="card-body p-4 bg-gray-100 bg-opacity-25">
                        <div class="row g-4">
                            <div class="col-12 col-md-4">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-file-alt me-2 opacity-50 text-primary"></i> {{__('Document Type')}}
                                </label>
                                <input type="text" class="form-control shadow-none rounded-3 border-light-2 py-2 bg-white" value="{{ $user->documentType?->name }}" disabled>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-hashtag me-2 opacity-50 text-primary"></i> {{__('Document')}}
                                </label>
                                <input type="text" class="form-control shadow-none rounded-3 border-light-2 py-2 bg-white" value="{{ $user->document }}" disabled>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-user me-2 opacity-50 text-primary"></i> {{__('Full Name')}}
                                </label>
                                <input type="text" class="form-control shadow-none rounded-3 border-light-2 py-2 bg-white" value="{{ $user->full_name }}" disabled>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Nueva contraseña --}}
                <div class="card border-0 shadow-card rounded-4 mb-4 overflow-hidden border-top border-4 border-primary">
                    <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h6 class="mb-0 fw-bold text-dark uppercase" style="font-size: 0.75rem;">
                            <i class="fas fa-key me-2 text-primary"></i> {{__('New Password')}}
                        </h6>
                    </div>
                    <div class="card-body p-4 bg-gray-100 bg-opacity-25">
                        <div class="row g-4 justify-content-center">
                            <div class="col-12 col-md-6 col-lg-4">
                                <label for="new_password" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-lock me-2 opacity-50 text-primary"></i> {{__('New Password')}} <span class="text-danger ms-1">*</span>
                                </label>
                                <div class="position-relative">
                                    <input id="new_password" name="new_password" type="password" maxlength="72" minlength="10" autocomplete="new-password" class="form-control shadow-none rounded-3 border-light-2 py-2" autofocus required>
                                    <div class="show-password position-absolute top-50 end-0 translate-middle-y me-3"><i class="icon-eye"></i></div>
                                </div>
                                @error('new_password')
                                    <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6 col-lg-4">
                                <label for="re_password" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                    <i class="fas fa-lock me-2 opacity-50 text-primary"></i> {{__('Repeat your Password')}} <span class="text-danger ms-1">*</span>
                                </label>
                                <div class="position-relative">
                                    <input id="re_password" name="re_password" type="password" maxlength="72" autocomplete="new-password" class="form-control shadow-none rounded-3 border-light-2 py-2" required>
                                    <div class="show-password position-absolute top-50 end-0 translate-middle-y me-3"><i class="icon-eye"></i></div>
                                </div>
                                @error('re_password')
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
        document.addEventListener("DOMContentLoaded", function (event) {
            $("#frm1").validate({
                errorPlacement: function (error, element) {
                    // Dentro de la columna: fuera de ella el mensaje ocupa una fila del grid y apila los campos.
                    var col = element.closest('[class*="col-"]');
                    col.length ? col.append(error) : element.parent().after(error);
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