@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <section>
        <div class="assessment-tab">
            <div class="pageTitle">
                <h2>Assessment</h2>
            </div>
        </div>

        <div class="card sa-mcq-shell">
            <div class="card-body">
                <div id="sa-mcq-setup-screen" @if(!empty($resumeRedirect)) style="display:none;" @endif>
                    <h3 class="sa-mcq-headline">Let's get started</h3>
                    <div class="sa-mcq-fixed-context">
                        Board: <strong>{{ $boardName ?: '-' }}</strong> | Country: <strong>{{ $countryName ?: '-' }}</strong>
                    </div>

                    <div class="sa-mcq-step">
                        <div class="sa-mcq-step-title"><span>1</span> Step 1: Select Your Grade</div>
                        <select id="sa_mcq_grade_id" class="form-control sa-mcq-select" disabled>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->id }}" {{ !empty($assignedGrade) && (int) $assignedGrade->id === (int) $grade->id ? 'selected' : '' }}>
                                    {{ $grade->name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" id="sa_mcq_grade_id_hidden" value="{{ $assignedGrade ? $assignedGrade->id : '' }}">
                    </div>

                    <div class="sa-mcq-step">
                        <div class="sa-mcq-step-title"><span>2</span> Step 2: Select Academic Subjects</div>
                        <div class="sa-mcq-subject-grid">
                            @foreach($subjects as $subject)
                                <label class="sa-mcq-subject-item">
                                    <input type="checkbox" value="{{ $subject->id }}" class="sa_mcq_subject_checkbox" checked />
                                    <span>{{ $subject->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="sa-mcq-step">
                        <div class="sa-mcq-step-title"><span>3</span> Step 3: Generate &amp; Select Topic</div>
                        <button type="button" id="sa_mcq_load_topics_btn" class="sa-mcq-click-btn">CLICK HERE</button>
                        <div class="sa-mcq-note">Select up to 2 topics per selected subject.</div>
                        <div id="sa_mcq_topics_wrap" class="sa-mcq-topics-wrap"></div>
                    </div>

                    <div class="sa-mcq-footer">
                        <button type="button" id="sa_mcq_start_assessment_btn" class="sa-mcq-start-btn">Start Assessment</button>
                    </div>
                </div>

                <div id="sa-mcq-question-screen" style="display:none;">
                    <div id="sa_mcq_overlay_loader" class="sa-overlay-loader" style="display:none;">
                        <div class="sa-spinner"></div>
                    </div>
                    <div class="sa-mcq-progress" id="sa_mcq_progress_text">Answering 1/10 Questions</div>
                    <div class="sa-mcq-card">
                        <!-- <div class="sa-mcq-row">
                            <h4 id="sa_mcq_title">Question 1</h4>
                        </div> -->
                        <div class="sa-mcq-row">
                            <label>Scenario:</label>
                            <p id="sa_mcq_scenario"></p>
                        </div>
                        <div class="sa-mcq-row">
                            <label>Question:</label>
                            <p id="sa_mcq_question" class="fw-bold"></p>
                        </div>
                        <div class="sa-mcq-row">
                            <label class="sa-mcq-option"><input type="radio" name="sa_mcq_option" value="A"><span id="sa_mcq_option_a"></span></label>
                            <label class="sa-mcq-option"><input type="radio" name="sa_mcq_option" value="B"><span id="sa_mcq_option_b"></span></label>
                            <label class="sa-mcq-option"><input type="radio" name="sa_mcq_option" value="C"><span id="sa_mcq_option_c"></span></label>
                            <label class="sa-mcq-option"><input type="radio" name="sa_mcq_option" value="D"><span id="sa_mcq_option_d"></span></label>
                        </div>
                    </div>

                    <div class="sa-mcq-footer">
                        <button type="button" id="sa_mcq_prev_btn" class="sa-mcq-back-btn">BACK</button>
                        <button type="button" id="sa_mcq_next_btn" class="sa-mcq-start-btn">NEXT</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    (function() {
        const routes = {
            topics: "{{ route('student.assessment.subjective.topics') }}",
            start: "{{ route('student.assessment.mcq.start') }}",
            saveAnswer: "{{ route('student.assessment.mcq.save-answer') }}",
            report: "{{ route('student.assessment.report', ['mode' => 'mcq']) }}",
            assessmentHome: "{{ route('student.assessment') }}"
        };

        let questions = [];
        let currentIndex = 0;
        let answersMap = {};

        function setQuestionLoading(isLoading) {
            $('#sa_mcq_prev_btn').prop('disabled', isLoading);
            $('#sa_mcq_next_btn').prop('disabled', isLoading);
            if (isLoading) {
                $('#sa_mcq_overlay_loader').css('display', 'flex');
            } else {
                $('#sa_mcq_overlay_loader').hide();
            }
        }

        function getSelectedSubjects() {
            const subjectIds = [];
            $('.sa_mcq_subject_checkbox:checked').each(function() {
                subjectIds.push($(this).val());
            });
            return subjectIds;
        }

        function getSelectedTopics() {
            const topicIds = [];
            $('.sa_mcq_topic_checkbox:checked').each(function() {
                topicIds.push($(this).val());
            });
            return topicIds;
        }

        function renderTopics(groups) {
            let html = '';
            (groups || []).forEach(function(group) {
                html += '<div class="sa-mcq-topic-group">';
                html += '<h5>' + (group.subject_name || 'Subject') + '</h5>';
                html += '<div class="sa-mcq-topic-list" data-subject-id="' + group.subject_id + '">';
                if ((group.topics || []).length) {
                    (group.topics || []).forEach(function(topic) {
                        html += '<label class="sa-mcq-topic-item">';
                        html += '<input type="checkbox" class="sa_mcq_topic_checkbox" data-subject-id="' + group.subject_id + '" value="' + topic.id + '">';
                        html += '<span>' + (topic.topic || '') + '</span>';
                        html += '</label>';
                    });
                } else {
                    html += '<p class="sa-mcq-topic-empty">No topics found for this subject.</p>';
                }
                html += '</div></div>';
            });
            $('#sa_mcq_topics_wrap').html(html || '<p class="mb-0">No topics found for selected filters.</p>');
        }

        function enforceTopicLimitPerSubject() {
            const selectedBySubject = {};
            $('.sa_mcq_topic_checkbox:checked').each(function() {
                const subjectId = $(this).data('subject-id');
                selectedBySubject[subjectId] = (selectedBySubject[subjectId] || 0) + 1;
            });

            $('.sa_mcq_topic_checkbox').each(function() {
                const subjectId = $(this).data('subject-id');
                const isChecked = $(this).is(':checked');
                if (!isChecked && (selectedBySubject[subjectId] || 0) >= 2) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            });
        }

        function renderCurrentQuestion() {
            const q = questions[currentIndex];
            if (!q) return;

            $('#sa_mcq_progress_text').text('Answering ' + (currentIndex + 1) + '/' + questions.length + ' Questions');
            $('#sa_mcq_title').text(q.title || ('Question ' + (currentIndex + 1)));
            $('#sa_mcq_scenario').text(q.scenario || '');
            $('#sa_mcq_question').text(q.question_text || '');
            $('#sa_mcq_option_a').text(q.option_a || '');
            $('#sa_mcq_option_b').text(q.option_b || '');
            $('#sa_mcq_option_c').text(q.option_c || '');
            $('#sa_mcq_option_d').text(q.option_d || '');

            $('input[name="sa_mcq_option"]').prop('checked', false);
            if (answersMap[q.id]) {
                $('input[name="sa_mcq_option"][value="' + answersMap[q.id] + '"]').prop('checked', true);
            }

            $('#sa_mcq_prev_btn').prop('disabled', currentIndex === 0);
            $('#sa_mcq_next_btn').text(currentIndex === (questions.length - 1) ? 'FINISH ASSESSMENT' : 'NEXT');
        }

        function saveCurrentAnswer(isSubmitted, onDone) {
            const q = questions[currentIndex];
            if (!q) {
                if (typeof onDone === 'function') onDone();
                return;
            }

            const selectedOption = $('input[name="sa_mcq_option"]:checked').val() || '';
            answersMap[q.id] = selectedOption;

            $.ajax({
                url: routes.saveAnswer,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    assessment_question_id: q.id,
                    selected_option: selectedOption,
                    is_submitted: isSubmitted ? 1 : 0
                },
                success: function() {
                    if (typeof onDone === 'function') onDone();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to save answer.';
                    swal('Error', msg, 'error');
                }
            });
        }

        $('#sa_mcq_load_topics_btn').on('click', function() {
            const gradeId = $('#sa_mcq_grade_id_hidden').val() || $('#sa_mcq_grade_id').val();
            const subjectIds = getSelectedSubjects();

            if (!gradeId) {
                swal('Error', 'Please select a grade first.', 'error');
                return;
            }
            if (!subjectIds.length) {
                swal('Error', 'Please select at least one subject.', 'error');
                return;
            }

            $.ajax({
                url: routes.topics,
                method: 'GET',
                data: {
                    grade_id: gradeId,
                    subject_ids: subjectIds
                },
                success: function(res) {
                    renderTopics(res.topics_by_subject || []);
                    enforceTopicLimitPerSubject();
                    if (res.used_fallback) {
                        swal('Info', 'No topics found for strict CBSE/Singapore filter. Showing available topics for selected grade/subjects.', 'info');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to load topics.';
                    swal('Error', msg, 'error');
                }
            });
        });

        $(document).on('change', '.sa_mcq_topic_checkbox', function() {
            enforceTopicLimitPerSubject();
        });

        const resumeRedirect = "{{ !empty($resumeRedirect) ? 1 : 0 }}";
        if (resumeRedirect) {
            $('#sa-mcq-question-screen').show();
            setQuestionLoading(true);
            $.ajax({
                url: routes.start,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    resume: 1
                },
                success: function(res) {
                    questions = res.questions || [];
                    answersMap = res.answers || {};
                    currentIndex = typeof res.current_index === 'number' ? res.current_index : 0;
                    if (currentIndex < 0 || currentIndex >= questions.length) {
                        currentIndex = 0;
                    }

                    if (!questions.length) {
                        $('#sa-mcq-question-screen').hide();
                        $('#sa-mcq-setup-screen').show();
                        setQuestionLoading(false);
                        return;
                    }

                    $('#sa-mcq-question-screen').show();
                    renderCurrentQuestion();
                    setQuestionLoading(false);
                },
                error: function(xhr) {
                    $('#sa-mcq-question-screen').hide();
                    $('#sa-mcq-setup-screen').show();
                    setQuestionLoading(false);
                    if (xhr.responseJSON && xhr.responseJSON.completed && xhr.responseJSON.report_url) {
                        swal('Info', xhr.responseJSON.message || 'Assessment already completed.', 'info').then(function() {
                            window.location.href = xhr.responseJSON.report_url;
                        });
                        return;
                    }
                }
            });
        }

        $('#sa_mcq_start_assessment_btn').on('click', function() {
            const gradeId = $('#sa_mcq_grade_id_hidden').val() || $('#sa_mcq_grade_id').val();
            const subjectIds = getSelectedSubjects();
            const topicIds = getSelectedTopics();

            if (!gradeId || !subjectIds.length || !topicIds.length) {
                swal('Error', 'Please select grade, subjects and topics.', 'error');
                return;
            }

            const payload = {
                _token: '{{ csrf_token() }}',
                grade_id: gradeId,
                subject_ids: subjectIds,
                topic_ids: topicIds
            };

            $.ajax({
                url: routes.start,
                method: 'POST',
                data: payload,
                beforeSend: function() {
                    setQuestionLoading(true);
                },
                success: function(res) {
                    questions = res.questions || [];
                    answersMap = res.answers || {};
                    currentIndex = typeof res.current_index === 'number' ? res.current_index : 0;
                    if (currentIndex < 0 || currentIndex >= questions.length) {
                        currentIndex = 0;
                    }

                    if (!questions.length) {
                        swal('Error', 'No questions available for selected filters.', 'error');
                        setQuestionLoading(false);
                        return;
                    }

                    $('#sa-mcq-setup-screen').hide();
                    $('#sa-mcq-question-screen').show();
                    renderCurrentQuestion();
                    setQuestionLoading(false);
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.completed && xhr.responseJSON.report_url) {
                        swal('Info', xhr.responseJSON.message || 'Assessment already completed.', 'info').then(function() {
                            window.location.href = xhr.responseJSON.report_url;
                        });
                        setQuestionLoading(false);
                        return;
                    }
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        swal('Error', xhr.responseJSON.message, 'error');
                        setQuestionLoading(false);
                        return;
                    }
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to start assessment.';
                    swal('Error', msg, 'error');
                    setQuestionLoading(false);
                }
            });
        });

        $('#sa_mcq_prev_btn').on('click', function() {
            setQuestionLoading(true);
            saveCurrentAnswer(false, function() {
                if (currentIndex > 0) {
                    currentIndex -= 1;
                    renderCurrentQuestion();
                }
                setQuestionLoading(false);
            });
        });

        $('#sa_mcq_next_btn').on('click', function() {
            const isLast = currentIndex === (questions.length - 1);
            const selectedOption = $('input[name="sa_mcq_option"]:checked').val() || '';
            if (!selectedOption) {
                swal('Error', 'Please select an option before continuing.', 'error');
                return;
            }
            setQuestionLoading(true);
            saveCurrentAnswer(true, function() {
                if (isLast) {
                    swal('Success', 'Assessment answers saved successfully.', 'success').then(function() {
                        window.location.href = routes.report;
                    });
                    setQuestionLoading(false);
                    return;
                }
                currentIndex += 1;
                renderCurrentQuestion();
                setQuestionLoading(false);
            });
        });

        $('#sa_mcq_start_assessment_btn').text('Start Assessment');
    })();
</script>
@endsection
