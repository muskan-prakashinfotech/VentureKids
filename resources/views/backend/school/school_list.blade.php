@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ __('admin.school_list') }}</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('admin.school_list') }}</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @php
                    $isPartner = isPartnerUser();
                @endphp
                <div class="card">
                    <div class="card-header">
                        @if (session()->has('message'))
                            <div class="alert alert-success">
                                {{ session()->get('message') }}
                            </div>
                        @endif
                        @if (Session::has('suspend_success'))
                            <div class="alert alert-success">
                                {{ Session::get('suspend_success') }}
                            </div>
                        @endif
                        <a href="{{ route('backend.schoolcreate.schoolCreate') }}" class="btn btn-primary">
                            + {{ __('admin.add_school') }}
                        </a>
                        <div class="card-tools">
                            <a href="{{route('backend.dashboard')}}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('admin.id') }}</th>
                                    <th>{{ __('admin.School_name') }}</th>
                                    <th>City</th>
                                    <th>{{ __('admin.students') }}</th>
                                    <th>Activity incharge</th>
                                    <th>{{ __('admin.status') }}</th>
                                    <th>{{ __('admin.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($school_list as $key => $row)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <!-- <td> <img src="../{{ $row->school_logo }}" alt="Product 1" class="mr-2 img-circle img-size-32"> </td> -->
                                        <td>{{ $row->school_name }}</td>
                                        <td style="word-break: break-all;">
                                            {{ $row->city }}
                                        </td>
                                        <td>{{ $row->is_pending ? $row->students_count : $row->students()->count() }}</td>
                                        <td>{{ $row->incharge_name }}</td>
                                        <td>
                                            @if ($row->is_pending)
                                                <small class="badge badge-warning">Approval Pending</small>
                                            @elseif (isset($row->user))
                                                @if ($row->user->suspend == 2)
                                                    <small class="badge badge-success">{{ __('admin.active') }}</small>
                                                @elseif($row->user->suspend == 1)
                                                    <small class="badge badge-danger">{{ __('admin.suspended') }}</small>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->is_pending)
                                                <small class="text-muted">No actions available until approval.</small>
                                            @else
                                            <a href="{{ route('backend.studentList.studentList', $row->id) }}"
                                                class="btn btn-block btn-secondary btn-sm school-action-list-btn">Student List</a>
                                            <!--                                             
                                            @php
                                                $deletestd = $row->students()->where('status','1')->get();
                                            @endphp
                                            @if(count($deletestd) > 0)
                                                <a href="{{ route('backend.studentDeleteRequest.studentDeleteRequest', $row->id) }}" class="btn btn-block btn-danger btn-sm">Student Delete Request</a>
                                            @endif
                                             -->
                                            @if (!$isPartner && isset($row->user))
                                                @if ($row->user->suspend == 1)
                                                    <a href="{{ route('backend.school-unsuspend', $row->user->id) }}"
                                                        id="unsuspend" class="btn btn-block btn-warning btn-sm"
                                                        title="Unuspend">
                                                        <i class="fas fa-user-lock"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('backend.school-suspend', $row->user->id) }}"
                                                        id="suspend" class="btn btn-block btn-success btn-sm school-action-suspend-btn"
                                                        title="Suspend">
                                                        <i class="fas fa-user-lock"></i>
                                                    </a>
                                                @endif
                                            @endif
                                            <a href="{{ route('backend.viewProgress', $row->id) }}"
                                                class="btn btn-block btn-primary btn-sm"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('backend.schooledit.schoolEdit', $row->id) }}"
                                                class="btn btn-block btn-info btn-sm school-action-edit-btn"><i class="fas fa-edit"></i></a>
                                            @if (!$isPartner)
                                                <a href="{{ route('backend.schooldelete.schoolDelete', $row->id) }}"
                                                    id="deleteSchool" class="btn btn-block btn-danger btn-sm"><i
                                                        class="fas fa-trash-alt"></i></a>
                                            @endif
                                            @endif
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
            //   alert('hello');

            //delete school sweetalert
            $(document).on('click', '#deleteSchool', function(e) {
                e.preventDefault();
                var Id = $(this).attr('href');

                swal({
                        title: "Are you sure?",
                        text: "You want to delete this file!",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            swal("Success! Your file has been deleted!", {
                                icon: "success",
                            });

                            window.location.href = Id;

                        } else {
                            swal("Great! School records are safe.");
                        }

                    });
            });

            //suspend sweetalert
            $(document).on('click', '#suspend', function(e) {
                e.preventDefault();
                var Id = $(this).attr('href');

                swal({
                        title: "Are you sure?",
                        text: "You want to suspend this school!",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            swal("Success! School successfully suspend!", {
                                icon: "success",
                            });

                            window.location.href = Id;

                        } else {
                            swal("Great! School records are safe.");
                        }

                    });
            });

            //unsuspend sweetalert
            $(document).on('click', '#unsuspend', function(e) {
                e.preventDefault();
                var Id = $(this).attr('href');

                swal({
                        title: "Are you sure?",
                        text: "You want to unsuspend this school!",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            swal("Success! School successfully unsuspend!", {
                                icon: "success",
                            });

                            window.location.href = Id;

                        } else {
                            swal("Your file is safe!");
                        }

                    });
            });


        });
    </script>
@endsection
