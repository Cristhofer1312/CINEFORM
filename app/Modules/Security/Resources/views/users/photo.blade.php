@extends('layouts.kaiadmin-login')
@section('content')
<style>
    input[type=text]:focus,  input[type=password]:focus{
        background-color: #FFFF99 !important;
    }
    .form-floating-custom .form-control:focus+label,.form-floating-custom .form-control:not(:placeholder-shown)+label,.form-floating-custom .form-select:focus+label,.form-floating-custom .form-select:not(:placeholder-shown)+label {
        font-weight: bold;
    }
</style>


<div class="card card-round">
    <div class="card-header">
        <div class="card-head-row card-tools-still-right">
            <h4 class="card-title">{{__('Photo')}}</h4>

        </div>

    </div>
    <div style="height: 80vh" class="card-body">
        <div class="row">
            <div class="col-md-6 text-center">
                
                <video id="video_cam_photo" style="width: 100%; height: 480px" autoplay></video>
                <h3>{{__('Click above the video to take a photo')}}</h3>
            </div>
            <div class="col-md-6 text-center">
                <canvas id="tagCanvas" style="display: none;"></canvas>
                <img  class="card-img-top" style="height: 400px; width: 550px" id="new_photo" src="#" alt="New Image" />
                
                <button style="display: none" type="button" onClick="savePhoto()" id="downF" class="btn btn-primary btn-sm mt-2"><li class="fas fa-download"></li></button>
                
                
            </div>

        </div>
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
        
        <div class="col-12 col-md-6 col-lg-1">
            <button type="button" onClick="getCam()" class="btn btn-success btn-sm"><li class="fas fa-sync"></li></button>
        </div>
        
    </div>
</div>
<!-- jQuery Crop -->
<script src="{{asset('template/kaiadmin/assets/js/plugin/jquery.crop/cropper.min.js')}}"></script>
<script src="{{asset('template/kaiadmin/assets/js/plugin/jquery.crop/dropzone-min.js')}}"></script>
<script type="text/javascript">
    var cropper=null;
    document.addEventListener("DOMContentLoaded", function (event) {
        $("#get_camera").on("change", function (){
            if (this.value!=''){
                showOverlay('bodyCardCam');
                setMediaDevice('video_cam_photo', this.value);
            }
        });
        
        
        $("#video_cam_photo").on("click", function (){
            $("#downF").show();
            video = document.getElementById("video_cam_photo");
            video.pause();
            
            const foto = document.getElementById('tagCanvas'); //canvas
            
            contexto = foto.getContext("2d");
            foto.width = video.videoWidth;
            foto.height = video.videoHeight;
            contexto.drawImage(video, 0, 0, foto.width, foto.height);
            $("#new_photo").attr("src", foto.toDataURL());
            video.play();
            quitCrop();
            setCrop();
            /*
            $("#cardPhoto").slideDown("fast", function (){
                
            });
            */
        });
        
        $("#get_camera").children().not('[value=""]').remove();
        $("#get_camera").append(OPTIONS_CAM);
    });
    function getCam(){
        getCamerasPC();
        $("#get_camera").children().not('[value=""]').remove();
        $("#get_camera").append(OPTIONS_CAM);
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

    }
        
    function savePhoto(){
        canvas = cropper.getCroppedCanvas({
            width: 400,
            height: 400
        });
        
        // Convertir a base64 y crear un enlace para descargar
        canvas.toBlob((blob) => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'photo.png'; // Nombre del archivo
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }, 'image/png');
        
    }    
</script>
@endsection
