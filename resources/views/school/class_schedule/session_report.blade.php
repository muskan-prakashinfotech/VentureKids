@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper school-session-report">
    <div class="pageTitle">
        <h2>Session Report</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">{{ __('admin.home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('school.Class_schedule') }}">Class Schedule</a></li>
            <li class="breadcrumb-item active">Session Report</li>
        </ol>
    </div>

    <section>
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h4 class="card-title mb-0">Session Reports</h4>
                            <div class="d-flex align-items-center flex-wrap" style="gap:12px;">
                                <form id="dateFilterForm" method="GET" action="{{ route('school.session_report') }}" class="d-flex align-items-center flex-wrap mb-0" style="gap:10px;">
                                    <div class="d-flex align-items-center" style="gap:6px;">
                                        <label class="mb-0 text-nowrap" for="from_date"><strong>From:</strong></label>
                                        <input type="date" id="from_date" name="from_date" class="form-control form-control-sm"
                                               value="{{ $fromDate ?? '' }}" style="width:145px;">
                                    </div>
                                    <div class="d-flex align-items-center" style="gap:6px;">
                                        <label class="mb-0 text-nowrap" for="to_date"><strong>To:</strong></label>
                                        <input type="date" id="to_date" name="to_date" class="form-control form-control-sm"
                                               value="{{ $toDate ?? '' }}" style="width:145px;">
                                    </div>
                                    <a id="clearFilterLink" href="{{ route('school.session_report') }}" class="btn btn-sm btn-secondary" style="{{ ($fromDate || $toDate) ? '' : 'display:none;' }}">Clear</a>
                                </form>
                                <a id="downloadPdfLink" data-base="{{ route('school.session_report.download') }}"
                                   href="{{ route('school.session_report.download', array_filter(['from_date' => $fromDate, 'to_date' => $toDate])) }}"
                                   class="btn btn-sm btn-primary disabled" aria-disabled="true" style="margin-left:8px;pointer-events:none;opacity:0.6;">
                                    <i class="material-icons align-middle" style="font-size:16px;vertical-align:middle;">download</i> Download PDF
                                </a>
                                <a href="{{ route('school.Class_schedule') }}" class="btn btn-sm btn-warning" style="margin-left:8px;">
                                    <i class="material-icons align-middle back-btn-icon">west</i> Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body table-responsive">
                            <table id="example1" class="table table-bordered table-striped session-report-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="session-detail-col">Session Details</th>
                                        <th class="session-photo-col">Class in Action</th>
                                        <th>What was covered during the session</th>
                                        <th>Learning Outcome</th>
                                        <th>Skill Focus</th>
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
        </div>
    </section>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form       = document.getElementById('dateFilterForm');
        var fromInput  = document.getElementById('from_date');
        var toInput    = document.getElementById('to_date');
        var clearLink  = document.getElementById('clearFilterLink');
        var downloadLink = document.getElementById('downloadPdfLink');

        // Keep min/max in sync on page load if values are already set
        if (fromInput.value) toInput.min = fromInput.value;
        if (toInput.value)   fromInput.max = toInput.value;

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/[&<>"']/g, function (ch) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
            });
        }

        function nl2brEscaped(str) {
            return str ? escapeHtml(str).replace(/\n/g, '<br>') : '<span class="text-muted">—</span>';
        }

        function setDownloadEnabled(enabled) {
            downloadLink.classList.toggle('disabled', !enabled);
            downloadLink.setAttribute('aria-disabled', String(!enabled));
            downloadLink.style.pointerEvents = enabled ? '' : 'none';
            downloadLink.style.opacity = enabled ? '' : '0.6';
        }

        function renderSessionDetailsCell(row) {
            return '<div class="session-topic"><strong>Topic:</strong> ' + escapeHtml(row.session_title) + '</div>'
                + '<div class="session-date"><strong>Date:</strong> ' + escapeHtml(row.date_formatted) + '</div>';
        }

        function renderPhotoCell(row, rowIndex) {
            if (!row.photos || !row.photos.length) {
                return '<span class="text-muted no-photos">No photos</span>';
            }

            if (row.photos.length === 1) {
                return '<img src="' + row.photos[0].url + '" alt="Session Photo" width="150" height="150" class="rounded session-photo-img">';
            }

            var carouselId = 'carousel-' + rowIndex;
            var items = row.photos.map(function (photo, idx) {
                return '<div class="carousel-item' + (idx === 0 ? ' active' : '') + '">'
                    +   '<img src="' + photo.url + '" alt="Session Photo" width="150" height="150" class="d-block rounded session-photo-img">'
                    + '</div>';
            }).join('');

            return '<div id="' + carouselId + '" class="carousel slide session-carousel" data-ride="carousel" data-interval="3000">'
                +   '<div class="carousel-inner">' + items + '</div>'
                +   '<a class="carousel-control-prev" href="#' + carouselId + '" role="button" data-slide="prev">'
                +     '<span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="sr-only">Previous</span>'
                +   '</a>'
                +   '<a class="carousel-control-next" href="#' + carouselId + '" role="button" data-slide="next">'
                +     '<span class="carousel-control-next-icon" aria-hidden="true"></span><span class="sr-only">Next</span>'
                +   '</a>'
                +   '<div class="session-photo-count">' + row.photos.length + ' photos</div>'
                + '</div>';
        }

        // ── Session report table — server-side DataTables, same pattern as trainer/student/list ──
        var table = $('#example1').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            order: [],
            ajax: {
                url: form.action,
                type: 'get',
                dataType: 'json',
                data: function (d) {
                    d.from_date = fromInput.value;
                    d.to_date   = toInput.value;
                }
            },
            columns: [
                { data: null, orderable: false, className: 'align-top session-detail-cell', render: renderSessionDetailsCell },
                { data: null, orderable: false, className: 'align-top session-photo-cell', render: function (row, type, fullRow, meta) { return renderPhotoCell(row, meta.row); } },
                { data: null, orderable: false, className: 'align-top session-summary-cell', render: function (row) { return nl2brEscaped(row.session_summary); } },
                { data: null, orderable: false, className: 'align-top', render: function (row) { return nl2brEscaped(row.learning_outcome); } },
                { data: null, orderable: false, className: 'align-top', render: function (row) { return nl2brEscaped(row.skill_focus); } },
            ],
            drawCallback: function () {
                // Carousels are inserted dynamically by DataTables, so Bootstrap's
                // automatic data-api scan (which only runs once at page load) never
                // sees them — they need to be initialised explicitly after every draw.
                $('.session-carousel').carousel();
                setDownloadEnabled(this.api().page.info().recordsTotal > 0);
            }
        });

        function updateDownloadLink() {
            var params = new URLSearchParams();
            if (fromInput.value) params.set('from_date', fromInput.value);
            if (toInput.value)   params.set('to_date', toInput.value);
            downloadLink.href = downloadLink.dataset.base + (params.toString() ? '?' + params.toString() : '');
        }

        function updateClearLinkVisibility() {
            clearLink.style.display = (fromInput.value || toInput.value) ? '' : 'none';
        }

        fromInput.addEventListener('change', function () {
            toInput.min = fromInput.value;
            // If to_date is now before from_date, clear it
            if (toInput.value && toInput.value < fromInput.value) {
                toInput.value = '';
            }
            if (fromInput.value && toInput.value) {
                updateDownloadLink();
                updateClearLinkVisibility();
                table.ajax.reload(null, true);
            }
        });

        toInput.addEventListener('change', function () {
            fromInput.max = toInput.value;
            // If from_date is now after to_date, clear it
            if (fromInput.value && fromInput.value > toInput.value) {
                fromInput.value = '';
            }
            if (fromInput.value && toInput.value) {
                updateDownloadLink();
                updateClearLinkVisibility();
                table.ajax.reload(null, true);
            }
        });

        clearLink.addEventListener('click', function (e) {
            e.preventDefault();
            fromInput.value = '';
            toInput.value = '';
            fromInput.removeAttribute('max');
            toInput.removeAttribute('min');
            updateDownloadLink();
            updateClearLinkVisibility();
            table.ajax.reload(null, true);
        });
    });
</script>
@endsection
