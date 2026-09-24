@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">AI Tool List</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item active">AI Tool List</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>

    <div class="card">
         <div class="card-header">
                        @if (session()->has('success'))
                            <div class="alert alert-success">
                                {{ session()->get('success') }}
                            </div>
                        @endif

                        <a href="{{ route('backend.projectThemecreate.projectThemeCreate') }}" class="btn btn-primary">{{ __('Add Project Theme') }}</a>
                        <div class="card-tools">
                            <a href="{{ route('backend.dashboard') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                        </div>
        </div>
        <div class="card-body">
            <table id="example_new" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Project Theme Name</th>
                        <th>Grade</th>
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
                url: "{{ route('backend.getProjectTheme') }}",
                type: 'GET',
                dataType: 'json',
            },
            columns: [{
                    data: 'project_theme_name',
                    name: 'project_theme_name'
                },
                { 
                    data: 'grade',
                    name: 'grade' 
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

        // Delete logic (optional, ensure route + JS confirm)
        $(document).on('click', '#deleteProjectTheme', function(e) {
            e.preventDefault();
            var deleteUrl = $(this).data('url');

            swal({
                    title: "Are you sure?",
                    text: "You want to delete this project theme!",
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
                                    swal("Deleted!", "Your project theme has been deleted.", "success");
                                    $('#example_new').DataTable().ajax.reload(); // 🔁 reload your table
                                } else {
                                    swal("Oops!", "Something went wrong.", "error");
                                }
                            },
                            error: function() {
                                swal("Error!", "Failed to delete the project theme.", "error");
                            }
                        });
                    } else {
                        swal("Cancelled", "Your project theme is safe.", "info");
                    }
                });
        });
    });
</script>
@endsection