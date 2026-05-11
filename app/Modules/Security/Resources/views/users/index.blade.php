@extends('layouts.kaiadmin-menu')

@section('content')

    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 offset-md-1 col-md-10 offset-lg-1  col-lg-10">
                        <table id="table1" class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>{{__('ID')}}</th>
                                    <th>{{__('user_name')}}</th>
                                    <th>{{__('email')}}</th>
                                    {{-- <th>{{__('password')}}</th>
                                    <th>{{__('Profile')}}</th> --}}
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
                    url: "{{route('users.list')}}",
                    data: function (d) {
                        d.search = $('#search').val();
                    },
                    dataFilter: function (data) {
                        console.log("Datos JSON recibidos en DataTables:");
                        console.dir(JSON.parse(data)); // imprime formato objeto en consola
                        return data; // debe retornar los datos sin modificar
                    }
                },
                initComplete: function () {
                    $("#table1_filter").removeClass('dataTables_filter').html('<div class="col-12  col-md-12  col-lg-12"><div class="form-floating form-floating-custom mb-2">{{html()->input("text", "search", "")->class("form-control")->placeholder("")->maxlength(20)}}<label for="search">{{__("Search")}}</label><div onClick="table1.draw();" class="icon-right"><i class="fa fa-search"></i></div></div></div>');
                    $("#table1_filter").parent().siblings().eq(0).html('<a href="{{route("users.create")}}"  class="btn btn-large btn-info"> <i class="fa fa-plus"></i> {{__("New User")}}</a>');
                },
                serverSide: true,
                processing: true,
                rowCallback: function (row, data) {
                    //$(row).addClass(data.class);
                },
                columns: [
                    { data: 'DT_RowIndex' },
                    { data: 'username' },
                    { data: 'email' },
                    //{data: 'password'},
                    // {data: 'get_perfiles'},
                    { data: 'action' },
                ],
                language: {
                    url: "{{url('/')}}/template/kaiadmin/assets/js/plugin/datatables/{{app()->getLocale()}}.json"
                }
            });

        });

    </script>
@endsection