@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">Standard Assessment Questionnaire</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item active">Questionnaire</li>
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

            <a href="{{ route('backend.standard_assessment.questions.create') }}" class="btn btn-primary">Add Question</a>
            <div class="card-tools">
                <a href="{{ route('backend.dashboard') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3 row">
                <div class="col-md-4">
                    <label for="categoryFilter">Filter by Category</label>
                    <select id="categoryFilter" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ optional($category->grade)->grade }} - {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <table id="standardQuestionsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Question</th>
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
        var questionsTable = $('#standardQuestionsTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: {
                url: "{{ route('backend.standard_assessment.questions.data') }}",
                type: 'GET',
                dataType: 'json',
                data: function(d) {
                    d.category_id = $('#categoryFilter').val();
                }
            },
            columns: [
                { data: 'question', name: 'question' },
                { data: 'status', name: 'status', searchable: false },
                { data: 'action', name: 'action', searchable: false },
            ]
        });

        $('#categoryFilter').on('change', function() {
            questionsTable.ajax.reload();
        });

        $(document).on('click', '#deleteQuestion', function(e) {
            e.preventDefault();
            var deleteUrl = $(this).data('url');

            swal({
                title: "Are you sure?",
                text: "You want to delete this question!",
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
                                swal("Deleted!", "Question deleted successfully.", "success");
                                $('#standardQuestionsTable').DataTable().ajax.reload();
                            } else {
                                swal("Oops!", "Something went wrong.", "error");
                            }
                        },
                        error: function(xhr) {
                            var message = "Failed to delete question.";
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

