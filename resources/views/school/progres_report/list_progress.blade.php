@extends('backend.layouts.app')
@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->

    <div class="pageTitle">
        <h2>Student Progress Report</h2>
        <a href="{{ route('school.dashboard') }}" class="btn btn-sm btn-warning float-right"> <i class="material-icons">west</i> Back</a>
    </div>

    <!-- /.content-header -->

    <!-- Main content -->
    <section>
        <div class="container-fluid p-0">
            <div class="row">
                @include('partials.progress_report_tabs', [
                    'schoolId'                => null,
                    'routeGetProgressByGrade'  => route('school.getProgressByGrade'),
                    'routeGenerateLeaderBoard' => route('school.generateLeaderBoard'),
                    'routeGetRewardPointDetails' => route('school.getRewardPointDetails'),
                    'routeGetStudentObservations' => route('school.observations'),
                ])
            </div>
        </div>
            <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
@endsection
