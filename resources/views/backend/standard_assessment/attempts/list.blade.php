@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">Standard Assessment Attempts</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item active">Attempts</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-tools">
                <a href="{{ route('backend.dashboard') }}" class="btn btn-warning">
                    <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            @php
                $defaultSchoolId = $schools->first()->id ?? '';
            @endphp
            <div class="mb-3 row">
                <div class="col-md-3">
                    <label for="schoolFilter">Filter by School</label>
                    <select id="schoolFilter" class="form-control">
                        <!-- <option value="">All Schools</option> -->
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}">
                                {{ $school->school_name }}
                                @if($school->standard_assessment_enabled_from || $school->standard_assessment_enabled_to)
                                    (
                                        {{ $school->standard_assessment_enabled_from
                                            ? \Carbon\Carbon::parse($school->standard_assessment_enabled_from)->format('d-m-Y')
                                            : 'N/A' }}
                                        to
                                        {{ $school->standard_assessment_enabled_to
                                            ? \Carbon\Carbon::parse($school->standard_assessment_enabled_to)->format('d-m-Y')
                                            : 'N/A' }}
                                    )
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="statusFilter">Filter by Status</label>
                    <select id="statusFilter" class="form-control" disabled>
                        <option value="all">All</option>
                        <option value="attempted">Fully Attempted</option>
                        <option value="started">Started</option>
                        <option value="not_started">Not Started</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="levelFilter">Filter by Level</label>
                    <select id="levelFilter" class="form-control" disabled>
                        <option value="">Select Level</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}">{{ $level->grade }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" id="standard-attempts-export-btn" class="btn btn-primary" disabled>Export</button>
                </div>
            </div>

            <table id="standardAttemptsTable" class="table table-bordered table-striped">
                <colgroup>
                    <col style="width: 25%;">
                    <col style="width: 35%;">
                    <col style="width: 15%;">
                    <col style="width: 15%;">
                    <col style="width: 10%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th>Attempted (Completed/Total)</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var attemptsTable = $('#standardAttemptsTable').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: {
                url: "{{ route('backend.standard_assessment.attempts.data') }}",
                type: 'GET',
                dataType: 'json',
                data: function(d) {
                    d.school_id = $('#schoolFilter').val();
                    d.status = $('#statusFilter').val();
                    d.level_id = $('#levelFilter').val();
                }
            },
            columns: [
                { data: 'student_name', name: 'student_name' },
                { data: 'levels', name: 'levels', searchable: false },
                { data: 'status', name: 'status', searchable: false },
                { data: 'attempt_progress', name: 'attempt_progress', searchable: false },
                { data: 'action', name: 'action', searchable: false },
            ]
        });

        function updateFilterState() {
            var hasSchool = !!$('#schoolFilter').val();
            $('#statusFilter').prop('disabled', !hasSchool);
            $('#levelFilter').prop('disabled', !hasSchool);
            $('#standard-attempts-export-btn').prop('disabled', !hasSchool);
        }

        $('#schoolFilter').on('change', function() {
            updateFilterState();
            attemptsTable.ajax.reload();
        });

        $('#statusFilter').on('change', function() {
            attemptsTable.ajax.reload();
        });

        $('#levelFilter').on('change', function() {
            attemptsTable.ajax.reload();
        });

        $(document).on('click', '.reset-attempt', function(e) {
            e.preventDefault();
            var studentId = $(this).data('student-id');

            swal({
                title: "Are you sure?",
                text: "This will remove all standard assessment answers and reports for the student.",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willReset) => {
                if (willReset) {
                    $.ajax({
                        url: "{{ route('backend.standard_assessment.attempts.reset') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            student_id: studentId
                        },
                        success: function(res) {
                            if (res.success) {
                                swal("Reset!", "Attempt reset successfully.", "success");
                                attemptsTable.ajax.reload();
                            } else {
                                swal("Oops!", "Something went wrong.", "error");
                            }
                        },
                        error: function(xhr) {
                            var message = "Failed to reset attempt.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            swal("Error!", message, "error");
                        }
                    });
                }
            });
        });

        function buildExportUrl() {
            const baseUrl = "{{ route('backend.standard_assessment.attempts.export') }}";
            const url = new URL(baseUrl, window.location.origin);
            const schoolId = $('#schoolFilter').val();
            const status = $('#statusFilter').val();
            const levelId = $('#levelFilter').val();
            if (schoolId) {
                url.searchParams.set('school_id', schoolId);
            }
            if (status) {
                url.searchParams.set('status', status);
            }
            if (levelId) {
                url.searchParams.set('level_id', levelId);
            }
            return url.toString();
        }

        $('#standard-attempts-export-btn').on('click', function() {
            var btn = $(this);
            if (btn.prop('disabled')) {
                return;
            }
            btn.prop('disabled', true).text('Exporting...');

            fetch(buildExportUrl(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Export failed.');
                }
                return response.blob().then(function (blob) {
                    return {
                        blob: blob,
                        disposition: response.headers.get('Content-Disposition') || ''
                    };
                });
            })
            .then(function (payload) {
                const link = document.createElement('a');
                const blobUrl = window.URL.createObjectURL(payload.blob);
                link.href = blobUrl;
                var fileName = 'standard_assessment_status_report.xlsx';
                var match = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(payload.disposition);
                if (match && match[1]) {
                    fileName = match[1].replace(/['"]/g, '');
                }
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(blobUrl);
            })
            .catch(function () {
                alert('Failed to export. Please try again.');
            })
            .finally(function () {
                btn.prop('disabled', false).text('Export');
            });
        });

        @if($defaultSchoolId)
            $('#schoolFilter').val("{{ $defaultSchoolId }}");
            $('#statusFilter').val('all');
            updateFilterState();
            attemptsTable.ajax.reload();
        @else
            updateFilterState();
        @endif
    });
</script>
@endsection
