@extends('layouts.kaiadmin-menu')

@section('content')

<div class="col-md-12">
    <div class="card">
        <div class="card-header">
            <div class="card-head-row">
                <h4 class="card-title">{{__('Profiles')}}</h4>
                <div class="card-tools">
                    <button onClick="closeCard(this)" class="btn btn-icon btn-link btn-primary btn-xs">
                        <span class="fa fa-times"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 offset-md-4 col-md-4 offset-lg-3  col-lg-6">
                    <table id="table1" class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>{{__('ID')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Actions')}}</th>

                            </tr>
                        </thead>
                        <tbody>
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


        table1 = $('#table1').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "order": [[0, 'desc']],

            ajax: {
                url: "{{route('profiles.list')}}",
                data: function (d) {
                    d.search = $('#search').val()
                }
            },
            initComplete: function () {
                $("#table1_filter").removeClass('dataTables_filter').html('<div class="col-12  col-md-12  col-lg-12"><div class="form-floating form-floating-custom mb-2">{{html()->input("text", "search", "")->class("form-control")->placeholder("")->maxlength(20)}}<label for="search">{{__("Search")}}</label><div onClick="table1.draw();" class="icon-right"><i class="fa fa-search"></i></div></div></div>');
                $("#table1_filter").parent().siblings().eq(0).html('<a href="{{route("profiles.create")}}"  class="btn btn-large btn-info"> <i class="fa fa-plus"></i> {{__("New Profile")}}</a>');
            },
            serverSide: true,
            processing: true,

            rowCallback: function (row, data) {
                //$(row).addClass(data.class);
            },
            columns: [
                {data: 'DT_RowIndex'},
                {data: 'name'},
                {data: 'action'},
            ],
            language: {
                url: "{{url('/')}}/template/kaiadmin/assets/js/plugin/datatables/{{app()->getLocale()}}.json"
            }


        });







    });

</script>
@endsection
