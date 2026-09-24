@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Question Bank</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">RealQ Assessment</li>
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
                    <div class="card-header d-flex justify-content-between align-items-start flex-wrap">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <a class="nav-link question-bank-tab active"
                                   href="#"
                                   data-mode="subjective">
                                    Subjective
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link question-bank-tab disabled"
                                   href="#"
                                   data-mode="mcq"
                                   aria-disabled="true"
                                   tabindex="-1">
                                    MCQ
                                </a>
                            </li>
                        </ul>
                        <div class="ml-auto">
                            <a href="{{ route('backend.realqassessment.index') }}" class="btn btn-warning">
                                <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <span class="badge badge-success mr-2">Approved: <span id="approvedCount">{{ $approvedCount }}</span></span>
                                <span class="badge badge-danger">Rejected: <span id="rejectedCount">{{ $rejectedCount }}</span></span>
                                <span class="badge badge-secondary ml-2">Archived: <span id="archivedCount">{{ $archivedCount ?? 0 }}</span></span>
                            </div>
                            <div class="col-md-6 text-md-right">
                                <div class="text-muted">Use filters to narrow the list.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 mb-3">
                                <label for="gradeFilter">Grade</label>
                                <select id="gradeFilter" class="form-control">
                                    <option value="">All Grades</option>
                                    @foreach ($grades as $grade)
                                        <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="boardFilter">Board</label>
                                <select id="boardFilter" class="form-control">
                                    <option value="">All Boards</option>
                                    @foreach ($boards as $board)
                                        <option value="{{ $board->id }}">{{ $board->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="countryFilter">Country</label>
                                <select id="countryFilter" class="form-control">
                                    <option value="">All Countries</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="subjectFilter">Subject</label>
                                <select id="subjectFilter" class="form-control">
                                    <option value="">All Subjects</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="topicFilter">Topic (Approved)</label>
                                <select id="topicFilter" class="form-control">
                                    <option value="">All Topics</option>
                                    @foreach ($topics as $topic)
                                        <option value="{{ $topic->id }}">{{ $topic->topic }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="statusFilter">Status</label>
                                <select id="statusFilter" class="form-control">
                                    <option value="all">All</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="archived">Archived</option>
                                    <!-- <option value="pending">Pending</option> -->
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="realqQuestionBankTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Question</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <div id="questionBankEmptyState" class="alert alert-info mt-3 d-none">
                            No questions are available for the selected filters yet. Try switching filters, or generate and approve questions first.
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="questionDetailsModal" tabindex="-1" role="dialog" aria-labelledby="questionDetailsTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="questionDetailsTitle">Question Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Status</strong>
                        <div id="modalStatus" class="mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <strong>Scenario</strong>
                        <div id="modalScenario" class="mt-1 text-muted"></div>
                    </div>
                    <div class="mb-3">
                        <strong>Question</strong>
                        <div id="modalQuestion" class="mt-1"></div>
                    </div>
                    <div id="modalOptionsWrap" class="mb-3">
                        <strong>Options</strong>
                        <ul class="mt-2">
                            <li id="modalOptionA"></li>
                            <li id="modalOptionB"></li>
                            <li id="modalOptionC"></li>
                            <li id="modalOptionD"></li>
                        </ul>
                    </div>
                    <div id="modalCorrectWrap" class="mb-3">
                        <strong>Correct Option</strong>
                        <div id="modalCorrectOption" class="mt-1"></div>
                    </div>
                    <div id="modalRejectWrap" class="mb-3">
                        <strong>Reject Reason</strong>
                        <div id="modalRejectReason" class="mt-1 text-muted"></div>
                    </div>
                    <div id="modalLoading" class="text-center text-muted d-none">
                        Loading details...
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    $(document).ready(function() {
        var currentMode = "subjective";
        $('.question-bank-tab').on('click', function(e) {
            e.preventDefault();
            var mode = $(this).data('mode');
            if (mode === 'mcq') {
                return;
            }
            if (mode === currentMode) {
                return;
            }
            currentMode = mode;
            $('.question-bank-tab').removeClass('active');
            $(this).addClass('active');
            resetFilters();
            table.ajax.reload();
        });

        var table = $('#realqQuestionBankTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: {
                url: "{{ route('backend.realqassessment.questions.bank.data') }}",
                type: 'GET',
                dataType: 'json',
                data: function(d) {
                    d.mode = currentMode;
                    d.grade_id = $('#gradeFilter').val();
                    d.board_id = $('#boardFilter').val();
                    d.country_id = $('#countryFilter').val();
                    d.subject_id = $('#subjectFilter').val();
                    d.topic_id = $('#topicFilter').val();
                    d.status = $('#statusFilter').val();
                },
                dataSrc: function(json) {
                    if (json && json.data && json.data.length === 0) {
                        $('#questionBankEmptyState').removeClass('d-none');
                    } else {
                        $('#questionBankEmptyState').addClass('d-none');
                    }
                    if (json && typeof json.approvedCount !== 'undefined') {
                        $('#approvedCount').text(json.approvedCount);
                    }
                    if (json && typeof json.rejectedCount !== 'undefined') {
                        $('#rejectedCount').text(json.rejectedCount);
                    }
                    if (json && typeof json.archivedCount !== 'undefined') {
                        $('#archivedCount').text(json.archivedCount);
                    }
                    return json.data;
                }
            },
            columns: [
                { data: 'question', name: 'question' },
                { data: 'status', name: 'status', searchable: false },
                { data: 'action', name: 'action', searchable: false },
            ]
        });

        $('#gradeFilter, #boardFilter, #countryFilter, #subjectFilter, #topicFilter, #statusFilter').on('change', function() {
            table.ajax.reload();
        });

        function resetFilters() {
            $('#gradeFilter').val('');
            $('#boardFilter').val('');
            $('#countryFilter').val('');
            $('#subjectFilter').val('');
            $('#topicFilter').val('');
            $('#statusFilter').val('all');
        }

        $(document).on('click', '.view-question', function() {
            var $btn = $(this);
            var id = $btn.data('id');
            if (!id) {
                return;
            }

            $('#modalScenario').text('');
            $('#modalQuestion').text('');
            $('#modalRejectReason').text('');
            $('#modalStatus').text('');
            $('#modalCorrectOption').text('');
            $('#modalCorrectWrap').hide();
            $('#modalOptionsWrap').hide();
            $('#modalLoading').removeClass('d-none');
            $('#questionDetailsModal').modal('show');

            $btn.prop('disabled', true).text('Loading...');
            $.ajax({
                url: "{{ route('backend.realqassessment.questions.bank.show', ['id' => '__id__']) }}".replace('__id__', id),
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (!res || !res.success || !res.data) {
                        swal("Error", "Failed to load question details.", "error");
                        return;
                    }
                    var data = res.data;
                    var mode = data.question_type || 'subjective';

                    $('#modalScenario').text(data.scenario || '-');
                    $('#modalQuestion').text(data.question_text || '-');
                    $('#modalRejectReason').text(data.reject_reason || '-');
                    if (data.moderation_status === 'approved') {
                        $('#modalStatus').html('<span class="badge badge-success">Approved</span>');
                        $('#modalRejectWrap').hide();
                    } else if (data.moderation_status === 'rejected') {
                        $('#modalStatus').html('<span class="badge badge-danger">Rejected</span>');
                        $('#modalRejectWrap').show();
                    } else if (data.moderation_status === 'archived') {
                        $('#modalStatus').html('<span class="badge badge-secondary">Archived</span>');
                        $('#modalRejectWrap').hide();
                    } else {
                        $('#modalStatus').html('<span class="badge badge-warning">Pending</span>');
                        $('#modalRejectWrap').hide();
                    }

                    if (mode === 'mcq') {
                        $('#modalOptionsWrap').show();
                        $('#modalOptionA').text('A. ' + (data.option_a || '-'));
                        $('#modalOptionB').text('B. ' + (data.option_b || '-'));
                        $('#modalOptionC').text('C. ' + (data.option_c || '-'));
                        $('#modalOptionD').text('D. ' + (data.option_d || '-'));
                        if (data.correct_option) {
                            $('#modalCorrectWrap').show();
                            $('#modalCorrectOption').text(data.correct_option);
                        } else {
                            $('#modalCorrectWrap').hide();
                            $('#modalCorrectOption').text('-');
                        }
                    } else {
                        $('#modalOptionsWrap').hide();
                        $('#modalCorrectWrap').hide();
                    }
                },
                error: function() {
                    swal("Error", "Failed to load question details.", "error");
                },
                complete: function() {
                    $('#modalLoading').addClass('d-none');
                    $btn.prop('disabled', false).text('View Details');
                }
            });
        });

        $(document).on('click', '.archive-question', function() {
            var id = $(this).data('id');
            var $btn = $(this);
            if (!id) {
                return;
            }

            swal({
                title: "Archive Question?",
                text: "Archived questions will not be shown to students in future assessments.",
                icon: "warning",
                buttons: true,
                dangerMode: false,
            }).then(function(willArchive) {
                if (!willArchive) {
                    return;
                }

                $btn.prop('disabled', true).text('Archiving...');
                $.ajax({
                    url: "{{ route('backend.realqassessment.questions.bank.archive', ['id' => '__id__']) }}".replace('__id__', id),
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if (!res || !res.success) {
                            swal("Error", (res && res.message) ? res.message : "Unable to archive question.", "error");
                            return;
                        }
                        swal("Success", res.message || "Question archived successfully.", "success");
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : "Unable to archive question.";
                        swal("Error", msg, "error");
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('Archive');
                    }
                });
            });
        });
    });
    </script>
@endsection
