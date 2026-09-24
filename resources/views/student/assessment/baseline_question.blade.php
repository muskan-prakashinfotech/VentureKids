@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper" id="sa-baseline-assessment-page">
    <section>
        <div class="assessment-tab">
            <div class="pageTitle">
                <h2>Assessment</h2>
            </div>
        </div>

        <div class="card sa-mcq-shell">
            <div class="card-body">
                <div id="sa_mcq_overlay_loader" class="sa-overlay-loader">
                    <div class="sa-spinner"></div>
                </div>

                <div id="sa-mcq-question-screen" style="display:none;">
                    <div class="text-center mb-2">
                        <small id="sa_mcq_progress_text">Answering 1/1 Questions</small>
                    </div>

                    <div class="sa-mcq-card">
                        <div class="sa-mcq-row mb-3">
                            <label class="d-block mb-1">Question:</label>
                            <p id="sa_mcq_question" class="fw-bold mb-0"></p>
                        </div>
                        <div class="sa-mcq-row">
                            <label class="sa-mcq-option"><input type="radio" name="sa_mcq_option" value="A"><span id="sa_mcq_option_a"></span></label>
                            <label class="sa-mcq-option"><input type="radio" name="sa_mcq_option" value="B"><span id="sa_mcq_option_b"></span></label>
                            <label class="sa-mcq-option"><input type="radio" name="sa_mcq_option" value="C"><span id="sa_mcq_option_c"></span></label>
                            <label class="sa-mcq-option"><input type="radio" name="sa_mcq_option" value="D"><span id="sa_mcq_option_d"></span></label>
                        </div>
                        <div id="sa_mcq_error" class="text-danger mt-2" style="display:none;"></div>
                    </div>

                    <div class="sa-mcq-footer">
                        <button type="button" id="sa_mcq_prev_btn" class="sa-mcq-back-btn" disabled>PREVIOUS</button>
                        <button type="button" id="sa_mcq_next_btn" class="sa-mcq-start-btn">NEXT</button>
                    </div>
                </div>

                <div id="sa_mcq_complete_state" style="display:none;">
                    <div class="sa-mcq-card text-center">
                        <h5 id="sa_mcq_complete_title" class="mb-2">Your report is generating...</h5>
                        <p id="sa_mcq_complete_message" class="text-muted mb-3">Please wait, this may take a few seconds.</p>
                        <a href="javascript:void(0);" id="sa_mcq_download_btn" class="sa-mcq-start-btn disabled" style="pointer-events:none; opacity:0.6;">DOWNLOAD REPORT</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    (function () {
        let currentAnswerId = {{ (int) $initial_answer_id }};
        let previousAnswerId = null;
        let nextAnswerId = null;

        const questionUrl = "{{ route('student.assessment.baseline.question') }}";
        const actionUrl = "{{ route('student.assessment.baseline.action') }}";
        const assessmentIndexUrl = "{{ route('student.assessment') }}";

        function showError(message) {
            const errorBox = $('#sa_mcq_error');
            errorBox.text(message || 'Something went wrong.');
            errorBox.show();
        }

        function clearError() {
            $('#sa_mcq_error').hide().text('');
        }

        function getSelectedOption() {
            return $('input[name="sa_mcq_option"]:checked').val() || null;
        }

        function setSelectedOption(option) {
            $('input[name="sa_mcq_option"]').prop('checked', false);
            if (option) {
                $('input[name="sa_mcq_option"][value="' + option + '"]').prop('checked', true);
            }
        }

        function setLoadingState(isLoading) {
            $('#sa_mcq_prev_btn').prop('disabled', isLoading);
            $('#sa_mcq_next_btn').prop('disabled', isLoading);
            if (isLoading) {
                $('#sa_mcq_overlay_loader').css('display', 'flex');
            } else {
                $('#sa_mcq_overlay_loader').hide();
            }
        }

        function renderQuestion(data) {
            currentAnswerId = data.answer_id;
            previousAnswerId = data.previous_answer_id;
            nextAnswerId = data.next_answer_id;

            $('#sa_mcq_question').text(data.question_text);
            $('#sa_mcq_option_a').text(data.option_a);
            $('#sa_mcq_option_b').text(data.option_b);
            $('#sa_mcq_option_c').text(data.option_c);
            $('#sa_mcq_option_d').text(data.option_d);
            $('#sa_mcq_progress_text').text('Answering ' + data.question_position + '/' + data.total_count + ' Questions');

            setSelectedOption(data.selected_option);
            $('#sa_mcq_prev_btn').prop('disabled', !previousAnswerId);
            if (!nextAnswerId) {
                $('#sa_mcq_next_btn').text('SUBMIT');
            } else {
                $('#sa_mcq_next_btn').text('NEXT');
            }
            clearError();
            $('#sa-mcq-question-screen').show();
        }

        function loadQuestion(answerId) {
            setLoadingState(true);
            $.ajax({
                url: questionUrl,
                method: 'GET',
                data: { answer_id: answerId },
                success: function (res) {
                    if (!res.success) {
                        showError(res.message);
                        return;
                    }
                    renderQuestion(res.data);
                },
                error: function () {
                    showError('Failed to load question.');
                },
                complete: function () {
                    setLoadingState(false);
                }
            });
        }

        function runAction(actionName, selectedOption) {
            setLoadingState(true);
            clearError();

            $.ajax({
                url: actionUrl,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    answer_id: currentAnswerId,
                    action: actionName,
                    selected_option: selectedOption
                },
                success: function (res) {
                    if (!res.success) {
                        showError(res.message);
                        return;
                    }

                    if (res.save_exit || res.completed) {
                        if (res.completed) {
                            $('#sa-mcq-question-screen').hide();
                            $('#sa_mcq_complete_state').show();
                            $('#sa_mcq_complete_title').text('Successfully submitted.');
                            $('#sa_mcq_complete_message').text(
                                res.completion_text || 'Use this self-assessment to identify areas where you can focus your personal development efforts. Remember, self-awareness is the first step towards improvement.'
                            );

                            const reportUrl = res.report_url || "{{ route('student.assessment.baseline.report') }}";
                            $('#sa_mcq_download_btn')
                                .attr('href', reportUrl)
                                .removeClass('disabled')
                                .css({'pointer-events':'auto','opacity':'1'})
                                .text('DOWNLOAD REPORT');
                            return;
                        }

                        window.location.href = assessmentIndexUrl;
                        return;
                    }

                    if (res.next_answer_id) {
                        loadQuestion(res.next_answer_id);
                        return;
                    }

                    if (res.previous_answer_id) {
                        loadQuestion(res.previous_answer_id);
                        return;
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                        showError(xhr.responseJSON.message);
                    } else {
                        showError('Action failed.');
                    }
                },
                complete: function () {
                    setLoadingState(false);
                }
            });
        }

        $('#sa_mcq_prev_btn').on('click', function () {
            const selectedOption = getSelectedOption();
            runAction('prev', selectedOption);
        });

        $('#sa_mcq_next_btn').on('click', function () {
            const selectedOption = getSelectedOption();
            if (!selectedOption) {
                showError('Please select an option.');
                return;
            }
            runAction('next', selectedOption);
        });

        loadQuestion(currentAnswerId);
    })();
</script>
@endsection
