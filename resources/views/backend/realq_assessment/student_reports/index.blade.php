@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">RealQ Student Reports</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.realqassessment.index') }}">RealQ Assessment</a></li>
                        <li class="breadcrumb-item active">Student Reports</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- ── Filter Bar ─────────────────────────────────────────────────────── --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="mb-1 small font-weight-bold">School <span class="text-danger">*</span></label>
                            <select id="filter-school" class="form-control form-control-sm">
                                <option value="">— Select School —</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}"
                                        {{ (string)$filterSchoolId === (string)$school->id ? 'selected' : '' }}>
                                        {{ $school->school_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="mb-1 small font-weight-bold">Student</label>
                            <select id="filter-student" class="form-control form-control-sm" disabled>
                                <option value="">— Select School First —</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="mb-1 small font-weight-bold">Status</label>
                            <select id="filter-status" class="form-control form-control-sm">
                                <option value="">— All Statuses —</option>
                                <option value="pending"   {{ $filterStatus === 'pending'   ? 'selected' : '' }}>Pending</option>
                                <option value="generated" {{ $filterStatus === 'generated' ? 'selected' : '' }}>Generated</option>
                                <option value="approved"  {{ $filterStatus === 'approved'  ? 'selected' : '' }}>Approved</option>
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="button" id="filter-reset" class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">RealQ Student Report Queue</h3>
                    <a href="{{ route('backend.realqassessment.index') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-chevron-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <div id="no-school-message" class="text-center py-4 text-muted" style="display:none;">
                        <i class="fas fa-school fa-2x mb-2"></i><br>
                        Please select a school above to view student reports.
                    </div>
                    <div id="reports-table-wrapper" class="table-responsive" style="display:none;">
                        <table id="realq_student_reports_table" class="table table-bordered table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>School</th>
                                    <th>Grade</th>
                                    <th>Submitted At</th>
                                    <th>Status</th>
                                    <th style="min-width:220px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<script>
$(document).ready(function () {

    var datatableUrl = "{{ route('backend.realqassessment.student-reports.datatable') }}";
    var table = null;

    // Populate student dropdown via AJAX for the given schoolId.
    // Pre-selects selectedStudentId if provided.
    function loadStudents(schoolId, selectedStudentId) {
        var $sel = $('#filter-student');
        if (!schoolId) {
            $sel.prop('disabled', true).html('<option value="">— Select School First —</option>');
            return;
        }
        $sel.prop('disabled', true).html('<option value="">Loading…</option>');
        $.ajax({
            url: '/admin/realq-assessment/student-reports/students-by-school/' + schoolId,
            method: 'GET',
            success: function (students) {
                var html = '<option value="">— All Students —</option>';
                $.each(students, function (i, s) {
                    var selected = String(s.id) === String(selectedStudentId) ? ' selected' : '';
                    html += '<option value="' + s.id + '"' + selected + '>' + s.name + '</option>';
                });
                $sel.html(html).prop('disabled', false);
            },
            error: function () {
                $sel.html('<option value="">— Failed to load students —</option>').prop('disabled', true);
            }
        });
    }

    // Lazily initialize the DataTable (only once a school is selected) and refresh it on filter changes.
    function refreshTable() {
        var schoolId = $('#filter-school').val();

        if (!schoolId) {
            $('#no-school-message').show();
            $('#reports-table-wrapper').hide();
            return;
        }

        $('#no-school-message').hide();
        $('#reports-table-wrapper').show();

        if (table) {
            table.ajax.reload();
            return;
        }

        table = $('#realq_student_reports_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: datatableUrl,
                type: 'GET',
                dataType: 'JSON',
                data: function (d) {
                    d.school_id  = $('#filter-school').val();
                    d.student_id = $('#filter-student').val();
                    d.status     = $('#filter-status').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'student_name', name: 'student.name' },
                { data: 'school_name', name: 'student.school.school_name' },
                { data: 'grade_name', name: 'grade_name', orderable: false, searchable: false },
                { data: 'submitted_at', name: 'created_at' },
                { data: 'status_badge', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    }

    // School change: repopulate student dropdown (reset to "All") and refresh the table
    $('#filter-school').on('change', function () {
        loadStudents($(this).val(), '');
        refreshTable();
    });

    // Student or status change: refresh the table only
    $('#filter-student, #filter-status').on('change', function () {
        refreshTable();
    });

    // Reset filters back to defaults and refresh
    $('#filter-reset').on('click', function () {
        $('#filter-school').val('');
        $('#filter-status').val('');
        loadStudents(null, null);
        refreshTable();
    });

    // On page load: if a school is already selected, populate student dropdown and load the table
    var initSchool  = '{{ $filterSchoolId ?? '' }}';
    var initStudent = '{{ $filterStudentId ?? '' }}';
    if (initSchool) {
        loadStudents(initSchool, initStudent);
    }
    refreshTable();

    // ── Generate ──────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-generate', function () {
        const btn      = $(this);
        const reportId = btn.data('id');

        swal({
            title: 'Generate Report?',
            text: 'This will call OpenAI to generate the student report. This may take a moment.',
            icon: 'info',
            buttons: { cancel: 'Cancel', confirm: { text: 'Generate', className: 'btn-primary' } },
            dangerMode: false,
        }).then(function (confirmed) {
            if (!confirmed) return;

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating…');

            $.ajax({
                url: '/admin/realq-assessment/student-reports/' + reportId + '/generate',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) {
                        swal('Done!', res.message, 'success').then(function () {
                            if (res.edit_url) {
                                window.location.href = res.edit_url;
                            } else {
                                table.ajax.reload(null, false);
                            }
                        });
                    } else {
                        swal('Error', res.message || 'Generation failed.', 'error');
                        btn.prop('disabled', false).html('<i class="fas fa-magic"></i> Generate');
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong.';
                    swal('Error', msg, 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-magic"></i> Generate');
                }
            });
        });
    });

    // ── Approve ───────────────────────────────────────────────────────────────
    $(document).on('click', '.btn-approve', function () {
        const btn      = $(this);
        const reportId = btn.data('id');

        swal({
            title: 'Approve Report?',
            text: 'The student will be able to view and download their report immediately after approval.',
            icon: 'warning',
            buttons: { cancel: 'Cancel', confirm: { text: 'Approve', className: 'btn-success' } },
            dangerMode: false,
        }).then(function (confirmed) {
            if (!confirmed) return;

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Approving…');

            $.ajax({
                url: '/admin/realq-assessment/student-reports/' + reportId + '/approve',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (res) {
                    if (res.success) {
                        swal('Approved!', res.message, 'success').then(function () {
                            table.ajax.reload(null, false);
                        });
                    } else {
                        swal('Error', res.message || 'Approval failed.', 'error');
                        btn.prop('disabled', false).html('<i class="fas fa-check"></i> Approve');
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Something went wrong.';
                    swal('Error', msg, 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-check"></i> Approve');
                }
            });
        });
    });

});
</script>
@endsection
