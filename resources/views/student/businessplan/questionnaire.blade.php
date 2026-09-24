@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
  <!-- Page Title -->
  <div class="pageTitle">
    <h2>Build Your Business Plan in Just 10 Easy Steps</h2>
  </div>

  <section>
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title business-tooltip-italic"
          data-toggle="tooltip"
          data-bs-trigger="hover"
          data-placement="top"
          title="A business plan is a step-by-step guide that explains what your business is, what problem it solves, what you are selling, who will buy it, and how much money you will make">
          What's a business plan?
        </h3>
        <!-- <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-warning">
          <i class="material-icons">west</i> Back
        </a> -->
      </div>

      <div class="card-body box_padd">
        <h4 id="stepTitle" class="mb-3 fw-bold text-primary"></h4>
        <form id="questionForm">
          <div class="question-div new_quiz_box">
            <input type="hidden" name="form_token" value="{{ session('business_plan_form_token') }}">
            <input type="hidden" name="queCnt" value="1" id="queCnt">
            <input type="hidden" id="totalSteps" value="{{ $totalSteps }}">

            <!-- Progress + Counter -->
            <div class="d-flex flex-wrap align-items-center justify-content-between progress_with_counter mb-4">
              <div class="progress w-75">
                <div id="formProgressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <div class="queCntDiv">
                Step <span class="currentCnt">1</span>/<span class="totalCnt">{{$totalSteps}}</span>
              </div>
            </div>

            <!-- Questions will be dynamically injected here -->
            <div id="questionFormContent" class="new_quiz_box">
              <!-- Dynamically loaded content -->
            </div>

            <div id="botMessageBox" class="mt-3 mb-4 d-none"></div>

            <!-- Navigation Buttons -->
            <div class="d-flex align-items-center pt-3 border-top mt-4">
              <div id="prev">
                <button type="button" id="prevBtn" class="btn btn-secondary hover-success">Previous</button>
              </div>
              <div class="ml-auto">
                <button type="submit" id="nextBtn" class="btn btn-success">Next</button>
                <button type="submit" id="submitBtn" class="btn btn-success d-none">Submit</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
</div>

