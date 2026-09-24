@extends('backend.layouts.app')

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('admin/event.student_challenge_list') }}</h1>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    @if (session()->has('message'))
                        <div class="alert alert-success">
                            {{ session()->get('message') }}
                        </div>
                    @endif
                    <div class="card-tools">
                        <a href="{{ URL::previous() }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('admin.School_name') }}</th>
                                <th>{{ __('admin.student_name') }}</th>
                                <th>{{ __('admin.description') }}</th>
                                <th>Files</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($studentChallengeList as $key => $studentChallenge)
                            <tr>
                                <td>{{$studentChallenge['schoolName']}}</td>        
                                <td>{{$studentChallenge['studentName']}}</td>        
                                <td><?= strip_tags($studentChallenge['description']) ?></td>        
                                <td>@if ($studentChallenge['attachmentId']) <a href="{{ route('backend.attachmentdownload.attachmentdownload', $studentChallenge['eventChallengeId']) }}">Download</a> @endif</td>        
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

@endsection