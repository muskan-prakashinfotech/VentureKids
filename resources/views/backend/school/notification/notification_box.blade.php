@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1> {{ __('admin/trainer.announcements') }}</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active"> {{ __('admin/trainer.inbox') }}</li>
                        </ol>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <!-- Main content -->
        <section class="content">
            @if (session()->has('success'))
                <div class="alert alert-success" style="text-align: center;">
                    {{ session()->get('success') }}
                </div>
            @endif
            <div class="row">
                <div class="col-md-3">
                    <a href="{{ route('backend.school-compose') }}" class="mb-3 btn btn-primary btn-block">
                        {{ __('admin/trainer.compose') }}</a>

                    <div class="card">
                        <div class="p-0 card-body">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item active">
                                    <a href="{{ route('backend.school-notificationbox') }}" class="nav-link">
                                        <i class="fas fa-inbox"></i> Notification List
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
                <!-- /.col -->
                <div class="col-md-9">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('admin/trainer.announcements') }}</h3>
                            <a href="{{route('backend.dashboard')}}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                            <!-- /.card-tools -->
                        </div>
                        <!-- /.card-header -->
                        @livewire('admin.school.notification-box')
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
@endsection
