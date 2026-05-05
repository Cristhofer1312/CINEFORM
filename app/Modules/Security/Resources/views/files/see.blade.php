@extends('layouts.kaiadmin-menu')

@section('content')
<style>
    img{
        pointer-events: none; /* Deshabilita los eventos del mouse en la imagen */
    }

</style>
<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <div class="card-head-row">
                <h4 class="card-title">{{__('Documents')}}</h4>
                {{html()->select("page", $pages, '0' )
                                                        ->class('form-select')
                                                    
                                                        ->required(true)
                }}

                <div class="card-tools">
                    <button onClick="closeCard(this)" class="btn btn-icon btn-link btn-primary btn-xs">
                        <span class="fa fa-times"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-12 ">
                    <iframe onload="Swal.close()" id="iLoadImg" oncontextmenu="return false;" style="width:100%; height: 80vh" src="{{route('show_pdf', [$id])}}">

                    </iframe>
                </div>
            </div>




        </div>
    </div>
</div>


<script type="text/javascript">

    document.addEventListener("DOMContentLoaded", function (event) {
        document.addEventListener('contextmenu', event => event.preventDefault());

        $("#frm1").validate({
            errorPlacement: function (error, element) {
                element.parents('.form-floating').after(error);
            }
        });

        $("#page").on("change", function () {
            showLoading({"title":"Buscando Pagina"});
            
            
            $("#iLoadImg").attr("src", "{{url('security/show-pdf')}}/{{$id}}/" + this.value);
        });





    });

</script>
@endsection
