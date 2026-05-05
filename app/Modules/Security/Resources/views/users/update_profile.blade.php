@extends('layouts.kaiadmin-menu')

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <div class="card-head-row">
                <h4 class="card-title">{{__('Account Setting')}}</h4>
                <div class="card-tools">
                    <button onClick="closeCard(this)" class="btn btn-icon btn-link btn-primary btn-xs">
                        <span class="fa fa-times"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-5 col-md-2">
                    <div class="nav flex-column nav-pills nav-secondary nav-pills-no-bd nav-pills-icons" id="v-pills-tab-with-icon" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active"  data-bs-toggle="pill" href="#v-photo" role="tab" aria-controls="v-pills-profile-icons" aria-selected="false" tabindex="-1">
                            <i class="fa fa-camera"></i>
                            {{__('Photo')}}
                        </a>
                        <a class="nav-link "  data-bs-toggle="pill" href="#v-profile" role="tab" aria-controls="v-pills-home-icons" aria-selected="true">
                            <i class="far fa-user"></i>
                            {{__('Profile')}}
                        </a>
                        <a class="nav-link"  data-bs-toggle="pill" href="#v-password" role="tab" aria-controls="v-pills-profile-icons" aria-selected="false" tabindex="-1">
                            <i class="fa fa-lock"></i>
                            {{__('Security')}}
                        </a>
                    </div>
                </div>
                <div class="col-7 col-md-10">
                    <div class="tab-content" >
                        <div class="tab-pane fade active show" id="v-photo" role="tabpanel" aria-labelledby="v-pills-home-tab-icons">
                            <div class="row mt-3">
                                <div class="col-12 col-md-6 col-lg-4">
                                    
                                    <div class="card card-post card-round">
                                        <div class="p-4 text-center">
                                            {!! renderAvatar(Auth::user(), 'avatar-xxl', 'mx-auto') !!}
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex">

                                                <div class="info-post ms-2">
                                                    <p class="username">{{Auth::user()->full_name}}</p>

                                                </div>
                                            </div>
                                            <div class="separator-solid"></div>


                                            <a href="javascript:void(0)" onClick="$('#panelVideo').slideDown('fast')" class="btn btn-primary btn-rounded btn-sm">
                                                <li class="fa fa-edit"></li>
                                                {{__('Change')}}</a>
                                            
                                        </div>
                                    </div>
                                    
                                    
                                </div>
                                <div id="panelVideo" style="display:none" class="col-12 col-md-6 col-lg-8">
                                    <div class="card-body">
                                        <div class="row">
                                            
                                            <div class="col-12 col-md-4 col-lg-4 col-stats">
                                                <div class="numbers">
                                                    <p class="card-category">{{__('Change Profile Picture')}}</p>

                                                </div>
                                            </div>
                                            
                                            <div class="col-12 col-md-4 col-lg-4 col-stats">
                                                <a href="javascript:void(0)" onClick="$('#file_photo').click()" class="btn btn-primary btn-rounded btn-xl">
                                                <li class="fa  fa-desktop"></li>
                                                {{__('Upload the computer')}}</a>
                                            </div>
                                            <div class="col-12 col-md-4 col-lg-4 col-stats">
                                                <a href="javascript:void(0)" id="setCam" class="btn btn-primary btn-rounded btn-xl">
                                                <li class="fa  fa-camera"></li>
                                                {{__('Hacer Una Foto')}}</a>
                                            </div>
                                   
                                        </div>
                                     
                                        {{
                                            html()->form("post", route('update_profile'))
                                                ->autocomplete("off")
                                                
                                                ->id("frm1")
                                                ->open()
                                        }}
                                        <input type="hidden" value="TEJLMERGSFo1SS9KSkJXL1VrU0p6dz09" name="option" />
                                        <input type="hidden" value="" name="set_new_photo" id="set_new_photo" />
                                        {{ html()->file('file')->id("file_photo")->name("file_photo")->style("display: none")->accept('image/*') }}
                                        
                                        <div style="display:none" id="cardPhoto" class="row mt-3 ">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <img  class="card-img-top" style="height: 400px; width: 550px" id="new_photo" src="#" alt="New Image" />
                                                    <div class="row mt-3">
                                                        <div class="col-12 col-md-6 col-lg-4 offset-lg-4 offset-md-3">
                                                        <button id="photoSave" type="button" class="btn btn-large btn-info">
                                                            <i class="fa fa-save"></i>
                                                            {{__('Update')}}</button>
                                                        </div>    
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{ html()->form()->close() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="v-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab-icons">
                            {{
                                html()->form("post", route('update_profile'))
                                        ->autocomplete("off")
                                        
                                        ->id("frm2")
                                        ->open()
                            }}
                                
                                <input type="hidden" value="dTBMWEdVQ1VucWw0dWVrSW9PWWxsZz09" name="option" />
                                <input type="hidden" value="" name="country_iso2" id="country_iso2" />
                                <div class="row mt-3">
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <div class="form-floating form-floating-custom mb-2">
                                            {{html()->select("type_document", $typeDoc, Auth::user()->getDocumentType?->crypt_id )
                                                        ->class('form-select')
                                                        ->placeholder(__("Select"))
                                                        ->required(true)
                                            }}
                                            <label for="type_document">{{__('Document Type')}}</label>
                                        </div>
                                        @error('type_document')
                                        <label id="type_document-error" class="error" for="type_document">{{__('This field is required')}}</label>
                                        @enderror
                                        <div class="form-floating form-floating-custom mb-2">
                                            {{
                                                html()->input("text", 'document', Auth::user()->document )
                                                        ->class('form-control')
                                                        ->placeholder(__('Document'))
                                                        ->maxlength(20)
                                                        ->required(true)
                                            }}
                                            <label for="document">{{__('Document')}}</label>
                                        </div>
                                        @error('document')
                                        <label id="document-error" class="error" for="document">{{__('This field is required')}}</label>
                                        @enderror
                                        <div class="form-floating form-floating-custom mb-2">
                                            {{
                                                html()->input("text", 'full_name', Auth::user()->full_name )
                                                        ->class('form-control')
                                                        ->placeholder(__('Full Name'))
                                                        ->maxlength(250)
                                                        ->required(true)
                                            }}
                                            
                                            <label for="full_name">{{__('Full Name')}}</label>
                                        </div>
                                        @error('full_name')
                                        <label id="full_name-error" class="error" for="full_name">{{__('This field is required')}}</label>
                                        @enderror
                                        <div class="form-floating form-floating-custom mb-2">
                                            {{
                                                html()->input("text", 'phone', Auth::user()->phone )
                                                        ->class('form-control phone')
                                                        ->maxlength(20)
                                                        ->required(true)
                                            }}
                                            
                                            <label class="phone" for="phone">{{__('Cell Phone')}}</label>
                                        </div>
                                         @error('phone')
                                        <label id="phone-error" class="error" for="phone">{{__('This field is required')}}</label>
                                        @enderror
                                        <div class="row">
                                            <button id="profileSave" type="button" class="btn btn-large btn-info">
                                                <i class="fa fa-save"></i>
                                                {{__('Update')}}</button>
                                        </div>

                                    </div>
                                    <div class="col-12 col-md-6 col-lg-8 ">
                                        
                                    </div> 
                                </div> 

                            {{ html()->form()->close() }}

                        </div>
                        <div class="tab-pane fade" id="v-password" role="tabpanel" aria-labelledby="v-pills-profile-tab-icons">
                            {{
                                html()->form("post", route('update_profile'))
                                        ->autocomplete("off")
                                        
                                        ->id("frm3")
                                        ->open()
                            }}
                                
                                <input type="hidden" value="b1VOTnVXeEp1KzZOOXl5bGhkZUk3dz09" name="option" />
                                <div class="row mt-3">
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <div class="login-form">
                                            <div class="form-sub">

                                                <div class="form-floating form-floating-custom mb-2">
                                                    <input
                                                        id="current_password"
                                                        name="current_password"
                                                        type="password"
                                                        maxlength="16"
                                                        class="form-control"
                                                        placeholder=""
                                                        required
                                                        />
                                                    <label for="password">{{__('Current Password')}}</label>

                                                    <div class="show-password icon-right">
                                                        <i class="icon-eye"></i>
                                                    </div>

                                                </div>
                                                @error('password')
                                                <label id="current_password-error" class="error" for="current_password">{{__('This field is required')}}</label>
                                                @enderror
                                                <div class="form-floating form-floating-custom mb-2">
                                                    <input
                                                        id="new_password"
                                                        name="new_password"
                                                        type="password"
                                                        maxlength="16"
                                                        class="form-control"
                                                        placeholder=""
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
                                                <div class="row">
                                                    <button id="credentialsSave" type="button" class="btn btn-large btn-info">
                                                        <i class="fa fa-save"></i>
                                                        {{__('Update')}}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {{ html()->form()->close() }}
                                
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div
    class="modal fade"
    id="modalCamera"
    tabindex="-1"
    role="dialog"
    aria-labelledby="ModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static" 
    data-bs-keyboard="false"
    >
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="ModalLabel">{{__('Camera')}}</h5>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                    >
                    
                </button>
            </div>
      
            <div class="modal-body text-center">
                <canvas id="tagCanvas" style="display: none;"></canvas>
                <video id="video_cam_photo" style="width: 100%; height: 480px" autoplay></video>
                
                <h3>{{__('Click above the video to take a photo')}}</h3>
            </div>
            
            <div id="bodyCardCam" class="modal-footer">
                
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="form-floating form-floating-custom mb-2">
                        {{html()->select("get_camera", [] )
                                                    ->class('form-select')
                                                    ->placeholder(__("Select"))
                        }}
                        <label for="get_camera">{{__('Camera')}}</label>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var iti=null;
    
    document.addEventListener("DOMContentLoaded", function (event) {
        var cropper=null;
         getCamerasPC();
        
        iti = window.intlTelInput(document.getElementById('phone'), {
            initialCountry: '{{Auth::user()->getCountry?->iso2 ?? 'us'}}',
            //onPhoneNumberBlur
            //countryDialCode: '+58',
            strictMode: true,
            separateDialCode: true,
             
            i18n: INTL_TEL_ES,
            utilsScript: APP_ROUTE + "template/kaiadmin/assets/js/plugin/intl-tel/intl-tel-input-utils.js",
        });
        
        $("#country_iso2").val(iti.getSelectedCountryData().iso2);
        
        $("#phone").on('countrychange', function(e) {
            this.value = "";
            $("#country_iso2").val(iti.getSelectedCountryData().iso2);
            /*
            console.log(iti.getSelectedCountryData()['dialCode']);
            console.log(document.querySelector(".iti__selected-country-primary").innerText);
            console.log(iti.getSelectedCountryData().iso2);
         
             */
            
        });
        
        $("#credentialsSave").on("click", function (){
            if ($("#frm3").valid() ){
                showLoading({"icon":"info", "title":"{{__('Processing...')}}", "html":'<i class="fas fa-spinner fa-spin"></i>' });
                $("#frm3").submit();
            }else{
                 showNotify('danger', "{{__('Missing Required Fields')}}");
            }
        });
        
        $("#frm3").validate({
            errorPlacement: function (error, element) {
                element.parents('.form-floating').after(error);
            }
        });
        $("#frm2").validate({
            errorPlacement: function (error, element) {
                element.parents('.form-floating').after(error);
            }
        });
        $("#profileSave").on("click", function (){
            if ($("#frm2").valid() ){
                if(iti.isValidNumber()==true){
                    showLoading({"icon":"info", "title":"{{__('Processing...')}}", "html":'<i class="fas fa-spinner fa-spin"></i>' });
                    $("#frm2").submit();
                }else{
                    showNotify('danger', "{{__('Wrong cell phone number')}}");
                }
            }else{
                showNotify('danger', "{{__('Missing Required Fields')}}");
            }
        });
       
        $("#file_photo").on('change', function(){
            readURL(this);
        });    
        
        
        $("#setCam").on("click", function (){
            $("#get_camera").children().not('[value=""]').remove();
            $("#get_camera").append(OPTIONS_CAM);
            document.getElementById("video_cam_photo").play();
            quitCrop();
            $("#modalCamera").modal('show');
        });
        
        $("#get_camera").on("change", function (){
            if (this.value!=''){
                showOverlay('bodyCardCam');
                setMediaDevice('video_cam_photo', this.value);
            }
        });
        
        $("#video_cam_photo").on("click", function (){
            video = document.getElementById("video_cam_photo");
            video.pause();
            
            const foto = document.getElementById('tagCanvas'); //canvas
            
            contexto = foto.getContext("2d");
            foto.width = video.videoWidth;
            foto.height = video.videoHeight;
            contexto.drawImage(video, 0, 0, foto.width, foto.height);
            $("#new_photo").attr("src", foto.toDataURL());
            $("#modalCamera").modal('hide');
            $("#cardPhoto").slideDown("fast", function (){
                setCrop();
            });
            
        });
        
        $("#photoSave").on("click", function (){
            canvas = cropper.getCroppedCanvas({
      		width: 400,
      		height: 400,
            });

            canvas.toBlob(function(blob) {
        	var reader = new FileReader();
         	reader.readAsDataURL(blob); 
         	reader.onloadend = function() {
                    $("#set_new_photo").val(reader.result);  
                    showLoading({"icon":"info", "title":"{{__('Processing...')}}", "html":'<i class="fas fa-spinner fa-spin"></i>' });
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
                    $("#cardPhoto").slideDown("fast", function (){
                        setCrop();
                    });
                }
                reader.readAsDataURL(input.files[0]);
                
            }
        }
        
        function setCrop(){
            cropper = new Cropper(document.getElementById('new_photo'), {
                        aspectRatio: 1,
                        viewMode: 1
                });
        }
        function quitCrop(){
            if (cropper!=null){
                cropper.destroy();
                cropper = null;
            }
            $("#cardPhoto").hide();
        }
        
       
        
        
        
        

    });

</script>
@endsection
