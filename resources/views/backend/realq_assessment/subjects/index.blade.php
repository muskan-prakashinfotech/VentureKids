@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Subject Management</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Subject Management</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @if (session()->has('success'))
                    <div class="alert alert-success" style="text-align: center;">
                        {{ session()->get('success') }}
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger" style="text-align: center;">
                        {{ session()->get('error') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <a href="{{ route('backend.realqassessment.subjects.create') }}" class="btn btn-primary">Add Subject</a>
                        <div class="card-tools">
                            <a href="{{ route('backend.realqassessment.index') }}" class="btn btn-warning">
                                <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subject Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subjects as $subject)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>{{ $subject->name }}</td>
                                        <td class="d-flex" style="gap: 12px;">
                                            <a href="{{ route('backend.realqassessment.subjects.edit', $subject->id) }}" class="btn btn-success">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($subject->can_delete)
                                                <form action="{{ route('backend.realqassessment.subjects.delete', $subject->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button onclick="deleteSubject(event, this)" type="button" class="btn btn-danger">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-danger" disabled title="Subject is in use and cannot be deleted.">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        function deleteSubject(e, target) {
            e.preventDefault();
            var form = $(target).parents('form');
            swal({
                    title: "Are you sure?",
                    text: "You want to delete this subject!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        swal("Deleted!", "Subject has been deleted.", "success");
                        form.submit();
                    } else {
                        swal("Cancelled", "Subject records are safe.");
                    }
                });
        }
    </script>
@endsection

