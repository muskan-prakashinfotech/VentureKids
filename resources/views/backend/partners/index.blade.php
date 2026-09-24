@extends('backend.layouts.app')

@section('title') {{ $title }} @endsection

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Partner Accounts</h3>
                    <a href="{{ route('backend.partners.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add Partner
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="partnersTable" class="table table-bordered table-hover w-100 mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Mobile</th>
                                <th>Licenses Purchased</th>
                                <th>Trainers Allowed</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
    <script>
    (function () {
        function initializePartnersTable() {
            if (!window.jQuery || !$.fn.DataTable) {
                return;
            }

            $('#partnersTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                ajax: @json(route('backend.partners.index_data')),
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'username', name: 'username' },
                    { data: 'mobile', name: 'mobile' },
                    { data: 'license_purchase', name: 'no_of_license_purchased', searchable: false },
                    { data: 'trainers_allowed', name: 'allow_add_trainers', searchable: false },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'asc']]
            });
        }

        window.addEventListener('load', initializePartnersTable);
    })();
    </script>
@endsection
