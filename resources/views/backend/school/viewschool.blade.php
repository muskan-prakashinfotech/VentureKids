@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Profile</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Profile</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="mb-4 text-center row justify-content-center">
                    <div class="col-sm-11 d-flex justify-content-center" >
                        <a href="{{ route('backend.schooledit.schoolEdit', $school->id) }}" class="m-2 btn btn-lg btn-info">
                            <i class=" fas fa-user"></i> Edit Profile
                        </a>
                    </div>
                    <div class="col-sm-1">
                        <a href="{{ route('backend.schoollist.schoolList') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                    </div>
                </div>
                <div class="mb-4 overflow-hidden card card-widget widget-user">
                    <div class="position-relative">
                        <div class="w-100 h-100 position-relative d-flex justify-content-center">
                            <img src="{{ asset($school->school_cover_image) }}" class="w-100 h-100"
                                style="object-fit: fill; aspect-ratio: 10 / 3;">
                        </div>
                        <img class="position-absolute rounded-circle" src="{{ asset($school->school_logo) }}"
                            alt="User Avatar"
                            style="height: 80px;width:90px;top: calc(50% - 32px); left: calc(50% - 32px);">
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="text-center col-sm-12">
                                <h3 class="widget-user-username text-primary"><strong>{{ $school->school_name }}</strong>
                                </h3>
                                <h4 class="widget-user-username">{{ $school->school_address }}</h4>
                                <h5 class="widget-user-desc">{{ $school->year_establish }}</h5>
                            </div>
                            <div class="col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">{{ $school->principle_name }}</h5>
                                    <span class="description-text">Principal </span>
                                </div>
                                <!-- /.description-block -->
                            </div>

                            <!-- /.col -->
                            <div class="col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">{{ $school->number_of_student }}</h5>
                                    <span class="description-text">Students</span>
                                </div>
                                <!-- /.description-block -->
                            </div>
                            <!-- /.col -->
                            <!-- /.col -->

                            <div class="text-center col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">{{ $school->user->email }}</h5>
                                    <span class="description-text"> Email ID </span>
                                </div>
                            </div>
                            <div class="text-center col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">{{ $school->contact_number }}</h5>
                                    <span class="description-text">Contact Number</span>
                                </div>
                            </div>

                            <div class="text-center col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">{{ $school->incharge_name }}</h5>
                                    <span class="description-text">Activity In charge Name</span>
                                </div>
                            </div>
                            <div class="text-center col-sm-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">{{ $school->venturekids_representative }}</h5>
                                    <span class="description-text">VentureKids Representative</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->

    </div>
@endsection
