@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">Question Generator</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.realqassessment.topics.index') }}">Topic Generator</a></li>
                        <li class="breadcrumb-item active">Question Generator</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Topic Context</h3>
                    <div class="card-tools">
                        <a href="{{ route('backend.realqassessment.topics.index') }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"><strong>Grade:</strong> {{ $topic->grade_name ?? 'N/A' }}</div>
                        <div class="col-md-3"><strong>Board:</strong> {{ $topic->board_name ?? 'N/A' }}</div>
                        <div class="col-md-3"><strong>Country:</strong> {{ $topic->country_name ?? 'N/A' }}</div>
                        <div class="col-md-3"><strong>Subject:</strong> {{ $topic->subject_name ?? 'N/A' }}</div>
                    </div>
                    <div class="mt-2"><strong>Topic:</strong> {{ $topic->topic }}</div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="questionTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="subjective-tab" data-toggle="tab" href="#subjective-pane" role="tab">Subjective</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled" id="mcq-tab" href="#" role="tab" aria-disabled="true" tabindex="-1">MCQ</a>
                        </li>
                    </ul>
                    <div class="tab-content pt-3">
                        <div class="tab-pane fade show active" id="subjective-pane" role="tabpanel">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="subjective_count">Question Count</label>
                                    <input type="number" min="1" max="20" value="5" class="form-control" id="subjective_count">
                                </div>
                                <div class="form-group col-md-3 d-flex align-items-end">
                                    <button class="btn btn-primary w-100" id="generate-subjective-btn">Generate Subjective</button>
                                </div>
                                <div class="col-md-12" id="subjective-loader" style="display:none;">
                                    <div class="alert alert-info mb-2">Generating subjective questions, please wait...</div>
                                </div>
                            </div>
                            <div id="subjective-list"></div>
                        </div>

                        <div class="tab-pane fade" id="mcq-pane" role="tabpanel">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="mcq_count">Question Count</label>
                                    <input type="number" min="1" max="20" value="5" class="form-control" id="mcq_count">
                                </div>
                                <div class="form-group col-md-3 d-flex align-items-end">
                                    <button class="btn btn-primary w-100" id="generate-mcq-btn">Generate MCQ</button>
                                </div>
                                <div class="col-md-12" id="mcq-loader" style="display:none;">
                                    <div class="alert alert-info mb-2">Generating MCQ questions, please wait...</div>
                                </div>
                            </div>
                            <div id="mcq-list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    const questionRoutes = {
        generate: "{{ route('backend.realqassessment.questions.generate', $topic->id) }}",
        store: "{{ route('backend.realqassessment.questions.store', $topic->id) }}",
    };
    const initialQuestions = {
        subjective: @json($existingSubjectiveQuestions ?? []),
        mcq: @json($existingMcqQuestions ?? []),
    };
    let generatedMeta = {
        subjective: { prompt: '', raw_response: '' },
        mcq: { prompt: '', raw_response: '' }
    };

    function renderSubjectiveRows(rows) {
        let html = '';
        rows.forEach((row, index) => {
            html += `
                <div class="card mb-3" data-question-id="${escapeHtml(row.id || '')}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Question ${index + 1}</strong>
                        <span class="badge ${row.moderation_status === 'approved' ? 'badge-success' : (row.moderation_status === 'rejected' ? 'badge-danger' : 'badge-secondary')} question-status-badge">
                            ${(row.moderation_status || 'pending').toString().replace(/^\w/, c => c.toUpperCase())}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Scenario</label>
                            <textarea class="form-control subjective-scenario" rows="4">${escapeHtml(row.scenario || '')}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Question</label>
                            <textarea class="form-control subjective-question-text" rows="2">${escapeHtml(row.question_text || '')}</textarea>
                        </div>
                        <input type="hidden" class="subjective-moderation-status" value="${escapeHtml(row.moderation_status || 'pending')}">
                        <input type="hidden" class="subjective-reject-reason" value="${escapeHtml(row.reject_reason || '')}">
                        <div class="alert alert-warning subject-reject-display" style="display:${row.moderation_status === 'rejected' ? 'block' : 'none'};">
                            <strong>Rejected Reason:</strong> <span class="subject-reject-text">${escapeHtml(row.reject_reason || '')}</span>
                        </div>
                        <div class="form-group subject-reject-wrap" style="display:none;">
                            <label>Reject Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control subject-reject-input" rows="2"></textarea>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-danger subject-reject-save-btn">Save Reject</button>
                                <button type="button" class="btn btn-sm btn-secondary subject-reject-cancel-btn">Cancel</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-sm btn-success subject-approve-btn">Approve & Save</button>
                        <button type="button" class="btn btn-sm btn-danger ml-2 subject-reject-btn">Reject</button>
                    </div>
                </div>
            `;
        });
        $('#subjective-list').html(html);
        $('#subjective-list .card').each(function() {
            const status = $(this).find('.subjective-moderation-status').val();
            if (status === 'approved' || status === 'rejected') {
                lockQuestionCard($(this));
            }
        });
    }

    function renderMcqRows(rows) {
        let html = '';
        rows.forEach((row, index) => {
            html += `
                <div class="card mb-3" data-question-id="${escapeHtml(row.id || '')}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Question ${index + 1}</strong>
                        <span class="badge ${row.moderation_status === 'approved' ? 'badge-success' : (row.moderation_status === 'rejected' ? 'badge-danger' : 'badge-secondary')} question-status-badge">
                            ${(row.moderation_status || 'pending').toString().replace(/^\w/, c => c.toUpperCase())}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Scenario</label>
                            <textarea class="form-control mcq-scenario" rows="4">${escapeHtml(row.scenario || '')}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Question</label>
                            <textarea class="form-control mcq-question-text" rows="2">${escapeHtml(row.question_text || '')}</textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Option A</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <input type="radio" class="mcq-correct-option-radio" name="mcq_correct_${index}" value="A" ${row.correct_option === 'A' ? 'checked' : ''}>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control mcq-option-a" value="${escapeHtml(row.option_a || '')}">
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Option B</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <input type="radio" class="mcq-correct-option-radio" name="mcq_correct_${index}" value="B" ${row.correct_option === 'B' ? 'checked' : ''}>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control mcq-option-b" value="${escapeHtml(row.option_b || '')}">
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Option C</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <input type="radio" class="mcq-correct-option-radio" name="mcq_correct_${index}" value="C" ${row.correct_option === 'C' ? 'checked' : ''}>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control mcq-option-c" value="${escapeHtml(row.option_c || '')}">
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Option D</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <input type="radio" class="mcq-correct-option-radio" name="mcq_correct_${index}" value="D" ${row.correct_option === 'D' ? 'checked' : ''}>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control mcq-option-d" value="${escapeHtml(row.option_d || '')}">
                                </div>
                            </div>
                        </div>
                        <input type="hidden" class="mcq-moderation-status" value="${escapeHtml(row.moderation_status || 'pending')}">
                        <input type="hidden" class="mcq-reject-reason" value="${escapeHtml(row.reject_reason || '')}">
                        <div class="alert alert-warning mcq-reject-display" style="display:${row.moderation_status === 'rejected' ? 'block' : 'none'};">
                            <strong>Rejected Reason:</strong> <span class="mcq-reject-text">${escapeHtml(row.reject_reason || '')}</span>
                        </div>
                        <div class="form-group mcq-reject-wrap" style="display:none;">
                            <label>Reject Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control mcq-reject-input" rows="2"></textarea>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-danger mcq-reject-save-btn">Save Reject</button>
                                <button type="button" class="btn btn-sm btn-secondary mcq-reject-cancel-btn">Cancel</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-sm btn-success mcq-approve-btn">Approve & Save</button>
                        <button type="button" class="btn btn-sm btn-danger ml-2 mcq-reject-btn">Reject</button>
                    </div>
                </div>
            `;
        });
        $('#mcq-list').html(html);
        $('#mcq-list .card').each(function() {
            const status = $(this).find('.mcq-moderation-status').val();
            if (status === 'approved' || status === 'rejected') {
                lockQuestionCard($(this));
            }
        });
    }

    function escapeHtml(str) {
        return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function generateQuestions(type) {
        if (type === 'mcq') {
            swal('Unavailable', 'MCQ generation is disabled for this page.', 'info');
            return;
        }

        const count = type === 'subjective' ? $('#subjective_count').val() : $('#mcq_count').val();
        const btn = type === 'subjective' ? $('#generate-subjective-btn') : $('#generate-mcq-btn');
        const loader = type === 'subjective' ? $('#subjective-loader') : $('#mcq-loader');
        btn.prop('disabled', true);
        loader.show();

        $.ajax({
            url: questionRoutes.generate,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                question_type: type,
                question_count: count
            },
            success: function(res) {
                generatedMeta[type].prompt = res.prompt || '';
                generatedMeta[type].raw_response = res.raw_response || '';
                if (type === 'subjective') {
                    const existingRows = collectSubjectivePayload();
                    const newRows = (res.questions || []).map(function(row) {
                        row.id = '';
                        return row;
                    });
                    const mergedRows = existingRows.concat(newRows);
                    renderSubjectiveRows(mergedRows);
                    $('#subjective_count').val('5');
                    if (newRows.length) {
                        const firstCard = $('#subjective-list .card').eq(existingRows.length);
                        if (firstCard.length) {
                            $('html, body').animate({ scrollTop: firstCard.offset().top - 120 }, 400);
                        }
                    }
                } else {
                    const existingRows = collectMcqPayload();
                    const newRows = (res.questions || []).map(function(row) {
                        row.id = '';
                        return row;
                    });
                    const mergedRows = existingRows.concat(newRows);
                    renderMcqRows(mergedRows);
                    $('#mcq_count').val('5');
                    if (newRows.length) {
                        const firstCard = $('#mcq-list .card').eq(existingRows.length);
                        if (firstCard.length) {
                            $('html, body').animate({ scrollTop: firstCard.offset().top - 120 }, 400);
                        }
                    }
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Generation failed.';
                swal('Error', message, 'error');
            },
            complete: function() {
                btn.prop('disabled', false);
                loader.hide();
            }
        });
    }

    function collectSubjectiveCard($card) {
        return {
            id: $card.data('question-id') || '',
            moderation_status: $card.find('.subjective-moderation-status').val(),
            question_text: $card.find('.subjective-question-text').val(),
            scenario: $card.find('.subjective-scenario').val(),
            reject_reason: $card.find('.subjective-reject-reason').val(),
        };
    }

    function collectMcqCard($card) {
        return {
            id: $card.data('question-id') || '',
            moderation_status: $card.find('.mcq-moderation-status').val(),
            scenario: $card.find('.mcq-scenario').val(),
            question_text: $card.find('.mcq-question-text').val(),
            option_a: $card.find('.mcq-option-a').val(),
            option_b: $card.find('.mcq-option-b').val(),
            option_c: $card.find('.mcq-option-c').val(),
            option_d: $card.find('.mcq-option-d').val(),
            correct_option: $card.find('.mcq-correct-option-radio:checked').val() || '',
            reject_reason: $card.find('.mcq-reject-reason').val(),
        };
    }

    function collectSubjectivePayload() {
        const rows = [];
        $('#subjective-list .card').each(function() {
            rows.push(collectSubjectiveCard($(this)));
        });
        return rows;
    }

    function collectMcqPayload() {
        const rows = [];
        $('#mcq-list .card').each(function() {
            rows.push(collectMcqCard($(this)));
        });
        return rows;
    }

    function saveSingleQuestion(type, $card, forceStatus) {
        const isSubjective = type === 'subjective';
        const question = isSubjective ? collectSubjectiveCard($card) : collectMcqCard($card);
        if (forceStatus) {
            question.moderation_status = forceStatus;
            $card.find(isSubjective ? '.subjective-moderation-status' : '.mcq-moderation-status').val(forceStatus);
        }

        if (question.moderation_status === 'rejected' && (!question.reject_reason || !question.reject_reason.trim())) {
            swal('Error', 'Reject reason is required.', 'error');
            return;
        }

        const buttons = $card.find('button');
        buttons.prop('disabled', true);

        $.ajax({
            url: questionRoutes.store,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                question_type: type,
                prompt: generatedMeta[type].prompt,
                raw_response: generatedMeta[type].raw_response,
                questions: [question]
            },
            success: function(res) {
                swal('Success', res.message || 'Saved successfully.', 'success');
                if (question.moderation_status === 'approved' || question.moderation_status === 'rejected') {
                    if (question.moderation_status === 'rejected') {
                        $card.find('.subject-reject-wrap, .mcq-reject-wrap').hide();
                        const reasonText = question.reject_reason || '';
                        $card.find('.subject-reject-text').text(reasonText);
                        $card.find('.mcq-reject-text').text(reasonText);
                        $card.find('.subject-reject-display, .mcq-reject-display').show();
                    }
                    const badge = $card.find('.question-status-badge');
                    if (badge.length) {
                        const label = question.moderation_status.charAt(0).toUpperCase() + question.moderation_status.slice(1);
                        badge.removeClass('badge-success badge-danger badge-secondary');
                        badge.addClass(question.moderation_status === 'approved' ? 'badge-success' : 'badge-danger');
                        badge.text(label);
                    }
                    lockQuestionCard($card);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Save failed.';
                swal('Error', message, 'error');
            },
            complete: function() {
                buttons.prop('disabled', false);
            }
        });
    }

    function lockQuestionCard($card) {
        $card.find('textarea, input, select').prop('disabled', true);
        $card.find('.subject-approve-btn, .subject-reject-btn, .mcq-approve-btn, .mcq-reject-btn, .subject-reject-save-btn, .subject-reject-cancel-btn, .mcq-reject-save-btn, .mcq-reject-cancel-btn').remove();
        $card.addClass('question-locked');
    }

    $(document).ready(function() {
        if ((initialQuestions.subjective || []).length) {
            renderSubjectiveRows(initialQuestions.subjective);
            $('#subjective-list .card').each(function() {
                const status = $(this).find('.subjective-moderation-status').val();
                if (status === 'approved' || status === 'rejected') {
                    lockQuestionCard($(this));
                }
            });
        }
        if ((initialQuestions.mcq || []).length) {
            renderMcqRows(initialQuestions.mcq);
            $('#mcq-list .card').each(function() {
                const status = $(this).find('.mcq-moderation-status').val();
                if (status === 'approved' || status === 'rejected') {
                    lockQuestionCard($(this));
                }
            });
        }

        $('#generate-subjective-btn').on('click', function() { generateQuestions('subjective'); });
        $('#mcq-tab').on('click', function(event) {
            event.preventDefault();
        });
        $('#generate-mcq-btn').prop('disabled', true);

        $(document).on('click', '.subject-approve-btn', function() {
            const $card = $(this).closest('.card');
            saveSingleQuestion('subjective', $card, 'approved');
        });
        $(document).on('click', '.subject-reject-btn', function() {
            const $card = $(this).closest('.card');
            $card.find('.subject-approve-btn, .subject-reject-btn').prop('disabled', true);
            $card.find('.subject-reject-wrap').show();
        });

        $(document).on('click', '.mcq-approve-btn', function() {
            const $card = $(this).closest('.card');
            saveSingleQuestion('mcq', $card, 'approved');
        });
        $(document).on('click', '.mcq-reject-btn', function() {
            const $card = $(this).closest('.card');
            $card.find('.mcq-approve-btn, .mcq-reject-btn').prop('disabled', true);
            $card.find('.mcq-reject-wrap').show();
        });

        $(document).on('click', '.subject-reject-cancel-btn', function() {
            const $card = $(this).closest('.card');
            $card.find('.subject-reject-input').val('');
            $card.find('.subject-reject-wrap').hide();
            $card.find('.subject-approve-btn, .subject-reject-btn').prop('disabled', false);
        });
        $(document).on('click', '.mcq-reject-cancel-btn', function() {
            const $card = $(this).closest('.card');
            $card.find('.mcq-reject-input').val('');
            $card.find('.mcq-reject-wrap').hide();
            $card.find('.mcq-approve-btn, .mcq-reject-btn').prop('disabled', false);
        });

        $(document).on('click', '.subject-reject-save-btn', function() {
            const $card = $(this).closest('.card');
            const reason = ($card.find('.subject-reject-input').val() || '').trim();
            if (!reason) {
                swal('Error', 'Reject reason is required.', 'error');
                return;
            }
            $card.find('.subjective-reject-reason').val(reason);
            $card.find('.subjective-moderation-status').val('rejected');
            saveSingleQuestion('subjective', $card, 'rejected');
        });
        $(document).on('click', '.mcq-reject-save-btn', function() {
            const $card = $(this).closest('.card');
            const reason = ($card.find('.mcq-reject-input').val() || '').trim();
            if (!reason) {
                swal('Error', 'Reject reason is required.', 'error');
                return;
            }
            $card.find('.mcq-reject-reason').val(reason);
            $card.find('.mcq-moderation-status').val('rejected');
            saveSingleQuestion('mcq', $card, 'rejected');
        });
    });
</script>
@endsection

