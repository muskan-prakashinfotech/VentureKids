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
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
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
                @if($project->feedback && $project->feedback->is_publish==0)
                    <button class="btn btn-sm border font-bold ml-auto" id="openEditFeedbackModal" data-id="{{ $project->feedback->id }}">
                        Edit Feedback
                    </button>
                @elseif($project->feedback && in_array((int) $project->feedback->is_publish, [1, 2], true))
                    <button class="btn btn-sm border font-bold ml-auto view-feedback-btn" data-id="{{ $project->feedback->id }}">
                        <img src="{{ asset('asset/dist/img/view-feedback.svg') }}" /> View Feedback
                    </button>
                @else
                    <form id="generateFeedbackForm" action="{{ route('trainer.generate-feedback', $project->id) }}" method="POST" class="ml-auto">
                        @csrf
                        <button type="submit" id="generateFeedbackBtn" class="btn btn-sm btn-success text-white font-bold">
                            Generate Feedback
                        </button>
                    </form>
                @endif
                <a href="{{ route('trainer.list-project') }}" class="btn btn-sm btn-warning" style="font-weight: 600;">
                    <i class="material-icons">west</i> Back
                </a>
            </div>
        </div>

        <div class="card light-gradiant p-4 overflow-hidden">
            <div class="success-badge text-right">
                <span class="text-success"><img src="{{ asset('asset/dist/img/check.svg') }}" />Successfully Submitted on {{ \Carbon\Carbon::parse($project->updated_at)->format('F d, Y') }}</span>
            </div>
            <div class="light-gradiant-card">
                <span>Project Title</span>
                <h2>{{ $project->title }}</h2>
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
                
                @if(!empty($images) && count($images) > 0)
                    <div class="discription">
                        <p>Supporting Materials</p>
                    </div>

                    <!-- Swiper -->
                    <div class="swiper about_project_slider images-swiper">
                        <div class="swiper-wrapper">
                            @foreach($images as $img)
                                <div class="swiper-slide">
                                    <div class="image">
                                        <img src="{{ $img }}"/>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                @endif
                <!-- slider end -->
                <!-- @if(!empty($pdfs) && count($pdfs) > 0)
                    @foreach($pdfs as $attachment)
                        <div class="col-lg-12 mt-2">
                            <div class="poster_link text-start mb-2">
                                <p><i class="fa fa-file-pdf"></i> <a href="{{ url($attachment) }}" download>{{basename($attachment)}}</a></p>
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
        
        @if($project->feedback && in_array((int) $project->feedback->is_publish, [1, 2], true))
        <div class="d-flex align-items-center justify-content-end mt-5">
            <div class="d-flex align-items-center">
                <button class="btn btn-primary btn-gradiant view-feedback-btn" data-id="{{ $project->feedback->id }}">
                    View Feedback
                </button>
            </div>
        </div>
        @endif
                

    </section>
</div>

<!-- Edit Feedback Modal -->
<div class="modal fade" id="editFeedbackModal" tabindex="-1" aria-labelledby="editFeedbackModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="editFeedbackContent">
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
          </div>
          <p class="mt-3 mb-0">Loading feedback editor...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- View Feedback Modal -->
<div class="modal fade" id="viewFeedbackModal" tabindex="-1" aria-labelledby="viewFeedbackModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewFeedbackModalLabel">Project Feedback</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="viewFeedbackContent">
        <div class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
          </div>
          <p class="mt-3 mb-0">Loading feedback...</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="{{ asset('asset/dist/js/swiper-bundle.min.js') }}"></script>
<script src="{{ asset('asset/dist/js/jquery.fancybox.min.js') }}"></script>

