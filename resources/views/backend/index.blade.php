@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ __('admin/dashboard.dashboard') }}</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item active">{{ __('admin/dashboard.dashboard') }}</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->
        <?php //echo '<pre>'; print_r($data['total_school']);die();
        ?>
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <!-- small box -->
                        <div class="small-box bg-warning dashboard-stat-schools">
                            <div class="inner">
                                <h3>{{ $data['total_school'] }}</h3>
                                <p>Schools</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-school"></i>
                            </div>
                            <a href="{{ route('backend.schoolcreate.schoolCreate') }}" class="small-box-footer">Add
                                School <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- small box -->
                        <div class="small-box bg-info dashboard-stat-trainers">
                            <div class="inner">
                                <h3>{{ $data['total_trainer'] }}</h3>
                                <p>Trainers </p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <a href="{{ route('backend.addtrainer.addTrainer') }}" class="small-box-footer">Add
                                Trainer <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <!-- small box -->
                        <div class="small-box bg-success dashboard-stat-students">
                            <div class="inner">
                                <h3>{{ $data['total_student'] }}</h3>
                                <p>Students</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <a href="{{ route('backend.schoollist.schoolList') }}" class="small-box-footer">Add Student<i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="border-transparent card-header">
                                <h3 class="card-title">Recently Added Schools</h3>
                            </div>
                            <div class="p-0 card-body">
                                <div class="table-responsive">
                                    <table class="table m-0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>School Name</th>
                                                <th>Status</th>
                                                <th>Country</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($data['latest_ten_school'] as $key => $schools)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $schools['school_name'] }}</td>
                                                    <td>
                                                        @if ($schools['user']['suspend'] == 2)
                                                            <span class="badge badge-success">{{ __('admin.active') }}</span>
                                                        @elseif($schools['user']['suspend'] == 1)
                                                            <span class="badge badge-danger">{{ __('admin.suspended') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="sparkbar" data-color="#00a65a" data-height="20">
                                                            {{ !empty($schools['country_id']) ? $data['country'][$schools['country_id']]['name'] : '' }}</div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
