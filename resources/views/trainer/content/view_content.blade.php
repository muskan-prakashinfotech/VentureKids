@extends('backend.layouts.app')
@section('content')

<!-- Content Wrapper. Contains page content -->
<link rel="stylesheet" href="{{asset('asset/dist/css/lightbox.min.css')}}" />

<!-- swiper CSS -->
<link rel="stylesheet" href="{{asset('asset/dist/css/swiper-bundle.min.css')}}">

<div class="content-wrapper">
    <!-- <div class="pageTitle">
        <h2>{{ __('admin/content.view_content') }}</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">{{ __('admin/content.home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin/content.view_content') }}</li>
        </ol>
    </div> -->

    <!-- Main content -->
    <section>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{$content['title']}}</h3>
                <a href="{{ route('trainer.contentlist.streamlist', $content['agegroup_id']) }}" class="btn btn-sm btn-warning">
                    <i class="material-icons">west</i>
                    Back
                </a>
            </div>
            <!-- /.card-header -->
            <div class="card-body ContentVideo">
                @if(!empty($content['video']) || !empty($content['video_url']))
                <div class="row">
                    <div class="col-12">
                        @if(!empty($content['video']))
                            <video width="100%" controls>
                                <source src="{{ url('/video/content/trainer/' . $content['video']) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                            </video>
                        @elseif(!empty($content['video_url']))
                            <iframe src="{{ str_replace('watch?v=', 'embed/', $content['video_url']) }}" title="YouTube video player"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            frameborder="0" width="100%" height="600" allowfullscreen></iframe>
                        @endif
                    </div>
                    @if (!$viewed)
                    <div class="col-12">
                        <div class="d-flex justify-content-end mt-3">
                            <a href="{{ route('trainer.contentview.finish', $content['id']) }}" class="btn btn-sm btn-orange text-normal">I finished this video <i class="material-icons">east</i></a>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <div class="row">
                    <div class="col-12 trainer-detail-odd-even mb-5">
                        <div class="row">
                            @if(!empty($content['learning_object']))
                            <div class="col-md-6 trainer-box mt-3">
                                <div class="description-info">
                                    <h5>Learning Objective</h5>
                                    {!! $content['learning_object'] !!}
                                </div>
                            </div>
                            @endif
                            @if(!empty($content['outcome_session']))
                            <div class="col-md-6 trainer-box mt-3">
                                <div class="description-info">
                                    <h5>Outcome of session</h5>
                                    {!! $content['outcome_session'] !!}
                                </div>
                            </div>
                            @endif
                            @if(!empty($content['question_access_knowledge']))
                            <div class="col-md-6 trainer-box mt-3">
                                <div class="description-info">
                                    <h5>Questions to assess prior knowledge</h5>
                                    {!! $content['question_access_knowledge'] !!}
                                </div>
                            </div>
                            @endif
                            @if(!empty($content['introduce_topic_student']))
                            <div class="col-md-6 trainer-box mt-3">
                                <div class="description-info">
                                    <h5>How to introduce the topic to the students</h5>
                                    {!! $content['introduce_topic_student'] !!}
                                </div>
                            </div>
                            @endif
                            @if(!empty($content['related_activity_one']))
                            <div class="col-md-6 trainer-box mt-3 ">
                                <div class="description-info">
                                    <h5>Related Activity 1</h5>
                                    {!! $content['related_activity_one'] !!}
                                </div>
                            </div>
                            @endif
                            @if(!empty($content['related_activity_two']))
                            <div class="col-md-6 trainer-box mt-3 ">
                                <div class="description-info">
                                    <h5>Related Activity 2</h5>
                                    {!! $content['related_activity_two'] !!}
                                </div>
                            </div>
                            @endif
                            @if(!empty($content['vocabulary']))
                            <div class="col-md-6 trainer-box mt-3 ">
                                <div class="description-info">
                                    <h5>Vocabulary</h5>
                                    {!! $content['vocabulary'] !!}
                                </div>
                            </div>
                            @endif
                            @if(!empty($content['tips_of_parents']))
                            <div class="col-md-6 trainer-box mt-3">
                                <div class="description-info">
                                    <h5>Tips for parents/home assignments</h5>
                                    {!! $content['tips_of_parents'] !!}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if(!empty($content['pdf']))
                <div class="row">
                    <div class="col-12">
                        <div class="description-info pdf-wrap mt-0">
                            <h4>Lesson Plan</h4>
                            <h6><i class="fa fa-file-pdf"></i> <a href="{{ url('/files/content/trainer/' . $content['pdf']) }}" download> {{$content['pdf_name']}} </a></h6>
                        </div>
                    </div>
                </div>
                @endif
                
                @if(!empty($content['worksheet']))
                <div class="row">
                    <div class="col-12">
                        <hr></hr>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="description-info pdf-wrap mt-0">
                            <h4>Workbook</h4>
                            <h6><i class="fas fa-paperclip"></i> <a href="{{ url('/files/content/trainer/' . $content['worksheet']) }}" download> {{$content['worksheet_name']}} </a></h6>
                        </div>
                    </div>
                </div>
                @endif

                @if(!empty($content['session_presentation']))
                <div class="row">
                    <div class="col-12">
                        <hr></hr>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="description-info pdf-wrap mt-0">
                            <h4>Lesson Presentation</h4>
                            <h6><i class="fas fa-paperclip"></i> <a href="{{ url('/files/content/trainer/' . $content['session_presentation']) }}" download> {{$content['session_presentation_name']}} </a></h6>
                        </div>
                    </div>
                </div>
                @endif
                
                @if(!empty($content['drive_url']))
                <div class="row">
                    <div class="col-12">
                        <hr></hr>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="description-info pdf-wrap mt-0">
                            <h4>Other Resources</h4>
                            <h6><i class="fa fa-link"></i> <a href="{{$content['drive_url']}}" target="_blank"> {{$content['drive_url']}} </a></h6>
                        </div>
                    </div>
                </div>
                @endif
                
                @if(!empty($content['trainer_content_images']))
                <div class="row">
                    <div class="col-12">
                        <hr></hr>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="swiper mySwiper">
                            <div class="swiper-wrapper">
                                @foreach($content['trainer_content_images'] as $image)
                                    @php
                                        $attachment = $image['attachment'];
                                    @endphp    
                                    @if(!empty($attachment) && in_array(strtolower(pathinfo($attachment)['extension']),['jpeg', 'jpg', 'png']))
                                        <div class="swiper-slide">
                                            <div class="gallery">
                                                <a data-fslightbox="gallery" href="{{url('/files/content/trainer/'.$attachment)}}">
                                                    <img src="{{url('/files/content/trainer/'.$attachment)}}" />
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
            <!-- /.card-body -->
        </div>
    </section>
</div>

<script src="{{asset('asset/dist/js/swiper-bundle.min.js')}}"></script>
<script src="{{asset('asset/dist/js/fslightbox.js')}}"></script>
<script>
    var swiper = new Swiper(".mySwiper", {
      slidesPerView: 1,
      spaceBetween: 20,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      loop: true,
      breakpoints: {
        640: {
          slidesPerView: 2,
          spaceBetween: 20,
        },
        768: {
          slidesPerView: 3,
          spaceBetween: 30,
        },
        1024: {
          slidesPerView: 4,
          spaceBetween: 30,
        },
      },
    });
</script>

@endsection