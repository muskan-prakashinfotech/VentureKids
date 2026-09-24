@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Student Stream List</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Student Stream List</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">

                        @if (session()->has('success'))
                            <div class="alert alert-success" style="text-align: center;">
                                {{ session()->get('success') }}
                            </div>
                        @endif

                        @if (session()->has('update_success'))
                            <div class="alert alert-success" style="text-align: center;">
                                {{ session()->get('update_success') }}
                            </div>
                        @endif

                        @if (session()->has('suspend_success'))
                            <div class="alert alert-success" style="text-align: center;">
                                {{ session()->get('suspend_success') }}
                            </div>
                        @endif

                        <a href="{{ route('backend.stream.create') }}" class="btn btn-primary">Add Stream</a>
                        <div class="card-tools">
                            <a href="{{ route('backend.dashboard') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Level</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($streamData as $stream)
                                    <tr>
                                        <td>{{ $loop->index + 1 }}</td>
                                        <td>{{ $stream->title }}</td>
                                        <td>{{ (!empty($stream->agegroup)) ? $stream->agegroup->grade : '' }}</td>
                                        <td>{{ (strlen($stream->description) > 25) ? substr($stream->description, 0, 25).'...' : $stream->description  }}</td>
                                        <td class="d-flex" style="gap: 12px;">
                                            <a href="{{ route('backend.stream.edit', $stream->id) }}"
                                                class="btn btn-success"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('backend.stream.delete', $stream->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="deleteStream(event, this)" type="button"
                                                    class="btn btn-danger">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </section>
    </div>

    <script>
        function deleteStream(e, target) {
            e.preventDefault();
            var form = $(target).parents('form');
            swal({
                    title: "Are you sure?",
                    text: "Session/Content created for this Stream will not be displayed anywhere. Are you suere want to delete this Stream!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        swal("Success! Your Stream has been deleted!", {
                            icon: "success",
                        });
                        form.submit();
                    } else {
                        swal("Great! Stream records are safe.");
                    }
                });
        }
    </script>
@endsection
