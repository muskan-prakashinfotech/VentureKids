@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">

   @php
      $journeySteps = [
         ['color' => '#F2994A', 'icon' => 'fas fa-pencil-alt', 'title' => 'Create Draft'],
         ['color' => '#F2C94C', 'icon' => 'fas fa-lightbulb', 'title' => 'Get Improvement Ideas'],
         ['color' => '#27AE60', 'icon' => 'fas fa-rocket', 'title' => 'Improve Project'],
         ['color' => '#2F80ED', 'icon' => 'fas fa-paper-plane', 'title' => 'Submit for Review'],
         ['color' => '#9B51E0', 'icon' => 'fas fa-medal', 'title' => 'Teacher Approval'],
         ['color' => '#EB5288', 'icon' => 'fas fa-globe-americas', 'title' => 'Publish Portfolio'],
      ];
      $activeJourneyStep = max(1, (int) optional($studentProject)->status);
   @endphp

   <div class="cp-page">

      <div class="cp-header">
         <a href="{{ route('student.my-projects') }}" class="cp-back"><i class="fas fa-arrow-left"></i></a>
         <div>
            @if($isImproving)
               <h2>Improve Your Project</h2>
               <p>Let's complete the improvement tasks one by one.</p>
            @else
               <h2>Create Your Project </h2>
               <p>Let's build your idea one small step at a time.</p>
            @endif
         </div>
      </div>

      @if(session('message'))
         <div class="alert alert-success">{{ session('message') }}</div>
      @endif

      <div class="cp-card cp-journey">
         <div class="cp-steps">
            @foreach($journeySteps as $index => $jStep)
               @php
                  $jStepNum = $index + 1;
                  $jIsDone = $jStepNum < $activeJourneyStep;
                  $jIsCurrent = $jStepNum === $activeJourneyStep;
               @endphp
               <div class="cp-jstep">
                  <div class="cp-jstep-num" style="{{ $jIsDone || $jIsCurrent ? 'background:'.$jStep['color'].';' : '' }}">{{ $jStepNum }}</div>
                  <div class="cp-jstep-icon" style="{{ $jIsDone || $jIsCurrent ? 'color:'.$jStep['color'].';' : '' }}"><i class="{{ $jStep['icon'] }}"></i></div>
                  <div class="cp-jstep-title">{{ $jStep['title'] }}</div>
               </div>
               @if(!$loop->last)
                  <div class="cp-jstep-connector"></div>
               @endif
            @endforeach
         </div>
      </div>

      <div class="cp-body-grid">
         <form method="POST" action="{{ route('student.save-project-step') }}" enctype="multipart/form-data" class="cp-form-col" id="cpWizardForm" >
            @csrf
            <input type="hidden" name="step" value="{{ $step }}">
            <input type="hidden" name="project_id" value="{{ $projectId }}">
            <input type="hidden" name="action" id="cpActionInput" value="continue">

            <div class="cp-fields-wrap">
               <div id="cpFieldsContainer" class="cp-fields-container">
                  @foreach($sections as $index => $sectionItem)
                     <div class="cp-step-panel" data-step="{{ $index + 1 }}" @if($index + 1 !== $step) style="display:none;" @endif>
                        @include('student.project.partials.steps_fields', ['currentSection' => $sectionItem, 'answers' => $answers, 'attachments' => $attachments])
                     </div>
                  @endforeach
               </div>
            </div>

            <div class="cp-bottom-bar">
               <div class="cp-bottom-actions">
                  <a href="#" id="cpBackBtn" class="cp-btn-back" style="{{ $step > 1 ? '' : 'display:none;' }}">
                     <i class="fas fa-angle-left"></i> Previous
                  </a>
                  <button type="submit" data-action="continue" class="cp-btn-continue" id="cpContinueBtn">
                      Save & Continue<i class="fas fa-chevron-right"></i>
                  </button>
               </div>
            </div>
         </form>

         <div class="cp-tip-col">
            @if($showImprovementPointers && !empty($improvementPointers['tips']))
               <div class="cp-tip-card cp-tip-card--ai-pointers">
                  <div class="cp-tip-icon"><i class="fas fa-clipboard-check"></i></div>
                  <h4>Improvement Tips</h4>
                  <p class="cp-tip-subtitle">Use these AI notes as your guide while editing this project.</p>

                  <div class="cp-ai-pointer-list">
                     @foreach($improvementPointers['tips'] as $index => $tip)
                        <div class="cp-ai-pointer-item">
                           <div class="cp-ai-pointer-num">{{ $index + 1 }}</div>
                           <div class="cp-ai-pointer-body">
                              <strong>{{ $tip['action'] }}</strong>
                              @if(!empty($tip['why']))
                                 <p>{{ $tip['why'] }}</p>
                              @endif
                           </div>
                        </div>
                     @endforeach
                  </div>

                  @if(!empty($improvementPointers['readinessLevel']))
                     <div class="cp-ai-readiness">
                        <span class="cp-ai-readiness-label">Portfolio Readiness</span>
                        <span class="cp-ai-readiness-pill">{{ $improvementPointers['readinessLevel'] }}</span>
                        @if(!empty($improvementPointers['readinessExplanation']))
                           <p>{{ $improvementPointers['readinessExplanation'] }}</p>
                        @endif
                     </div>
                  @endif
               </div>
            @else
               <div class="cp-tip-card">
                  <div class="cp-tip-icon"><i class="fas fa-lightbulb"></i></div>
                  <h4>Short and clear is great!</h4>
                  <p>Use your own words. It doesn't have to be perfect.</p>
                  <div class="cp-tip-mascot"><i class="fas fa-robot"></i></div>
                  <p class="cp-tip-encourage">You're doing awesome!</p>
               </div>
            @endif
         </div>
      </div>

   </div>
