@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">RealQ Accuracy Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('backend.realqassessment.index') }}">RealQ Assessment</a></li>
                        <li class="breadcrumb-item active">Accuracy Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            {{-- ── Filter Bar (AJAX — no page reload) ────────────────────────────────── --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="mb-1 small font-weight-bold">School</label>
                            <select id="filter-school" class="form-control form-control-sm">
                                <option value="">— All Schools —</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}"
                                        {{ (string)$filterSchoolId === (string)$school->id ? 'selected' : '' }}>
                                        {{ $school->school_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <button type="button" id="filter-reset" class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i> Reset
                            </button>
                        </div>
                        <div class="col-md-auto ml-auto">
                            <a href="{{ route('backend.realqassessment.index') }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-chevron-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Summary Cards ──────────────────────────────────────────────────── --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-muted small text-uppercase">Overall Accuracy</div>
                            <div class="h3 mb-0" id="statOverallAccuracy">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-muted small text-uppercase">Reports Reviewed</div>
                            <div class="h3 mb-0" id="statReportsReviewed">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-muted small text-uppercase">Reports Needing Most Edits</div>
                            <div class="h3 mb-0" id="statReportsNeedingEdits">—</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Reports Requiring Most Edits</h3>
                </div>
                <div class="card-body">
                    <div id="accuracyTableEmptyState" class="text-center py-4 text-muted">
                        <i class="fas fa-school fa-2x mb-2"></i><br>
                        Please select a school above to view report data.
                    </div>
                    <div id="accuracyTableWrapper" class="table-responsive d-none">
                        <table id="accuracyReportsTable" class="table table-bordered table-striped" style="width:100%">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Student</th>
                                    <th>Grade</th>
                                    <th>Accuracy Score</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
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
    var table = null;

    function initTable() {
        if (table) {
            return table;
        }
        table = $('#accuracyReportsTable').DataTable({
            processing: true,
            serverSide: true,
            order: [[5, 'desc']],
            ajax: {
                url: "{{ route('backend.realqassessment.accuracy-dashboard.data') }}",
                type: 'GET',
                dataType: 'json',
                data: function (d) {
                    d.school_id = $('#filter-school').val();
                },
                dataSrc: function (json) {
                    updateSummaryCards(json.overallAccuracy, json.reportsReviewed, json.reportsNeedingEdits);
                    return json.data;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'student_name', name: 'student_name', defaultContent: '—' },
                { data: 'grade_name', name: 'grade_name', defaultContent: '—' },
                { data: 'accuracy_score', name: 'accuracy_score', searchable: false },
                { data: 'status', name: 'status' },
                { data: 'updated_at', name: 'updated_at' },
            ]
        });
        return table;
    }

    function updateSummaryCards(overallAccuracy, reportsReviewed, reportsNeedingEdits) {
        $('#statOverallAccuracy').text(overallAccuracy === null || overallAccuracy === undefined ? '—' : overallAccuracy + '%');
        $('#statReportsReviewed').text(reportsReviewed ?? '—');
        $('#statReportsNeedingEdits').text(reportsNeedingEdits ?? '—');
    }

    function showTable() {
        $('#accuracyTableEmptyState').addClass('d-none');
        $('#accuracyTableWrapper').removeClass('d-none');
        if (!table) {
            initTable();
        } else {
            table.draw();
        }
    }

    function hideTable() {
        $('#accuracyTableWrapper').addClass('d-none');
        $('#accuracyTableEmptyState').removeClass('d-none');
        updateSummaryCards(null, null, null);
    }

    $('#filter-school').on('change', function () {
        if ($(this).val()) {
            showTable();
        } else {
            hideTable();
        }
    });

    $('#filter-reset').on('click', function () {
        $('#filter-school').val('');
        hideTable();
    });

    if ($('#filter-school').val()) {
        showTable();
    }
});
</script>
@endsection
