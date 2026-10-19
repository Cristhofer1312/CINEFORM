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
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">{{__('Security')}}</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">{{__('Users')}}</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-dark mb-0">{{__('Users')}}</h2>
                    <p class="text-muted small mb-0"><i class="fas fa-user-cog me-1 text-primary"></i> {{__('Manage the system users and their assigned profiles.')}}</p>
                </div>
                <div class="text-end">
                    <a href="{{ route('users.create') }}"
                        class="btn btn-primary shadow-sm rounded-pill px-4 fw-bold transition-hover">
                        <i class="fas fa-plus me-2"></i> {{__('New User')}}
                    </a>
                </div>
            </div>

            {{-- Card de listado --}}
            <div class="card border-0 shadow-card rounded-4 overflow-hidden border-top border-4 border-primary mb-5">
                <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-dark uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <i class="fas fa-list-alt me-2 text-primary"></i> {{__('User List')}}
                    </h6>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="input-group shadow-xs rounded-pill overflow-hidden border" style="max-width: 320px; width: 100%;">
                            <span class="input-group-text bg-white border-0 ps-3">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-0 py-2 shadow-none" id="search" placeholder="{{__('Search')}}..." autocomplete="off">
                            <button type="button" class="btn btn-white border-0 text-muted" id="clear-search" title="{{__('Clear')}}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 bg-light bg-opacity-25">
                    <div class="table-responsive">
                        <table id="table1" class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">{{__('ID')}}</th>
                                    <th>{{__('Username')}}</th>
                                    <th>{{__('Email')}}</th>
                                    <th>{{__('Status')}}</th>
                                    <th class="text-end pe-4">{{__('Actions')}}</th>
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

    @include('security::partials.style')

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
                    url: "{{route('users.list')}}",
                    data: function (d) {
                        d.search = $('#search').val();
                    }
                },
                serverSide: true,
                processing: true,
                columns: [
                    {data: 'DT_RowIndex'},
                    {data: 'username'},
                    {data: 'email'},
                    {data: 'status'},
                    {data: 'action', className: 'text-end pe-4'},
                ],
                language: {
                    url: "{{url('/')}}/template/kaiadmin/assets/js/plugin/datatables/{{app()->getLocale()}}.json"
                }
            });

            $('#search').on('keyup', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () { table1.draw(); }, 400);
            });

            $('#clear-search').on('click', function () {
                $('#search').val('');
                table1.draw();
            });
        });
    </script>
@endsection