</div>

<script>
   jQuery(document).ready(function($) {
      var $form = $('#cpWizardForm');
      var $fields = $('#cpFieldsContainer');
      var totalSteps = {{ $totalSteps }};
      var isImproving = @json($isImproving);
      var initialStep = {{ $step }};

      function setBottomBar(step, isLast) {
         var continueLabel = 'Save & Continue';
         $('#cpContinueBtn').html(continueLabel + ' <i class="fas fa-chevron-right"></i>');
         if (step > 1) {
            $('#cpBackBtn').show();
         } else {
            $('#cpBackBtn').hide();
         }
      }

      function showStep(step) {
         step = parseInt(step, 10);

         $fields.find('.cp-step-panel').each(function() {
            var $panel = $(this);
            var isActive = parseInt($panel.data('step'), 10) === step;

            $panel.css('display', isActive ? 'flex' : 'none');
            $panel.find('textarea[data-required]').prop('required', function() {
               return isActive && $(this).data('required') == 1;
            });
         });
      }

      showStep(initialStep);

      function buildFileInputRow(questionId, accept) {
         return $(
            '<div class="cp-file-input-row">' +
               '<div class="custom-file">' +
                  '<input type="file" name="attachments[' + questionId + '][]" class="custom-file-input cp-file-input" accept="' + accept + '">' +
                  '<label class="custom-file-label">Choose File</label>' +
               '</div>' +
               '<button type="button" class="cp-file-remove-row" title="Remove"><i class="fas fa-times"></i></button>' +
            '</div>'
         );
      }

      $(document).on('click', '.cp-add-more-file', function() {
         var questionId = $(this).data('question-id');
         var accept = $(this).data('accept');
         buildFileInputRow(questionId, accept).appendTo('.cp-file-inputs[data-question-id="' + questionId + '"]');
      });

      $(document).on('change', '.cp-file-input', function() {
         var $input = $(this);
         var $label = $input.next('.custom-file-label');
         var $container = $input.closest('.cp-file-inputs');
         var allowed = (($container.data('allowed-extensions') || '').toString()).split(',').filter(Boolean);

         if (!allowed.length || !this.files.length) {
            return;
         }

         var file = this.files[0];
         var extension = file.name.split('.').pop().toLowerCase();

         if (allowed.indexOf(extension) === -1) {
            alert('"' + file.name + '" is not an allowed file type here. Allowed types: ' + allowed.join(', ').toUpperCase() + '.');
            $input.val('');
            $label.text('Choose File');
            return;
         }

         $label.text(file.name);
      });

      $(document).on('click', '.cp-file-remove-row', function() {
         $(this).closest('.cp-file-input-row').remove();
      });

      $(document).on('click', '.cp-file-chip-remove', function() {
         var $chip = $(this).closest('.cp-file-chip');
         var $block = $chip.closest('.cp-attachment-block');

         $('<input>', { type: 'hidden', name: 'remove_attachments[]', value: $chip.data('attachment-id') }).appendTo($block);
         $chip.remove();
      });

      function buildVideoUrlInputRow(questionId) {
         return $(
            '<div class="cp-file-input-row">' +
               '<input type="url" name="video_urls[' + questionId + '][]" class="cp-input cp-video-url-input" placeholder="Paste a video URL (e.g. YouTube link)">' +
               '<button type="button" class="cp-file-remove-row" title="Remove"><i class="fas fa-times"></i></button>' +
            '</div>'
         );
      }

      $(document).on('click', '.cp-add-more-video-url', function() {
         var questionId = $(this).data('question-id');
         buildVideoUrlInputRow(questionId).appendTo('.cp-video-url-inputs[data-question-id="' + questionId + '"]');
      });

      function setLoading(isLoading) {
         $('#cpContinueBtn, .cp-btn-draft, #cpBackBtn').prop('disabled', isLoading).toggleClass('cp-disabled', isLoading);
      }

      function clearValidationErrors() {
         $fields.find('.cp-error-dynamic').remove();
      }

      function fieldTargetForErrorKey(questionId, group) {
         if (group === 'answers') {
            return $fields.find('textarea[name="answers[' + questionId + ']"]');
         }
         if (group === 'attachments') {
            return $fields.find('.cp-file-inputs[data-question-id="' + questionId + '"]');
         }
         if (group === 'video_urls') {
            return $fields.find('.cp-video-url-inputs[data-question-id="' + questionId + '"]');
         }
         return $();
      }

      function showValidationErrors(errors) {
         clearValidationErrors();

         var $firstTarget = null;

         $.each(errors, function(key, messages) {
            var parts = key.split('.');
            var group = parts[0];
            var questionId = parts[1];
            var message = Array.isArray(messages) ? messages[0] : messages;

            var $target = fieldTargetForErrorKey(questionId, group);
            if (!$target.length) {
               return;
            }

            $target.after('<small class="cp-error cp-error-dynamic">' + message + '</small>');

            if (!$firstTarget) {
               $firstTarget = $target;
            }
         });

         if ($firstTarget) {
            $firstTarget.get(0).scrollIntoView({ behavior: 'smooth', block: 'center' });
         }
      }

      function attachmentTargetFor(questionId) {
         var $fileTarget = fieldTargetForErrorKey(questionId, 'attachments');
         if ($fileTarget.length) {
            return $fileTarget;
         }
         return fieldTargetForErrorKey(questionId, 'video_urls');
      }

      function validateRequiredAttachments(step) {
         var isValid = true;
         var $firstInvalid = null;

         $fields.find('.cp-step-panel[data-step="' + step + '"] .cp-attachment-block[data-required="1"]').each(function() {
            var $block = $(this);
            var questionId = $block.data('question-id');

            var hasExistingFile = $block.find('.cp-file-chip').length > 0;

            var hasNewFile = false;
            $block.find('.cp-file-input').each(function() {
               if (this.files && this.files.length > 0) {
                  hasNewFile = true;
               }
            });

            var hasVideoUrl = false;
            $block.find('.cp-video-url-input').each(function() {
               if ($.trim($(this).val()) !== '') {
                  hasVideoUrl = true;
               }
            });

            if (!hasExistingFile && !hasNewFile && !hasVideoUrl) {
               isValid = false;

               var $target = attachmentTargetFor(questionId);
               if ($target.length) {
                  $target.after('<small class="cp-error cp-error-dynamic">This field is required.</small>');
                  if (!$firstInvalid) {
                     $firstInvalid = $target;
                  }
               }
            }
         });

         if ($firstInvalid) {
            $firstInvalid.get(0).scrollIntoView({ behavior: 'smooth', block: 'center' });
         }

         return isValid;
      }

      $form.on('submit', function(e) {
         e.preventDefault();

         var $submitBtn = $(document.activeElement).closest('button[data-action]');
         var action = $submitBtn.length ? $submitBtn.data('action') : 'continue';
         $('#cpActionInput').val(action);

         var currentStep = parseInt($form.find('input[name="step"]').val(), 10) || 1;

         clearValidationErrors();

         if (!validateRequiredAttachments(currentStep)) {
            return;
         }

         setLoading(true);

         $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            dataType: 'json',
            data: new FormData($form.get(0)),
            processData: false,
            contentType: false,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            success: function(res) {
               if (res.redirect) {
                  window.location.href = res.redirect;
                  return;
               }

               if (res.answers && res.savedStep) {
                  var $savedPanel = $fields.find('.cp-step-panel[data-step="' + res.savedStep + '"]');
                  $.each(res.answers, function(questionId, answerText) {
                     $savedPanel.find('textarea[name="answers[' + questionId + ']"]').val(answerText);
                  });
               }

               showStep(res.step);
               $form.find('input[name="step"]').val(res.step);
               $form.find('input[name="project_id"]').val(res.projectId);
               setBottomBar(res.step, res.step >= res.totalSteps);
               $('.content-wrapper').get(0).scrollIntoView({ behavior: 'smooth' });
            },
            error: function(xhr) {
               if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                  showValidationErrors(xhr.responseJSON.errors);
                  return;
               }
               alert('Something went wrong. Please try again.');
            },
            complete: function() {
               setLoading(false);
            }
         });
      });

      $(document).on('click', '#cpBackBtn', function(e) {
         e.preventDefault();

         if ($(this).hasClass('cp-disabled')) {
            return;
         }

         var currentStep = parseInt($form.find('input[name="step"]').val(), 10) || 1;
         if (currentStep <= 1) {
            return;
         }

         var prevStep = currentStep - 1;

         clearValidationErrors();
         showStep(prevStep);
         $form.find('input[name="step"]').val(prevStep);
         setBottomBar(prevStep, prevStep >= totalSteps);
         $('.content-wrapper').get(0).scrollIntoView({ behavior: 'smooth' });
      });
   });
</script>

@endsection
