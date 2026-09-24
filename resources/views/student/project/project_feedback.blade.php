@extends('backend.layouts.app')
@section('content')

<link rel="stylesheet" href="{{ asset('asset/dist/css/swiper-bundle.min.css') }}">

<div class="content-wrapper">

   @php
      $activeStep = 5; // Teacher Approval
      if ($studentProject->status == 4) {
         $activeStep = 4; // Submit for Review
      } elseif ((int) $studentProject->status >= 6) {
         $activeStep = 6; // Publish to Portfolio
      }
      $statusPillLabel = 'Approved';
      if ($activeStep == 4 && (int) $feedback->is_publish === 1) {
         $statusPillLabel = 'Feedback Received';
      }
      if ($activeStep == 6) {
         $statusPillLabel = 'Published';
      }
      $isApproved = (int) $feedback->is_publish === 2;
      $teacherName = optional($feedback->trainer)->trainer_name ?: 'Your Teacher';
      $teacherInitial = strtoupper(substr($teacherName, 0, 1));
      $teacherImage = optional($feedback->trainer)->image;
      $teacherPhotoUrl = ($teacherImage && $teacherImage !== 'no_image') ? asset('image/trainer/' . $teacherImage) : null;
   @endphp

   <div class="pi-page">

      <div class="pi-header">
         <a href="{{ route('student.my-projects') }}" class="pi-back"><i class="fas fa-arrow-left"></i></a>
         <div>
            <h2>Teacher Feedback</h2>
            <p>Your teacher has reviewed your project.</p>
         </div>
      </div>

      @include('student.project.partials.journey_stepper', ['activeStep' => $activeStep])

      <div class="pi-feedback-grid">
         <div class="pi-card">
            <div class="pi-feedback-approved-wrap">
               <span class="pi-feedback-approved-pill"><i class="fas fa-check-circle"></i> {{ $statusPillLabel }}</span>
            </div>

            <div class="pi-feedback-teacher">
               @if($teacherPhotoUrl)
                  <img src="{{ $teacherPhotoUrl }}" alt="{{ $teacherName }}" class="pi-feedback-teacher-avatar pi-feedback-teacher-avatar--photo">
               @else
                  <div class="pi-feedback-teacher-avatar">{{ $teacherInitial }}</div>
               @endif
               <div>
                  <div class="pi-feedback-teacher-name">{{ $teacherName }}</div>
                  <div class="pi-feedback-teacher-date"><i class="far fa-calendar-alt"></i> Reviewed on {{ optional($feedback->updated_at)->format('F d, Y') }}</div>
               </div>
            </div>

            <div class="pi-feedback-quote">
               <h4><i class="fas fa-quote-left"></i> Teacher's Feedback</h4>
               <p>{{ $feedback->public_note ?: 'No feedback note was provided.' }}</p>
            </div>

            @if($feedback->private_suggestions)
               <div class="pi-feedback-suggestions">
                  <h4><i class="fas fa-lightbulb"></i> Suggestions for Improvement</h4>
                  <p>{{ $feedback->private_suggestions }}</p>
               </div>
            @endif
         </div>

         <div class="pi-card pi-feedback-next-card">
            <div class="pi-feedback-mascot"><i class="fas fa-robot"></i></div>
            <h4>What can I do now?</h4>
            <p>Your project is approved! You can publish it or make more changes.</p>

            <a href="#student-project-content" class="pi-feedback-btn pi-feedback-btn--orange pi-feedback-btn--interactive">
               <i class="fas fa-rocket"></i>
               <span>View Project<small>See all answers and files</small></span>
               <i class="fas fa-chevron-right"></i>
            </a>

            <a href="{{ route('student.download-project-pdf', $studentProject->id) }}" class="pi-feedback-btn pi-feedback-btn--purple pi-feedback-btn--interactive">
               <i class="fas fa-download"></i>
               <span>Download Project<small>Save as PDF</small></span>
               <i class="fas fa-chevron-right"></i>
            </a>

            <a href="{{ route('student.make-changes', $feedback->student_project_id) }}"
               class="pi-feedback-btn pi-feedback-btn--blue pi-feedback-btn--interactive"
               id="makeChangesBtn">
               <i class="fas fa-pencil-alt"></i>
               <span>Make Changes<small>Teacher will need to approve again</small></span>
               <i class="fas fa-chevron-right"></i>
            </a>

            @if($isApproved)
               <button type="button" class="pi-feedback-btn pi-feedback-btn--green pi-feedback-btn--interactive" id="goToPublishBtn" data-toggle="modal" data-target="#publishSectionsModal">
                  <i class="fas fa-rocket"></i>
                  <span><span id="goToPublishBtnLabel">{{ (int) $studentProject->status >= 6 ? 'Published' : 'Go to Publish' }}</span><small>Choose sections to publish</small></span>
                  <i class="fas fa-chevron-right"></i>
               </button>
            @else
               <button type="button" class="pi-feedback-btn pi-feedback-btn--green" disabled>
                  <i class="fas fa-rocket"></i>
                  <span>Go to Publish<small>Available after approval</small></span>
                  <i class="fas fa-chevron-right"></i>
               </button>
            @endif
         </div>
      </div>

      @php
         $sectionIcons = ['fas fa-lightbulb', 'fas fa-compass', 'fas fa-rocket', 'fas fa-flask', 'fas fa-star', 'fas fa-comments', 'fas fa-map-signs', 'fas fa-globe-americas'];
         $sectionColors = ['project_details', 'project_overview', 'step_by_step_process', 'reflections_challenges'];
      @endphp

      <div id="student-project-content" class="pi-project-content">
         <div class="card">
            <div class="card-body new-text">
               <div class="mb-3 add_project_heading">
                  <div class="icon project_details green">
                     <img src="{{ asset('asset/dist/img/project-detail-overview.svg') }}"/>
                  </div>
                  <div class="icon_text">
                     <h4>Project Content</h4>
                     <p class="mb-0">Everything your teacher reviewed, all in one place.</p>
                  </div>
               </div>
            </div>
         </div>

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
                                 <div class="swiper student-project-swiper">
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
      </div>

      <!-- <div class="pi-feedback-banner">
         <i class="fas fa-star"></i> You're doing amazing! Every great inventor started with one idea <i class="fas fa-rocket"></i>
      </div> -->

   </div>

   @if($isApproved)
      <div class="modal fade" id="publishSectionsModal" tabindex="-1" role="dialog" aria-labelledby="publishSectionsModalLabel" aria-hidden="true">
         <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="publishSectionsModalLabel">Select Sections to Publish</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-body">
                  <div id="publishSectionsBody"></div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="button" class="btn btn-success" id="savePublishSectionsBtn">
                     <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true" id="savePublishSectionsSpinner" style="display:none;"></span>
                     <span id="savePublishSectionsBtnLabel">Publish</span>
                  </button>
               </div>
            </div>
         </div>
      </div>
   @endif
