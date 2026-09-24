@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">

   @php
      $activeStep = 4; // Submit for Review
   @endphp

   <div class="pi-page">

      <div class="pi-header">
         <a href="{{ route('student.my-projects') }}" class="pi-back"><i class="fas fa-arrow-left"></i></a>
         <div>
            <h2>Submit for Review</h2>
            <p>Your teacher will review your final project before it goes to your portfolio.</p>
         </div>
      </div>

      @include('student.project.partials.journey_stepper', ['activeStep' => $activeStep])

      <div class="pi-submit-grid">
         <div class="pi-card pi-submit-card">
            <div class="pi-submit-hero">
               <div class="pi-submit-illustration">
                  <i class="fas fa-paper-plane"></i>
               </div>

               @if(count($checklist))
                  <div class="pi-submit-checklist">
                     @foreach($checklist as $item)
                        <div class="pi-submit-check-row">
                           <span class="pi-submit-check-icon"><i class="fas fa-check-circle"></i></span>
                           <span>{{ $item }}</span>
                        </div>
                     @endforeach
                  </div>
               @endif
            </div>

            <h3 class="pi-submit-heading">You're Almost There!</h3>
            <p class="pi-submit-subtext">Great job! You've completed all the important parts of your project.</p>

            <hr class="pi-submit-divider">

            <div id="piSubmitError" class="pi-confirm-error" style="display:none;"></div>

            <label class="pi-submit-confirm-row">
               <input type="checkbox" id="piSubmitCheckbox" {{ $alreadySubmitted ? 'checked disabled' : '' }}>
               <span>I am ready to send my project to my teacher.</span>
            </label>

            <button type="button" id="piSubmitBtn" class="pi-submit-btn" disabled>
               <i class="fas fa-paper-plane"></i>
               <span id="piSubmitBtnLabel">{{ $alreadySubmitted ? 'Already Submitted' : 'Submit to Teacher' }}</span>
            </button>

            @unless($alreadySubmitted)
               <a href="{{ $makeChangesUrl }}" class="pi-submit-makechanges-btn">
                  <i class="fas fa-pencil-alt"></i> Make Changes to Project
               </a>
            @endunless

         </div>

         <div class="pi-card pi-next-card">
            <h4>What happens next?</h4>

            <div class="pi-next-row">
               <div class="pi-next-icon" style="background:#E1EFFC; color:#2F80ED;"><i class="fas fa-book-reader"></i></div>
               <div>
                  <strong>Teacher reviews</strong>
                  <p>Your teacher will read your project carefully.</p>
               </div>
            </div>

            <div class="pi-next-row">
               <div class="pi-next-icon" style="background:#F1E6FB; color:#9B51E0;"><i class="fas fa-sticky-note"></i></div>
               <div>
                  <strong>Teacher adds note</strong>
                  <p>They may add helpful notes or suggestions.</p>
               </div>
            </div>

            <div class="pi-next-row">
               <div class="pi-next-icon" style="background:#FCE7F0; color:#EB5288;"><i class="fas fa-star"></i></div>
               <div>
                  <strong>You can publish</strong>
                  <p>Once approved, you can publish your project to your portfolio!</p>
               </div>
            </div>
         </div>
      </div>

   </div>
</div>

<script>
   jQuery(document).ready(function($) {
      var alreadySubmitted = @json($alreadySubmitted);

      if (alreadySubmitted) {
         return;
      }

      var submitUrl = "{{ $submitUrl }}";
      var csrfToken = "{{ csrf_token() }}";

      $('#piSubmitCheckbox').on('change', function() {
         $('#piSubmitBtn').prop('disabled', !this.checked);
      });

      $('#piSubmitBtn').on('click', function() {
         var $btn = $(this);
         $btn.prop('disabled', true);
         $('#piSubmitCheckbox').prop('disabled', true);
         $('#piSubmitError').hide();
         $('#piSubmitBtnLabel').text('Submitting...');

         $.ajax({
            url: submitUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            success: function(res) {
               if (res.redirect) {
                  window.location.href = res.redirect;
               }
            },
            error: function(xhr) {
               $('#piSubmitBtnLabel').text('Submit to Teacher');
               $btn.prop('disabled', false);
               $('#piSubmitCheckbox').prop('disabled', false);
               var message = (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong. Please try again.';
               $('#piSubmitError').text(message).show();
            }
         });
      });
   });
</script>

@endsection
