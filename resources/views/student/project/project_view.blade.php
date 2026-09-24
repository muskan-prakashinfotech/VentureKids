@extends('backend.layouts.app')
@section('content')

<!-- <link rel="stylesheet" href="{{ asset('asset/dist/css/swiper-bundle.min.css') }}"> -->
   <!-- Link Swiper's CSS -->
  <link rel="stylesheet" href="{{ asset('asset/dist/css/swiper-bundle.min.css') }}">
<link rel="stylesheet" href="{{ asset('asset/dist/css/jquery.fancybox.min.css') }}">

<div class="content-wrapper">
    <div class="pageTitle">
        <h2>{{ $project->student->name }} - Project Details</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">{{ $project->title }}</li>
        </ol>
    </div>
        
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <section>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-title">{{ $project->theme->project_theme_name ?? 'No Theme Assigned' }}</h2>
                @if($project->feedback && $project->feedback->is_publish == 1)
                    <button class="btn btn-sm border font-bold ml-auto view-feedback-btn" data-toggle="modal" data-target="#feedbackModal">
                        <img src="{{ asset('asset/dist/img/view-feedback.svg') }}" /> View Feedback
                    </button>
                @endif
                <a href="{{ route('student.list-project') }}" class="btn btn-sm btn-warning" style="font-weight: 600;">
                    <i class="material-icons">west</i> Back
                </a>
            </div>
        </div>

        <div class="card light-gradiant p-4 overflow-hidden">
            @if($project->feedback && $project->feedback->is_publish == 1)
            <div class="success-badge text-right">
                <span class="text-success"><img src="{{ asset('asset/dist/img/check.svg') }}" />Successfully Submitted on {{ \Carbon\Carbon::parse($project->updated_at)->format('F d, Y') }}</span>
            </div>
            @endif
            <div class="light-gradiant-card">
                <span>Project Title</span>
                <h2>{{ $project->title}}</h2>
                <div class="prj_detail_img">
                    <img src="{{ asset('asset/dist/img/project-detail-banner.svg') }}" />
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body new-text">
                <!-- Grade -->
                <div class="mb-3 add_project_heading">
                    <div class="icon project_details green">
                        <img src="{{ asset('asset/dist/img/project-detail-overview.svg') }}"/>
                    </div>
                    <div class="icon_text">
                        <h4>Project Overview</h4>
                    </div>
                </div>
                <div class="mb-3 discription">
                    <h4>About This Project</h4>
                    <p>{{$project->project_overview}}</p> 
                </div>
                <!-- slider -->
                
                @if(!empty($projectImages) && count($projectImages) > 0)
                    <div class="discription">
                        <p>Supporting Materials</p>
                    </div>

                    <!-- Swiper -->
                    <div class="swiper about_project_slider images-swiper">
                        <div class="swiper-wrapper">
                            @foreach($projectImages as $img)
                                <div class="swiper-slide">
                                    <div class="image">
                                        <img src="{{ asset($img['path']) }}"/>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                @endif
                <!-- slider end -->

                <!-- @if(!empty($projectAttachments) && count($projectAttachments) > 0)
                    @foreach($projectAttachments as $attachment)
                        <div class="col-lg-12 mt-2">
                            <div class="poster_link text-start mb-2">
                                <p><i class="fa fa-file-pdf"></i> <a href="{{ url($attachment['path']) }}" download>{{basename($attachment['path'])}}</a></p>
                            </div>
                        </div>
                    @endforeach
                @endif -->
            </div>
        </div>

        <div class="card">
            <div class="card-body new-text">
                <!-- Grade -->
                <div class="mb-3 add_project_heading">
                    <div class="icon project_details green">
                        <img src="{{ asset('asset/dist/img/Step-by-Step-Process-green.svg') }}"/>
                    </div>
                    <div class="icon_text">
                        <h4>Step-by-Step Process</h4>
                    </div>
                </div>
                <div class="mb-3 discription">
                    <p>{{$project->project_skills}}</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body new-text">
                <!-- Grade -->
                <div class="mb-3 add_project_heading">
                    <div class="icon project_details green">
                        <img src="{{ asset('asset/dist/img/Challenges-Reflections-green.svg') }}"/>
                    </div>
                    <div class="icon_text">
                        <h4>Challenges & Reflections</h4>
                    </div>
                </div>
                <div class="mb-3 discription">
                    <h4>Challenges Faced</h4>
                    <p>{{$project->project_challenges}}</p>
                </div>
                <div class="mb-3 discription">
                    <h4>What I Would Do Differently</h4>
                    <p>{{$project->project_reflection}}</p>
                </div>
            </div>
        </div>
        @if($project->feedback && $project->feedback->is_publish == 1)
        <div class="d-flex align-items-center justify-content-between mt-5">
            <span>Great work! Your project has been successfully submitted.</span>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-primary btn-gradiant" data-toggle="modal" data-target="#feedbackModal">
                    View Feedback
                </button>
            </div>
        </div>
        @endif     
    </section>
</div>

<!-- Feedback Modal - -->
@if($project->feedback && $project->feedback->is_publish == 1)
<div class="modal fade" id="feedbackModal" tabindex="-1" role="dialog" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="feedbackContent">
                <div class="text-center py-5">
                    <div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="{{ asset('asset/dist/js/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('asset/dist/js/jquery.fancybox.min.js') }}"></script>

<script>
    // Initialize Swiper
    var swiper = new Swiper(".about_project_slider", {
      spaceBetween: 30,
      pagination: {
        el: ".about_project_slider .swiper-pagination",
        clickable: true,
      },
      breakpoints: {
        640: {
          slidesPerView: 1,
          spaceBetween: 30,
        },
        768: {
          slidesPerView: 2,
          spaceBetween: 30,
        },
        1024: {
          slidesPerView: 3,
          spaceBetween: 30,
        },
        1399: {
          slidesPerView: 4,
          spaceBetween: 30,
        },
      },
    });

    @if(!empty($project->feedback))
    $(document).ready(function() {
        // Load feedback content when modal is shown
        $('#feedbackModal').on('show.bs.modal', function (e) {
            let modal = $(this);
            let modalContent = $('#feedbackContent');

            // Show loading state
            modalContent.html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0">Loading feedback...</p>
                </div>
            `);

            // Load feedback via AJAX
            $.ajax({
                url: '{{ route("student.view-feedback", $project->feedback->id) }}',
                type: 'GET',
                success: function(response) {
                    console.log('Feedback loaded successfully');
                    modalContent.html(response);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading feedback:', error);
                    modalContent.html(`
                        <div class="alert alert-danger m-4">
                            <h5>Error Loading Feedback</h5>
                            <p>Failed to load feedback. Please try again.</p>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    `);
                }
            });
        });

        // Cleanup when modal is closed
        $('#feedbackModal').on('hidden.bs.modal', function () {
            console.log('Modal closed, cleaning up');
            // Destroy chart instance if exists
            if (window.studentSpiderChartInstance) {
                window.studentSpiderChartInstance.destroy();
                window.studentSpiderChartInstance = null;
            }
            // Reset modal content
            $('#feedbackContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0">Loading feedback...</p>
                </div>
            `);
        });
    });
    @endif
</script>

@endsection
