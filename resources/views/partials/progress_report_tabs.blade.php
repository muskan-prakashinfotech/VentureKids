@php
    $schoolId        = $schoolId ?? null;
    $showProgressTab = $showProgressTab ?? true;
    $showStudentList = $showStudentList ?? true;
    $showGradeFilter = $showGradeFilter ?? true;
    $showLeaderBoard = $showLeaderBoard ?? true;
    // Empty string keeps all IDs identical for school/admin (backward-compatible)
    $instanceId      = isset($instanceId) && $instanceId !== '' ? '_' . $instanceId : '';
    $studentInfoUrl  = $studentInfoUrl ?? route('school.student-info');
    $routeGetRewardPointDetails = $routeGetRewardPointDetails ?? route('school.getRewardPointDetails');
    $routeGetStudentObservations = $routeGetStudentObservations ?? route('school.observations');
@endphp

<div class="col-12">
    <div class="nav nav-tabs BeginnerTab scroll-tab pb-0" role="tablist"
        aria-orientation="vertical">
        @if($showProgressTab)
        <button @class(['nav-item nav-link btn text-left active'])
            id="progress-report{{ $instanceId }}"
            data-toggle="pill" data-target="#progress-report-tab{{ $instanceId }}" type="button"
            role="tab" aria-controls="progress-report-tab{{ $instanceId }}"
            aria-selected="true">Student Progress Report</button>
        @endif
        @if($showLeaderBoard)
        <button @class(['nav-item nav-link btn text-left', 'active' => !$showProgressTab])
            id="leader-board{{ $instanceId }}"
            data-toggle="pill" data-target="#leader-board-tab{{ $instanceId }}" type="button"
            role="tab" aria-controls="leader-board-tab{{ $instanceId }}"
            aria-selected="true">Leader Board</button>
        @endif
        @if(!empty($academicYears) && count($academicYears))
            <div class="ml-auto mt-2 mt-lg-0 d-flex align-items-center" style="gap:8px;">
                <select name="academic_year" id="academic_year{{ $instanceId }}" class="form-control form-control-sm academic-year-select">
                    @foreach($academicYears as $academicYear)
                        <option value="{{ $academicYear['id'] }}" @if((string)$selectedAcademicYear === (string)$academicYear['id']) selected @endif>
                            {{ $academicYear['label'] }}
                        </option>
                    @endforeach
                </select>
                @if($showLeaderBoard)
                <div id="lb_month_wrapper{{ $instanceId }}" style="display:none;">
                    <select class="form-control form-control-sm" id="lb_month{{ $instanceId }}" style="min-width:120px;">
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
                @endif
            </div>
        @endif
    </div>
</div>

