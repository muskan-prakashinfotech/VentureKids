@extends('backend.layouts.app')
@section('content')

<link rel="stylesheet" href="{{asset('asset/dist/css/lightbox.min.css')}}" />

<!-- swiper CSS -->
<link rel="stylesheet" href="{{asset('asset/dist/css/swiper-bundle.min.css')}}">

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- <div class="pageTitle">
        <h2>{{ __('admin/content.content_list_trainer') }}</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">{{ __('admin/content.home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin/content.content_list_trainer') }}</li>
        </ol>
    </div> -->

    <!-- Main content -->
    <section>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $grade->grade }}</h3>
                <a href="{{ route('trainer.content/list.contentList') }}" class="btn btn-sm btn-warning">
                    <i class="material-icons">west</i>
                    Back
                </a>
            </div>

            <div class="card-body">
                <div class="topics-scroll-tab">
                    <div class="row">
                        @if($streams->count())
                        <div class="col-12 scroll-container-wrapper">
                            <div class="scroll-tab-container">
                                <button class="scroll-arrow left" id="scrollLeft">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <div class="tabs-wrapper" id="tabsWrapper">
                                    <div class="nav nav-tabs scroll-tab pb-0" id="trainer" role="tablist">
                                        @forelse ($streams as $stream)
                                            <button @class(['nav-item nav-link btn text-left', 'active'=> $loop->first])
                                            id="trainer-tab-{{ $stream->id }}-tab"
                                            data-toggle="pill" data-target="#trainer-tab-{{ $stream->id }}" type="button"
                                            role="tab" aria-controls="trainer-tab-{{ $stream->id }}"
                                            aria-selected="true">{{ $stream->title }}
                                            </button>
                                        @empty
                                        @endforelse
                                    </div>
                                </div>
                                <button class="scroll-arrow right" id="scrollRight">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="tab-content" id="trainer" role="tablist">
                                @forelse ($streams as $stream)
                                <div @class(['tab-pane fade', 'show active'=> $loop->first]) id="trainer-tab-{{ $stream->id }}"  role="tabpanel" aria-labelledby="trainer-tab-{{ $stream->id }}-tab">
                                    @php
                                    $maincontents = $contents->where('stream_id', $stream->id)->all();
                                    @endphp
                                    <ul class="beginner-cardList stream-sessionList new_design_three_col trainer_new row w-100">
                                        @forelse ($maincontents as $content)
                                        <li @if(!$content['hasAccess']) class="bg-color-unset" @endif>
                                            <div class="detailsInfo @if(!$content['hasAccess']) stream-list-lock @endif">
                                                <h2>{{ $content->title }}</h2>
                                                <div class="btns-group d-flex flex-wrap justify-content-end" aria-label="Basic example">
                                                    <a href="{{ route('trainer.contentview.contentView', $content['id']) }}" class="btn btn-sm btn-light">
                                                        <span>View Content</span>
                                                        <i class="material-icons">east</i>
                                                    </a>
                                                </div>
                                            </div>
                                        </li>
                                        @empty
                                        @endforelse
                                    </ul>
                                    <div class="card-body ContentVideo">
                                        @if(!empty($stream->video) || !empty($stream->video_url))
                                        <div class="row">
                                            <div class="col-12">
                                                @if(!empty($stream->video))
                                                    <video width="100%" controls>
                                                        <source src="{{ url('/video/stream/trainer/' . $stream->video) }}" type="video/mp4">
                                                            Your browser does not support the video tag.
                                                    </video>
                                                @elseif(!empty($stream->video_url))
                                                    <iframe src="{{ str_replace('watch?v=', 'embed/', $stream->video_url) }}" title="YouTube video player"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    frameborder="0" width="100%" height="600" allowfullscreen></iframe>
                                                @endif
                                            </div>
                                        </div>
                                        @endif

                                        <div class="row">
                                            <div class="col-12 trainer-detail-odd-even mb-5">
                                                <div class="row">
                                                    @if(!empty($stream->learning_object))
                                                    <div class="col-md-6 trainer-box mt-3">
                                                        <div class="description-info">
                                                            <h5>Learning Objective</h5>
                                                            {!! $stream->learning_object !!}
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @if(!empty($stream->outcome_session))
                                                    <div class="col-md-6 trainer-box mt-3">
                                                        <div class="description-info">
                                                            <h5>Outcome of session</h5>
                                                            {!! $stream->outcome_session !!}
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @if(!empty($stream->question_prior_knowledge))
                                                    <div class="col-md-6 trainer-box mt-3">
                                                        <div class="description-info">
                                                            <h5>Questions to assess prior knowledge</h5>
                                                            {!! $stream->question_prior_knowledge !!}
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @if(!empty($stream->introduce_topic))
                                                    <div class="col-md-6 trainer-box mt-3">
                                                        <div class="description-info">
                                                            <h5>How to introduce the topic to the students</h5>
                                                            {!! $stream->introduce_topic !!}
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @if(!empty($stream->related_activity_one))
                                                    <div class="col-md-6 trainer-box mt-3 ">
                                                        <div class="description-info">
                                                            <h5>Related Activity 1</h5>
                                                            {!! $stream->related_activity_one !!}
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @if(!empty($stream->related_activity_two))
                                                    <div class="col-md-6 trainer-box mt-3 ">
                                                        <div class="description-info">
                                                            <h5>Related Activity 2</h5>
                                                            {!! $stream->related_activity_two !!}
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @if(!empty($stream->vocabulary))
                                                    <div class="col-md-6 trainer-box mt-3 ">
                                                        <div class="description-info">
                                                            <h5>Vocabulary</h5>
                                                            {!! $stream->vocabulary !!}
                                                        </div>
                                                    </div>
                                                    @endif
                                                    @if(!empty($stream->home_assignments))
                                                    <div class="col-md-6 trainer-box mt-3">
                                                        <div class="description-info">
                                                            <h5>Tips for parents/home assignments</h5>
                                                            {!! $stream->home_assignments !!}
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if(!empty($stream->pdf))
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="description-info pdf-wrap mt-0">
                                                    <h4>Lesson Plan</h4>
                                                    <h6><i class="fa fa-file-pdf"></i> <a href="{{ url('/files/stream/trainer/' . $stream->pdf) }}" download> {{$stream->pdf_name}} </a></h6>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        @if(!empty($stream->worksheet))
                                        <div class="row">
                                            <div class="col-12">
                                                <hr></hr>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="description-info pdf-wrap mt-0">
                                                    <h4>Workbook</h4>
                                                    <h6><i class="fas fa-paperclip"></i> <a href="{{ url('/files/stream/trainer/' . $stream->worksheet) }}" download> {{$stream->worksheet_name}} </a></h6>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        @if(!empty($stream->drive_url))
                                        <div class="row">
                                            <div class="col-12">
                                                <hr></hr>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="description-info pdf-wrap mt-0">
                                                    <h4>Other Resources</h4>
                                                    <h6><i class="fa fa-link"></i> <a href="{{$stream->drive_url}}" target="_blank"> {{$stream->drive_url}} </a></h6>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        @if($stream->trainerStreamImages->count())
                                        <div class="row">
                                            <div class="col-12">
                                                <hr></hr>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="swiper mySwiper">
                                                    <div class="swiper-wrapper">
                                                        @foreach($stream->trainerStreamImages as $image)
                                                            @php
                                                                $attachment = $image['attachment'];
                                                            @endphp    
                                                            @if(!empty($attachment) && in_array(strtolower(pathinfo($attachment)['extension']),['jpeg', 'jpg', 'png']))
                                                                <div class="swiper-slide">
                                                                    <div class="gallery">
                                                                        <a data-fslightbox="gallery" href="{{url('/image/stream/trainer/'.$attachment)}}">
                                                                            <img src="{{url('/image/stream/trainer/'.$attachment)}}" />
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
                                </div>
                                @empty
                                @endforelse
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
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
    
    $(document).ready(function() {
        const tabsContainer = $('#trainer');
        const tabsWrapper = $('#tabsWrapper');
        const scrollLeftBtn = $('#scrollLeft');
        const scrollRightBtn = $('#scrollRight');
        
        function updateScrollButtons() {
            const scrollLeft = tabsContainer.scrollLeft();
            const scrollWidth = tabsContainer[0].scrollWidth;
            const clientWidth = tabsContainer[0].clientWidth;
            
            // Update button states
            scrollLeftBtn.prop('disabled', scrollLeft <= 0);
            scrollRightBtn.prop('disabled', scrollLeft >= scrollWidth - clientWidth - 1);
            
            // Update fade indicators
            tabsWrapper.toggleClass('can-scroll-left', scrollLeft > 0);
            tabsWrapper.toggleClass('can-scroll-right', scrollLeft < scrollWidth - clientWidth - 1);
        }
        
        function scrollTabs(direction) {
            const scrollAmount = 200;
            const currentScroll = tabsContainer.scrollLeft();
            const newScroll = direction === 'left' 
                ? currentScroll - scrollAmount 
                : currentScroll + scrollAmount;
            
            tabsContainer.animate({
                scrollLeft: newScroll
            }, 300, updateScrollButtons);
        }
        
        // Scroll button events
        scrollLeftBtn.click(function() {
            scrollTabs('left');
        });
        
        scrollRightBtn.click(function() {
            scrollTabs('right');
        });
        
        // Update buttons on scroll
        tabsContainer.on('scroll', updateScrollButtons);
        
        // Update buttons on window resize
        $(window).resize(updateScrollButtons);
        
        // Initial update
        setTimeout(updateScrollButtons, 100);
        
        // Auto-scroll to active tab when clicked
        $('.nav-link').click(function() {
            setTimeout(() => {
                const activeTab = $(this);
                const tabPosition = activeTab.position().left;
                const containerWidth = tabsContainer.width();
                const tabWidth = activeTab.outerWidth();
                
                if (tabPosition < 0 || tabPosition + tabWidth > containerWidth) {
                    const scrollTo = tabsContainer.scrollLeft() + tabPosition - (containerWidth / 2) + (tabWidth / 2);
                    tabsContainer.animate({
                        scrollLeft: scrollTo
                    }, 300, updateScrollButtons);
                }
            }, 50);
        });
    });
</script>
@endsection