<script>
  let currentStep = 1;

  const botFollowUpMessages = {
    1: "Awesome! Let's get started!",
    2: "Great! Solving real problems is what entrepreneurs do!",
    3: "Amazing! Your solution can help so many people!",
    4: "Great. It's always essential to consider what makes your solution unique or special.",
    5: "Awesome. That's a fantastic goal! You're off to a strong start!",
    6: "Perfect! The details sound super exciting. I can already see people loving it!",
    7: "Well done! Hope this customer group will love your solution.",
    8: "Awesome! Now you know your cost per product. This will help with financial projections!",
    9: "Perfect! This information will help create a marketing plan for your solution.",
    10: "Well done! You've figured out how your business will earn, spend, and make a profit. That's a huge step in becoming a smart young entrepreneur!"
  };

  function initializeTooltips() {
    $('[data-toggle="tooltip"]').tooltip(); // Bootstrap 4 style
  }

  function loadQuestion(step) {
    $.ajax({
      url: "{{ route('student.businessplan.get') }}",
      method: "POST",
      data: {
        step: step
      },
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      success: function(response) {
        $('#questionFormContent').html(response.html);
        $('#stepTitle').text(response.step_title);
        toggleNavigationButtons(step, response.is_last);
        updateProgressBar(step);
        initializeTooltips()
      },
      error: function(xhr) {
        console.error("Error loading question:", xhr.responseText);
        alert("Could not load question. Please try again.");
      }
    });
  }

  function updateProgressBar(step) {
    const totalSteps = parseInt(document.getElementById('totalSteps').value) || 1;
    const progressPercent = (step / totalSteps) * 100;

    // console.log(`Updating progress: Step ${step} of ${totalSteps} = ${progressPercent.toFixed(2)}%`);

    $('#formProgressBar').css('width', `${progressPercent}%`);
    $('.currentCnt').text(step);
    $('.totalCnt').text(totalSteps);
  }

  function toggleNavigationButtons(step, isLast) {
    const $nextBtn = $('#nextBtn');
    const $submitBtn = $('#submitBtn');
    const $prevBtn = $('#prevBtn');

    // Reset button visibility
    $nextBtn.removeClass('d-none').prop('disabled', false);
    $submitBtn.addClass('d-none').prop('disabled', false);

    if (isLast) {
      $nextBtn.addClass('d-none');
      $submitBtn.removeClass('d-none');
    }

    // Handle previous button - show if step > 1, hide if step = 1
    if (step > 1) {
      $prevBtn.removeClass('d-none').prop('disabled', false);
    } else {
      $prevBtn.addClass('d-none');
    }
  }

  function disableNavButtons(disable = true, hide = false) {
    const $nextBtn = $('#nextBtn');
    const $submitBtn = $('#submitBtn');
    const $prevBtn = $('#prevBtn');

    if (hide) {
      $nextBtn.addClass('d-none');
      $submitBtn.addClass('d-none');
      $prevBtn.addClass('d-none'); // Hide previous button too
      return;
    }

    // Respect current visibility rule for next/submit buttons
    if (!$nextBtn.hasClass('d-none')) {
      $nextBtn.prop('disabled', disable);
    }

    if (!$submitBtn.hasClass('d-none')) {
      $submitBtn.prop('disabled', disable);
    }

    // Handle previous button - only disable if it's currently visible
    if (currentStep > 1 && !$prevBtn.hasClass('d-none')) {
      $prevBtn.prop('disabled', disable);
    }
  }

  $(document).ready(function() {
    loadQuestion(currentStep);

    $('#prevBtn').click(() => {
      if (currentStep > 1) {
        currentStep--;
        loadQuestion(currentStep);
      }
    });

    //  Form submission
    $('#questionForm').on('submit', function(e) {
      e.preventDefault();

      const totalSteps = parseInt($('#totalSteps').val());
      let formData = $(this).serialize();

      // Add final_step=1 if we're on the last step
      const isLastStep = currentStep === totalSteps;
      if (isLastStep) {
        formData += '&final_step=1';
      }

      $.ajax({
        url: "{{ route('student.businessplan.save') }}",
        method: "POST",
        data: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
         success: function(res) {
      const botMsg = botFollowUpMessages[currentStep];
      const nextStep = currentStep + 1;

      if (res.success) {
        if (botMsg) {
          $('#botMessageBox')
            .html('<strong><span class="fw-bold">VentureKids Bot:</span></strong> <span>' + botMsg + '</span>')
            .removeClass('d-none');

          disableNavButtons(true, true); // Hide all buttons during bot message

          setTimeout(() => {
            $('#botMessageBox').addClass('d-none').html('');

            if (res.redirect) {
              // Final step: redirect after bot message
              window.location.href = res.redirect;
            } else {
              currentStep = nextStep;
              loadQuestion(currentStep);
              initializeTooltips();
            }
          }, 3000);
        } else {
          if (res.redirect) {
            window.location.href = res.redirect;
          } else {
            currentStep = nextStep;
            loadQuestion(currentStep);
            initializeTooltips();
          }
        }
      } else {
        alert("Failed to save answer.");
      }
    },
        error: function(xhr) {
          console.error("Server error:", xhr.responseText);
          alert("Something went wrong.");
        }
      });

    });

    //  Not now / No logic
    $(document).on('change', '.step-1-option', function() {
      const text = $(this).data('option-text')?.trim().toLowerCase();
      const qid = $(this).data('question-id');
      const messageDiv = $(`#not-now-message-${qid}`);

      if (['not now', 'no'].includes(text)) {
        messageDiv.show();
        disableNavButtons(true);
      } else {
        messageDiv.hide();
        const anyBlocked = $('.step-1-option:checked').toArray().some(el => ['not now', 'no'].includes($(el).data('option-text')?.trim().toLowerCase()));
        disableNavButtons(anyBlocked);
      }
    });

    // Trigger step-1 initial check
    $(document).on('loadQuestionComplete', function() {
      $('.step-1-option:checked').trigger('change');
      // $('.checkbox-option:checked').trigger('change');
    });
  });
</script>


@endsection