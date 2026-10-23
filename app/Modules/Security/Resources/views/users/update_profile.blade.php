@extends('layouts.kaiadmin-menu')

@section('content')

    <style>
        .profile-header-bg {
            background: linear-gradient(90deg, #008be3 0%, #00c6ff 100%);
            border-radius: 16px 16px 0 0;
        }

        .info-card {
            background-color: #f3f6f9;
            border-radius: 12px;
        }

        .info-icon {
            width: 36px;
            height: 36px;
            background-color: #3b82f6;
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .badge-status-top {
            background-color: #c7f9cc;
            color: #127c56;
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 20px;
        }

        .badge-outline-blue {
            border: 1px solid #3b82f6;
            color: #1d4ed8;
            background-color: #eff6ff;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.85rem;
        }

        .btn-custom-cyan {
            background-color: #38b6ff;
            color: white;
            border: none;
        }
        .btn-custom-cyan:hover { background-color: #2ca0e2; color: white; }

        .btn-custom-blue {
            background-color: #0066ff;
            color: white;
            border: none;
        }
        .btn-custom-blue:hover { background-color: #0052cc; color: white; }

        .btn-custom-purple {
            background-color: #6f42c1;
            color: white;
            border: none;
        }
        .btn-custom-purple:hover { background-color: #59339d; color: white; }
    </style>

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
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">

                <div class="card border-0 shadow-sm mx-auto" style="max-width: 850px; border-radius: 16px; overflow: hidden;">

                    {{-- Encabezado --}}
                    <div class="profile-header-bg text-white p-3 px-4 d-flex align-items-center">
                        <a href="{{ route('home') }}" title="{{__('Back')}}" class="text-white me-3 fs-5 text-decoration-none">
                            <i class="fa fa-arrow-left"></i>
                        </a>
                        <h4 class="mb-0 fw-bold fs-5">{{__('Profile')}}</h4>
                    </div>

                    {{-- Cuerpo del Perfil --}}
                    <div class="card-body p-4 bg-white">

                        {{-- Cabecera de Usuario --}}
                        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                {!! renderAvatar(Auth::user(), 'avatar-xl') !!}
                                <div>
                                    <h3 class="fw-bold mb-0 text-dark fs-4">{{ Auth::user()->full_name ?: '-' }}</h3>
                                    <p class="text-muted mb-0 small">
                                        <i class="fa fa-envelope me-1"></i> {{ Auth::user()->email }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <span class="badge-status-top d-inline-flex align-items-center gap-2">
                                    <span class="rounded-circle" style="width: 8px; height: 8px; background-color: #10b981;"></span>
                                    {{__('Status')}}: {{ Auth::user()->active ? __('Active') : __('Inactive') }}
                                </span>
                            </div>
                        </div>

                        {{-- Bloques de Información --}}
                        <div class="row g-3 mb-4">

                            {{-- Columna Izquierda --}}
                            <div class="col-md-6">
                                <div class="info-card p-3">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="info-icon"><i class="fa fa-user"></i></div>
                                        <div>
                                            <small class="text-muted d-block">{{__('Username')}}</small>
                                            <span class="fw-bold text-dark">{{ Auth::user()->username ?: '-' }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="info-icon"><i class="fa fa-user-circle"></i></div>
                                        <div>
                                            <small class="text-muted d-block">{{__('Full Name')}}</small>
                                            <span class="fw-bold text-dark">{{ Auth::user()->full_name ?: '-' }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="info-icon"><i class="fa fa-id-card"></i></div>
                                        <div>
                                            <small class="text-muted d-block">{{__('Document')}}</small>
                                            <span class="fw-bold text-dark">{{ Auth::user()->full_document ?: '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Columna Derecha --}}
                            <div class="col-md-6">
                                <div class="info-card p-3">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="info-icon"><i class="fa fa-phone"></i></div>
                                        <div>
                                            <small class="text-muted d-block">{{__('Cell Phone')}}</small>
                                            <span class="fw-bold text-dark">{{ Auth::user()->cellPhone ?: '-' }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="info-icon"><i class="fa fa-globe"></i></div>
                                        <div>
                                            <small class="text-muted d-block">{{__('Country')}}</small>
                                            <span class="fw-bold text-dark">{{ Auth::user()->country?->name ?: '-' }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="info-icon"><i class="fa fa-envelope"></i></div>
                                        <div>
                                            <small class="text-muted d-block">{{__('Email')}}</small>
                                            <span class="fw-bold text-dark">{{ Auth::user()->email ?: '-' }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-3">
                                        <div class="info-icon"><i class="fa fa-calendar-alt"></i></div>
                                        <div>
                                            <small class="text-muted d-block">{{__('Register Date')}}</small>
                                            <span class="fw-bold text-dark">{{ Auth::user()->register_date ? date('d/m/Y', strtotime(Auth::user()->register_date)) : '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Dirección --}}
                            <div class="col-12">
                                <div class="info-card p-3 d-flex align-items-center gap-3">
                                    <div class="info-icon"><i class="fa fa-map-marker-alt"></i></div>
                                    <div>
                                        <small class="text-muted d-block">{{__('Address')}}</small>
                                        <span class="fw-bold text-dark">{{ Auth::user()->address ?: '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Perfiles Asignados --}}
                        <div class="info-card p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <span class="fw-semibold text-secondary">{{__('Assigned Profiles')}}</span>
                                <div class="d-flex gap-2 flex-wrap">
                                    @forelse((Auth::user()->perfiles ?? collect()) as $perfil)
                                        <span class="badge-outline-blue">{{ $perfil->name }}</span>
                                    @empty
                                        <span class="badge-outline-blue">-</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold text-secondary">{{__('Status')}}</span>
                                @if(Auth::user()->active)
                                    <span class="badge badge-primary">{{__('Active')}}</span>
                                @else
                                    <span class="badge badge-secondary">{{__('Inactive')}}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <button type="button" id="btnPhoto" class="btn btn-custom-cyan px-4 py-2 rounded-3 fw-semibold">
                                <i class="fa fa-camera me-2"></i>{{__('Change Photo')}}
                            </button>
                            <button type="button" id="btnData" class="btn btn-custom-blue px-4 py-2 rounded-3 fw-semibold">
                                <i class="fa fa-user-edit me-2"></i>{{__('Edit Data')}}
                            </button>
                            <button type="button" id="btnPassword" class="btn btn-custom-purple px-4 py-2 rounded-3 fw-semibold">
                                <i class="fa fa-lock me-2"></i>{{__('Change Password')}}
                            </button>
                        </div>

                        {{-- Panel: Foto --}}
                        <div id="panelPhoto" style="display:none" class="mt-3">
                            {{
                                html()->form("post", route('update_profile'))
                                    ->autocomplete("off")
                                    ->id("frm1")
                                    ->open()
                            }}
                            <input type="hidden" value="TEJLMERGSFo1SS9KSkJXL1VrU0p6dz09" name="option" />
                            <input type="hidden" value="" name="set_new_photo" id="set_new_photo" />
                            {{ html()->file('file')->id("file_photo")->name("file_photo")->style("display: none")->accept('image/*') }}

                            <div class="card border text-center">
                                <div class="card-body">
                                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                                        <a href="javascript:void(0)" onClick="$('#file_photo').click()"
                                            class="btn btn-white btn-sm">
                                            <i class="fas fa-desktop me-2"></i>{{__('Upload from your device')}}
                                        </a>
                                        <a href="javascript:void(0)" id="setCam"
                                            class="btn btn-white btn-sm">
                                            <i class="fas fa-camera me-2"></i>{{__('Take a photo with the camera')}}
                                        </a>
                                    </div>

                                    <div id="cardPhoto" style="display:none">
                                        <img id="new_photo" src="#" alt="New Image"
                                            class="d-inline-block mt-3"
                                            style="width: 100%; max-width: 380px; height: 300px; object-fit: contain; background: #fff;" />
                                        <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
                                            <button id="photoSave" type="button" class="btn btn-primary btn-sm">
                                                <i class="fas fa-save me-2"></i>{{__('Update')}}
                                            </button>
                                            <button type="button" id="cancelPhoto" class="btn btn-link btn-sm">
                                                {{__('Cancel')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{ html()->form()->close() }}
                        </div>

                        {{-- Panel: Datos --}}
                        <div id="panelData" style="display:none" class="mt-3">
                            {{
                                html()->form("post", route('update_profile'))
                                    ->autocomplete("off")
                                    ->id("frm2")
                                    ->open()
                            }}
                            <input type="hidden" value="dTBMWEdVQ1VucWw0dWVrSW9PWWxsZz09" name="option" />
                            <input type="hidden" value="" name="country_iso2" id="country_iso2" />

                            <div class="card border">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="form-floating form-floating-custom">
                                                <input id="address" name="address" type="text" class="form-control" maxlength="255" placeholder="{{__('Address')}}" value="{{ Auth::user()->address }}">
                                                <label for="address">{{__('Address')}}</label>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <label for="phone" class="text-muted small fw-bold mb-2 d-flex align-items-center">
                                                <i class="fas fa-phone-alt me-2 opacity-50 text-primary"></i> {{__('Cell Phone')}} <span class="text-danger ms-1">*</span>
                                            </label>
                                            <input id="phone" name="phone" type="text" class="form-control phone" maxlength="20" value="{{ Auth::user()->phone }}" required>
                                            @error('phone')
                                                <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                            @enderror
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="form-floating form-floating-custom">
                                                <input id="email" name="email" type="email" class="form-control" maxlength="100" placeholder="{{__('Email')}}" value="{{ Auth::user()->email }}" required>
                                                <label for="email">{{__('Email')}} *</label>
                                            </div>
                                            @error('email')
                                                <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <div class="alert alert-light border d-flex flex-wrap align-items-center gap-2 mb-0 rounded-3">
                                                <div class="flex-grow-1">
                                                    <label for="code" class="text-muted small fw-bold d-flex align-items-center mb-2">
                                                        <i class="fas fa-shield-alt me-2 opacity-50 text-primary"></i> {{__('Verification Code')}} <span class="text-danger ms-1">*</span>
                                                    </label>
                                                    <input id="code" name="code" type="text" class="form-control" maxlength="6" inputmode="numeric" placeholder="••••••" autocomplete="one-time-code" required>
                                                    @error('code')
                                                        <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <button type="button" id="btnSendCode" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-paper-plane me-1"></i>{{__('Send Verification Code')}}
                                                    </button>
                                                </div>
                                                <div id="code_message" class="w-100 small mb-0"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer border-top-0 d-flex justify-content-end gap-2">
                                    <button id="profileSave" type="button" class="btn btn-primary btn-sm">
                                        <i class="fas fa-save me-2"></i>{{__('Update')}}
                                    </button>
                                    <button type="button" id="cancelData" class="btn btn-link btn-sm">
                                        {{__('Cancel')}}
                                    </button>
                                </div>
                            </div>
                            {{ html()->form()->close() }}
                        </div>

                        {{-- Panel: Contraseña --}}
                        <div id="panelPassword" style="display:none" class="mt-3">
                            {{
                                html()->form("post", route('update_profile'))
                                    ->autocomplete("off")
                                    ->id("frm3")
                                    ->open()
                            }}
                            <input type="hidden" value="b1VOTnVXeEp1KzZOOXl5bGhkZUk3dz09" name="option" />

                            <div class="card border">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-4">
                                            <div class="form-floating form-floating-custom position-relative">
                                                <input id="current_password" name="current_password" type="password" maxlength="16" class="form-control" placeholder="{{__('Current Password')}}" required>
                                                <label for="current_password">{{__('Current Password')}} *</label>
                                                <div class="show-password position-absolute top-50 end-0 translate-middle-y me-2"><i class="icon-eye"></i></div>
                                            </div>
                                            @error('current_password')
                                                <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                            @enderror
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-floating form-floating-custom position-relative">
                                                <input id="new_password" name="new_password" type="password" maxlength="72" minlength="10" autocomplete="new-password" class="form-control" placeholder="{{__('New Password')}}" required>
                                                <label for="new_password">{{__('New Password')}} *</label>
                                                <div class="show-password position-absolute top-50 end-0 translate-middle-y me-2"><i class="icon-eye"></i></div>
                                            </div>
                                            @error('new_password')
                                                <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                            @enderror
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <div class="form-floating form-floating-custom position-relative">
                                                <input id="re_password" name="re_password" type="password" maxlength="72" autocomplete="new-password" class="form-control" placeholder="{{__('Repeat your Password')}}" required>
                                                <label for="re_password">{{__('Repeat your Password')}} *</label>
                                                <div class="show-password position-absolute top-50 end-0 translate-middle-y me-2"><i class="icon-eye"></i></div>
                                            </div>
                                            @error('re_password')
                                                <small class="text-danger fw-bold d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>{{__('This field is required')}}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer border-top-0 d-flex justify-content-end gap-2">
                                    <button id="credentialsSave" type="button" class="btn btn-primary btn-sm">
                                        <i class="fas fa-save me-2"></i>{{__('Update')}}
                                    </button>
                                    <button type="button" id="cancelPassword" class="btn btn-link btn-sm">
                                        {{__('Cancel')}}
                                    </button>
                                </div>
                            </div>
                            {{ html()->form()->close() }}
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal Cámara --}}
    <div class="modal fade" id="modalCamera" tabindex="-1" role="dialog" aria-labelledby="ModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="ModalLabel">{{__('Camera')}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <canvas id="tagCanvas" style="display: none;"></canvas>
                    <video id="video_cam_photo" style="width: 100%; height: auto; max-height: 480px; aspect-ratio: 16/9;" autoplay></video>
                    <h3>{{__('Click above the video to take a photo')}}</h3>
                </div>
                <div id="bodyCardCam" class="modal-footer">
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="form-floating form-floating-custom mb-2">
                            {{ html()->select("get_camera", [])
                                ->class('form-select')
                                ->placeholder(__('Select'))
                            }}
                            <label for="get_camera">{{__('Camera')}}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var iti = null;

        document.addEventListener("DOMContentLoaded", function (event) {
            var cropper = null;
            getCamerasPC();

            iti = window.intlTelInput(document.getElementById('phone'), {
                initialCountry: '{{Auth::user()->country?->iso2 ?? 'us'}}',
                strictMode: true,
                i18n: INTL_TEL_ES,
                utilsScript: APP_ROUTE + "template/kaiadmin/assets/js/plugin/intl-tel/intl-tel-input-utils.js",
            });

            $("#country_iso2").val(iti.getSelectedCountryData().iso2);

            $("#phone").on('countrychange', function (e) {
                this.value = "";
                $("#country_iso2").val(iti.getSelectedCountryData().iso2);
            });

            $("#btnPhoto").on("click", function () {
                $("#panelPhoto").slideToggle("fast");
                $("#panelData").slideUp("fast");
                $("#panelPassword").slideUp("fast");
            });

            $("#btnData").on("click", function () {
                $("#panelData").slideToggle("fast");
                $("#panelPhoto").slideUp("fast");
                $("#panelPassword").slideUp("fast");
            });

            $("#btnPassword").on("click", function () {
                $("#panelPassword").slideToggle("fast");
                $("#panelPhoto").slideUp("fast");
                $("#panelData").slideUp("fast");
            });

            $("#cancelPhoto").on("click", function () {
                $("#cardPhoto").slideUp("fast");
                $("#panelPhoto").slideUp("fast");
            });

            $("#cancelData").on("click", function () {
                $("#panelData").slideUp("fast");
            });

            $("#cancelPassword").on("click", function () {
                $("#panelPassword").slideUp("fast");
            });

            $("#credentialsSave").on("click", function () {
                if ($("#frm3").valid()) {
                    showLoading({ "icon": "info", "title": "{{__('Processing...')}}", "html": '<i class="fas fa-spinner fa-spin"></i>' });
                    $("#frm3").submit();
                } else {
                    showNotify('danger', "{{__('Missing Required Fields')}}");
                }
            });

            $("#frm3").validate({
                errorPlacement: function (error, element) {
                    var wrap = element.closest('.form-floating');
                    if (wrap.length) {
                        wrap.after(error);
                    } else {
                        element.parent().after(error);
                    }
                }
            });
            $("#frm2").validate({
                rules: {
                    email: { required: true, email: true },
                    phone: { required: true },
                    code: { required: true, digits: true, minlength: 6, maxlength: 6 }
                },
                errorPlacement: function (error, element) {
                    var wrap = element.closest('.form-floating');
                    if (wrap.length) {
                        wrap.after(error);
                    } else {
                        element.parent().after(error);
                    }
                }
            });
            $("#frm1").validate({
                errorPlacement: function (error, element) {
                    var wrap = element.closest('.form-floating');
                    if (wrap.length) {
                        wrap.after(error);
                    } else {
                        element.parent().after(error);
                    }
                }
            });

            $("#btnSendCode").on("click", function () {
                var email = $("#email").val();
                var $btn = $(this);
                var $message = $("#code_message");

                if (!email) {
                    $message.removeClass('text-success').addClass('text-danger').text("{{__('Email')}}...");
                    return;
                }

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{__('Processing...')}}');
                $message.empty();

                $.ajax({
                    url: '{{ route("update_profile.send_code") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        email: email
                    },
                    success: function (response) {
                        if (response.status == "1") {
                            $message.removeClass('text-danger').addClass('text-success').text(response.message);
                            var timeLeft = 60;
                            var interval = setInterval(function () {
                                timeLeft--;
                                $btn.text('{{__("Send Verification Code")}} (' + timeLeft + 's)');
                                if (timeLeft <= 0) {
                                    clearInterval(interval);
                                    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> {{__('Send Verification Code')}}');
                                }
                            }, 1000);
                        } else {
                            $message.removeClass('text-success').addClass('text-danger').text(response.message);
                            $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> {{__('Send Verification Code')}}');
                        }
                    },
                    error: function (xhr) {
                        var errorMsg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error';
                        $message.removeClass('text-success').addClass('text-danger').text(errorMsg);
                        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> {{__('Send Verification Code')}}');
                    }
                });
            });

            $("#profileSave").on("click", function () {
                if ($("#frm2").valid()) {
                    if (iti.isValidNumber() == true) {
                        if ($("#code").val() != "") {
                            var dial = (iti.getSelectedCountryData().dialCode || '').toString().replace(/[^0-9]/g, '');
                            var raw = $("#phone").val().replace(/[^0-9]/g, '');
                            if (dial && raw.indexOf(dial) === 0) {
                                raw = raw.substring(dial.length);
                            }
                            $("#phone").val(raw);
                            showLoading({ "icon": "info", "title": "{{__('Processing...')}}", "html": '<i class="fas fa-spinner fa-spin"></i>' });
                            $("#frm2").submit();
                        } else {
                            showNotify('danger', "{{__('Verification Code')}}");
                        }
                    } else {
                        showNotify('danger', "{{__('Wrong cell phone number')}}");
                    }
                } else {
                    showNotify('danger', "{{__('Missing Required Fields')}}");
                }
            });

            $("#file_photo").on('change', function () {
                readURL(this);
            });

            $("#setCam").on("click", function () {
                $("#get_camera").children().not('[value=""]').remove();
                $("#get_camera").append(OPTIONS_CAM);
                document.getElementById("video_cam_photo").play();
                quitCrop();
                $("#modalCamera").modal('show');
            });

            $("#get_camera").on("change", function () {
                if (this.value != '') {
                    showOverlay('bodyCardCam');
                    setMediaDevice('video_cam_photo', this.value);
                }
            });

            $("#video_cam_photo").on("click", function () {
                video = document.getElementById("video_cam_photo");
                video.pause();

                const foto = document.getElementById('tagCanvas');

                contexto = foto.getContext("2d");
                foto.width = video.videoWidth;
                foto.height = video.videoHeight;
                contexto.drawImage(video, 0, 0, foto.width, foto.height);
                $("#new_photo").attr("src", foto.toDataURL());
                $("#modalCamera").modal('hide');
                $("#cardPhoto").slideDown("fast", function () {
                    setCrop();
                });
            });

            $("#photoSave").on("click", function () {
                canvas = cropper.getCroppedCanvas({
                    width: 400,
                    height: 400,
                });

                canvas.toBlob(function (blob) {
                    var reader = new FileReader();
                    reader.readAsDataURL(blob);
                    reader.onloadend = function () {
                        $("#set_new_photo").val(reader.result);
                        showLoading({ "icon": "info", "title": "{{__('Processing...')}}", "html": '<i class="fas fa-spinner fa-spin"></i>' });
                        $("#frm1").submit();
                    }
                });
            });

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    quitCrop();
                    reader.onload = function (e) {
                        $('#new_photo').attr('src', e.target.result);
                        $("#cardPhoto").slideDown("fast", function () {
                            setCrop();
                        });
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function setCrop() {
                cropper = new Cropper(document.getElementById('new_photo'), {
                    aspectRatio: 1,
                    viewMode: 1
                });
            }

            function quitCrop() {
                if (cropper != null) {
                    cropper.destroy();
                    cropper = null;
                }
                $("#cardPhoto").hide();
            }
        });
    </script>
@endsection