<div class="col-12">
    <div class="tab-content" id="tab-content{{ $instanceId }}" role="tablist">
        {{-- Progress Report Tab --}}
        @if($showProgressTab)
        <div @class(['tab-pane fade', 'show active']) id="progress-report-tab{{ $instanceId }}" role="tabpanel" aria-labelledby="progress-report-tab{{ $instanceId }}">
            <div class="row">
                @if($showGradeFilter || $showStudentList)
                <div class="col-md-12">
                    <div class="mb-2 sticky-top">
                        <div class="card card-default">
                            <div class="card-header">
                                <div class="ProgressRow">
                                    @if($showStudentList)
                                    <div class="Heading">
                                        <p class="m-0 total-students-pursuing"> </p>
                                    </div>
                                    @endif
                                    @if($showGradeFilter)
                                    <div>
                                        <select class="form-control" name="grades" id="grades{{ $instanceId }}">
                                            @if(isset($grades['primary']))
                                                <optgroup label="Levels">
                                                @foreach ($grades['primary'] as $k => $level)
                                                    <option value="{{ $level['id'] }}" @if($level['id'] == $currentGradeId) selected @endif>{{ $level['grade'] }}</option>
                                                @endforeach
                                                </optgroup>
                                            @endif
                                            @if(isset($grades['add-ons']))
                                                <optgroup label="Content Add-Ons">
                                                @foreach ($grades['add-ons'] as $k => $level)
                                                    <option value="{{ $level['id'] }}">{{ $level['grade'] }}</option>
                                                @endforeach
                                                </optgroup>
                                            @endif
                                        </select>
                                        @if($schoolId)
                                            <input type="hidden" id="school_id{{ $instanceId }}" value="{{ $schoolId }}">
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @if($showStudentList)
                            <div class="card-body">
                                <div class="Studentslist progress-student-list" id="student_list{{ $instanceId }}">
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <div class="col-md-12 student_information">
                    <div class="progress-student-information">
                        <section class="pr-0 border-bottom mb-4 pb-3 pt-3">
                            <div class="row">
                                <div class="col-lg-12 mb-2 pl-xl-3 pr-xl-3">
                                    <div class="title"><h3 class="progress-completion-rate">Completion Rate</h3></div>
                                </div>
                                <div class="col-xl-6 mb-3 pl-xl-3 pr-xl-3">
                                    <div class="InnerBox">
                                        <div class="IconCounter">
                                            <div class="IconRaps blue-bg">
                                                <span><i class="fa fa-boxes fs-50"></i></span>
                                            </div>
                                            <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Session</p></div>
                                            <div class="CounterRaps">
                                                <span class="module-percentage"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 mb-3 pl-xl-3 pr-xl-3">
                                    <div class="InnerBox">
                                        <div class="IconCounter">
                                            <div class="IconRaps yellow-bg">
                                                <span><i class="fa fa-book fs-50"></i></span>
                                            </div>
                                            <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Assignment</p></div>
                                            <div class="CounterRaps">
                                                <span class="assignment-percentage"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <section class="mt-4">
                            <div class="row">
                                <div class="col-xl-7">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="row">
                                                <div class="col-xl-6 pl-xl-3 pr-xl-3 mb-4">
                                                    <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 orange_box student-dashboard-card student-dashboard-card--industry">
                                                        <div class="IconCounter">
                                                            <div class="IconRaps">
                                                                <span><i class="fa fa-industry"></i></span>
                                                            </div>
                                                            <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Industry Challenges</p></div>
                                                            <div class="CounterRaps">
                                                                <span class="challenges-percentage"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-6 pl-xl-3 pr-xl-3 mb-4">
                                                    <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 blue_box student-dashboard-card student-dashboard-card--projects">
                                                        <div class="IconCounter">
                                                            <div class="IconRaps">
                                                                <span><i class="fa fa-list"></i></span>
                                                            </div>
                                                            <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Projects Uploaded</p></div>
                                                            <div class="CounterRaps">
                                                                <span class="total-projects"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-6 pl-xl-3 pr-xl-3 mb-4">
                                                    <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 yellow_box student-dashboard-card student-dashboard-card--daily-challenges">
                                                        <div class="IconCounter">
                                                            <div class="IconRaps">
                                                                <span><i class="fa fa-industry"></i></span>
                                                            </div>
                                                            <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Daily Challenges</p></div>
                                                            <div class="CounterRaps">
                                                                <span class="weekly-challenges-percentage"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-xl-6 pl-xl-3 pr-xl-3 mb-4">
                                                    <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 orange_box student-dashboard-card student-dashboard-card--affirmation">
                                                        <div class="IconCounter">
                                                            <div class="IconRaps">
                                                                <span><i class="fa fa-volume-up" style="font-size: 22px;"></i></span>
                                                            </div>
                                                            <div class="BoxTitle">
                                                                <p class="m-0 stud-dash-cnt-box">Play Affirmation</p>
                                                            </div>
                                                            <div class="CounterRaps">
                                                                <span class="play-affirmation-count"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pl-xl-3 pr-xl-3 mt-4 mb-4">
                                        <div class="row">
                                            <div class="col-12 p-0">
                                                <div class="card bg_brick_orange">
                                                    <div class="card-header custom_padding_badges d-flex flex-wrap justify-content-between bg-skyblue">
                                                        <h4 class="m-0">Badges</h4>
                                                        <h4 class="m-0 total-points"></h4>
                                                        <div class="top_badge_wrap"> <img src="{{asset('/image/badge2.png')}}"> </div>
                                                    </div>
                                                    <div class="card-body badge-box-height">
                                                        <div class="row">
                                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                                    <div class="badge w-100 learning-reward-grayscale">
                                                                        <img src="{{asset('/asset/dist/img/reward_points/brain.png')}}" />
                                                                    </div>
                                                                    <div class="d-block text-center w-100">
                                                                        <h6 class="text-center mb-0 pt-learning-reward d-flex flex-column"></h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                                    <div class="badge w-100 assignment-scorm-reward-grayscale">
                                                                        <img src="{{asset('/asset/dist/img/reward_points/Lightbulb.png')}}" />
                                                                    </div>
                                                                    <div class="d-block text-center w-100">
                                                                        <h6 class="text-center mb-0 pt-assignment-scorm-reward d-flex flex-column"></h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                                    <div class="badge w-100 weekly-reward-grayscale">
                                                                        <img src="{{asset('/asset/dist/img/reward_points/rocket.png')}}" />
                                                                    </div>
                                                                    <div class="d-block text-center w-100">
                                                                        <h6 class="text-center mb-0 pt-weekly-reward d-flex flex-column"></h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                                    <div class="badge w-100 external-reward-grayscale">
                                                                        <img src="{{asset('/asset/dist/img/reward_points/parent.png')}}" />
                                                                    </div>
                                                                    <div class="d-block text-center w-100">
                                                                        <h6 class="text-center mb-0 pt-external-reward d-flex flex-column"></h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                                    <div class="badge w-100 project-reward-grayscale">
                                                                        <img src="{{asset('/asset/dist/img/reward_points/growth.png')}}" />
                                                                    </div>
                                                                    <div class="d-block text-center w-100">
                                                                        <h6 class="text-center mb-0 pt-project-reward d-flex flex-column"></h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                                    <div class="badge w-100 scorm-reward-grayscale">
                                                                        <img src="{{asset('/asset/dist/img/reward_points/steps.png')}}" />
                                                                    </div>
                                                                    <div class="d-block text-center w-100">
                                                                        <h6 class="text-center mb-0 pt-scorm-reward d-flex flex-column"></h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                                    <div class="badge w-100 pre-assessment-reward-grayscale">
                                                                        <img src="{{asset('/asset/dist/img/reward_points/Paperplane1.png')}}" />
                                                                    </div>
                                                                    <div class="d-block text-center w-100">
                                                                        <h6 class="text-center mb-0 pt-pre-assessment-reward d-flex flex-column"></h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                                    <div class="badge w-100 post-assessment-reward-grayscale">
                                                                        <img src="{{asset('/asset/dist/img/reward_points/Paperplane1.png')}}" />
                                                                    </div>
                                                                    <div class="d-block text-center w-100">
                                                                        <h6 class="text-center mb-0 pt-post-assessment-reward d-flex flex-column"></h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                                    <div class="badge w-100 industry-reward-grayscale">
                                                                        <img src="{{asset('/asset/dist/img/reward_points/education.png')}}" />
                                                                    </div>
                                                                    <div class="d-block text-center w-100">
                                                                        <h6 class="text-center mb-0 pt-industry-reward d-flex flex-column"></h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-5">
                                    <div class="row">
                                        <div class="col-12 mb-4">
                                            <div class="card direct-chat direct-chat-primary">
                                                <div class="card-header custom_padding">
                                                    <h4 class="m-0">Feedback/Comment</h4>
                                                    <span><i class="fa fa-comments"></i></span>
                                                </div>
                                                <div class="card-body">
                                                    <ul class="comments_list remarkable-note-list">
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-4">
                                            <div class="card">
                                                <div class="card-header custom_padding bg-skyblue">
                                                    <h4 class="m-0">Download Profile</h4>
                                                    <div class="btn-group download-profile-disable">
                                                        <a>
                                                            <i class="fa fa-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="card-body padding-0">
                                                    <div class="w-100 height-fix text-center">
                                                        <img src="{{asset('/dashboard/downloadProfile1.jpg')}}">
                                                    </div>
                                                    <div class="px-3 pb-3 pt-2">
                                                        <button type="button" id="loadObservationsBtn{{ $instanceId }}"
                                                                class="btn btn-warning btn-block font-weight-bold"
                                                                style="letter-spacing:.3px;">
                                                            Observation Report
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        @endif

        {{-- Leader Board Tab --}}
        @if($showLeaderBoard)
        <div @class(['tab-pane fade', 'show active' => !$showProgressTab]) id="leader-board-tab{{ $instanceId }}" role="tabpanel" aria-labelledby="leader-board-tab{{ $instanceId }}">
            <div class="leader-board-data">
                <div class="row">
                    <div class="col-lg-12 mx-auto">
                        <div class="card mb-3">
                            @if($showGradeFilter)
                            <div class="card-header d-flex flex-wrap align-items-center justify-content-end" style="gap:10px;">
                                <div>
                                    <select class="form-control" id="lb_grade{{ $instanceId }}">
                                        @if(isset($grades['primary']))
                                            <optgroup label="Levels">
                                            @foreach ($grades['primary'] as $k => $level)
                                                <option value="{{ $level['id'] }}" @if($level['id'] == $currentGradeId) selected @endif>{{ $level['grade'] }}</option>
                                            @endforeach
                                            </optgroup>
                                        @endif
                                        @if(isset($grades['add-ons']))
                                            <optgroup label="Content Add-Ons">
                                            @foreach ($grades['add-ons'] as $k => $level)
                                                <option value="{{ $level['id'] }}">{{ $level['grade'] }}</option>
                                            @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div id="accordion{{ $instanceId }}" class="accordion">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Observation Modal -->
