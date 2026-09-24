@extends('backend.layouts.app')
@section('content')

<link rel="stylesheet" href="{{ asset('asset/dist/css/swiper-bundle.min.css') }}">

<div class="content-wrapper">
    <div class="pageTitle">
        <h2>{{ optional($studentProject->student)->name }} - Submitted Project</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Submitted Project</li>
        </ol>
    </div>

    <section>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="card-title mb-0">{{ $displayTheme ?: 'No Theme Assigned' }}</h2>

                @if($latestFeedback && (int) $latestFeedback->is_publish === 3)
                    <form id="generateSubmissionFeedbackForm" action="{{ route('trainer.generate-submission-feedback', $studentProject->id) }}" method="POST" class="ml-auto">
                        @csrf
                        <button type="submit" id="generateSubmissionFeedbackBtn" class="btn btn-sm btn-success text-white font-bold">
                            Generate Feedback
                        </button>
                    </form>
                @elseif($latestFeedback && (int) $latestFeedback->is_publish !== 2)
                    <button class="btn btn-sm border font-bold ml-auto" id="openEditSubmissionFeedbackModal" data-id="{{ $latestFeedback->id }}">
                        Edit Feedback
                    </button>
                @elseif($latestFeedback && (int) $latestFeedback->is_publish === 2)
                    <button class="btn btn-sm border font-bold ml-auto view-submission-feedback-btn" data-id="{{ $latestFeedback->id }}">
                        View Feedback
                    </button>
                @else
                    <form id="generateSubmissionFeedbackForm" action="{{ route('trainer.generate-submission-feedback', $studentProject->id) }}" method="POST" class="ml-auto">
                        @csrf
                        <button type="submit" id="generateSubmissionFeedbackBtn" class="btn btn-sm btn-success text-white font-bold">
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
                <span class="text-success"><img src="{{ asset('asset/dist/img/check.svg') }}" />Successfully Submitted on {{ optional($studentProject->updated_at)->format('F d, Y') }}</span>
            </div>
            <div class="light-gradiant-card">
                <span>Project Title</span>
                <h2>{{ $displayTitle ?: 'Untitled Project' }}</h2>
                <div class="prj_detail_img">
                    <img src="{{ asset('asset/dist/img/project-detail-banner.svg') }}" />
                </div>
            </div>
        </div>

        @php
            $sectionIcons = ['fas fa-lightbulb', 'fas fa-compass', 'fas fa-rocket', 'fas fa-flask', 'fas fa-star', 'fas fa-comments', 'fas fa-map-signs', 'fas fa-globe-americas'];
            $sectionColors = ['project_details', 'project_overview', 'step_by_step_process', 'reflections_challenges'];
        @endphp

        @forelse($sections as $sIndex => $section)
            <div class="card">
                <div class="card-body new-text">
                    <div class="mb-3 add_project_heading">
                        <div class="icon {{ $sectionColors[$sIndex % count($sectionColors)] }}">
                            <i class="{{ $sectionIcons[$sIndex % count($sectionIcons)] }}"></i>
                        </div>
                        <div class="icon_text">
                            <h4>{{ $section->section_title }}</h4>
                        </div>
                    </div>

                    @forelse($section->questions as $question)
                        <div class="mb-3 discription submission-qa-block">
                            <h4>{{ $question->field_text }}</h4>

                            @if($question->field_type !== 'file')
                                <p>{{ $answers->get($question->id) ?: 'No answer provided.' }}</p>
                            @endif

                            @if($question->allow_attachments)
                                @php
                                    $questionAttachments = $attachments->get($question->id, collect());
                                    $imageAttachments = $questionAttachments->where('file_type', 'images');
                                    $videoAttachments = $questionAttachments->where('file_type', 'videos');
                                    $otherAttachments = $questionAttachments->reject(fn ($a) => in_array($a->file_type, ['images', 'videos']));
                                @endphp

                                @if($questionAttachments->isEmpty())
                                    <p class="text-muted mb-0"><small>No attachments provided.</small></p>
                                @endif

                                @if($imageAttachments->isNotEmpty())
                                    <div class="submission-image-carousel">
                                        <div class="swiper submission-swiper">
                                            <div class="swiper-wrapper">
                                                @foreach($imageAttachments as $attachment)
                                                    <div class="swiper-slide">
                                                        <a href="{{ $attachment->url }}" target="_blank" rel="noopener">
                                                            <img src="{{ $attachment->url }}" alt="{{ $attachment->file_name }}">
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @if($imageAttachments->count() > 1)
                                                <div class="swiper-button-next"></div>
                                                <div class="swiper-button-prev"></div>
                                                <div class="swiper-pagination"></div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if($videoAttachments->isNotEmpty())
                                    <div class="submission-file-list">
                                        @foreach($videoAttachments as $attachment)
                                            <div class="submission-file-row">
                                                <div class="submission-file-info">
                                                    <i class="fas fa-film text-primary"></i>
                                                    <span>{{ $attachment->file_name }}</span>
                                                </div>
                                                <div class="submission-file-actions">
                                                    <a href="{{ $attachment->url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-external-link-alt"></i> Open
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($otherAttachments->isNotEmpty())
                                    <div class="submission-file-list">
                                        @foreach($otherAttachments as $attachment)
                                            <div class="submission-file-row">
                                                <div class="submission-file-info">
                                                    <i class="fas fa-file-pdf text-danger"></i>
                                                    <span>{{ $attachment->file_name }}</span>
                                                </div>
                                                <div class="submission-file-actions">
                                                    <a href="{{ $attachment->url }}" download="{{ $attachment->file_name }}" class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                    @empty
                        <p class="text-muted">No questions configured for this section.</p>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-0">No project sections have been configured yet.</p>
                </div>
            </div>
        @endforelse

        @if($feedbackHistory->isNotEmpty())
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-1">Feedback History</h4>
                            <p class="text-muted mb-0">A record of every feedback version for this submission.</p>
                        </div>
                        <span class="trainer-feedback-history-count">{{ $feedbackHistory->count() }} version(s)</span>
                    </div>

                    <div class="trainer-feedback-history-list">
                        @foreach($feedbackHistory as $historyFeedback)
                            @php
                                $historyStatusLabel = match ((int) $historyFeedback->is_publish) {
                                    0 => 'Draft',
                                    1 => 'Published',
                                    2 => 'Approved',
                                    3 => 'Re-submitted',
                                    default => 'Draft',
                                };
                                $historyStatusClass = match ((int) $historyFeedback->is_publish) {
                                    0 => 'trainer-project-status-draft',
                                    1 => 'trainer-project-status-published',
                                    2 => 'trainer-project-status-approved',
                                    3 => 'trainer-project-status-resubmitted',
                                    default => 'trainer-project-status-draft',
                                };
                                $isCurrentHistory = $latestFeedback && (int) $latestFeedback->id === (int) $historyFeedback->id;
                            @endphp
                            <div class="trainer-feedback-history-item {{ $isCurrentHistory ? 'trainer-feedback-history-item--current' : '' }}">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <div>
                                        <div class="trainer-feedback-history-title">
                                            {{ $historyStatusLabel }}
                                            @if($isCurrentHistory)
                                                <span class="trainer-feedback-history-current">Current</span>
                                            @endif
                                        </div>
                                        <div class="trainer-feedback-history-meta">
                                            {{ optional($historyFeedback->updated_at)->format('F d, Y') }}
                                            @if($historyFeedback->smart_score !== null)
                                                <span>- Smart Score {{ $historyFeedback->smart_score }}/12</span>
                                            @endif
                                            @if(optional($historyFeedback->trainer)->trainer_name)
                                                <span>- {{ $historyFeedback->trainer->trainer_name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="trainer-project-status-chip {{ $historyStatusClass }}">{{ $historyStatusLabel }}</span>
                                </div>
                                <div class="trainer-feedback-history-snippet">
                                    {{ \Illuminate\Support\Str::limit($historyFeedback->public_note ?: 'No public note provided.', 180) }}
                                </div>
                                <div class="trainer-feedback-history-actions">
                                    <button type="button" class="btn btn-sm border view-submission-feedback-btn" data-id="{{ $historyFeedback->id }}">
                                        View
                                    </button>
                                    @if($isCurrentHistory && in_array((int) $historyFeedback->is_publish, [0, 1], true))
                                        <button type="button" class="btn btn-sm btn-primary open-edit-submission-feedback-modal" data-id="{{ $historyFeedback->id }}">
                                            Edit
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </section>
</div>

<!-- Edit Submission Feedback Modal -->
<div class="modal fade" id="editSubmissionFeedbackModal" tabindex="-1" aria-labelledby="editSubmissionFeedbackModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-body" id="editSubmissionFeedbackContent">
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

<!-- View Submission Feedback Modal -->
<div class="modal fade" id="viewSubmissionFeedbackModal" tabindex="-1" aria-labelledby="viewSubmissionFeedbackModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewSubmissionFeedbackModalLabel">Project Feedback</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="viewSubmissionFeedbackContent">
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


<script src="{{ asset('asset/dist/js/swiper-bundle.min.js') }}"></script>
<script>
    document.querySelectorAll('.submission-swiper').forEach(function (el) {
        var slideCount = el.querySelectorAll('.swiper-slide').length;

        new Swiper(el, {
            slidesPerView: 'auto',
            spaceBetween: 10,
            loop: slideCount > 1,
            navigation: {
                nextEl: el.querySelector('.swiper-button-next'),
                prevEl: el.querySelector('.swiper-button-prev'),
            },
            pagination: {
                el: el.querySelector('.swiper-pagination'),
                clickable: true,
            },
        });
    });

</script>

<script>
    function openEditSubmissionFeedbackModal(feedbackId) {
        var modal = $('#editSubmissionFeedbackModal');
        var modalContent = $('#editSubmissionFeedbackContent');

        modalContent.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3 mb-0">Loading feedback editor...</p>
            </div>
        `);

        // Locked: this feedback isn't confirmed until the trainer explicitly saves it,
        // so accidental outside-clicks/ESC must not be able to dismiss it early.
        modal.modal({ backdrop: 'static', keyboard: false });

        $.ajax({
            url: '/trainer/project/submission/feedback/' + feedbackId + '/edit',
            type: 'GET',
            success: function (response) {
                modalContent.html(response);
            },
            error: function () {
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

    function openViewSubmissionFeedbackModal(feedbackId) {
        var modal = $('#viewSubmissionFeedbackModal');
        var modalContent = $('#viewSubmissionFeedbackContent');

        modalContent.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-3 mb-0">Loading feedback...</p>
            </div>
        `);

        modal.modal('show');

        $.ajax({
            url: '/trainer/project/submission/feedback/' + feedbackId,
            type: 'GET',
            success: function (response) {
                modalContent.html(response);
            },
            error: function () {
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

    $(document).ready(function () {
        // Delegated (not bound once at ready) since the Generate button gets replaced with
        // this same button/id once feedback is generated, without a page reload.
        $(document).on('click', '#openEditSubmissionFeedbackModal, .open-edit-submission-feedback-modal', function (e) {
            e.preventDefault();
            openEditSubmissionFeedbackModal($(this).data('id'));
        });

        $(document).on('click', '.view-submission-feedback-btn', function (e) {
            e.preventDefault();
            openViewSubmissionFeedbackModal($(this).data('id'));
        });

        $(document).on('submit', '#generateSubmissionFeedbackForm', function (e) {
            e.preventDefault();
            var form = $(this);
            var generateBtn = $('#generateSubmissionFeedbackBtn');

            generateBtn.prop('disabled', true);
            generateBtn.html('<span class="spinner-border spinner-border-sm mr-2"></span>Generating...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                timeout: 120000,
                success: function (response) {
                    if (response.success && response.feedback_id) {
                        // Swap the Generate button/form for a real Edit Feedback button right
                        // away, so the page reflects reality even before the modal is saved.
                        form.replaceWith(
                            '<button class="btn btn-sm border font-bold ml-auto" id="openEditSubmissionFeedbackModal" data-id="' + response.feedback_id + '">Edit Feedback</button>'
                        );
                        openEditSubmissionFeedbackModal(response.feedback_id);
                        return;
                    }

                    alert(response.message || 'Something went wrong. Please try again.');
                    generateBtn.prop('disabled', false);
                    generateBtn.html('Generate Feedback');
                },
                error: function (xhr, status) {
                    var message = 'Failed to generate feedback. Please try again.';
                    if (status === 'timeout') {
                        message = 'The request timed out. Please try again in a few moments.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    alert(message);
                    generateBtn.prop('disabled', false);
                    generateBtn.html('Generate Feedback');
                }
            });
        });

        $('#editSubmissionFeedbackModal').on('hidden.bs.modal', function () {
            $('#editSubmissionFeedbackContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0">Loading feedback editor...</p>
                </div>
            `);
        });

        $('#viewSubmissionFeedbackModal').on('hidden.bs.modal', function () {
            $('#viewSubmissionFeedbackContent').html(`
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
