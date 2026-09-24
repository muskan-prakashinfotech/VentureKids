@extends('backend.layouts.app')
@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ $content['title'] }}</h1>
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
                        <a href="{{ route('backend.contentlist.contentList') }}" class="btn btn-primary"> All Content</a>
                        <div class="card-tools">
                            <a href="{{ URL::previous() }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-7">
                                @if (isset($content['video_url']) && empty($content['video']))
                                    <div class="embed-responsive embed-responsive-16by9">
                                        <iframe src="{{ $content['video_url'] }}" title="YouTube video player" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" frameborder="0" width="100%" height="600" allowfullscreen></iframe>
                                    </div>
                                @else
                                    <video width="100%" controls>
                                        <source src="{{ url('video/content/trainer/' . $content['video']) }}" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                @endif
                                <div class="d-flex align-items-center flex-wrap">
                                    @if($content['worksheet'])
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ url('/files/content/'.$content['worksheet']) }}" target="_blank" class="btn btn-warning" download>
                                            <i class="fas fa-file-pdf"></i>
                                            <span>Download Worksheet</span>
                                        </a>
                                    </div>
                                    @endif

                                    @if($content['pdf'])
                                    <div class="d-flex justify-content-between align-items-center ml-2">
                                        <a href="{{ url('/trainer/content/pdf-view/'.$content['id']) }}" target="_blank" class="btn btn-warning">
                                            <i class="material-icons">picture_as_pdf</i>
                                            <span>Download PDF</span>
                                        </a>
                                    </div>
                                    @endif
                                    
                                    @if($content->hasContentImages())
                                    <div class="d-flex justify-content-between align-items-center ml-2">
                                        <a href="{{ route('trainer.imagesdownload.imagesdownload', $content['id']) }}"
                                            class="btn btn-warning">
                                            <i class="material-icons">picture_as_pdf</i>
                                            <span>Download Images</span>
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-5">
                                <h5><b>Learning Objective</b></h5>
                                {!! $content['learning_object'] !!}
                                <h5><b>Outcome of session</b></h5>
                                {!! $content['outcome_session'] !!}
                                <h5><b>Questions to assess prior knowledge</b></h5>
                                {!! $content['question_access_knowledge'] !!}
                                <h5><b>How to introduce the topic to the students</b></h5>
                                {!! $content['introduce_topic_student'] !!}
                                <h5><b>Related Activity 1</b></h5>
                                {!! $content['related_activity_one'] !!}
                                <h5><b>Related Activity 2</b></h5>
                                {!! $content['related_activity_two'] !!}
                                <h5><b>Vocabulary</b></h5>
                                {!! $content['vocabulary'] !!}
                                <h4><b>Tips for parents/home assignments</b></h4>
                                {!! $content['tips_of_parents'] !!}
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </section>
    </div>
@endsection
