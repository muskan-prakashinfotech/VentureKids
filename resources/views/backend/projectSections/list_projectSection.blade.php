@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">Project Section List</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.projectmanagement.index') }}">Project Management</a></li>
                        <li class="breadcrumb-item active">Project Section List</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session()->get('success') }}
                </div>
            @endif

            <a href="{{ route('backend.projectSectioncreate.projectSectionCreate') }}" class="btn btn-primary">{{ __('Add Project Section') }}</a>
            <div class="card-tools">
                <a href="{{ route('backend.projectmanagement.index') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <table id="example_new" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Section Title</th>
                        <th>Display Order</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let table = $('#example_new').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('backend.getProjectSection') }}",
                type: 'GET',
                dataType: 'json',
            },
            columns: [{
                    data: 'section_title',
                    name: 'section_title'
                },
                {
                    data: 'display_order',
                    name: 'display_order'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });

        $(document).on('click', '#deleteProjectSection', function(e) {
            e.preventDefault();
            var deleteUrl = $(this).data('url');

            swal({
                    title: "Are you sure?",
                    text: "You want to delete this project section! Its questions will also be deleted.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: deleteUrl,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                if (res.success) {
                                    swal("Deleted!", "Your project section has been deleted.", "success");
                                    $('#example_new').DataTable().ajax.reload();
                                } else {
                                    swal("Oops!", "Something went wrong.", "error");
                                }
                            },
                            error: function(xhr) {
                                var message = (xhr.responseJSON && xhr.responseJSON.message) || "Failed to delete the project section.";
                                swal("Error!", message, "error");
                            }
                        });
                    } else {
                        swal("Cancelled", "Your project section is safe.", "info");
                    }
                });
        });
    });
</script>
@endsection
