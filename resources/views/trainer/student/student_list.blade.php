@extends('backend.layouts.app')

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="pageTitle">
        <h2>Students</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Students</li>
        </ol>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section>
        <div class="container-fluid p-0">
            <div class="card">
                <div class="card-header">
                    <div class="row col-md-12">
                        <div class="col-md-4">
                            <select class="form-control" id="select_school">
                                @forelse ($school_list as $school)
                                <option value="{{ $school['get_school']['id'] }}">{{ $school['get_school']['school_name'] }}</option>
                                @empty
                                <option value="">---Select School---</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-control" id="select_grade">
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
                        <div class="col-md-4">
                            <select class="form-control" id="assigned_grade">
                                <option value="">---Select Grade---</option>
                                @foreach ($gradeList as $grade)
                                <option value="{{ $grade['id'] }}">{{ $grade['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row col-md-12">
                        <div class="col-md-4">
                            <select class="form-control" id="school_batch_id">
                                <option value="">---Select Batch---</option>
                                @foreach ($batch_list as $batch)
                                <option value="{{ $batch['get_batch']['id'] }}">{{ $batch['get_school']['school_name'] }} - {{ $batch['get_batch']['batch_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8 d-flex align-items-center justify-content-end" style="gap:8px;">
                            <button id="genObsReportBtn" type="button" class="btn btn-sm"
                                    style="background-color:#4F46E5; border-color:#4F46E5; color:#fff;"
                                    data-toggle="modal" data-target="#obsReportModal" disabled>
                                <i class="material-icons" style="font-size:15px;vertical-align:middle;">description</i>
                                Generate Observation Report
                            </button>
                            <a href="{{ route('trainer.dashboard') }}" class="btn btn-sm btn-warning">
                                <i class="material-icons">west</i>
                                Back
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive">
                    <table id="example_new" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width:36px;"></th>
                                <th>Name</th>
                                <th>School</th>
                                <th>Level</th>
                                <th>Grade</th>
                                <th>Batch</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

{{-- Per-school Student Progress + Leaderboard sections --}}
@foreach($schoolsData as $school)
<div id="school-section-{{ $school['id'] }}" class="school-progress-section" style="display:none;">
    <div class="content-wrapper pt-0">
        <div class="pageTitle">
            <h4 class="m-0">{{ $school['name'] }}</h4>
        </div>
        <section>
            <div class="container-fluid p-0">
                <div class="row">
                    @include('partials.progress_report_tabs', [
                        'instanceId'               => $school['id'],
                        'grades'                   => $filteredLevels,
                        'schoolId'                 => $school['id'],
                        'showStudentList'          => false,
                        'showGradeFilter'          => false,
                        'showLeaderBoard'          => false,
                        'routeGetProgressByGrade'  => route('trainer.getProgressByGrade'),
                        'routeGenerateLeaderBoard' => route('trainer.generateLeaderBoard'),
                        'routeGetRewardPointDetails' => route('trainer.getRewardPointDetails'),
                        'routeGetStudentObservations' => route('trainer.getStudentObservations'),
                        'currentGradeId'           => $currentGradeId,
                        'academicYears'            => $school['academicYears'],
                        'selectedAcademicYear'     => $school['selectedAcademicYear'],
                    ])
                </div>
            </div>
        </section>
    </div>
</div>
@endforeach

<!-- Approve Modal -->
<div class="modal fade" id="approve_modal" role="dialog" tabindex="-1" aria-labelledby="approve_modal"
    aria-hidden="true">
    <div class="modal-dialog" style="width: 400px;">
        <div class="modal-content">
            <input type="hidden" name="advertisements_id" id="approve_advertisement_id" value="">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">Confirm Approve</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <form action="" id="approve_project_submit">
                    <p>Are You Sure You Want To Approve This?</p>
                    <div class="approve_data">

                    </div>
                    <div class="text-right">
                        <button data-dismiss="modal" class="btn btn-default btn-sm" type="button"
                            id="modal_close">Close</button>
                        <button type="button" class="btn btn-success btn-sm approve_project" id=""
                            value="">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Approve Modal -->

<!-- Reject Modal -->
<div class="modal fade" id="reject_modal" role="dialog" tabindex="-1" aria-labelledby="approve_modal"
    aria-hidden="true">
    <div class="modal-dialog" style="width: 400px;">
        <div class="modal-content">
            <input type="hidden" name="advertisements_id" id="reject_advertisement_id" value="">
            <!--Modal header-->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><i class="pci-cross pci-circle"></i></button>
                <h4 class="modal-title">Confirm Reject</h4>
            </div>
            <!--Modal body-->
            <div class="modal-body">
                <form action="" id="reject_project_submit">
                    <p>Are You Sure You Want To Reject This?</p>
                    <div class="approve_data">

                    </div>
                    <div class="text-right">
                        <button data-dismiss="modal" class="btn btn-default btn-sm" type="button"
                            id="modal_close">Close</button>
                        <button type="button" class="btn btn-success btn-sm reject_project" id=""
                            value="">Reject</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
<!-- End Reject Modal -->
<script>
var trainerStudentInfoUrl = "{{ route('trainer.studentProgressInfo') }}";

// Map batch ID → school ID for filter detection
var batchSchoolMap = {
    @foreach($batch_list as $batch)
    '{{ $batch['get_batch']['id'] }}': '{{ $batch['get_school']['id'] }}',
    @endforeach
};

var batchesBySchool = @json($batch_list);

function populateBatchOptions(schoolId) {
    var batches = batchesBySchool.filter(function(batch) {
        return String(batch.get_school.id) === String(schoolId);
    });

    var options = '<option value="">---Select Batch---</option>';
    if (batches.length) {
        batches.forEach(function(batch) {
            options += '<option value="' + batch.get_batch.id + '">' + batch.get_batch.batch_name + '</option>';
        });
    }

    $('#school_batch_id').html(options);
}

$(document).on('click', '.view-student-progress', function() {
    var studentId = $(this).data('id');
    var schoolId  = $(this).data('school');
    var gradeId   = $('#select_grade').val() || $(this).data('grade') || {{ $currentGradeId ?? 'null' }};
    var sfx       = '_' + schoolId;

    // Determine which school filter is active (school select or batch select)
    var selectedSchool = $('#select_school').val();
    var selectedBatch  = $('#school_batch_id').val();
    var filterSchool   = selectedSchool || (selectedBatch ? batchSchoolMap[selectedBatch] : null);

    // Collapse the datatable wrapper's min-height so the school section sits close below
    $('.content-wrapper').first().css('min-height', 'auto');

    // Show only the relevant school's Student Progress Report section
    $('.school-progress-section').hide();
    $('#school-section-' + (filterSchool || schoolId)).show();

    // Activate Student Progress tab for the clicked student's school
    $('#progress-report' + sfx).trigger('click');

    // Load the student's progress data in their school's instance
    if (window['ps' + sfx]) {
        window['ps' + sfx].setStudent(studentId, gradeId);
        window['ps' + sfx].loadStudentProgressInfo(studentId, gradeId);
    }

    $('html, body').animate({ scrollTop: $('#school-section-' + schoolId).offset().top - 100 }, 500);
});


$(document).on('shown.bs.tab', '.school-progress-section', function(e) {
    var onLeaderBoard = /^leader-board/.test($(e.target).attr('id') || '');
    $(this).find('.content-wrapper').first().css('min-height', onLeaderBoard ? 'auto' : '');
});

$(document).ready(function() {

    // id -> name map, persists across DataTable redraws
    var selectedStudents = {};

    function updateGenBtn() {
        $('#genObsReportBtn').prop('disabled', Object.keys(selectedStudents).length === 0);
    }

    function obsModalLock() {
        var m = $('#obsReportModal').data('bs.modal');
        if (m) { m._config.backdrop = 'static'; m._config.keyboard = false; }
        $('#obsReportModal [data-dismiss="modal"]').prop('disabled', true);
    }

    function obsModalUnlock() {
        var m = $('#obsReportModal').data('bs.modal');
        if (m) { m._config.backdrop = true; m._config.keyboard = true; }
        $('#obsReportModal [data-dismiss="modal"]').prop('disabled', false);
    }

    function obsReportReset() {
        obsModalUnlock();
        $('#obsDateFields').show();
        $('#obsLoadingState').hide();
        $('#obsDownloadSection').hide().empty();
        $('#obsErrorMsg').hide().text('');
        $('#obsReportSubmitBtn').prop('disabled', false)
            .html('<i class="material-icons" style="font-size:14px;vertical-align:middle;">picture_as_pdf</i> Generate PDF')
            .show();
        $('#obsFromDate').removeAttr('disabled');
        $('#obsToDate').removeAttr('disabled');
    }

    var table = $('#example_new').DataTable({
        //order: [[ 0, 'DESC']],
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{route('trainer.student_list_datatable')}}",
            type: "get",
            dataType: 'JSON',
            data: function(d) {
                d.school_id = $('#select_school').val(),
                d.grade_id = $('#select_grade').val(),
                d.assigned_grade = $('#assigned_grade').val(),
                d.school_batch_id = $('#school_batch_id').val()
            }
        },
        columns: [
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    var name = (row.std_user && row.std_user.name) ? row.std_user.name : 'Student';
                    return '<input type="checkbox" class="student-check" data-id="' + data + '" data-name="' + name.replace(/"/g, '&quot;') + '" style="width:16px;height:16px;cursor:pointer;">';
                }
            },
            {
                data: 'std_user.name',
                name: 'std_user.name'
            },
            {
                data: 'school.school_name',
                name: 'school.school_name'
            },
            {
                data: 'grade_id',
                name: 'grade_id'
            },
            {
                data: 'get_assigned_grade.name',
                name: 'get_assigned_grade.name',
                "render": function(data, type, row) {
                    return data ? data : "";
                }
            },
            {
                data: 'get_assigned_batch.batch_name',
                name: 'get_assigned_batch.batch_name',
                "render": function(data, type, row) {
                    return data ? data : "";
                }
            },
            {
                data: 'action',
                name: 'action',
                orderable: true,
                searchable: true
            },
        ],
        rowCallback: function(row, data) {
            if (selectedStudents.hasOwnProperty(data.id)) {
                $(row).find('.student-check').prop('checked', true);
            }
        },
        drawCallback: function() {
            updateGenBtn();
        }
    });

    $(document).on('change', '.student-check', function() {
        var id   = parseInt($(this).data('id'));
        var name = $(this).data('name') || 'Student';
        if ($(this).prop('checked')) {
            selectedStudents[id] = name;
        } else {
            delete selectedStudents[id];
        }
        updateGenBtn();
    });

    $('#obsReportModal').on('show.bs.modal', function() {
        obsReportReset();
    });

    $('#obsReportSubmitBtn').on('click', function() {
        var $btn     = $(this);
        var fromDate = $('#obsFromDate').val();
        var toDate   = $('#obsToDate').val();

        if (!fromDate || !toDate) {
            $('#obsErrorMsg').text('Please select both From Date and To Date.').show();
            if (!fromDate) { $('#obsFromDate').focus(); }
            else { $('#obsToDate').focus(); }
            return;
        }

        if (toDate < fromDate) {
            $('#obsErrorMsg').text('To Date cannot be earlier than From Date.').show();
            $('#obsToDate').focus();
            return;
        }

        var studentIds = Object.keys(selectedStudents);

        $('#obsDateFields').hide();
        $('#obsErrorMsg').hide().text('');
        $('#obsDownloadSection').hide().empty();
        $('#obsLoadingState').show();
        $btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Generating...');
        $('#obsFromDate, #obsToDate').prop('disabled', true);
        obsModalLock();

        var body = new URLSearchParams();
        studentIds.forEach(function (id) { body.append('student_ids[]', id); });
        body.append('from_date', fromDate);
        body.append('to_date', toDate);

        fetch('{{ route("trainer.observation_reports") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            body: body
        })
            .then(function (response) {
                if (!response.ok) {
                    return response.json().then(function (data) {
                        throw new Error(data.error || ('Server error (' + response.status + ')'));
                    });
                }
                var filename = studentIds.length === 1 ? 'observation-report.pdf' : 'observation-reports.zip';
                var cd = response.headers.get('Content-Disposition') || '';
                var match = cd.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                if (match) filename = match[1].replace(/['"]/g, '').trim();
                return response.blob().then(function (blob) {
                    return { blob: blob, filename: filename };
                });
            })
            .then(function (result) {
                $('#obsLoadingState').hide();
                $('#obsFromDate, #obsToDate').prop('disabled', false);
                obsModalUnlock();
                $btn.hide();

                var isZip = /\.zip$/i.test(result.filename);
                var header = '<div class="text-center mb-3">'
                           + '<i class="material-icons text-success" style="font-size:36px;">check_circle</i>'
                           + '<p class="mt-1 mb-0 text-muted" style="font-size:12px;">Report' + (studentIds.length > 1 ? 's' : '') + ' generated successfully!</p>'
                           + '</div>';

                var blobUrl = window.URL.createObjectURL(result.blob);
                $('#obsDownloadSection').html(
                    header +
                    '<div class="text-center">' +
                    '<a href="' + blobUrl + '" download="' + result.filename + '" class="btn btn-success btn-sm px-4">' +
                    '<i class="material-icons" style="font-size:14px;vertical-align:middle;">' + (isZip ? 'archive' : 'download') + '</i> Download ' + (isZip ? 'ZIP' : 'Report') +
                    '</a></div>'
                ).show();
            })
            .catch(function(err) {
                obsModalUnlock();
                $('#obsLoadingState').hide();
                $('#obsDateFields').show();
                $('#obsErrorMsg').text('Error: ' + err.message).show();
                $('#obsFromDate, #obsToDate').prop('disabled', false);
                $btn.prop('disabled', false).show()
                    .html('<i class="material-icons" style="font-size:14px;vertical-align:middle;">picture_as_pdf</i> Generate PDF');
            });
    });

    $('#select_school, #select_grade, #assigned_grade, #school_batch_id').change(function() {
        if ($(this).attr('id') === 'select_school') {
            populateBatchOptions($(this).val());
        }
        table.draw();
        // Hide all school sections, restore datatable wrapper height, and reset each instance's state
        $('.school-progress-section').hide();
        $('.content-wrapper').first().css('min-height', '');
        @foreach($schoolsData as $school)
        if (window['ps_{{ $school['id'] }}']) {
            window['ps_{{ $school['id'] }}'].reset('{{ $school['selectedAcademicYear'] }}');
            window['ps_{{ $school['id'] }}'].showProgressTab();
        }
        @endforeach
    });

    populateBatchOptions($('#select_school').val());

    //Approve project------------------------------------
    $(document).on("click", "#approve_project", function() {
        $('#approve_modal').modal('show');
        var student_id = $(this).attr('data-id');
        $.ajax({
            url: "{{route('trainer.project_approve')}}",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'post',
            async: false,
            data: {
                'student_id': student_id
            },
            dataType: 'JSON',
            success: function(data) {

                console.log(data);
                var i;
                var html = '';
                if (data.getproject.length != 0) {

                    for (i = 0; i < data.getproject.length; i++) {
                        //console.log(data.getproject[i].title);
                        html +=
                            '<div class="form-group"><label for="assessmentdone"></label><input type="checkbox" name="project_approve[]" class="checkBox" id="assessmentdone" value="' +
                            data.getproject[i].id + '"> ' + data.getproject[i].title +
                            '</div>';
                    }
                    $('.approve_data').html(html);

                } else {
                    $('.approve_data').html('No data found');
                }
            }
        });

    });

});

//From submit------------
$('.approve_project').click(function(e) {

    var custom_data = $('#approve_project_submit').serialize();
    $.ajax({
        type: "POST",
        url: "{{route('trainer.approve_project_submit')}}",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        async: false,
        data: custom_data,
        dataType: 'json',
        success: function(data) {
            //console.log(data);
            if (data.success) {
                $('#approve_modal').modal('hide');
                alert('Successfully Project Approve');
            }
        }
    });
});

//Reject Project------------------------
$(document).on("click", "#reject_project", function() {
    $('#reject_modal').modal('show');
    var student_id = $(this).attr('data-id');
    $.ajax({
        url: "{{route('trainer.project_approve')}}",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'post',
        async: false,
        data: {
            'student_id': student_id
        },
        dataType: 'JSON',
        success: function(data) {

            console.log(data);
            var i;
            var html = '';
            if (data.getproject.length != 0) {

                for (i = 0; i < data.getproject.length; i++) {
                    //console.log(data.getproject[i].title);
                    html +=
                        '<div class="form-group"><label for="assessmentdone"></label><input type="checkbox" name="project_approve[]" class="checkBox" id="assessmentdone" value="' +
                        data.getproject[i].id + '"> ' + data.getproject[i].title + '</div>';
                }
                $('.approve_data').html(html);

            } else {
                $('.approve_data').html('No data found');
            }
        }
    });

    //From submit------------
    $('.reject_project').click(function(e) {
        var custom_data = $('#reject_project_submit').serialize();
        $.ajax({
            type: "POST",
            url: "{{route('trainer.reject_project_submit')}}",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            async: false,
            data: custom_data,
            dataType: 'json',
            success: function(data) {
                //console.log(data);
                if (data.success) {
                    $('#reject_modal').modal('hide');
                    alert('Successfully Reject Approve')

                }
            }
        });
    });

});
</script>

<!-- Observation Report Modal -->
<div class="modal fade" id="obsReportModal" tabindex="-1" role="dialog" aria-labelledby="obsReportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document" style="max-width:380px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="obsReportModalLabel">Generate Observation Report</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                {{-- Date fields --}}
                <div id="obsDateFields">
                    <p class="text-muted mb-3" style="font-size:12px;">Select a date range to filter observations. Leave blank to include all.</p>
                    <div class="form-group">
                        <label class="font-weight-bold" style="font-size:13px;">From Date</label>
                        <input type="date" class="form-control form-control-sm" id="obsFromDate" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold" style="font-size:13px;">To Date</label>
                        <input type="date" class="form-control form-control-sm" id="obsToDate" required>
                    </div>
                </div>

                {{-- Loading state --}}
                <div id="obsLoadingState" style="display:none; text-align:center; padding:24px 0;">
                    <div class="spinner-border" role="status" style="width:2.5rem;height:2.5rem;color:#4F46E5;"></div>
                    <p class="mt-3 mb-0 font-weight-bold" style="font-size:13px;">Generating report…</p>
                </div>

                {{-- Download section (shown after success) --}}
                <div id="obsDownloadSection" style="display:none;"></div>

                {{-- Error message --}}
                <div id="obsErrorMsg" class="alert alert-danger mt-2 mb-0" style="display:none; font-size:12px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-sm" id="obsReportSubmitBtn"
                        style="background-color:#4F46E5; border-color:#4F46E5; color:#fff;">
                    <i class="material-icons" style="font-size:14px;vertical-align:middle;">picture_as_pdf</i>
                    Generate PDF
                </button>
            </div>
        </div>
    </div>
</div>
@endsection