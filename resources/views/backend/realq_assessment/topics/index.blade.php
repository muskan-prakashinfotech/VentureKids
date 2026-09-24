@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Subject-Wise Topic Generator</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Topic Generator</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @if (session()->has('success'))
                    <div class="alert alert-success" style="text-align: center;">
                        {{ session()->get('success') }}
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger" style="text-align: center;">
                        {{ session()->get('error') }}
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Generate Topics</h3>
                        <div class="card-tools">
                            <a href="{{ route('backend.realqassessment.index') }}" class="btn btn-warning">
                                <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="topic-generate-form" action="{{ route('backend.realqassessment.topics.generate') }}" method="POST" class="row">
                            @csrf
                            <div class="form-group col-md-4">
                                <label for="grade_id">Grade</label>
                                <select class="form-control @error('grade_id') is-invalid @enderror" id="grade_id" name="grade_id" required>
                                    <option value="">Select Grade</option>
                                    @foreach ($grades as $grade)
                                        <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>
                                            {{ $grade->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('grade_id')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="board_id">Board</label>
                                <select class="form-control @error('board_id') is-invalid @enderror" id="board_id" name="board_id" required>
                                    <option value="">Select Board</option>
                                    @foreach ($boards as $board)
                                        <option value="{{ $board->id }}" {{ old('board_id') == $board->id ? 'selected' : '' }}>
                                            {{ $board->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('board_id')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="country_id">Country</label>
                                <select class="form-control @error('country_id') is-invalid @enderror" id="country_id" name="country_id" required>
                                    <option value="">Select Country</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_id')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="subject_id">Subject</label>
                                <select class="form-control @error('subject_id') is-invalid @enderror" id="subject_id" name="subject_id" required>
                                    <option value="">Select Subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="form-group col-md-4">
                                <label for="topic_count">Topic Count</label>
                                <input type="number" min="1" max="50" class="form-control @error('topic_count') is-invalid @enderror"
                                    id="topic_count" name="topic_count" value="{{ old('topic_count', 10) }}">
                                @error('topic_count')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="form-group col-md-4 d-flex align-items-end">
                                <button type="submit" id="generate-btn" class="btn btn-primary w-100">Generate</button>
                            </div>
                        </form>
                        <div id="topic-loader" class="text-center mt-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <div class="mt-2">Generating topics, please wait...</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Generated Topics</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped realq-topics-table">
                            <thead>
                                <tr>
                                    <th>Topic</th>
                                    <th>Status</th>
                                    <th>Grade</th>
                                    <th>Board</th>
                                    <th>Country</th>
                                    <th>Subject</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="topicModerationModal" tabindex="-1" role="dialog" aria-labelledby="topicModerationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="topicModerationModalLabel">Review Generated Topics</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="topicModerationTable">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">Topic</th>
                                    <th style="width: 25%;">Moderation</th>
                                    <th style="width: 20%;">Save</th>
                                    <th style="width: 20%;">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="text-muted">Edit the topic inline, choose approve, then save each topic.</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const table = $('#example1').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: "{{ route('backend.realqassessment.topics.list') }}",
                    type: 'GET',
                    dataType: 'json',
                },
                columns: [
                    { data: 'topic', name: 'realq_assessment_topics.topic' },
                    { data: 'status', name: 'realq_assessment_topics.moderation_status', orderable: false, searchable: false },
                    { data: 'grade', name: 'student_grade.name' },
                    { data: 'board', name: 'student_board.name' },
                    { data: 'country', name: 'countrys.name' },
                    { data: 'subject', name: 'realq_assessment_subjects.name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                columnDefs: [
                    { targets: -1, className: 'text-nowrap' }
                ]
            });

            let lastGenerateContext = null;

            $('#topic-generate-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const formData = form.serialize();
                $('#generate-btn').prop('disabled', true);
                $('#topic-loader').show();

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(res) {
                        if (res && res.topics && res.topics.length) {
                            lastGenerateContext = {
                                grade_id: $('#grade_id').val(),
                                board_id: $('#board_id').val(),
                                country_id: $('#country_id').val(),
                                subject_id: $('#subject_id').val(),
                                prompt: res.prompt || '',
                                raw_response: res.raw_response || ''
                            };
                            renderModerationRows(res.topics);
                            $('#topicModerationModal').modal('show');
                        } else {
                            swal("Error", "No topics generated.", "error");
                        }
                    },
                    error: function(xhr) {
                        let message = 'Failed to generate topics.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        swal("Error", message, "error");
                    },
                    complete: function() {
                        $('#generate-btn').prop('disabled', false);
                        $('#topic-loader').hide();
                    }
                });
            });

            $('#topicModerationModal').on('hidden.bs.modal', function () {
                $('#topicModerationTable tbody').empty();
                lastGenerateContext = null;
                $('#topic-generate-form')[0].reset();
            });

            function renderModerationRows(topics) {
                const tbody = $('#topicModerationTable tbody');
                tbody.empty();
                (topics || []).forEach(function(topic, idx) {
                    const safeTopic = (topic || '').toString();
                    const row = `
                        <tr data-index="${idx}" data-saved="0">
                            <td>
                                <input type="text" class="form-control topic-input" value="${safeTopic.replace(/"/g, '&quot;')}">
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-outline-secondary btn-sm active">
                                        <input type="radio" name="topic_status_${idx}" value="pending" checked> Pending
                                    </label>
                                    <label class="btn btn-outline-success btn-sm">
                                        <input type="radio" name="topic_status_${idx}" value="approved"> Approve
                                    </label>
                                    <label class="btn btn-outline-danger btn-sm">
                                        <input type="radio" name="topic_status_${idx}" value="rejected"> Reject
                                    </label>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary topic-save-btn">Save</button>
                                <span class="badge badge-success topic-saved-badge" style="display:none;">Saved</span>
                            </td>
                            <td class="topic-status text-muted">Pending</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            }

            $(document).on('click', '.topic-save-btn', function() {
                const row = $(this).closest('tr');
                const topicText = row.find('.topic-input').val().trim();
                const selectedStatus = row.find('input[type="radio"][name^="topic_status_"]:checked').val() || 'pending';

                if (!topicText) {
                    swal("Error", "Topic cannot be empty.", "error");
                    return;
                }
                if (!lastGenerateContext) {
                    swal("Error", "Missing generation context. Please generate topics again.", "error");
                    return;
                }

                if (row.data('saved') === 1) {
                    return;
                }

                row.find('.topic-save-btn').prop('disabled', true);
                row.find('.topic-status').text('Saving...');

                $.ajax({
                    url: "{{ route('backend.realqassessment.topics.store') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        grade_id: lastGenerateContext.grade_id,
                        board_id: lastGenerateContext.board_id,
                        country_id: lastGenerateContext.country_id,
                        subject_id: lastGenerateContext.subject_id,
                        topic: topicText,
                        moderation_status: selectedStatus,
                        prompt: lastGenerateContext.prompt,
                        raw_response: lastGenerateContext.raw_response
                    },
                    success: function(res) {
                        let badge = '<span class="badge badge-secondary">Pending</span>';
                        if (selectedStatus === 'approved') {
                            badge = '<span class="badge badge-success">Approved</span>';
                        } else if (selectedStatus === 'rejected') {
                            badge = '<span class="badge badge-danger">Rejected</span>';
                        }
                        row.find('.topic-status').html(badge);
                        row.data('saved', 1);
                        row.find('.topic-input, input[type="radio"][name^="topic_status_"]').prop('disabled', true);
                        row.find('.topic-save-btn').hide();
                        row.find('.topic-saved-badge').show();
                        row.addClass('topic-row-disabled');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        let message = 'Failed to save topic.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        swal("Error", message, "error");
                        row.find('.topic-status').text('Pending');
                    },
                    complete: function() {
                        row.find('.topic-save-btn').prop('disabled', false);
                    }
                });
            });

            $(document).on('click', '.delete-topic', function() {
                const deleteUrl = $(this).data('url');
                swal({
                    title: "Are you sure?",
                    text: "You want to delete this topic!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (!willDelete) {
                        return;
                    }
                    $.ajax({
                        url: deleteUrl,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            swal("Deleted!", "Topic has been deleted.", "success");
                            table.ajax.reload();
                        },
                        error: function(xhr) {
                            let message = 'Failed to delete the topic.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            swal("Error!", message, "error");
                        }
                    });
                });
            });
        });
    </script>
@endsection

