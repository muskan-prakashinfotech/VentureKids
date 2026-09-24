@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ __('admin/student_communication.assignment_list') }}</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('admin/student_communication.assignment_list') }}</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        @if (Session::has('message'))
            <div class="alert alert-success">
                {{ Session::get('message') }}
            </div>
        @endif
        
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

                        <a href="{{ route('backend.create-assignment') }}" class="btn btn-primary">+ Create Assigment</a>
                        <div class="card-tools">
                            <a href="{{ route('backend.dashboard')}}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        <table id="assignment-list" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>School</th>
                                    <th>Level</th>
                                    <th>Category</th>
                                    <th>Trainer</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($assignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment['title'] }}</td>
                                        <td>@if(!empty($assignment['school'])) {{$assignment['school']['school_name']}} @else All @endif</td>
                                        <td>{{ $assignment['level']['grade'] }}</td>
                                        <td>{{ $assignment->category === 'self_learning' ? 'Self Learning' : ucfirst($assignment->category) }}</td>
                                        <td>@if(!empty($assignment['trainer'])) {{$assignment['trainer']['trainer_name']}} @else All @endif</td>
                                        <td>
                                            <a type="button"
                                                href="{{ route('backend.edit-assignment', $assignment['id']) }}"
                                                class="btn btn-block btn-info btn-sm"><i class="fas fa-edit"></i></a>
                                            <a type="button"
                                                href="{{ route('backend.delete-assignment', $assignment['id']) }}"
                                                id="deleteAssignment" class="btn btn-block btn-danger btn-sm"><i
                                                    class="fas fa-trash-alt"></i></a>
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
        $(document).ready(function() {
            
            $('#assignment-list').DataTable();
            
            $(document).on('click', '#deleteAssignment', function(e) {
                e.preventDefault();
                var Id = $(this).attr('href');

                swal({
                        title: "Are you sure?",
                        text: "You want to delete this Assignment!",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            swal("Success! Your Assignment has been deleted!", {
                                icon: "success",
                            });

                            window.location.href = Id;

                        } else {
                            swal("Your Assignment is safe!");
                        }

                    });
            })
            
        });
    </script>
@endsection