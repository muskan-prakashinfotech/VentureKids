<strong>VentureKids Bot:</strong>

@foreach ($questions as $question)
@php
  $answer = $question->answers->first(); // Assuming only one answer per question
  $isNumericOnly = $question->step == 8 || ($question->step == 10 && in_array($question->display_order, [2, 3]) || $question->step ==5);
  $inputType = $isNumericOnly ? 'number' : 'text';
@endphp

<div class="mb-4">
  @if (!empty($question->prompt_text))
  <div class="mb-2">
    <p class="mb-1">{!! nl2br(e($question->prompt_text)) !!}</p>

    @if ($question->step == 8 && isset($currencies))
    <div class="d-flex align-items-center gap-2 mt-2">
      <label class="mb-0 fw-semibold">Select Currency:</label>
      <select
        class="form-select form-select-sm currency-selector"
        name="currency_id[{{ $question->id }}]"
        style="width: auto; min-width: 80px;">
        <option value="">-- Select --</option>
        @foreach ($currencies as $currency)
        <option value="{{ $currency->id }}" {{ isset($answer) && $answer->currency_id == $currency->id ? 'selected' : '' }}>
          {{ $currency->code }}
        </option>
        @endforeach
      </select>
    </div>
    @endif
  </div>
  @endif

  <label class="fw-bold">{{ $question->question_value }}</label>
  <input type="hidden" name="question_id[]" value="{{ $question->id }}">

 @if ($question->question_type === 'textarea')
  <textarea
    name="response_text[{{ $question->id }}]"
    id="step{{ $question->step }}-order{{ $question->display_order }}"
    class="form-control"
    rows="4"
    placeholder="{{ $question->placeholder_text }}"
    required>{{ old("response_text.{$question->id}", $answer->response_text ?? '') }}</textarea>

@elseif ($question->question_type === 'text')
  <input type="{{ $inputType }}"
    name="response_text[{{ $question->id }}]"
    id="step{{ $question->step }}-order{{ $question->display_order }}"
    class="form-control"
    value="{{ old("response_text.{$question->id}", $answer->response_text ?? '') }}"
    placeholder="{{ $question->placeholder_text }}"
    @if($isNumericOnly) min="0" step="any" @endif
    required>



  @elseif ($question->question_type === 'radio')
    @foreach ($question->options as $option)
    <div class="form-check">
      <input type="radio"
        class="form-check-input step-1-option"
        name="selected_option_id[{{ $question->id }}]"
        value="{{ $option->id }}"
        data-option-text="{{ strtolower($option->option_text) }}"
        data-question-id="{{ $question->id }}"
       {{ isset($answer) && $answer->selected_option_id == $option->id ? 'checked' : '' }}
        required>
      <label class="form-check-label">{{ $option->option_text }}</label>

      @if (strtolower($option->option_text) === 'not now' || strtolower($option->option_value ?? '') === 'no')
      <em class="not-now-warning text-danger ms-2"
        id="not-now-message-{{ $question->id }}"
        style="display: none;">
        – Oops..Come back once you are ready
      </em>
      @endif
    </div>
    @endforeach

  {{-- Uncomment below if checkbox question type is needed in future --}}
  {{-- 
  @elseif ($question->question_type === 'checkbox')
    @foreach ($question->options as $option)
    <div class="form-check">
      <input type="checkbox"
        class="form-check-input checkbox-option"
        name="selected_options[{{ $question->id }}][]"
        value="{{ $option->id }}"
        data-show-input="{{ $option->show_input_field }}"
        data-question-id="{{ $question->id }}">
      <label class="form-check-label">{{ $option->option_text }}</label>
    </div>
    @endforeach

    <div class="mt-2 custom-text-input" data-question-id="{{ $question->id }}" style="display: none;">
      <input type="text" name="custom_text[{{ $question->id }}]" class="form-control"
        placeholder="Please specify..." value="">
    </div>
  --}}
  @endif
</div>
@endforeach
