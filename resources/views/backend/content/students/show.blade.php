@extends('backend.layouts.app')
@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ $studentscontents->title }}</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('admin/content.view_content') }}</li>
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
                        <a href="{{ route('backend.addcontent.contentListStudents') }}" class="btn btn-primary"> All Content</a>
                        <div class="card-tools">
                            <a @if(!empty($studentscontents->worksheet)) href="{{ url('/files/content/' . $studentscontents->worksheet) }}" @else href="javascript:void(0)" @endif
                                    class="btn btn-warning" download>
                                    <i class="fas fa-file-pdf"></i>
                                    <span>Download Worksheet</span>
                            </a>
                            @if($isQuizPublish)
                            <a href="{{ route('backend.quizResult', $studentscontents->id) }}" class="btn btn-sm btn-primary"> View Quiz Result</a>
                            @endif
                            <a href="{{ URL::previous() }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    @if(!empty($studentscontents->video_url) || !empty($studentscontents->video))
                    <div class="card-body">
                        <div class="row">
                            @if (isset($studentscontents->video_url))
                                <div class="embed-responsive embed-responsive-16by9">
                                    <iframe src="{{ $studentscontents->video_url }}" title="YouTube video player"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        frameborder="0" width="100%" height="600" allowfullscreen></iframe>
                                </div>
                            @else
                                <video width="100%" controls>
                                    <source src="{{ url('/video/content/' . $studentscontents->video) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            @endif
                        </div>
                    </div>
                    @endif
                    <!-- /.card-body -->
                </div>
            </div>
        </section>
    </div>
@endsection
