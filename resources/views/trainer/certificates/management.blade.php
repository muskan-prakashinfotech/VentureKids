@extends('backend.layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
@endsection

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Student Certificates Management</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Student Certificates</li>
        </ol>
    </div>

    <!-- Main content -->
    <section>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter Students</h3>
            </div>
            <div class="card-body">
                <form id="filterForm" class="row">
                    <div class="col-md-4">
                        <label>School</label>
                        <select name="school_id" class="form-control" id="schoolFilter" required>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->school_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Level</label>
                        <input type="text" class="form-control" value="{{ $thinkpreneurGrade ? $thinkpreneurGrade->grade : 'No level available' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label>Student Name</label>
                        <input type="text" name="name" class="form-control" id="nameFilter" value="{{ request('name') }}" placeholder="Search by name">
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Certificate Quote</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-info btn-sm" id="previewCertBtn">
                        <i class="fas fa-eye"></i> Preview Certificate
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="quoteForm">
                    <div class="form-group">
                        <label for="certificateQuote">Certificate Description (Optional)</label>
                        <textarea class="form-control" id="certificateQuote" name="quote" rows="3" placeholder="Enter a custom description for the certificate" maxlength="170"></textarea>
                        <small class="form-text text-muted">This description will appear on all certificates released in this session.</small>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Students</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary" id="bulkReleaseBtn" disabled>
                        <i class="fas fa-certificate"></i> Bulk Release Certificates
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="bulkReleaseForm" method="POST" action="{{ route('trainer.certificates.bulkRelease') }}">
                    @csrf
                    <input type="hidden" name="grade_id" value="{{ $thinkpreneurGrade ? $thinkpreneurGrade->id : '' }}">
                    <div class="table-responsive">
                        <table id="studentsTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>Student Name</th>
                                    <th>School</th>
                                    <th>Certificate Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTableBody">
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <div id="paginationContainer"></div>
            </div>
        </div>
    </section>
</div>

<!-- Certificate Preview Modal -->
<div class="modal fade" id="certificatePreviewModal" tabindex="-1" role="dialog" aria-labelledby="certificatePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 1100px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="certificatePreviewModalLabel">Certificate Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <iframe id="certificatePreviewFrame" title="Certificate Preview" style="width: 100%; height: 75vh; border: 0;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let dataTable;

    // Initialize DataTable
    function initializeDataTable() {
        dataTable = $('#studentsTable').DataTable({
            serverSide: true,
            processing: true,
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
            },
            ordering: false,
            ajax: {
                url: '{{ route("trainer.certificates.getStudents") }}',
                type: 'GET',
                data: function(d) {
                    d.school_id = $('#schoolFilter').val();
                    // Remove the custom name filter since DataTables handles search globally
                }
            },
            columns: [
                {
                    data: 'checkbox',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    orderable: false
                },
                {
                    data: 'school',
                    orderable: false
                },
                {
                    data: 'certificate_status',
                    orderable: false
                },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            responsive: true,
            drawCallback: function() {
                // Re-bind events after table draw
                bindEvents();
            }
        });
    }

    // Bind events for checkboxes and buttons
    function bindEvents() {
        // Select all checkbox
        $('#selectAll').off('change').on('change', function() {
            $('.student-checkbox').prop('checked', $(this).prop('checked'));
            toggleBulkReleaseBtn();
        });

        // Individual checkboxes
        $(document).off('change', '.student-checkbox').on('change', '.student-checkbox', function() {
            toggleBulkReleaseBtn();
        });

        // Download certificate
        $(document).off('click', '.download-cert').on('click', '.download-cert', function() {
            var token = $(this).data('token');
            window.open('/trainer/certificate/download/' + token, '_blank');
        });

        // Single release
        $(document).off('click', '.release-single').on('click', '.release-single', function() {
            var studentId = $(this).data('student-id');
            var gradeId = '{{ $thinkpreneurGrade ? $thinkpreneurGrade->id : "" }}';
            var $btn = $(this);

            if (confirm('Are you sure you want to release certificate for this student?')) {
                // Show loading state
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

                $.post('{{ route("trainer.certificates.bulkRelease") }}', {
                    _token: '{{ csrf_token() }}',
                    student_ids: [studentId],
                    grade_id: gradeId,
                    quote: $('#certificateQuote').val()
                }).done(function(response) {
                    if (response.success) {
                        alert(response.message);
                        dataTable.ajax.reload();
                    } else {
                        alert(response.message);
                    }
                }).fail(function() {
                    alert('An error occurred. Please try again.');
                }).always(function() {
                    // Reset button state
                    $btn.prop('disabled', false).html('<i class="fas fa-certificate"></i> Release');
                });
            }
        });
    }

    function toggleBulkReleaseBtn() {
        var checkedCount = $('.student-checkbox:checked').length;
        $('#bulkReleaseBtn').prop('disabled', checkedCount === 0);
    }

    // Filter events
    $('#schoolFilter').on('change', function() {
        dataTable.ajax.reload();
    });

    $('#nameFilter').on('keyup', function() {
        dataTable.search($(this).val()).draw();
    });

    // Bulk release
    $('#bulkReleaseBtn').on('click', function() {
        var gradeId = '{{ $thinkpreneurGrade ? $thinkpreneurGrade->id : "" }}';
        var $btn = $(this);

        if (confirm('Are you sure you want to release certificates for selected students?')) {
            // Show loading state
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('grade_id', gradeId);
            formData.append('quote', $('#certificateQuote').val());

            $('.student-checkbox:checked').each(function() {
                formData.append('student_ids[]', $(this).val());
            });

            $.ajax({
                url: '{{ route("trainer.certificates.bulkRelease") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        dataTable.ajax.reload();
                        // Uncheck all checkboxes
                        $('.student-checkbox').prop('checked', false);
                        $('#selectAll').prop('checked', false);
                        toggleBulkReleaseBtn();
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    // Reset button state
                    $btn.prop('disabled', true).html('<i class="fas fa-certificate"></i> Bulk Release Certificates');
                    toggleBulkReleaseBtn();
                }
            });
        }
    });

    // Preview certificate
    $('#previewCertBtn').on('click', function() {
        var customQuote = $('#certificateQuote').val();
        var schoolId = $('#schoolFilter').val();
        var previewUrl = '{{ route("trainer.test.certificate") }}';
        
        var params = [];
        if (customQuote) {
            params.push('quote=' + encodeURIComponent(customQuote));
        }
        if (schoolId) {
            params.push('school_id=' + encodeURIComponent(schoolId));
        }
        params.push('format=pdf');
        if (params.length) {
            previewUrl += '?' + params.join('&');
        }

        $('#certificatePreviewFrame').attr('src', previewUrl);
        $('#certificatePreviewModal').modal('show');
    });

    // Initialize
    initializeDataTable();
});
</script>
@endsection
