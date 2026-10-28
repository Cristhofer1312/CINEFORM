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
                        <i class="fas fa-users me-2 text-primary"></i>{{__('Profiles')}}
                    </h4>
                    <div class="card-tools">
                        <a href="{{ route('profiles.create') }}" class="btn btn-icon btn-primary btn-round">
                            <span class="fa fa-plus"></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12 col-md-5 col-lg-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="search" placeholder="{{__('Search')}}..." autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="table1" class="table table-bordered table-head-bg-primary table-bordered-bd-primary">
                        <thead>
                            <tr>
                                <th>{{__('ID')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Status')}}</th>
                                <th class="text-center">{{__('Actions')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function (event) {
            var searchTimer = null;

            table1 = $('#table1').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "order": [[0, 'desc']],

                ajax: {
                    url: "{{route('profiles.list')}}",
                    data: function (d) {
                        d.search = $('#search').val();
                    }
                },
                serverSide: true,
                processing: true,
                columns: [
                    {data: 'DT_RowIndex'},
                    {data: 'name'},
                    {data: 'status'},
                    {data: 'action', className: 'text-center'},
                ],
                language: {
                    url: "{{url('/')}}/template/kaiadmin/assets/js/plugin/datatables/{{app()->getLocale()}}.json"
                }
            });

            $('#search').on('keyup', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () { table1.draw(); }, 400);
            });

            $('#table1').on('click', '.btn-toggle', function (e) {
                e.preventDefault();
                var url = $(this).attr('href');
                var deact = $(this).data('action') === 'deactivate';
                Swal.fire({
                    title: deact ? "{{__('Deactivate')}}" : "{{__('Activate')}}",
                    html: deact
                        ? "{{__('Are you sure you want to deactivate this profile?')}}"
                        : "{{__('Are you sure you want to activate this profile?')}}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: deact ? "{{__('Yes, deactivate')}}" : "{{__('Yes, activate')}}",
                    cancelButtonText: "{{__('Cancel')}}",
                    confirmButtonColor: deact ? '#dc2626' : '#198754',
                    cancelButtonColor: '#64748b'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>
@endsection