</div>

<script src="{{ asset('asset/dist/js/swiper-bundle.min.js') }}"></script>

<script>
   document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.student-project-swiper').forEach(function (el) {
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

      var makeChangesBtn = document.getElementById('makeChangesBtn');

      if (makeChangesBtn) {
         makeChangesBtn.addEventListener('click', function (event) {
            var confirmed = window.confirm('If you make changes and resubmit, your teacher will need to approve the project again. Do you want to continue?');

            if (!confirmed) {
               event.preventDefault();
            }
         });
      }
   });
</script>

@if($isApproved)
   <script>
      jQuery(document).ready(function($) {
         var publishSectionsData = @json($publishModalData);
         var publishSectionsUrl = @json($publishSectionsUrl);
         var savedPublishSections = @json($studentProject->published_sections);
         var sectionIcons = ['fas fa-lightbulb', 'fas fa-compass', 'fas fa-rocket', 'fas fa-flask', 'fas fa-star', 'fas fa-comments', 'fas fa-map-signs', 'fas fa-globe-americas'];
         var sectionColors = ['project_details', 'project_overview', 'step_by_step_process', 'reflections_challenges'];
         var $publishBody = $('#publishSectionsBody');
         var $saveBtn = $('#savePublishSectionsBtn');
         var $saveSpinner = $('#savePublishSectionsSpinner');
         var $saveBtnLabel = $('#savePublishSectionsBtnLabel');
         var publishModalBuilt = false;

         function setSaving(isSaving) {
            $saveBtn.prop('disabled', isSaving);
            $('[data-dismiss="modal"]').prop('disabled', isSaving);
            $saveSpinner.toggle(isSaving);
            $saveBtnLabel.text(isSaving ? 'Saving...' : 'Publish');
         }

         function escapeHtml(value) {
            return $('<div>').text(value === null || value === undefined ? '' : value).html();
         }

         function setStepperStepState(stepNum, state) {
            var $step = $('.pi-jstep[data-step="' + stepNum + '"]');
            var color = $step.data('color');

            $step.removeClass('pi-jstep--done pi-jstep--current');
            $step.find('.pi-jstep-num').css('background', '');
            $step.find('.pi-jstep-icon').css('color', '');

            if (state === 'done' || state === 'current') {
               $step.addClass('pi-jstep--' + state);
               $step.find('.pi-jstep-num').css('background', color);
               $step.find('.pi-jstep-icon').css('color', color);
            }
         }

         function markStepperPublished() {
            setStepperStepState(5, 'done');
            $('.pi-jstep-connector[data-connector-after="5"]').addClass('pi-jstep-connector--done');
            setStepperStepState(6, 'current');
         }

         function buildPublishModal() {
            if (publishModalBuilt) {
               return;
            }
            publishModalBuilt = true;

            var selected = Array.isArray(savedPublishSections) ? savedPublishSections.map(Number) : null;
            var html = '';

            publishSectionsData.forEach(function(section, sIndex) {
               var isRequired = sIndex === 0;
               var isChecked = isRequired || (Array.isArray(selected) && selected.indexOf(section.id) !== -1);
               var colorClass = sectionColors[sIndex % sectionColors.length];
               var iconClass = sectionIcons[sIndex % sectionIcons.length];

               html += '<div class="card mb-3">';
               html += '<div class="card-body new-text">';
               html += '<div class="mb-3 add_project_heading flex-nowrap">';
               html += '<div class="icon ' + colorClass + '"><i class="' + iconClass + '"></i></div>';
               html += '<div class="icon_text" style="width:auto;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><h4 style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + escapeHtml(section.title) + '</h4></div>';
               html += '<div class="custom-control custom-checkbox" style="flex:0 0 auto;">';
               html += '<input type="checkbox" class="custom-control-input publish-section-checkbox" id="publishSection' + section.id + '" value="' + section.id + '" aria-label="Include this section"' + (isChecked ? ' checked' : '') + (isRequired ? ' disabled' : '') + '>';
               html += '<label class="custom-control-label" for="publishSection' + section.id + '">' + (isRequired ? 'Required' : '') + '</label>';
               html += '</div>';
               html += '</div>';

               section.questions.forEach(function(question) {
                  var hasAnswer = question.answer !== null && question.answer !== undefined && question.answer !== '';
                  var hasImages = Array.isArray(question.images) && question.images.length > 0;

                  if (!hasAnswer && !hasImages) {
                     return;
                  }

                  html += '<div class="mb-3 discription submission-qa-block">';
                  html += '<h4>' + escapeHtml(question.text) + '</h4>';

                  if (hasAnswer) {
                     html += '<p>' + escapeHtml(question.answer) + '</p>';
                  }

                  if (hasImages) {
                     html += '<div class="d-flex flex-wrap" style="gap:10px;">';
                     question.images.forEach(function(image) {
                        html += '<img src="' + image.url + '" alt="' + escapeHtml(image.name) + '" style="width:120px;height:120px;object-fit:cover;border-radius:10px;">';
                     });
                     html += '</div>';
                  }

                  html += '</div>';
               });

               html += '</div>';
               html += '</div>';
            });

            $publishBody.html(html || '<p class="text-muted mb-0">No project sections have been configured yet.</p>');
         }

         $('#goToPublishBtn').on('click', function() {
            buildPublishModal();
         });

         $saveBtn.on('click', function() {
            var selectedIds = $('.publish-section-checkbox:checked').map(function() {
               return $(this).val();
            }).get();

            setSaving(true);

            $.ajax({
               url: publishSectionsUrl,
               method: 'POST',
               dataType: 'json',
               data: {
                  _token: "{{ csrf_token() }}",
                  sections: selectedIds
               },
               success: function(res) {
                  savedPublishSections = res.published_sections;
                  swal('Published', 'Your project has been published successfully!', 'success').then(function() {
                     $('#publishSectionsModal').modal('hide');
                     markStepperPublished();
                     $('#goToPublishBtnLabel').text('Published');
                  });
               },
               error: function(xhr) {
                  var message = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Something went wrong. Please try again.';
                  swal('Oops!', message, 'error');
               },
               complete: function() {
                  setSaving(false);
               }
            });
         });
      });
   </script>
@endif

@endsection
