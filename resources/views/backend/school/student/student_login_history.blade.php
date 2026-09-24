@extends('backend.layouts.app')
@section('content')

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="{{ asset('asset/dist/css/flatpickr.min.css') }}">

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">Login History</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item active"> Student Login History</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="row col-md-12">
                        <div class="col-md-3">
                            <select class="form-control" id="select_school">
                                <option value="">---Select School---</option>
                                @foreach ($schoolList as $school)
                                <option value="{{ $school['id']}}">{{ $school['school_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="select_grade" disabled>
                                <option value="">---Select Level---</option>
                                @if(isset($filteredLevels['primary']))
                                <optgroup label="Levels">
                                    @foreach ($filteredLevels['primary'] as $k => $level)
                                    <option value="{{ $level['id'] }}">{{ $level['grade'] }}</option>
                                    @endforeach
                                </optgroup>
                                @endif
                                @if(isset($filteredLevels['add-ons']))
                                <optgroup label="Content Add-Ons">
                                    @foreach ($filteredLevels['add-ons'] as $k => $level)
                                    <option value="{{ $level['id'] }}">{{ $level['grade'] }}</option>
                                    @endforeach
                                </optgroup>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-center" style="gap: 8px;">
                            <label for="from_date" class="mb-0" style="white-space: nowrap;">From Date</label>
                            <input type="text" id="from_date" class="form-control " placeholder="From Date" autocomplete="off" disabled>
                        </div>

                        <div class="col-md-3 d-flex align-items-center ms-2" style="gap: 8px;">
                            <label for="to_date" class="mb-0" style="white-space: nowrap;">To Date</label>
                            <input type="text" id="to_date" class="form-control " placeholder="To Date" autocomplete="off" disabled>
                        </div>

                        <!-- <div class="col-md-12 mt-3">
                            <a href="{{ URL::previous() }}" class="btn btn-sm btn-warning float-right">
                                <i class="material-icons">west</i>
                                Back
                            </a>
                        </div> -->
                    </div>
                </div>
            </div>
            <div class="row mb-1">
                <div class="col-12 d-flex justify-content-end">
                    <form id="exportForm" method="GET" action="{{ route('backend.export-login-history') }}">
                        <input type="hidden" name="school_id" id="export_school_id">
                        <input type="hidden" name="grade_id" id="export_grade_id">
                        <input type="hidden" name="from_date" id="export_from_date">
                        <input type="hidden" name="to_date" id="export_to_date">
                        <button type="submit" class="btn btn-primary">
                            Export
                        </button>
                    </form>
                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive">
                <table id="example_new" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Last Login</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <!-- /.card-body -->
        </div>
</div><!-- /.container-fluid -->
</section>
<!-- /.content -->
</div>

<!-- Modal to view all login detail of student -->
<div class="modal fade" id="loginHistoryModal" tabindex="-1" role="dialog" aria-labelledby="loginHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between">
                <h5 class="modal-title" id="loginHistoryModalLabel">
                    Login History <span id="studentNameHeading"></span>
                </h5>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-primary me-2" id="exportLoginHistoryBtn">
                        Export
                    </button>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Login Date</th>
                        </tr>
                    </thead>
                    <tbody id="loginHistoryTableBody">
                        <tr>

                            <td colspan="2">Loading...</td>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Flatpickr JS -->
<script src="{{ asset('asset/dist/js/flatpickr.js') }}"></script>

<script>
    $(document).ready(function() {
       const $schoolSelect = $('#select_school');
       const $exportButton = $('#exportForm button[type="submit"]');

        function toggleExportButton() {
            const schoolSelected = $schoolSelect.val();
            $exportButton.prop('disabled', !schoolSelected);
        }

        toggleExportButton();

        // Initialize Flatpickr
        flatpickr("#from_date", {
            dateFormat: "Y-m-d",
            onChange: function() {
                $('#example_new').DataTable().ajax.reload();
            }
        });

        flatpickr("#to_date", {
            dateFormat: "Y-m-d",
            onChange: function() {
                $('#example_new').DataTable().ajax.reload();
            }
        });

        $('#example_new').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('backend.login_history_datatable') }}",
                type: "get",
                dataType: 'JSON',
                data: function(d) {
                    d.grade_id = $('#select_grade').val();
                    d.school_id = $('#select_school').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                }
            },
            columns: [{
                    data: 'student_name',
                    name: 'student.name' // searchable and sortable
                },
                {
                    data: 'login_at',
                    name: 'login_at' // sortable
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // Reload table when filters change
        $('#select_school').on('change', function() {
            const schoolSelected = $(this).val();
            toggleExportButton();

            // Reset values regardless of school selection
            $('#select_grade').val('');
            $('#from_date').val('');
            $('#to_date').val('');

            if (schoolSelected) {
                // Enable filters if school selected
                $('#select_grade, #from_date, #to_date').prop('disabled', false);
                $('#from_date').prop('disabled', false);
                $('#to_date').prop('disabled', false);
            } else {
                // Disable filters if no school selected
                $('#select_grade').prop('disabled', true);
                $('#from_date').prop('disabled', true);
                $('#to_date').prop('disabled', true);
            }

            // Reload DataTable with reset filters
            const fromDate = $('#from_date').val();
            const toDate = $('#to_date').val();

            if (fromDate && toDate && fromDate > toDate) {
                alert(' To Date cannot be earlier than From Date.');
                return;
            }

            $('#example_new').DataTable().ajax.reload();
        });
        // Grade and Date change listeners
        $('#select_grade, #from_date, #to_date').on('change', function() {
            const fromDate = $('#from_date').val();
            const toDate = $('#to_date').val();

            if (fromDate && toDate && fromDate > toDate) {
                alert('To Date cannot be earlier than From Date.');
                return;
            }

            $('#example_new').DataTable().ajax.reload();
        });

        //To export all student and also filter according to school
        $('#exportForm').on('submit', function(e) {
            const schoolId = $('#select_school').val(); // Get directly from select input
            if (!schoolId) {
                e.preventDefault();
                alert('Please select school first.');
                return;
            }

            // Set export fields only if validation passed
            $('#export_school_id').val(schoolId);
            $('#export_grade_id').val($('#select_grade').val());
            $('#export_from_date').val($('#from_date').val());
            $('#export_to_date').val($('#to_date').val());
        });

        //To get all the student login History
        $(document).on('click', '.view-logins-btn', function() {
            const studentId = $(this).data('student-id');

            $('#loginHistoryModal').data('student-id', studentId).modal('show');
            $('#studentNameHeading').text(''); // clear old text
            $('#loginHistoryTableBody').html('<tr><td colspan="2">Loading...</td></tr>');

            $.ajax({
                url: '{{ route("backend.student_all_login_history") }}', // Adjust route name if needed
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                data: {
                    student_id: studentId
                },
                success: function(response) {
                    let rows = '';

                    if (response.logins.length > 0) {
                        response.logins.forEach((item, index) => {
                            rows += `<tr><td>${index + 1}</td><td>${item.login_at}</td></tr>`;
                        });
                    } else {
                        rows = '<tr><td colspan="2">No login records found.</td></tr>';
                    }

                    $('#loginHistoryTableBody').html(rows);
                    $('#studentNameHeading').text('- ' + response.student_name);
                },
                error: function() {
                    $('#loginHistoryTableBody').html('<tr><td colspan="2">Failed to load data.</td></tr>');
                }
            });
        });

        //export all login of particular student
        $('#exportLoginHistoryBtn').on('click', function() {
            const studentId = $('#loginHistoryModal').data('student-id');
            const exportUrl = "{{ route('backend.export-all-login-history', ':id') }}".replace(':id', studentId);
            window.location.href = exportUrl;
        });
    });
</script>

@endsection