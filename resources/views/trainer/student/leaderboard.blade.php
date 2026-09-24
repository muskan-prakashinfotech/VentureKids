@extends('backend.layouts.app')

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="pageTitle">
        <h2>Students</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Leaderboard</li>
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
                            <a href="{{ route('trainer.dashboard') }}" class="btn btn-sm btn-warning">
                                <i class="material-icons">west</i>
                                Back
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="leader-board-data">
                        <div class="row">
                            <div class="col-lg-12 mx-auto">
                                <div class="card mb-3">
                                    <div class="card-header d-flex flex-wrap align-items-center justify-content-start" style="gap:8px;">
                                        <span class="mr-2 font-weight-bold">Academic Year Duration</span>
                                        <select name="academic_year" id="academic_year" class="form-control form-control-sm academic-year-select" style="max-width:220px;">
                                        </select>
                                        <div id="lb_month_wrapper" style="display:none;">
                                            <select class="form-control form-control-sm" id="lb_month" style="min-width:120px;">
                                                <option value="">All Months</option>
                                                <option value="1">January</option>
                                                <option value="2">February</option>
                                                <option value="3">March</option>
                                                <option value="4">April</option>
                                                <option value="5">May</option>
                                                <option value="6">June</option>
                                                <option value="7">July</option>
                                                <option value="8">August</option>
                                                <option value="9">September</option>
                                                <option value="10">October</option>
                                                <option value="11">November</option>
                                                <option value="12">December</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div id="accordion" class="accordion">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

<script>
$(document).ready(function() {

    var academicYearsBySchool = @json($academicYearsBySchool);
    var batchesBySchool = @json($batch_list);
    var selectedLeaderBoardMonth = '';

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

    function updateMonthFilterVisibility() {
        var selectedAcademicYear = $('#academic_year').val();
        if (selectedAcademicYear && selectedAcademicYear !== 'past') {
            $('#lb_month_wrapper').show();
        } else {
            $('#lb_month').val('');
            selectedLeaderBoardMonth = '';
            $('#lb_month_wrapper').hide();
        }
    }

    function populateAcademicYearOptions(schoolId) {
        var years = academicYearsBySchool[schoolId] || [];
        var options = '';
        if (years.length) {
            years.forEach(function(yr) {
                options += '<option value="' + yr.id + '">' + yr.label + '</option>';
            });
        } else {
            options += '<option value="past">All Time</option>';
        }
        $('#academic_year').html(options);
        updateMonthFilterVisibility();
    }

    function loadLeaderBoard() {
        $.ajax({
            type: "POST",
            url: "{{ route('trainer.generateLeaderBoard') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                academic_year: $('#academic_year').val(),
                grade_id: $('#select_grade').val(),
                assigned_grade: $('#assigned_grade').val(),
                school_batch_id: $('#school_batch_id').val(),
                month: selectedLeaderBoardMonth,
                school_id: $('#select_school').val(),
            },
            beforeSend: function() {
                $('#accordion').html('<div class="card"><div class="card-header bg-white shadow-sm border-0">please wait...</div></div>');
            },
            success: function(result) {
                var data = jQuery.parseJSON(result);
                if (!$.isEmptyObject(data)) {
                    var lb_data = '';
                    $.each(data, function(index, value) {
                        var image = value.image;
                        image = (!$.isEmptyObject(image) && image != 'no_image')
                            ? "{{ url('') }}/tenants/" + image
                            : "{{ url('') }}/img/default_image.png";
                        lb_data += '<div class="card">'
                            + '<div id="heading_' + index + '" class="card-header bg-white shadow-sm border-0">'
                            + '<div type="button" data-toggle="collapse" data-target="#collapse_' + index + '" aria-expanded="false" aria-controls="collapse_' + index + '" class="d-flex flex-wrap justify-content-between align-items-center w-100 collapsible-link">'
                            + '<div class="leader-board-avtar mr-2"><img src="' + image + '" /></div>'
                            + '<span class="text text-bold">' + value.user.name + '</span>'
                            + '<span class="ml-auto text-bold">' + value.tot_reward_points + '</span>'
                            + '</div></div>'
                            + '<div id="collapse_' + index + '" data-id="' + value.id + '" aria-labelledby="heading_' + index + '" data-parent="#accordion" class="collapse">'
                            + '<div class="card-body p-5 lb-reward-detail-' + value.id + ' text-center"></div>'
                            + '</div></div>';
                    });
                    $('#accordion').html(lb_data);
                } else {
                    $('#accordion').html('<div class="card"><div class="card-header bg-white shadow-sm border-0">No data found!</div></div>');
                }
            }
        });
    }

    $('#accordion').on('show.bs.collapse', function(e) {
        var stud_id = $('#' + e.target.id).attr('data-id');
        $.ajax({
            type: "POST",
            url: "{{ route('trainer.getRewardPointDetails') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            beforeSend: function() { $('.lb-reward-detail-' + stud_id).html('please wait...'); },
            data: { stud_id: stud_id, academic_year: $('#academic_year').val(), month: selectedLeaderBoardMonth },
            success: function(result) {
                var data = jQuery.parseJSON(result);
                if (!$.isEmptyObject(data)) {
                    var reward_detail = '', i = 0;
                    var reward_title = { weekly_challenge: "Daily Quiz Whiz", video_learning_point: "Learning Voyager", project: "Project Posted", challenge_respond: "Industry Innovator", assignment_submission: "Guided Assignment Ace", project_ai_score_point: "Project SmartScore", scorm_learning_reward: "BrainBoosters", pre_assessment_score: "Getting Started (Pre-Assessment)", post_assessment_score: "Victory Lap (Post-Assessment)" };
                    $.each(data, function(index, value) {
                        if (i == 0 || i == 3) { reward_detail += '<div class="row">'; }
                        reward_detail += '<div class="col-md-4 text-center">' + (reward_title[index] || index) + ' : ' + value + '</div>';
                        if (i == 2 || i == 5) { reward_detail += '</div>'; }
                        i++;
                    });
                    $('.lb-reward-detail-' + stud_id).html(reward_detail);
                } else {
                    $('.lb-reward-detail-' + stud_id).html('No data found!');
                }
            },
            error: function(xhr) {
                var msg = (xhr.status === 419)
                    ? 'Session expired. Please refresh the page.'
                    : 'Could not load reward details. Please try again.';
                $('.lb-reward-detail-' + stud_id).html(msg);
            }
        });
    });

    $('#select_school').change(function() {
        populateBatchOptions($(this).val());
        populateAcademicYearOptions($(this).val());
        loadLeaderBoard();
    });

    $('#select_grade, #assigned_grade, #school_batch_id').change(function() {
        loadLeaderBoard();
    });

    $('#academic_year').change(function() {
        updateMonthFilterVisibility();
        loadLeaderBoard();
    });

    $('#lb_month').change(function() {
        selectedLeaderBoardMonth = $(this).val();
        loadLeaderBoard();
    });

    populateBatchOptions($('#select_school').val());
    populateAcademicYearOptions($('#select_school').val());
    loadLeaderBoard();
});
</script>

@endsection