<div class="modal fade" id="observationModal{{ $instanceId }}" tabindex="-1" role="dialog" aria-labelledby="observationModalLabel{{ $instanceId }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:10px; overflow:hidden;">
            <div class="modal-header" style="background:#f8f9fa; border-bottom:1px solid #eee; padding:14px 20px;">
                <h5 class="modal-title font-weight-bold" id="observationModalLabel{{ $instanceId }}" style="font-size:15px;">
                    Observations Recorded
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3"
                 id="obsModalBody{{ $instanceId }}"
                 style="max-height:460px; overflow-y:auto;">
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var sfx  = '{{ $instanceId }}';
    var $ctx = null; // set after DOM ready

    var selectedAcademicYear   = 'past';
    var selectedStudentId      = null;
    var selectedStudentGradeId = null;
    var selectedLeaderBoardMonth = '';
    var schoolId      = {{ $schoolId ? (int) $schoolId : 'null' }};
    var showStudentList = {{ $showStudentList ? 'true' : 'false' }};
    var showGradeFilter = {{ $showGradeFilter ? 'true' : 'false' }};

    function updateMonthFilterVisibility() {
        var onLeaderBoard = $('#leader-board' + sfx).hasClass('active') || $('#leader-board-tab' + sfx).hasClass('active');
        if (onLeaderBoard && selectedAcademicYear && selectedAcademicYear !== 'past') {
            $('#lb_month_wrapper' + sfx).show();
        } else {
            $('#lb_month' + sfx).val('');
            selectedLeaderBoardMonth = '';
            $('#lb_month_wrapper' + sfx).hide();
        }
    }

    function loadLeaderBoard() {
        $.ajax({
            type: "POST",
            url: "{{ $routeGenerateLeaderBoard }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                academic_year: selectedAcademicYear,
                grade_id: showGradeFilter ? $('#lb_grade' + sfx).val() : ($('#select_grade').val() || null),
                month: selectedLeaderBoardMonth,
                school_id: schoolId,
            },
            beforeSend: function() {
                $('#accordion' + sfx).html('<div class="card"><div class="card-header bg-white shadow-sm border-0">please wait...</div></div>');
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
                            + '<div id="heading' + sfx + '_' + index + '" class="card-header bg-white shadow-sm border-0">'
                            + '<div type="button" data-toggle="collapse" data-target="#collapse' + sfx + '_' + index + '" aria-expanded="false" aria-controls="collapse' + sfx + '_' + index + '" class="d-flex flex-wrap justify-content-between align-items-center w-100 collapsible-link">'
                            + '<div class="leader-board-avtar mr-2"><img src="' + image + '" /></div>'
                            + '<span class="text text-bold">' + value.user.name + '</span>'
                            + '<span class="ml-auto text-bold">' + value.tot_reward_points + '</span>'
                            + '</div></div>'
                            + '<div id="collapse' + sfx + '_' + index + '" data-id="' + value.id + '" aria-labelledby="heading' + sfx + '_' + index + '" data-parent="#accordion' + sfx + '" class="collapse">'
                            + '<div class="card-body p-5 lb-reward-detail-' + value.id + ' text-center"></div>'
                            + '</div></div>';
                    });
                    $('#accordion' + sfx).html(lb_data);
                } else {
                    $('#accordion' + sfx).html('<div class="card"><div class="card-header bg-white shadow-sm border-0">No data found!</div></div>');
                }
            }
        });
    }

    function renderStudentProgress(res) {
        $ctx.find('.progress-completion-rate').html(res.student.user.name + ' - Completion Rate');
        $ctx.find('.total-points').html('Total Points : ' + res.total_points);
        $ctx.find('.weekly-challenges-percentage').html(res.weeklyChallengeCount);
        $ctx.find('.assignment-percentage').html(res.assignmentPercentage + '% (' + res.completionData.assignment_submission.completed + '/' + res.completionData.assignment_submission.total + ')');
        $ctx.find('.module-percentage').html(res.modulePercentage + '% (' + res.completionData.scorm_completion.completed + '/' + res.completionData.scorm_completion.total + ')');
        $ctx.find('.challenges-percentage').html(res.industryChallengeCount);
        $ctx.find('.total-projects').html(res.projectCount);
        $ctx.find('.play-affirmation-count').html(res.play_count);

        function badgeHtml(label, tooltip, pointKey, cssClass) {
            var html = '<span>' + label + '</span>'
                + '<span class="reward-tooltip-info" data-toggle="tooltip" data-bs-trigger="hover" data-placement="top" title="' + tooltip + '"><i class="fas fa-exclamation-circle"></i></span>';
            if (typeof res.reward_points[pointKey] !== "undefined") {
                html += '<p class="">(' + res.reward_points[pointKey] + ')</p>';
                $ctx.find('.' + cssClass).removeClass('filter_grayscale');
            } else {
                $ctx.find('.' + cssClass).addClass('filter_grayscale');
            }
            return html;
        }

        $ctx.find('.pt-weekly-reward').html(badgeHtml('Daily Quiz Whiz', "Answer 5 mini-questions daily; earn +1 wing per correct answer (max +5/day). A new quiz unlocks each day and if you don't log in, it stays locked, so keep your streak!", 'weekly_challenge', 'weekly-reward-grayscale'));
        $ctx.find('.pt-learning-reward').html(badgeHtml('Learning Voyager', "Watch the entire learning video from start to finish to earn +5 wings. Earn once per video (replays don't add more wings).", 'video_learning_point', 'learning-reward-grayscale'));
        $ctx.find('.pt-project-reward').html(badgeHtml('Project Posted', "Submit your project to your portfolio to earn +1 wings—awarded per project submitted.", 'project', 'project-reward-grayscale'));
        $ctx.find('.pt-industry-reward').html(badgeHtml('Industry Innovator', "Submit an Industry Challenge entry to earn +1 wings. Awarded per challenge submitted.", 'challenge_respond', 'industry-reward-grayscale'));
        $ctx.find('.pt-scorm-reward').html(badgeHtml('Guided Assignment Ace', "Upload a facilitator-led assignment to earn +2 wings—awarded when teacher reviews your submission.", 'assignment_submission', 'scorm-reward-grayscale'));
        $ctx.find('.pt-external-reward').html(badgeHtml('Project SmartScore', "Each project gets an AI score (teacher can adjust) worth 1-12 wings—higher quality work, higher wings.", 'project_ai_score_point', 'external-reward-grayscale'));
        $ctx.find('.pt-assignment-scorm-reward').html(badgeHtml('BrainBoosters', "Attempt the self-correcting assignment once: +5 wings for correct answers, 0 if incorrect. Give it your best! No retry.", 'scorm_learning_reward', 'assignment-scorm-reward-grayscale'));
        $ctx.find('.pt-pre-assessment-reward').html(badgeHtml('Getting Started (Pre-Assessment)', "Complete your starting checkpoint or pre assessment to earn +5 wings. Awarded once only at the beginning of a level.", 'pre_assessment_score', 'pre-assessment-reward-grayscale'));
        $ctx.find('.pt-post-assessment-reward').html(badgeHtml('Victory Lap (Post-Assessment)', "Finish the end-of-level checkpoint or post-assessment to earn +5 wings. Awarded once when you complete the level.", 'post_assessment_score', 'post-assessment-reward-grayscale'));

        $ctx.find('[data-toggle="tooltip"]').tooltip();
        $ctx.find('.student_information').show();
        $('html, body').animate({ scrollTop: $ctx.find('.student_information').offset().top - 100 }, 1000);
    }

    function loadStudentProgressInfo(studentId, gradeId) {
        var studentInfoUrl = (typeof trainerStudentInfoUrl !== 'undefined')
            ? trainerStudentInfoUrl
            : "{{ $studentInfoUrl }}";
        $ctx.find('.student_information').hide();
        $.ajax({
            type: "POST",
            url: studentInfoUrl,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { studentId: studentId, gradeId: gradeId, academic_year: selectedAcademicYear },
            dataType: 'json',
            success: function(res) { renderStudentProgress(res); },
            error: function(xhr) {
                var msg = (xhr.status === 419)
                    ? 'Session expired. Please refresh the page.'
                    : 'Could not load student information. Please try again.';
                alert(msg);
            }
        });
    }

    $(document).ready(function() {
        $ctx = $('#tab-content' + sfx);
        selectedAcademicYear = $('#academic_year' + sfx).val() || 'past';
        updateMonthFilterVisibility();

        @if(!$showProgressTab)
            loadLeaderBoard();
        @endif

        $('#grades' + sfx).change(function() {
            var grade_id = $(this).val();
            selectedStudentGradeId = grade_id;

            if (!showStudentList) {
                if (selectedStudentId) { loadStudentProgressInfo(selectedStudentId, grade_id); }
                if ($('#leader-board-tab' + sfx).hasClass('active') || $('#leader-board' + sfx).hasClass('active')) { loadLeaderBoard(); }
                return;
            }

            $ctx.find('.total-students-pursuing').empty();
            $('#student_list' + sfx).empty();
            $ctx.find('.student_information').hide();
            selectedStudentId = null;
            $.ajax({
                type: "POST",
                url: "{{ $routeGetProgressByGrade }}",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { grade_id: grade_id, school_id: schoolId },
                dataType: 'json',
                success: function(data) {
                    $ctx.find('.total-students-pursuing').html('You have ' + data.students.length + ' students from your school pursuing ' + data.grade_name.grade + ' Course');
                    if (data.session_completed !== undefined) { $('.school-module-percentage').html(data.session_completed + '%'); }
                    var html = '';
                    for (var i = 0; i < data.students.length; i++) {
                        var image = data.students[i].image;
                        var imgSrc = (!$.isEmptyObject(image) && image != 'no_image')
                            ? "{{ url('') }}/tenants/" + image : "{{ url('') }}/img/default_image.png";
                        html += '<a href="javascript:void(0);" class="student_info" data-id="' + data.students[i].id + '">'
                            + '<div class="Student_infos"><div class="Student_img"><img src="' + imgSrc + '" alt="Student" class="mr-2 img-circle img-size-32"></div>'
                            + '<div class="Student_name"><p class="m-0"> ' + data.students[i].user['name'] + ' </p></div></div></a>';
                    }
                    $('#student_list' + sfx).html(html);
                }
            });
        });

        $(document).on('change', '#academic_year' + sfx, function() {
            selectedAcademicYear = $(this).val();
            updateMonthFilterVisibility();
            var onLeaderBoard = $('#leader-board-tab' + sfx).hasClass('active') || $('#leader-board' + sfx).hasClass('active');
            if (onLeaderBoard) {
                loadLeaderBoard();
            } else if (selectedStudentId) {
                var $studentBtn = $ctx.find('.student_info[data-id="' + selectedStudentId + '"]');
                if ($studentBtn.length) {
                    $studentBtn.trigger('click');
                } else {
                    loadStudentProgressInfo(selectedStudentId, selectedStudentGradeId || $('#grades' + sfx).val());
                }
            }
        });

        $(document).on('change', '#lb_grade' + sfx, function() {
            if ($('#leader-board-tab' + sfx).hasClass('active') || $('#leader-board' + sfx).hasClass('active')) { loadLeaderBoard(); }
        });

        $(document).on('change', '#lb_month' + sfx, function() {
            selectedLeaderBoardMonth = $(this).val();
            if ($('#leader-board-tab' + sfx).hasClass('active') || $('#leader-board' + sfx).hasClass('active')) { loadLeaderBoard(); }
        });

        $('#progress-report' + sfx + ', #leader-board' + sfx).on('shown.bs.tab', function(e) {
            updateMonthFilterVisibility();
            if ($(e.target).attr("id") === 'leader-board' + sfx) { loadLeaderBoard(); }
        });

        $('#accordion' + sfx).on('show.bs.collapse', function(e) {
            var stud_id = $('#' + e.target.id).attr('data-id');
            $.ajax({
                type: "POST",
                url: "{{ $routeGetRewardPointDetails }}",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                beforeSend: function() { $('.lb-reward-detail-' + stud_id).html('please wait...'); },
                data: { stud_id: stud_id, academic_year: selectedAcademicYear, month: selectedLeaderBoardMonth },
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

        if (showStudentList) { $('select#grades' + sfx).change(); }

        $ctx.on('click', '.student_info', function() {
            var studentId = $(this).attr('data-id');
            var gradeId   = $('#grades' + sfx).val();
            selectedStudentId      = studentId;
            selectedStudentGradeId = gradeId;
            loadStudentProgressInfo(studentId, gradeId);
        });

        $(document).on('click', '#loadObservationsBtn' + sfx, function () {
            if (!selectedStudentId) {
                alert('Please select a student first.');
                return;
            }

            var gradeId = selectedStudentGradeId || $('#grades' + sfx).val();

            $('#obsModalBody' + sfx).html(
                '<div class="text-center py-4">' +
                '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>' +
                '<span style="font-size:13px;color:#888;margin-left:8px;">Loading observations...</span>' +
                '</div>'
            );
            $('#observationModal' + sfx).modal('show');

            $.ajax({
                type: 'POST',
                url: "{{ $routeGetStudentObservations }}",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { studentId: selectedStudentId, gradeId: gradeId },
                dataType: 'json',
                success: function (records) {
                    if (!records || !records.length) {
                        $('#obsModalBody' + sfx).html(
                            '<p class="text-center text-muted py-4" style="font-size:13px;">No observations recorded yet for this level.</p>'
                        );
                        return;
                    }

                    var grouped = {};
                    var sessionOrder = [];
                    $.each(records, function (i, rec) {
                        var key = (rec.session_name || '—') + '|' + (rec.session_date || '');
                        if (!grouped[key]) {
                            grouped[key] = [];
                            sessionOrder.push(key);
                        }
                        grouped[key].push(rec);
                    });

                    var html = '';
                    $.each(sessionOrder, function (i, sessionName) {
                        var obs = grouped[sessionName];
                        var first = obs[0] || {};
                        var title = first.session_name || '—';
                        var sessionDate = first.session_date_formatted || '';

                        html += '<div style="border:1px solid #e0e0e0;border-radius:10px;margin-bottom:12px;overflow:hidden;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,0.06);">';
                        html += '<div style="background:#f5f7fa;padding:8px 14px;border-bottom:1px solid #e8e8e8;">';
                        html +=   '<span style="font-size:11px;font-weight:600;color:#666;text-transform:uppercase;letter-spacing:0.4px;">Session</span>';
                        html +=   '<div style="font-size:13px;font-weight:700;color:#333;margin-top:2px;line-height:1.3;">' + title + '</div>';
                        if (sessionDate) {
                            html += '<div style="font-size:11px;color:#888;margin-top:2px;">' + sessionDate + '</div>';
                        }
                        html += '</div>';

                        $.each(obs, function (j, rec) {
                            html += '<div style="padding:12px 14px;display:flex;gap:12px;align-items:flex-start;' +
                                    (j > 0 ? 'border-top:1px solid #f0f0f0;' : '') + '">';

                            html += '<div style="flex-shrink:0;width:36px;height:36px;margin-top:2px;">';
                            if (rec.icon_url) {
                                html += '<img src="' + rec.icon_url + '" style="width:36px;height:36px;object-fit:contain;display:block;" onerror="this.style.opacity=0.3;">';
                            }
                            html += '</div>';

                            html += '<div style="flex:1;min-width:0;">';
                            html +=   '<div style="font-weight:700;font-size:13px;color:#222;line-height:1.3;">' + (rec.name || '') + '</div>';
                            html +=   '<div style="font-size:11px;color:#aaa;margin-top:2px;">' + (rec.created_at || '') + '</div>';
                            if (rec.short_note) {
                                html += '<div style="font-size:12px;color:#555;margin-top:5px;line-height:1.5;word-break:break-word;">' + rec.short_note + '</div>';
                            }
                            html += '</div>';

                            if (rec.image_url) {
                                html += '<img src="' + rec.image_url + '" style="width:68px;height:68px;border-radius:8px;object-fit:cover;flex-shrink:0;margin-top:2px;">';
                            }

                            html += '</div>';
                        });

                        html += '</div>';
                    });

                    $('#obsModalBody' + sfx).html(html);
                },
                error: function (xhr) {
                    var msg = (xhr.status === 419)
                        ? 'Session expired. Please refresh the page.'
                        : 'Could not load observations. Please try again.';
                    $('#obsModalBody' + sfx).html(
                        '<p class="text-center text-muted py-4" style="font-size:13px;">' + msg + '</p>'
                    );
                }
            });
        });
    });

    // Expose per-instance interface for the trainer blade
    window['ps' + sfx] = {
        loadLeaderBoard: loadLeaderBoard,
        loadStudentProgressInfo: loadStudentProgressInfo,
        setStudent: function(studentId, gradeId) {
            selectedStudentId      = studentId;
            selectedStudentGradeId = gradeId;
        },
        hideProgressTab: function() {
            $('#progress-report' + sfx).hide();
            $('#leader-board' + sfx).trigger('click');
        },
        showProgressTab: function() {
            $('#progress-report' + sfx).show();
        },
        reset: function(academicYear) {
            selectedStudentId        = null;
            selectedStudentGradeId   = null;
            selectedLeaderBoardMonth = '';
            if (academicYear !== undefined) {
                $('#academic_year' + sfx).val(academicYear);
                selectedAcademicYear = academicYear;
            }
            $('#lb_month' + sfx).val('');
            $('#accordion' + sfx).empty();
            if ($ctx) { $ctx.find('.student_information').hide(); }
            updateMonthFilterVisibility();
        }
    };
})();
</script>
