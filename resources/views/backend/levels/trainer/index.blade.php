@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Trainer Level List</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Trainer Level List</li>
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

                        <a href="{{ route('backend.trainerlevel.create') }}" class="btn btn-primary">Add Level</a>
                        <div class="card-tools">
                            <a href="{{ route('backend.dashboard')}}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Unique Code</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($levels as $level)
                                    <tr>
                                        <td>{{ $level->unique_code }}</td>
                                        <td>
                                            <img src="{{ asset('image/level/trainer/' . $level->image) }}" alt="{{ $level->grade }}"
                                                style="height: 200px; width:200px;">
                                        </td>
                                        <td>{{ $level->grade }}</td>
                                        <td class="d-flex" style="gap: 12px;">
                                            <a href="{{ route('backend.trainerlevel.edit', $level->id) }}"
                                                class="btn btn-success"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('backend.trainerlevel.delete', $level->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button onclick="deleteLevel(event, this)" type="button"
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
        function deleteLevel(e, target) {
            e.preventDefault();
            var form = $(target).parents('form');
            console.log(form);
            swal({
                    title: "Are you sure?",
                    text: "You want to delete this level!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        swal("Success! Your level has been deleted!", {
                            icon: "success",
                        });
                        form.submit();
                    } else {
                        swal("Great! Level records are safe.");
                    }
                });
        }
    </script>
@endsection
