@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <section>
        <div class="assessment-tab">
            <div class="pageTitle">
                <h2>Assessment</h2>
            </div>
        </div>

        <div class="card sa-subj-shell">
            <div class="card-body">
                <div id="sa-subj-setup-screen" @if(!empty($resumeRedirect)) style="display:none;" @endif>
                    <h3 class="sa-subj-headline">Let’s get started</h3>
                    <div class="sa-subj-fixed-context">
                        Board: <strong>{{ $boardName ?: '-' }}</strong> | Country: <strong>{{ $countryName ?: '-' }}</strong>
                    </div>

                    <div class="sa-subj-step">
                        <div class="sa-subj-step-title"><span>1</span> Step 1: Select Your Grade</div>
                        <select id="sa_grade_id" class="form-control sa-subj-select" disabled>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->id }}" {{ !empty($assignedGrade) && (int) $assignedGrade->id === (int) $grade->id ? 'selected' : '' }}>
                                    {{ $grade->name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" id="sa_grade_id_hidden" value="{{ $assignedGrade ? $assignedGrade->id : '' }}">
                    </div>

                    <div class="sa-subj-step">
                        <div class="sa-subj-step-title"><span>2</span> Step 2: Select Academic Subjects</div>
                        <div class="sa-subj-subject-grid">
                            @foreach($subjects as $subject)
                                <label class="sa-subj-subject-item">
                                    <input type="checkbox" value="{{ $subject->id }}" class="sa_subject_checkbox" checked disabled />
                                    <span>{{ $subject->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="sa-subj-step">
                        <div class="sa-subj-step-title"><span>3</span> Step 3: Generate &amp; Select Topic</div>
                        <button type="button" id="sa_load_topics_btn" class="sa-subj-click-btn">CLICK HERE</button>
                        <div class="sa-subj-note">Select up to 2 topics per selected subject.</div>
                        <div id="sa_topics_wrap" class="sa-subj-topics-wrap"></div>
                    </div>

                    <div class="sa-subj-footer">
                        <button type="button" id="sa_start_assessment_btn" class="sa-subj-start-btn">Start Assessment</button>
                    </div>
                </div>

                <div id="sa-subj-loader" style="display:none;text-align:center;padding:48px 0;">
                    <p style="color:#888;font-size:15px;">Loading your assessment, please wait…</p>
                </div>

                <div id="sa-subj-question-screen" style="display:none;">
                    <div id="sa_subj_overlay_loader" class="sa-overlay-loader" style="display:none;">
                        <div class="sa-spinner"></div>
                    </div>
                    <div class="sa-q-progress" id="sa_q_progress_text">Answering 1/5 Questions</div>
                    <div class="sa-q-card">
                        <div class="sa-q-row">
                            <h4 id="sa_q_title">Question 1</h4>
                        </div>
                        <div class="sa-q-row">
                            <label>Scenario:</label>
                            <p id="sa_q_scenario"></p>
                        </div>
                        <div class="sa-q-row">
                            <label>Task:</label>
                            <p id="sa_q_task"></p>
                        </div>
                        <div class="sa-q-row">
                            <textarea id="sa_q_answer" class="form-control" rows="4" placeholder="Your Answer..." required autocomplete="off"></textarea>
                        </div>
                    </div>

                    <div class="sa-subj-footer">
                        <button type="button" id="sa_prev_btn" class="sa-subj-back-btn">BACK</button>
                        <button type="button" id="sa_next_btn" class="sa-subj-start-btn">NEXT</button>
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
            start: "{{ route('student.assessment.subjective.start') }}",
            saveAnswer: "{{ route('student.assessment.subjective.save-answer') }}",
            report: "{{ route('student.assessment.report') }}",
            assessmentHome: "{{ route('student.assessment') }}"
        };

        let questions = [];
        let currentIndex = 0;
        let answersMap = {};
        let assessmentInProgress = false;
        let allowAssessmentExit = false;

        function setQuestionLoading(isLoading) {
            $('#sa_prev_btn').prop('disabled', isLoading);
            $('#sa_next_btn').prop('disabled', isLoading);
            if (isLoading) {
                $('#sa_subj_overlay_loader').css('display', 'flex');
            } else {
                $('#sa_subj_overlay_loader').hide();
            }
        }

        function getSelectedSubjects() {
            const subjectIds = [];
            $('.sa_subject_checkbox:checked').each(function() {
                subjectIds.push($(this).val());
            });
            return subjectIds;
        }

        function getSelectedTopics() {
            const topicIds = [];
            $('.sa_topic_checkbox:checked').each(function() {
                topicIds.push($(this).val());
            });
            return topicIds;
        }

        function renderTopics(groups) {
            let html = '';
            (groups || []).forEach(function(group) {
                html += '<div class="sa-subj-topic-group">';
                html += '<h5>' + (group.subject_name || 'Subject') + '</h5>';
                html += '<div class="sa-subj-topic-list" data-subject-id="' + group.subject_id + '">';
                if ((group.topics || []).length) {
                    (group.topics || []).forEach(function(topic) {
                        html += '<label class="sa-subj-topic-item">';
                        html += '<input type="checkbox" class="sa_topic_checkbox" data-subject-id="' + group.subject_id + '" value="' + topic.id + '">';
                        html += '<span>' + (topic.topic || '') + '</span>';
                        html += '</label>';
                    });
                } else {
                    html += '<p class="sa-subj-topic-empty">No topics found for this subject.</p>';
                }
                html += '</div></div>';
            });
            $('#sa_topics_wrap').html(html || '<p class="mb-0">No topics found for selected filters.</p>');
        }

        function enforceTopicLimitPerSubject() {
            const selectedBySubject = {};
            $('.sa_topic_checkbox:checked').each(function() {
                const subjectId = $(this).data('subject-id');
                selectedBySubject[subjectId] = (selectedBySubject[subjectId] || 0) + 1;
            });

            $('.sa_topic_checkbox').each(function() {
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
            assessmentInProgress = true;

            $('#sa_q_progress_text').text('Answering ' + (currentIndex + 1) + '/' + questions.length + ' Questions');
            $('#sa_q_title').text(q.title || ('Question ' + (currentIndex + 1)));
            $('#sa_q_scenario').text(q.scenario || '');
            $('#sa_q_task').text(q.question_text || '');
            $('#sa_q_answer').val(answersMap[q.id] || '');
            $('#sa_prev_btn').prop('disabled', currentIndex === 0);
            $('#sa_next_btn').text(currentIndex === (questions.length - 1) ? 'FINISH ASSESSMENT' : 'NEXT');
        }

        function preventAnswerTransfer(event) {
            event.preventDefault();
            swal('Info', 'Please type your answer yourself. Copy, paste, cut and drop are disabled during this assessment.', 'info');
        }

        $('#sa_q_answer').on('paste copy cut drop contextmenu', preventAnswerTransfer);
        $('#sa_q_answer').on('keydown', function(event) {
            const key = (event.key || '').toLowerCase();
            if ((event.ctrlKey || event.metaKey) && ['v', 'c', 'x', 'a'].includes(key)) {
                event.preventDefault();
                if (key !== 'a') {
                    swal('Info', 'Please type your answer yourself. Copy, paste and cut are disabled during this assessment.', 'info');
                }
            }
        });

        window.history.pushState({ realqAssessment: true }, '', window.location.href);
        window.addEventListener('popstate', function() {
            if (!assessmentInProgress || allowAssessmentExit) {
                return;
            }

            window.history.pushState({ realqAssessment: true }, '', window.location.href);
            swal('Assessment in progress', 'Please complete the assessment before leaving this page.', 'warning');
        });

        window.addEventListener('beforeunload', function(event) {
            if (!assessmentInProgress || allowAssessmentExit) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        });

        $(document).on('click', 'a', function(event) {
            if (!assessmentInProgress || allowAssessmentExit) {
                return;
            }

            const href = $(this).attr('href') || '';
            if (href === '' || href === '#' || href.toLowerCase().startsWith('javascript:')) {
                return;
            }

            event.preventDefault();
            swal('Assessment in progress', 'Please complete the assessment before leaving this page.', 'warning');
        });

        function saveCurrentAnswer(isSubmitted, onDone) {
            const q = questions[currentIndex];
            if (!q) {
                if (typeof onDone === 'function') onDone();
                return;
            }

            const answerText = $('#sa_q_answer').val() || '';
            answersMap[q.id] = answerText;

            $.ajax({
                url: routes.saveAnswer,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    assessment_question_id: q.id,
                    answer_text: answerText,
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

        $('#sa_load_topics_btn').on('click', function() {
            const gradeId = $('#sa_grade_id_hidden').val() || $('#sa_grade_id').val();
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

        $(document).on('change', '.sa_topic_checkbox', function() {
            enforceTopicLimitPerSubject();
        });

        // Note: no quotes — must be a real JS boolean, not a string ("0" is truthy in JS)
        const resumeRedirect = {{ !empty($resumeRedirect) ? 'true' : 'false' }};
        if (resumeRedirect) {
            $('#sa-subj-loader').show();
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

                    $('#sa-subj-loader').hide();

                    if (!questions.length) {
                        $('#sa-subj-setup-screen').show();
                        return;
                    }

                    $('#sa-subj-question-screen').show();
                    renderCurrentQuestion();
                },
                error: function(xhr) {
                    $('#sa-subj-loader').hide();
                    $('#sa-subj-setup-screen').show();
                    if (xhr.responseJSON && xhr.responseJSON.completed && xhr.responseJSON.report_url) {
                        swal('Info', xhr.responseJSON.message || 'Assessment already completed.', 'info').then(function() {
                            window.location.href = xhr.responseJSON.report_url;
                        });
                        return;
                    }
                }
            });
        }

        $('#sa_start_assessment_btn').on('click', function() {
            const gradeId = $('#sa_grade_id_hidden').val() || $('#sa_grade_id').val();
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
                    $('#sa-subj-setup-screen').hide();
                    $('#sa-subj-loader').show();
                },
                success: function(res) {
                    questions = res.questions || [];
                    answersMap = res.answers || {};
                    currentIndex = typeof res.current_index === 'number' ? res.current_index : 0;
                    if (currentIndex < 0 || currentIndex >= questions.length) {
                        currentIndex = 0;
                    }

                    $('#sa-subj-loader').hide();

                    if (!questions.length) {
                        $('#sa-subj-setup-screen').show();
                        swal('Error', 'No questions available for selected filters.', 'error');
                        return;
                    }

                    $('#sa-subj-question-screen').show();
                    renderCurrentQuestion();
                },
                error: function(xhr) {
                    $('#sa-subj-loader').hide();
                    $('#sa-subj-setup-screen').show();
                    if (xhr.responseJSON && xhr.responseJSON.completed && xhr.responseJSON.report_url) {
                        swal('Info', xhr.responseJSON.message || 'Assessment already completed.', 'info').then(function() {
                            window.location.href = xhr.responseJSON.report_url;
                        });
                        return;
                    }
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Unable to start assessment.';
                    swal('Error', msg, 'error');
                }
            });
        });

        $('#sa_prev_btn').on('click', function() {
            setQuestionLoading(true);
            saveCurrentAnswer(false, function() {
                if (currentIndex > 0) {
                    currentIndex -= 1;
                    renderCurrentQuestion();
                }
                setQuestionLoading(false);
            });
        });

        $('#sa_next_btn').on('click', function() {
            const isLast = currentIndex === (questions.length - 1);
            const answerText = ($('#sa_q_answer').val() || '').trim();
            if (!answerText) {
                swal('Error', 'Please enter your answer before continuing.', 'error');
                return;
            }

            if (!isLast) {
                setQuestionLoading(true);
                saveCurrentAnswer(true, function() {
                    currentIndex += 1;
                    renderCurrentQuestion();
                    setQuestionLoading(false);
                });
                return;
            }

            // ── Finish Assessment 
            setQuestionLoading(false);

            var saveDone  = false;
            var okClicked = false;

            function maybeRedirect() {
                if (okClicked && saveDone) {
                    allowAssessmentExit  = true;
                    assessmentInProgress = false;
                    window.location.href = routes.report;
                }
            }

            saveCurrentAnswer(true, function() {
                saveDone = true;
                maybeRedirect();
            });

            swal('Success', 'Assessment completed successfully!', 'success').then(function() {
                okClicked = true;

                $('body').append(
                    '<div id="sa-submit-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;' +
                    'background:#fff;z-index:99999;display:flex;flex-direction:column;align-items:center;' +
                    'justify-content:center;gap:16px;">' +
                    '<p style="font-size:16px;color:#555;margin:0;">Submitting your assessment, please wait…</p>' +
                    '</div>'
                );

                maybeRedirect();
            });
        });

        $('#sa_start_assessment_btn').text('Start Assessment');
    })();
</script>
@endsection