<script>
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

    // Handle Generate Feedback Form
    $('#generateFeedbackForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const modal = $('#editFeedbackModal');
        const modalContent = $('#editFeedbackContent');
        const generateBtn = $('#generateFeedbackBtn');
    
        // Disable the generate button immediately
        generateBtn.prop('disabled', true);
        generateBtn.html('<span class="spinner-border spinner-border-sm mr-2"></span>Generating...');
    
        // Open modal immediately with generating state
        modal.modal({
            backdrop: 'static',  // Prevent closing on backdrop click
            keyboard: false      // Prevent closing on ESC key
        });
        modal.find('.close').prop('disabled', true).css('opacity', '0.5').css('cursor', 'not-allowed');
        modal.modal('show');
    
        modalContent.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Generating...</span>
                </div>
                <h5 class="mt-4 mb-2">Generating AI Feedback...</h5>
                <p class="text-muted mb-0">Please wait...</p>
                <p class="text-muted mt-2"><small>Please do not close this window or refresh the page.</small></p>
            </div>
        `);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            timeout: 120000, 
            success: function(response) {
                if (response.success && response.feedback_id) {
                    modalContent.html(`
                        <div class="text-center py-5">
                            <div class="spinner-border text-success" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-3 mb-0 text-success">✓ Feedback generated! Loading editor...</p>
                        </div>
                    `);
            
                $.ajax({
                    url: '/trainer/project/feedback/' + response.feedback_id + '/edit',
                    type: 'GET',
                    success: function(editResponse) {
                        modalContent.html(editResponse);
                        
                        // Re-enable modal closing after successful load
                        modal.modal({
                            backdrop: true,
                            keyboard: true
                        });
                        
                        // Re-enable the button and restore text (in case user closes modal)
                        generateBtn.prop('disabled', false);
                        generateBtn.html('Generate Feedback');
                    },
                    error: function() {
                        modalContent.html(`
                            <div class="alert alert-danger m-4">
                                <h5>Error Loading Editor</h5>
                                <p>Feedback was generated but unable to load editor. Please refresh the page.</p>
                                <button type="button" class="btn btn-secondary mr-2" onclick="location.reload()">Refresh Page</button>
                                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                            </div>
                        `);
                        
                        // Re-enable modal closing
                        modal.modal({
                            backdrop: true,
                            keyboard: true
                        });
                        
                        // Re-enable button
                        generateBtn.prop('disabled', false);
                        generateBtn.html('Generate Feedback');
                    }
                });
            } else {
                modalContent.html(`
                    <div class="alert alert-warning m-4">
                        <h5>Generation Complete</h5>
                        <p>${response.message || 'Feedback generated successfully'}</p>
                        <button type="button" class="btn btn-primary" onclick="window.location.reload()">Refresh Page</button>
                    </div>
                `);
                
                // Re-enable modal closing
                modal.modal({
                    backdrop: true,
                    keyboard: true
                });
                
                // Re-enable button
                generateBtn.prop('disabled', false);
                generateBtn.html('Generate Feedback');
            }
        },
            error: function(xhr, status, error) {
                let errorMessage = 'Failed to generate feedback. Please try again.';
                let technicalDetails = '';
    
                if (status === 'timeout') {
                    errorMessage = 'The request timed out after 2 minutes. The AI service may be overloaded. Please try again in a few moments.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error occurred. Please contact support if this issue persists.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
            
                // Add error ID if available
                if (xhr.responseJSON && xhr.responseJSON.error_id) {
                    technicalDetails = `<small class="text-muted d-block mt-2">Error ID: ${xhr.responseJSON.error_id}</small>`;
                }
    
                modalContent.html(`
                    <div class="alert alert-danger m-4">
                        <h5>Generation Failed</h5>
                        <p>${errorMessage}</p>
                        ${technicalDetails}
                        <div class="mt-3">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary ml-2" onclick="$('#generateFeedbackForm').submit()">Try Again</button>
                        </div>
                    </div>
                 `);
            
                // Re-enable modal closing on error
                modal.modal({
                    backdrop: true,
                    keyboard: true
                });
            
                // Re-enable button
                generateBtn.prop('disabled', false);
                generateBtn.html('Generate Feedback');
            }
        });
    });

    $(document).ready(function() {
        // Open Edit Feedback Modal (from button click)
        $('#openEditFeedbackModal').on('click', function(e) {
            e.preventDefault();
            const feedbackId = $(this).data('id');
            openEditFeedbackModal(feedbackId);
        });

        // Function to open Edit Feedback Modal
        function openEditFeedbackModal(feedbackId) {
            const modal = $('#editFeedbackModal');
            const modalContent = $('#editFeedbackContent');

            modalContent.html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0">Loading feedback editor...</p>
                </div>
            `);

            modal.modal('show');

            // Fetch feedback edit form via AJAX
            $.ajax({
                url: '/trainer/project/feedback/' + feedbackId + '/edit',
                type: 'GET',
                success: function(response) {
                    modalContent.html(response);
                },
                error: function(xhr, status, error) {
                    modalContent.html(`
                        <div class="alert alert-danger m-4">
                            <h5>Error Loading Feedback</h5>
                            <p>Unable to load feedback editor. Please try again.</p>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                   `);
                }
            });
        }

        // Open View Feedback Modal
        $(document).on('click', '.view-feedback-btn', function(e) {
            e.preventDefault();
            const feedbackId = $(this).data('id');
            openViewFeedbackModal(feedbackId);
        });

        // Function to open View Feedback Modal
        function openViewFeedbackModal(feedbackId) {
            const modal = $('#viewFeedbackModal');
            const modalContent = $('#viewFeedbackContent');

            // Show loading state
            modalContent.html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0">Loading feedback...</p>
                </div>
            `);

            modal.modal('show');

            // Fetch feedback view via AJAX
            $.ajax({
                url: '/trainer/project/feedback/' + feedbackId,
                type: 'GET',
                success: function(response) {
                    modalContent.html(response);
                },
                error: function(xhr, status, error) {
                    modalContent.html(`
                        <div class="alert alert-danger m-4">
                            <h5>Error Loading Feedback</h5>
                            <p>Unable to load feedback. Please try again.</p>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    `);
                }
            });
        }

        // Make function globally accessible
         window.openEditFeedbackModal = openEditFeedbackModal;

        // Cleanup when edit modal is closed
        $('#editFeedbackModal').on('hidden.bs.modal', function () {
            if (window.modalSpiderChartInstance) {
                window.modalSpiderChartInstance.destroy();
                window.modalSpiderChartInstance = null;
            }
            $('#editFeedbackContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0">Loading feedback editor...</p>
                </div>
            `);
        
            // Ensure button is re-enabled when modal closes
            const generateBtn = $('#generateFeedbackBtn');
            if (generateBtn.prop('disabled')) {
                generateBtn.prop('disabled', false);
                generateBtn.html('Generate Feedback');
            }
        });

        // Cleanup when view modal is closed
        $('#viewFeedbackModal').on('hidden.bs.modal', function () {
            if (window.viewSpiderChartInstance) {
                window.viewSpiderChartInstance.destroy();
                window.viewSpiderChartInstance = null;
            }
            $('#viewFeedbackContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0">Loading feedback...</p>
                </div>
            `);
        });
    });
</script>
@endsection
