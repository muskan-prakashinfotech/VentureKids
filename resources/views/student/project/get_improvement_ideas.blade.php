@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">

   @php
      $activeStep = 2; // Get Improvement Ideas
   @endphp

   <div class="pi-page">

      <div class="pi-header">
         <a href="{{ route('student.my-projects') }}" class="pi-back"><i class="fas fa-arrow-left"></i></a>
         <div>
            <h2>Get Improvement Ideas</h2>
            <p>You've finished all 8 sections - nice work!</p>
         </div>
      </div>

      @include('student.project.partials.journey_stepper', ['activeStep' => $activeStep])

      <div class="pi-confirm-wrap">
         <div class="pi-card pi-ai-card" id="piIntroCard">
            <div class="pi-ai-header">
               <div class="pi-ai-avatar"><i class="fas fa-robot"></i></div>
               <div class="pi-ai-greeting">
                  <h3>Ready for your Improvement Ideas?</h3>
                  <p>AI powered ImproveBuddy will look at everything you've shared - your answers, photos, and links - and suggest simple ways to make your project even stronger.</p>
               </div>
            </div>

            <div class="pi-confirm-note">
               <i class="fas fa-info-circle"></i>
               This only happens once for this project, so make sure your answers, photos, and links are exactly how you want them before you continue.
            </div>

            <div id="piConfirmError" class="pi-confirm-error" style="display:none;"></div>

            <div class="pi-improvement-actions">
               <a href="{{ route('student.create-project', ['id' => $studentProject->id]) }}" class="pi-btn-secondary pi-btn-large pi-review-edit-btn">
                  <i class="fas fa-pen"></i> Review & Edit
               </a>
               <button type="button" id="piGetIdeasBtn" class="pi-btn-primary pi-btn-large">
                  <i class="fas fa-magic"></i> Get Improvement Ideas
               </button>
            </div>
         </div>

         <div class="pi-card pi-ai-card" id="piLoadingCard" style="display:none;">
            <div class="pi-loading-avatar"><i class="fas fa-robot"></i></div>
            <h3 class="pi-loading-title">AI powered ImproveBuddy is reviewing your project...</h3>

            <div class="pi-loading-sections">
               @foreach($sections as $index => $section)
                  <div class="pi-loading-row" data-index="{{ $index }}">
                     <span class="pi-loading-check"><i class="fas fa-circle"></i></span>
                     <span class="pi-loading-label">{{ $section->section_title }}</span>
                  </div>
               @endforeach
            </div>

            <p class="pi-loading-hint">This can take up to a minute - hang tight!</p>
         </div>
      </div>

   </div>
</div>

<script>
   jQuery(document).ready(function($) {
      var generateUrl = "{{ $generateUrl }}";
      var csrfToken = "{{ csrf_token() }}";
      var $rows = $('.pi-loading-row');
      var totalSections = $rows.length;
      var cycleIndex = 0;
      var cycleTimer = null;

      function updateRows() {
         $rows.each(function(i) {
            var $row = $(this);
            var $check = $row.find('.pi-loading-check');

            if (i < cycleIndex) {
               $row.removeClass('pi-loading-row--active').addClass('pi-loading-row--done');
               $check.html('<i class="fas fa-check"></i>');
            } else if (i === cycleIndex) {
               $row.addClass('pi-loading-row--active').removeClass('pi-loading-row--done');
               $check.html('<i class="fas fa-circle-notch fa-spin"></i>');
            } else {
               $row.removeClass('pi-loading-row--active pi-loading-row--done');
               $check.html('<i class="fas fa-circle"></i>');
            }
         });
      }

      function startCycling() {
         updateRows();
         cycleTimer = setInterval(function() {
            if (cycleIndex < totalSections) {
               cycleIndex++;
               updateRows();
            }
            if (cycleIndex >= totalSections) {
               $('.pi-loading-title').text('Finalizing your improvement plan...');
            }
         }, 1800);
      }

      function stopCycling() {
         if (cycleTimer) {
            clearInterval(cycleTimer);
            cycleTimer = null;
         }
         cycleIndex = 0;
      }

      $('#piGetIdeasBtn').on('click', function() {
         var $btn = $(this);
         $btn.prop('disabled', true);
         $('#piConfirmError').hide();
         $('#piIntroCard').hide();
         $('#piLoadingCard').show();
         startCycling();

         $.ajax({
            url: generateUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            success: function(res) {
               if (res.redirect) {
                  window.location.href = res.redirect;
               }
            },
            error: function(xhr) {
               stopCycling();
               $('#piLoadingCard').hide();
               $('#piIntroCard').show();
               $btn.prop('disabled', false);
               var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong. Please try again.';
               $('#piConfirmError').text(message).show();
            }
         });
      });
   });
</script>

@endsection
