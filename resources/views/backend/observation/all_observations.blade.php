@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">

    {{-- Page header --}}
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="m-0">School Sessions &amp; Updates</h1>
                    <p class="text-muted mb-0">View trainer session updates and student observations by school</p>
                </div>
                <div class="col-sm-4">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="{{ route('backend.home') }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">View All Observation</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            {{-- ===== FILTER CARD ===== --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('backend.observations.all') }}" id="filterForm">
                        <div class="row align-items-end">

                            {{-- School --}}
                            <div class="col-lg-3 col-md-6 mb-2">
                                <label class="font-weight-bold small mb-1">Select School</label>
                                <select class="form-control" name="school_id" id="school_id">
                                    <option value="" {{ $selected_school ? '' : 'selected' }}>Select School</option>
                                    @foreach ($school_list as $school)
                                        <option value="{{ $school->id }}"
                                            {{ $selected_school == $school->id ? 'selected' : '' }}>
                                            {{ $school->school_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Trainer — populated for the selected school (see rebuildTrainerDropdown) --}}
                            <div class="col-lg-3 col-md-6 mb-2">
                                <label class="font-weight-bold small mb-1">Select Trainer</label>
                                <select class="form-control" name="trainer_id" id="trainer_id" {{ $selected_school ? '' : 'disabled' }}>
                                    @if ($selected_school)
                                        <option value="">All Trainers</option>
                                        @foreach ($trainers as $trainer)
                                            <option value="{{ $trainer->id }}"
                                                {{ $selected_trainer == $trainer->id ? 'selected' : '' }}>
                                                {{ $trainer->trainer_name }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" selected>Select School First</option>
                                    @endif
                                </select>
                            </div>

                            {{-- Level — same structure as index_new.blade.php --}}
                            <div class="col-lg-2 col-md-6 mb-2">
                                <label class="font-weight-bold small mb-1">Select Level</label>
                                <select class="form-control" name="level_id" id="level_id">
                                    <option value="">All Levels</option>
                                    @if(isset($filtered_levels['primary']))
                                        <optgroup label="Levels">
                                            @foreach ($filtered_levels['primary'] as $level)
                                                <option value="{{ $level['id'] }}"
                                                    {{ $selected_level == $level['id'] ? 'selected' : '' }}>
                                                    {{ $level['grade'] }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    @if(isset($filtered_levels['add-ons']))
                                        <optgroup label="Content Add-Ons">
                                            @foreach ($filtered_levels['add-ons'] as $level)
                                                <option value="{{ $level['id'] }}"
                                                    {{ $selected_level == $level['id'] ? 'selected' : '' }}>
                                                    {{ $level['grade'] }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                            </div>

                            {{-- Date range --}}
                            <div class="col-lg-3 col-md-6 mb-2">
                                <label class="font-weight-bold small mb-1">Select Date Range</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" name="date_from" id="date_from"
                                        value="{{ $date_from }}" placeholder="From">
                                    <div class="input-group-prepend input-group-append">
                                        <span class="input-group-text">–</span>
                                    </div>
                                    <input type="date" class="form-control" name="date_to" id="date_to"
                                        value="{{ $date_to }}" placeholder="To">
                                </div>
                            </div>

                            {{-- Clear filters — hidden until a filter is selected; same look as school.session_report's Clear button --}}
                            <div class="col-lg-1 col-md-6 mb-2" id="clearFiltersWrap"
                                 style="{{ ($selected_school || $selected_trainer || $selected_level || $date_from || $date_to) ? '' : 'display:none;' }}">
                                <button type="button" id="clearFiltersBtn" class="btn btn-sm btn-secondary">Clear</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            {{-- ===== SCHOOL INFO CARD (shown when a school is selected) ===== --}}
            <div id="schoolInfoWrap">
            @if ($selected_school_data)
            <div class="card mb-3">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                             style="width:52px;height:52px;background:#3a7bd5;flex-shrink:0;">
                            <i class="fas fa-school text-white" style="font-size:1.3rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold">{{ $selected_school_data->school_name }}</h5>
                            <small class="text-muted">
                                Total Trainers: <strong>{{ $school_trainer_count }}</strong>
                                &nbsp;|&nbsp;
                                Total Students: <strong>{{ $school_student_count }}</strong>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            </div>

            {{-- ===== SESSION TABLE ===== --}}
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="card-title mb-0">Session Updates &amp; Observations</h5>
                    <div class="d-flex flex-wrap" style="gap:8px;">
                        <button type="button" id="downloadPdfBtn" class="btn btn-sm btn-success" disabled
                                data-base="{{ route('backend.observations.all.download') }}">
                            <i class="fas fa-file-pdf mr-1"></i> Download Session Report
                        </button>
                        <button type="button" id="downloadStudentObsBtn" class="btn btn-sm btn-warning" disabled
                                data-base="{{ route('backend.observations.all.download_student_reports') }}">
                            <i class="fas fa-file-archive mr-1"></i> Download Student Observation Report
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="example1">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:280px;">Session Details</th>
                                    <th style="width:220px;">Trainer Update</th>
                                    <th style="width:380px;">Student Observations</th>
                                    <th style="width:140px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Populated entirely via the server-side DataTable AJAX call below,
                                     same pattern as trainer/student/list. --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

{{-- ===== SESSION OBSERVATIONS MODAL ===== --}}
<div class="modal fade" id="sessionObsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">

            <div class="modal-header border-0 pb-0">
                <div style="flex:1;min-width:0;">
                    <small class="text-muted font-weight-bold text-uppercase" id="modalSchoolName" style="letter-spacing:.04em;"></small>
                    <h5 class="modal-title font-weight-bold mb-1 mt-1" id="modalSessionTitle">Session Observations</h5>
                    <div class="d-flex flex-wrap" style="gap:16px;font-size:0.82rem;">
                        <span class="text-muted">Date:&nbsp;<strong class="text-dark" id="modalSessionDate"></strong></span>
                        <span class="text-muted">Trainer:&nbsp;<strong class="text-dark" id="modalSessionTrainer"></strong></span>
                    </div>
                </div>
                <button type="button" class="close ml-3 mt-0" data-dismiss="modal" style="flex-shrink:0;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body pt-2" id="modalBody">
                {{-- filled by JS --}}
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // On a true browser refresh, clear filters by redirecting to the bare URL.
    const navEntry = performance.getEntriesByType('navigation')[0];
    if (navEntry && navEntry.type === 'reload' && window.location.search) {
        window.location.replace(window.location.pathname);
        return;
    }

    const allTrainers        = @json($all_trainers);
    const trainerAllocations = @json($trainer_allocations);
    const selectedTrainer    = '{{ $selected_trainer }}';

    const form          = document.getElementById('filterForm');
    const schoolSelect  = document.getElementById('school_id');
    const trainerSelect = document.getElementById('trainer_id');
    const levelSelect   = document.getElementById('level_id');
    const dateFrom      = document.getElementById('date_from');
    const dateTo        = document.getElementById('date_to');
    const clearFiltersBtn  = document.getElementById('clearFiltersBtn');
    const clearFiltersWrap = document.getElementById('clearFiltersWrap');
    const downloadBtn   = document.getElementById('downloadPdfBtn');
    const studentObsBtn = document.getElementById('downloadStudentObsBtn');
    const studentObsBtnDefaultHtml = studentObsBtn.innerHTML;

    const avatarColors = ['#7c6fe0','#f5a623','#2d8cff','#e05c5c','#27ae60','#e67e22','#8e44ad','#16a085'];

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function (ch) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
        });
    }

    let hasSessions = false;

    // Download PDF — disabled until a school is selected AND it has session reports/observations
    function updateDownloadButton() {
        const disabled = !schoolSelect.value || !hasSessions;
        downloadBtn.disabled = disabled;
        studentObsBtn.disabled = disabled;
    }

    downloadBtn.addEventListener('click', function () {
        if (downloadBtn.disabled) return;
        const params = new URLSearchParams(new FormData(form));
        window.location.href = downloadBtn.dataset.base + '?' + params.toString();
    });

    function extractFilename(response, fallback) {
        let filename = fallback;
        const cd = response.headers.get('Content-Disposition') || '';
        const match = cd.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
        if (match) filename = match[1].replace(/['"]/g, '').trim();
        return filename;
    }

    function triggerBlobDownload(blob, filename) {
        const blobUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(blobUrl);
    }

    studentObsBtn.addEventListener('click', function () {
        if (studentObsBtn.disabled) return;

        if (!dateFrom.value || !dateTo.value) {
            alert('Please select a date range (From and To) before downloading student observation reports.');
            return;
        }

        if (dateFrom.value > dateTo.value) {
            alert('From Date should not be greater than To Date.');
            return;
        }

        const params = new URLSearchParams(new FormData(form));
        const url    = studentObsBtn.dataset.base + '?' + params.toString();

        studentObsBtn.disabled = true;
        studentObsBtn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Generating...';

        // Single request — the server generates every qualifying student's PDF and
        // returns either the lone PDF or a ZIP of them all, regardless of how many
        // students match (no more one AJAX call per student).
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) {
                if (!response.ok) {
                    return response.json().then(function (data) {
                        throw new Error(data.error || ('Server error (' + response.status + ')'));
                    });
                }
                const filename = extractFilename(response, 'student-observations.zip');
                return response.blob().then(function (blob) {
                    triggerBlobDownload(blob, filename);
                });
            })
            .catch(function (err) {
                alert(err.message);
            })
            .finally(function () {
                studentObsBtn.innerHTML = studentObsBtnDefaultHtml;
                updateDownloadButton();
            });
    });

    function rebuildTrainerDropdown(schoolId) {
        trainerSelect.disabled = !schoolId;
        if (!schoolId) {
            trainerSelect.innerHTML = '<option value="" selected>Select School First</option>';
            return;
        }
        trainerSelect.innerHTML = '<option value="">All Trainers</option>';
        allTrainers.filter(t => (trainerAllocations[schoolId] || []).includes(t.id)).forEach(function (t) {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.trainer_name;
            if (String(t.id) === String(selectedTrainer)) opt.selected = true;
            trainerSelect.appendChild(opt);
        });
    }


    function updateClearFiltersVisibility() {
        const hasFilters = !!(schoolSelect.value || trainerSelect.value || levelSelect.value || dateFrom.value || dateTo.value);
        clearFiltersWrap.style.display = hasFilters ? '' : 'none';
    }

    function renderSchoolInfoCard(school) {
        if (!school) return '';
        return '<div class="card mb-3"><div class="card-body py-3"><div class="d-flex align-items-center">'
            +   '<div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width:52px;height:52px;background:#3a7bd5;flex-shrink:0;">'
            +     '<i class="fas fa-school text-white" style="font-size:1.3rem;"></i>'
            +   '</div>'
            +   '<div>'
            +     '<h5 class="mb-0 font-weight-bold">' + escapeHtml(school.school_name) + '</h5>'
            +     '<small class="text-muted">Total Trainers: <strong>' + school.trainer_count + '</strong>'
            +       '&nbsp;|&nbsp; Total Students: <strong>' + school.student_count + '</strong></small>'
            +   '</div>'
            + '</div></div></div>';
    }

    // ── Session table column renderers (row = one session's data) ───────────
    function renderSessionDetailsCell(row) {
        const levelBadgesHtml = row.level_badges.length
            ? row.level_badges.map(function (b) {
                  return '<span class="badge badge-' + escapeHtml(b.type) + ' mr-1">' + escapeHtml(b.label) + '</span>';
              }).join('')
            : '<span class="badge badge-secondary">—</span>';

        return '<div style="font-size:0.82rem;">'
            +   '<div class="d-flex align-items-center mb-2"><span class="text-muted mr-2" style="min-width:46px;">Date</span>'
            +     '<span><i class="fas fa-calendar-alt text-primary mr-1"></i><strong>' + escapeHtml(row.date) + '</strong></span></div>'
            +   '<div class="d-flex align-items-center mb-2"><span class="text-muted mr-2" style="min-width:46px;">Level</span><span>' + levelBadgesHtml + '</span></div>'
            +   '<div class="d-flex mb-2"><span class="text-muted mr-2" style="min-width:46px;">Topic</span><span>' + escapeHtml(row.topic || '—') + '</span></div>'
            +   '<div class="d-flex align-items-center"><span class="text-muted mr-2" style="min-width:46px;">Trainer</span><span>' + escapeHtml(row.trainer_name) + '</span></div>'
            + '</div>';
    }

    function renderTrainerUpdateCell(row) {
        return row.trainer_update
            ? '<p class="mb-0" style="font-size:0.875rem;line-height:1.5;">' + escapeHtml(row.trainer_update) + '</p>'
            : '<span class="text-muted">N/A</span>';
    }

    function renderStudentObservationsCell(row) {
        if (row.total_students === 0) {
            return '<span class="text-muted">No observations recorded</span>';
        }

        const avatarsHtml = row.students.map(function (student) {
            const color = avatarColors[student.id % avatarColors.length];
            const imgHtml = student.image_url
                ? '<img src="' + student.image_url + '" alt="' + escapeHtml(student.first_name) + '"'
                + ' style="position:absolute;top:0;left:0;width:40px;height:40px;border-radius:50%;object-fit:cover;"'
                + ' onerror="this.style.display=\'none\';">'
                : '';

            return '<div class="text-center" style="width:56px;">'
                +   '<div style="position:relative;width:40px;height:40px;margin:0 auto;">'
                +     '<div style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;'
                +          'justify-content:center;font-size:15px;font-weight:700;color:#fff;background:' + color + ';">'
                +       escapeHtml(student.initial)
                +     '</div>'
                +     imgHtml
                +   '</div>'
                +   '<div style="font-size:0.7rem;margin-top:3px;word-break:break-word;line-height:1.2;">' + escapeHtml(student.first_name) + '</div>'
                + '</div>';
        }).join('');

        const moreHtml = row.more_count > 0
            ? '<div class="d-flex align-items-center justify-content-center" style="width:56px;">'
            +   '<span class="badge badge-secondary" style="font-size:0.75rem;">+' + row.more_count + ' More</span>'
            + '</div>'
            : '';

        return '<div class="d-flex flex-wrap" style="gap:10px;">' + avatarsHtml + moreHtml + '</div>';
    }

    function renderActionCell(row) {
        return '<button type="button" class="btn btn-primary btn-sm view-details-btn" style="white-space:nowrap;" data-url="' + row.view_url + '">'
            +     '<i class="fas fa-eye mr-1"></i>View Details'
            + '</button>';
    }

    // ── Session table — server-side DataTables, same pattern as trainer/student/list ──
    const table = $('#example1').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        order: [],
        ajax: {
            url: form.action,
            type: 'get',
            dataType: 'json',
            data: function (d) {
                d.school_id  = schoolSelect.value;
                d.trainer_id = trainerSelect.value;
                d.level_id   = levelSelect.value;
                d.date_from  = dateFrom.value;
                d.date_to    = dateTo.value;
            },
            dataSrc: function (json) {
                document.getElementById('schoolInfoWrap').innerHTML = renderSchoolInfoCard(json.school);

                hasSessions = json.recordsFiltered > 0;
                table.settings()[0].oLanguage.sEmptyTable = json.empty_message || 'No sessions found matching the selected filters.';

                updateClearFiltersVisibility();
                updateDownloadButton();

                return json.data;
            }
        },
        columns: [
            { data: null, orderable: false, className: 'align-middle', render: renderSessionDetailsCell },
            { data: null, orderable: false, className: 'align-middle', render: renderTrainerUpdateCell },
            { data: null, orderable: false, className: 'align-middle', render: renderStudentObservationsCell },
            { data: null, orderable: false, className: 'align-middle text-center', render: renderActionCell },
        ],
    });

    rebuildTrainerDropdown(schoolSelect.value);
    updateDownloadButton();
    updateClearFiltersVisibility();

    // School change: reset trainer, rebuild options, then reload the table
    schoolSelect.addEventListener('change', function () {
        trainerSelect.value = '';
        rebuildTrainerDropdown(this.value);
        updateDownloadButton();
        table.ajax.reload(null, true);
    });

    // Trainer and level: reload immediately on change
    [trainerSelect, levelSelect].forEach(function (el) {
        el.addEventListener('change', function () {
            table.ajax.reload(null, true);
        });
    });

    function applyDateRangeFilter() {
        if (dateFrom.value && dateTo.value && dateFrom.value > dateTo.value) {
            alert('From Date should not be greater than To Date.');
            return;
        }

        updateClearFiltersVisibility();
        if ((dateFrom.value && dateTo.value) || (!dateFrom.value && !dateTo.value)) {
            table.ajax.reload(null, true);
        }
    }
    dateFrom.addEventListener('change', applyDateRangeFilter);
    dateTo.addEventListener('change', applyDateRangeFilter);

    clearFiltersBtn.addEventListener('click', function () {
        schoolSelect.value = '';
        trainerSelect.value = '';
        levelSelect.value = '';
        dateFrom.value = '';
        dateTo.value = '';
        rebuildTrainerDropdown('');
        updateDownloadButton();
        table.ajax.reload(null, true);
    });

    // ── View Details modal ───────────────────────────────────────────────────
    function buildModalContent(data) {
        document.getElementById('modalSchoolName').textContent   = data.session.school_name  || '';
        document.getElementById('modalSessionTitle').textContent = data.session.title        || 'Session Observations';
        document.getElementById('modalSessionDate').textContent  = data.session.date         || '—';
        document.getElementById('modalSessionTrainer').textContent = data.session.trainer_name || '—';

        const body = document.getElementById('modalBody');

        if (!data.students || !data.students.length) {
            body.innerHTML = '<p class="text-center text-muted py-4">No observations recorded for this session yet.</p>';
            return;
        }

        let html = '';

        data.students.forEach(function (s, si) {
            const color   = s.avatar_color || avatarColors[si % avatarColors.length];
            const initial = s.initial || '?';

            // Student header
            const imgHtml = s.image_url
                ? '<img src="' + s.image_url + '" alt="' + s.first_name + '"'
                + ' style="position:absolute;top:0;left:0;width:40px;height:40px;border-radius:50%;object-fit:cover;"'
                + ' onerror="this.style.display=\'none\'">'
                : '';

            html += '<div class="mb-4">';
            html += '<div class="d-flex align-items-center mb-3">'
                  +   '<div style="position:relative;width:40px;height:40px;flex-shrink:0;">'
                  +     '<div style="width:40px;height:40px;border-radius:50%;background:' + color + ';'
                  +          'display:flex;align-items:center;justify-content:center;'
                  +          'font-size:15px;font-weight:700;color:#fff;">' + initial + '</div>'
                  +     imgHtml
                  +   '</div>'
                  +   '<div class="ml-3">'
                  +     '<div class="font-weight-bold" style="font-size:0.95rem;">' + s.student_name + '</div>'
                  +     '<small class="text-muted">' + s.observations.length + ' observation' + (s.observations.length !== 1 ? 's' : '') + '</small>'
                  +   '</div>'
                  + '</div>';

            // Observation cards grid
            html += '<div class="row">';
            s.observations.forEach(function (obs) {
                const iconHtml = obs.icon_url
                    ? '<div style="width:48px;height:48px;border-radius:50%;background:#fff8e1;'
                    +      'display:flex;align-items:center;justify-content:center;border:1px solid #ffe082;flex-shrink:0;">'
                    +   '<img src="' + obs.icon_url + '" alt="" style="width:30px;height:30px;object-fit:contain;"'
                    +        ' onerror="this.style.opacity=0.3;">'
                    + '</div>'
                    : '<div style="width:48px;height:48px;border-radius:50%;background:#f0f0f0;'
                    +      'display:flex;align-items:center;justify-content:center;flex-shrink:0;">'
                    +   '<i class="fas fa-star text-muted"></i>'
                    + '</div>';

                const photoHtml = obs.image_url
                    ? '<a href="' + obs.image_url + '" target="_blank" style="flex-shrink:0;">'
                    +   '<img src="' + obs.image_url + '" alt="evidence"'
                    +        ' style="width:72px;height:72px;border-radius:8px;object-fit:cover;border:1px solid #eee;"'
                    +        ' onerror="this.style.display=\'none\'">'
                    + '</a>'
                    : '';

                html += '<div class="col-md-6 col-lg-4 mb-3">'
                      +   '<div class="card h-100 shadow-sm" style="border-radius:12px;border:1px solid #f0f0f0;">'
                      +     '<div class="card-body" style="padding:16px;">'
                      +       '<div class="d-flex" style="gap:12px;">'
                      +         iconHtml
                      +         '<div style="flex:1;min-width:0;">'
                      +           '<div class="font-weight-bold mb-1" style="font-size:0.88rem;line-height:1.3;">' + (obs.obs_names || '—') + '</div>'
                      +           '<div class="text-muted mb-2" style="font-size:0.75rem;">' + (obs.created_at || '') + '</div>'
                      +           (obs.short_note ? '<p class="mb-0" style="font-size:0.82rem;line-height:1.5;word-break:break-word;">' + obs.short_note + '</p>' : '')
                      +         '</div>'
                      +         photoHtml
                      +       '</div>'
                      +     '</div>'
                      +   '</div>'
                      + '</div>';
            });
            html += '</div>'; // row

            if (si < data.students.length - 1) {
                html += '<hr class="mb-4">';
            }
            html += '</div>'; // student block
        });

        body.innerHTML = html;
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.view-details-btn');
        if (!btn) return;

        const url  = btn.dataset.url;
        const body = document.getElementById('modalBody');
        body.innerHTML = '<div class="text-center py-5">'
            + '<div class="spinner-border text-primary" role="status"></div>'
            + '<p class="mt-3 text-muted">Loading observations...</p>'
            + '</div>';

        $('#sessionObsModal').modal('show');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) { buildModalContent(data); })
            .catch(function () {
                body.innerHTML = '<p class="text-center text-danger py-4">Could not load observations. Please try again.</p>';
            });
    });
});
</script>
@endsection
