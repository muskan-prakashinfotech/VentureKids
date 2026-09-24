@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">Standard Assessment Categories</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item active">Categories</li>
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
            @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session()->get('error') }}
                </div>
            @endif

            <a href="{{ route('backend.standard_assessment.categories.create') }}" class="btn btn-primary">Add Category</a>
            <div class="card-tools">
                <a href="{{ route('backend.dashboard') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <table id="standardCategoriesTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Level</th>
                        <th>Category Name</th>
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
        $('#standardCategoriesTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: {
                url: "{{ route('backend.standard_assessment.categories.data') }}",
                type: 'GET',
                dataType: 'json',
            },
            columns: [
                { data: 'level', name: 'level' },
                { data: 'category_name', name: 'category_name' },
                { data: 'status', name: 'status', searchable: false },
                { data: 'action', name: 'action', searchable: false },
            ]
        });

        $(document).on('click', '#deleteCategory', function(e) {
            e.preventDefault();
            var deleteUrl = $(this).data('url');

            swal({
                title: "Are you sure?",
                text: "You want to delete this category!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: deleteUrl,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.success) {
                                swal("Deleted!", "Category deleted successfully.", "success");
                                $('#standardCategoriesTable').DataTable().ajax.reload();
                            } else {
                                swal("Oops!", "Something went wrong.", "error");
                            }
                        },
                        error: function(xhr) {
                            var message = "Failed to delete category.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            swal("Error!", message, "error");
                        }
                    });
                }
            });
        });
    });
</script>
@endsection

