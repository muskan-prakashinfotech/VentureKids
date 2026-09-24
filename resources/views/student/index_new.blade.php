@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <!-- <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Profile</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Profile</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="pr-0 border-bottom mb-4 pb-3 pt-3"> 
            <div class="row mb-3">
                <div class="col-xl-3 col-md-8 mb-3 pl-xl-3 pr-xl-3">
                    <select class="form-select w-100" aria-label="Default select example" name="primaryLevel" id="primaryLevel">
                        <option value="">Select Level</option>
                        @if(isset($filteredLevels['primary']))
                            <optgroup label="Levels">
                            @foreach ($filteredLevels['primary'] as $k => $level)
                                <option value="{{ $level['id'] }}" @if($subscribedLevel==$level['id']) selected @endif>{{ $level['grade'] }}</option>
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
            </div>
            <div class="row">
                <div class="col-lg-12 mb-2 pl-xl-3 pr-xl-3">
                    <div class="title"><h3>Completion Rate</h3></div>
                </div>
                <div class="col-xl-6 mb-3 pl-xl-3 pr-xl-3">
                    <div class="InnerBox">
                        <div class="IconCounter">
                            <div class="IconRaps blue-bg">
                                <span><i class="fa fa-boxes fs-50"></i></span>
                            </div>
                            <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Session</p></div>
                            <div class="CounterRaps">
                                <span class="module-percentage">{{$modulePercentage}}% ({{$completionData['scorm_completion']['completed']}}/{{$completionData['scorm_completion']['total']}})</span>
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
                                <span class="assignment-percentage">{{$assignmentPercentage}}% ({{$completionData['assignment_submission']['completed']}}/{{$completionData['assignment_submission']['total']}})</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="mt-4 ">
            <div class="row">
                <div class="col-xl-7">
                    <div class="row">
                        <div class="col-xl-6 mb-3">
                            <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 orange_box student-dashboard-card student-dashboard-card--industry">
                                <div class="IconCounter">
                                    <div class="IconRaps">
                                        <span><i class="fa fa-industry"></i></span>
                                    </div>
                                    <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Industry Challenges</p></div>
                                    <div class="CounterRaps">
                                        <span>{{$industryChallengeCount}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 mb-3">
                            <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 blue_box student-dashboard-card student-dashboard-card--projects">
                                <div class="IconCounter">
                                    <div class="IconRaps">
                                        <span><i class="fa fa-list"></i></span>
                                    </div>
                                    <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Projects Uploaded</p></div>
                                    <div class="CounterRaps">
                                        <span>{{$projectCount}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 mb-3">
                            <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 yellow_box student-dashboard-card student-dashboard-card--daily-challenges">
                                <div class="IconCounter">
                                    <div class="IconRaps">
                                        <span><i class="fa fa-industry"></i></span>
                                    </div>
                                    <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Daily Challenges</p></div>
                                    <div class="CounterRaps">
                                        <span>{{$weeklyChallengeCount}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 mb-3">
                            <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 orange_box student-dashboard-card student-dashboard-card--affirmation">
                                <div class="IconCounter">
                                    <div class="IconRaps" id="play-icon" @if(!empty($latestAffirmation)) style="cursor: pointer;" @endif>
                                        <span><i class="fa fa-volume-up"></i></span>
                                    </div>
                                    <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Play Affirmation</p></div>
                                    <div class="CounterRaps">
                                        <span><i class="fa fa-play-circle text-primary" style="font-size: 18px;"></i></span>
                                        <span id="play-count" class="fw-bold fs-6">{{ $studentPlayCount }}</span>
                                    </div>
                                </div>
                                {{-- Hidden Audio --}}
                                <div class="Audio w-100 mt-3">
                                    @if (!empty($latestAffirmation) && $latestAffirmation->file_path)
                                    <div class="audio-wrapper" id="audio-wrapper" style="display: none !important;">
                                        <audio controls style="display: none !important;" id="student-audio">
                                            <source src="{{ asset($latestAffirmation->file_path) }}" type="audio/{{ pathinfo($latestAffirmation->file_path, PATHINFO_EXTENSION) }}">
                                            Your browser does not support the audio element.
                                        </audio>
                                    </div>
                                    @else
                                    <!-- <span class="text-muted">No affirmation available</span> -->
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @if($recommendedChallenges->count())
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header custom_padding">
                                    <h4 class="m-0">Recommended Challenges</h4>
                                </div>
                                <div class="card-body ">
                                    <div class="row pt-3">
                                        @foreach($recommendedChallenges as $challenge)
                                        <div class="col-lg-6 mb-4 pl-3 pr-3">
                                            <a href="{{ route('student.event_view',$challenge->id) }}">
                                                <div class="recommended_challenges_box" style="background-image: url({{asset($challenge->event_image)}})">
                                                    <h5>{{$challenge->event_name}}</h5>    
                                                </div>
                                            </a>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>    
                    </div>
                    @endif
                    <div class="row mb-4">
                        <div class="col-12">
                                <div class="card bg_brick_orange">
                                    <div class="card-header custom_padding_badges d-flex flex-wrap justify-content-between bg-skyblue">
                                        <div class="d-flex flex-wrap align-items-center">
                                            <h4 class="m-0 mr-3">Badges</h4>
                                            @if(!empty($academicYears) && count($academicYears))
                                                <select name="academic_year" id="academic_year" class="form-control-sm academic-year-select">
                                                    @foreach($academicYears as $academicYear)
                                                        <option value="{{ $academicYear['id'] }}" @if((string)$selectedAcademicYear === (string)$academicYear['id']) selected @endif>
                                                            {{ $academicYear['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </div>
                                        <h4 class="m-0">Total Points : <span id="academic-year-total-points">{{array_sum($reward_points)}}</span></h4>
                                        <div class="top_badge_wrap"> <img src="{{asset('/image/badge2.png')}}"> </div>
                                    </div>
                                    <div class="card-body badge-box-height">
                                        <div class="row">
                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                    <div class="badge w-100 academic-reward-badge @if(!array_key_exists('video_learning_point', $reward_points)) filter_grayscale @endif" data-reward-key="video_learning_point">
                                                        <img src="{{asset('/asset/dist/img/reward_points/brain.png')}}" />
                                                    </div>
                                                    <div class="d-block text-center w-100">
                                                        <h6 class="text-center tooltip_h6">
                                                            <span>Learning Voyager</span> 
                                                            <span class="reward-tooltip-info" data-toggle="tooltip" data-bs-trigger="hover" data-placement="top" title="Watch the entire learning video from start to finish to earn +5 wings. Earn once per video (replays don't add more wings).">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                            </span> 
                                                            <p class="mb-0" id="reward-count-video_learning_point">
                                                                @if(array_key_exists('video_learning_point', $reward_points))({{$reward_points['video_learning_point']}})@endif
                                                            </p>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                    <div class="badge w-100 academic-reward-badge @if(!array_key_exists('scorm_learning_reward', $reward_points)) filter_grayscale @endif" data-reward-key="scorm_learning_reward">
                                                        <img src="{{asset('/asset/dist/img/reward_points/Lightbulb.png')}}" />
                                                    </div>
                                                    <div class="d-block text-center w-100">
                                                        <h6 class="text-center tooltip_h6">
                                                            <span>BrainBoosters</span> 
                                                            <span class="reward-tooltip-info" data-toggle="tooltip" data-bs-trigger="hover" data-placement="top" title="Attempt the self-correcting assignment once: +5 wings for correct answers, 0 if incorrect. Give it your best! No retry.">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                            </span> 
                                                            <p class="mb-0" id="reward-count-scorm_learning_reward">
                                                                @if(array_key_exists('scorm_learning_reward', $reward_points))({{$reward_points['scorm_learning_reward']}})@endif 
                                                            </p>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                    <div class="badge w-100 academic-reward-badge @if(!array_key_exists('weekly_challenge', $reward_points)) filter_grayscale @endif" data-reward-key="weekly_challenge">
                                                        <img src="{{asset('/asset/dist/img/reward_points/rocket.png')}}" />
                                                    </div>
                                                    <div class="d-block text-center w-100">
                                                        <h6 class="text-center tooltip_h6 mb-0">
                                                            <span>Daily Quiz Whiz</span> 
                                                            <span class="reward-tooltip-info" data-toggle="tooltip" data-bs-trigger="hover" data-placement="top" title="Answer 5 mini-questions daily; earn +1 wing per correct answer (max +5/day). A new quiz unlocks each day and if you don't log in, it stays locked, so keep your streak!">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                            </span> 
                                                            <p class="mb-0" id="reward-count-weekly_challenge">
                                                                @if(array_key_exists('weekly_challenge', $reward_points))({{$reward_points['weekly_challenge']}})@endif
                                                            </p>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                    <div class="badge w-100 academic-reward-badge @if(!array_key_exists('project_ai_score_point', $reward_points)) filter_grayscale @endif" data-reward-key="project_ai_score_point">
                                                        <img src="{{asset('/asset/dist/img/reward_points/parent.png')}}" />
                                                    </div>
                                                    <div class="d-block text-center w-100">
                                                        <h6 class="text-center tooltip_h6">
                                                            <span>Project SmartScore</span> 
                                                            <span class="reward-tooltip-info" data-toggle="tooltip" data-bs-trigger="hover" data-placement="top" title="Each project gets an AI score (teacher can adjust) worth 1-12 wings—higher quality work, higher wings.">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                            </span> 
                                                            <p class="mb-0" id="reward-count-project_ai_score_point">
                                                                @if(array_key_exists('project_ai_score_point', $reward_points))({{$reward_points['project_ai_score_point']}})@endif
                                                            </p>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                    <div class="badge w-100 academic-reward-badge @if(!array_key_exists('project', $reward_points)) filter_grayscale @endif" data-reward-key="project">
                                                        <img src="{{asset('/asset/dist/img/reward_points/growth.png')}}" />
                                                    </div>
                                                    <div class="d-block text-center w-100">
                                                        <h6 class="text-center tooltip_h6">
                                                            <span>Project Posted</span> 
                                                            <span class="reward-tooltip-info" data-toggle="tooltip" data-bs-trigger="hover" data-placement="top" title="Submit your project to your portfolio to earn +1 wings-awarded per project submitted.">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                            </span> 
                                                            <p class="mb-0" id="reward-count-project">
                                                                @if(array_key_exists('project', $reward_points))({{$reward_points['project']}})@endif
                                                            </p>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                    <div class="badge w-100 academic-reward-badge @if(!array_key_exists('assignment_submission', $reward_points)) filter_grayscale @endif" data-reward-key="assignment_submission">
                                                        <img src="{{asset('/asset/dist/img/reward_points/steps.png')}}" />
                                                    </div>
                                                    <div class="d-block text-center w-100">
                                                        <h6 class="text-center tooltip_h6">
                                                            <span>Guided Assignment Ace</span> 
                                                            <span class="reward-tooltip-info" data-toggle="tooltip" data-bs-trigger="hover" data-placement="top" title="Upload a facilitator-led assignment to earn +2 wings—awarded when teacher reviews your submission.">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                            </span> 
                                                            <p class="mb-0" id="reward-count-assignment_submission">
                                                                @if(array_key_exists('assignment_submission', $reward_points))({{$reward_points['assignment_submission']}})@endif
                                                            </p>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                    <div class="badge w-100 academic-reward-badge @if(!array_key_exists('pre_assessment_score', $reward_points)) filter_grayscale @endif" data-reward-key="pre_assessment_score">
                                                        <img src="{{asset('/asset/dist/img/reward_points/Paperplane1.png')}}" />
                                                    </div>
                                                    <div class="d-block text-center w-100">
                                                        <h6 class="text-center tooltip_h6">
                                                            <span>Getting Started (Pre-Assessment)</span> 
                                                            <span class="reward-tooltip-info" data-toggle="tooltip" data-bs-trigger="hover" data-placement="top" title="Complete your starting checkpoint or pre assessment to earn +5 wings. Awarded once only at the beginning of a level.">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                            </span> 
                                                            <p class="mb-0" id="reward-count-pre_assessment_score">
                                                                @if(array_key_exists('pre_assessment_score', $reward_points))({{$reward_points['pre_assessment_score']}})@endif
                                                            </p>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                    <div class="badge w-100 academic-reward-badge @if(!array_key_exists('post_assessment_score', $reward_points)) filter_grayscale @endif" data-reward-key="post_assessment_score">
                                                        <img src="{{asset('/asset/dist/img/reward_points/Paperplane1.png')}}" />
                                                    </div>
                                                    <div class="d-block text-center w-100">
                                                        <h6 class="text-center tooltip_h6">
                                                            <span>Victory Lap (Post-Assessment)</span> 
                                                            <span class="reward-tooltip-info" data-toggle="tooltip" data-bs-trigger="hover" data-placement="top" title="Finish the end-of-level checkpoint or post-assessment to earn +5 wings. Awarded once when you complete the level.">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                            </span> 
                                                            <p class="mb-0" id="reward-count-post_assessment_score">
                                                                @if(array_key_exists('post_assessment_score', $reward_points))({{$reward_points['post_assessment_score']}})@endif
                                                            </p>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-4 ps-3 pe-3 mb-3">
                                                <div class="bg-white h-100 p-2 borde-radius-15 position-relative">
                                                    <div class="badge w-100 academic-reward-badge @if(!array_key_exists('challenge_respond', $reward_points)) filter_grayscale @endif" data-reward-key="challenge_respond">
                                                        <img src="{{asset('/asset/dist/img/reward_points/education.png')}}" />
                                                    </div>
                                                    <div class="d-block text-center w-100">
                                                        <h6 class="text-center tooltip_h6">
                                                            <span>Industry Innovator</span> 
                                                            <span class="reward-tooltip-info" data-toggle="tooltip" data-bs-trigger="hover" data-placement="top" title="Submit an Industry Challenge entry to earn +1 wings. Awarded per challenge submitted.">
                                                                <i class="fas fa-exclamation-circle"></i>
                                                            </span> 
                                                            <p class="mb-0" id="reward-count-challenge_respond">
                                                                @if(array_key_exists('challenge_respond', $reward_points))({{$reward_points['challenge_respond']}})@endif
                                                            </p>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>    
                    </div>
                    @include('student.partials.dashboard_leaderboard', ['topFiveStudents' => $topFiveStudents])
                </div>
                <div class="col-xl-5">
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card direct-chat direct-chat-primary">
                                <div class="card-header custom_padding">
                                    <h4 class="m-0">Feedback/Comment</h4>
                                    <span><i class="fa fa-comments"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-4">
                            <div class="card">
                                <div class="card-header pb-0 custom_padding subscription-activity-tab">
                                    <h4 class="m-0 w-100 ">Subscription Activity</h4>
                                    <ul class="w-100 nav nav-tabs" role="tablist">
                                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#primary-level" role="tab">Level</a></li>
                                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#content-add-on" role="tab">Content Add On</a></li>
                                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#student-observations" role="tab" id="studentObsTab">Observation Report</a></li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="primary-level" role="tabpanel">
                                            @if(isset($filteredLevels['primary']))
                                                <ul class="subscription-activity">
                                                @foreach ($filteredLevels['primary'] as $k => $level)
                                                    <li @if(in_array($level['id'], $subscribedGradeIds)) class="active" @endif>
                                                    @if(in_array($level['id'], $subscribedGradeIds))<span>Subscribed to</span>@endif
                                                        <span>{{ $level['grade'] }}</span> 
                                                    </li>
                                                @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                        <div class="tab-pane" id="content-add-on" role="tabpanel">
                                            @if(isset($filteredLevels['add-ons']))
                                                <ul class="subscription-activity content-add-on">
                                                @foreach ($filteredLevels['add-ons'] as $k => $level)
                                                    <li @if($subscribedLevel == $level['id']) class="active" @endif>
                                                        <span>@if($subscribedLevel == $level['id']) Currently @endif Subscribed to</span>
                                                        <span>{{ $level['grade'] }}</span>
                                                    </li>
                                                @endforeach
                                                </ul>
                                            @else
                                                <div class="subscription-activity content-add-on">
                                                    You've not subscribed to any content add-ons
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Observation tab pane — loaded lazily via AJAX -->
                                        <div class="tab-pane" id="student-observations" role="tabpanel">
                                            <div id="obsLoadingSpinner" style="display:none; text-align:center; padding:20px;">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                                <span style="font-size:13px; color:#888; margin-left:6px;">Loading observations...</span>
                                            </div>
                                            <div id="obsRecordsContainer" class="obsRecordsContainer"></div>
                                        </div>

                                    </div>
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
                                        <img src="{{asset('/dashboard/download-profile.gif')}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                
            </div>
        </div>

            <!-- Modal -->
        <div class="modal fade" id="howItWorksModal" tabindex="-1" role="dialog" aria-labelledby="howItWorksModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="howItWorksModalLabel">How It Works</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <iframe id="howItWorksVideo" width="100%" height="480" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
<script>

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    })

    var msg =  '{{ session()->get('change-password-success') }}';
    
    if($.trim(msg).length) {
        alert(msg);
    }
    
    $("#primaryLevel").change(function () {
        var gradeId = parseInt(this.value);
        if(!isNaN(gradeId)) {
            $("#primaryLevel").attr('disabled','disabled');
            $.ajax({
                url: "{{ route('student.getCompletionRatePercentage') }}",
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    gradeId: gradeId,
                },
                method: "POST",
                success: function(res) {
                    if(res.success) {
                        // $('.quiz-percentage').html(res.success.quizPercentage + '%');
                        $('.assignment-percentage').html(`${res.success.assignmentPercentage}% (${res.success.completionData.assignment_submission.completed}/${res.success.completionData.assignment_submission.total})`);
                        $('.module-percentage').html(`${res.success.modulePercentage}% (${res.success.completionData.scorm_completion.completed}/${res.success.completionData.scorm_completion.total})`);
                        $("#primaryLevel").removeAttr('disabled');
                    } 
                }
            });
        } else {
            $("#primaryLevel").removeAttr('disabled');
        }
    });

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    function updateAcademicYearRewards(response) {
        if (!response || !response.success) {
            return;
        }

        var rewardPoints = response.reward_points || {};

        $('#academic-year-total-points').text(response.total_points || 0);

        $('.academic-reward-badge').each(function () {
            var $badge = $(this);
            var rewardKey = $badge.data('reward-key');
            var $count = $('#reward-count-' + rewardKey);

            if (Object.prototype.hasOwnProperty.call(rewardPoints, rewardKey)) {
                $badge.removeClass('filter_grayscale');
                if ($count.length) {
                    $count.text('(' + rewardPoints[rewardKey] + ')');
                }
            } else {
                $badge.addClass('filter_grayscale');
                if ($count.length) {
                    $count.text('');
                }
            }
        });

        if (response.leaderboard_html) {
            $('#student-leaderboard-section').replaceWith(response.leaderboard_html);
        }

        $('[data-toggle="tooltip"]').tooltip();
    }

    $(document).on('change', '.academic-year-select', function () {
        var selectedAcademicYear = $(this).val();

        if (!selectedAcademicYear) {
            return;
        }

        $.ajax({
            url: "{{ route('student.academic-year-rewards') }}",
            method: "GET",
            dataType: "json",
            data: {
                academic_year: selectedAcademicYear
            },
            beforeSend: function () {
                $('.academic-year-select').prop('disabled', true);
            },
            success: function (response) {
                updateAcademicYearRewards(response);
            },
            complete: function () {
                $('.academic-year-select').prop('disabled', false);
            }
        });
    });

    //Audio file completely listened and also update total count
    document.addEventListener("DOMContentLoaded", function() {
        const audio = document.getElementById("student-audio");
        const playIcon = document.getElementById("play-icon");

        if (!audio || !playIcon) return;

        // Click on speaker icon triggers play
        playIcon.addEventListener("click", function() {
            audio.play();
        });

        // Track when audio is fully listened
        audio.addEventListener("ended", function() {
            fetch("{{ route('student.affirmation.tracking') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    event: "ended"
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.studentPlayCount !== undefined) {
                    document.getElementById("play-count").innerText = `${data.studentPlayCount}`;
                }
            })
            .catch(error => {
                // console.error("Error logging play count:", error);
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function() {

        function getYouTubeID(url) {
        var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        var match = url.match(regExp);
        return (match && match[2].length == 11) ? match[2] : null;
        }

        var button = document.getElementById("howItWorksBtn");
        var iframe = document.getElementById("howItWorksVideo");

        button.addEventListener("click", function() {
        var videoUrl = this.getAttribute("data-video-url");

            if (!videoUrl) {
                alert("No video URL found!");
                return;
            }

                var videoId = getYouTubeID(videoUrl);

            if (!videoId) {
                alert("Invalid YouTube URL!");
                return;
            }

            iframe.src = "https://www.youtube.com/embed/" + videoId + "?autoplay=1";

            // Show modal (Bootstrap)
            $('#howItWorksModal').modal('show');
        });

            // Clear iframe when modal closes
            $('#howItWorksModal').on('hidden.bs.modal', function () {
                iframe.src = "";
            });
    });

    // ── Observation tab — lazy AJAX load, scoped to the selected level ─────
    var studentObsLoaded = false;

    function loadStudentObservations() {
        var gradeId = parseInt($('#primaryLevel').val());

        $('#obsLoadingSpinner').hide();
        $('#obsRecordsContainer').html('');

        if (isNaN(gradeId)) {
            $('#obsRecordsContainer').html(
                '<p style="text-align:center;color:#aaa;font-size:13px;padding:24px 0;">Please select a level to view observations.</p>'
            );
            return;
        }

        $('#obsLoadingSpinner').show();

        $.ajax({
            url:  '{{ route("student.observations") }}',
            type: 'GET',
            data: { gradeId: gradeId },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (records) {
                $('#obsLoadingSpinner').hide();

                if (!records || !records.length) {
                    $('#obsRecordsContainer').html(
                        '<p style="text-align:center;color:#aaa;font-size:13px;padding:24px 0;">No observations recorded yet.</p>'
                    );
                    return;
                }

                // Group records by session
                var grouped = {};
                var sessionOrder = [];
                records.forEach(function (rec) {
                    var key = (rec.session_name || '—') + '|' + (rec.session_date || '');
                    if (!grouped[key]) {
                        grouped[key] = [];
                        sessionOrder.push(key);
                    }
                    grouped[key].push(rec);
                });

                var html = '';
                sessionOrder.forEach(function (sessionName) {
                    var obs = grouped[sessionName];
                    var first = obs[0] || {};
                    var title = first.session_name || '—';
                    var sessionDate = first.session_date_formatted || '';

                    html += '<div style="border:1px solid #e0e0e0;border-radius:10px;margin-bottom:12px;overflow:hidden;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,0.06);">'

                          // Card header — session name (shown once)
                          + '<div style="background:#f5f7fa;padding:8px 14px;border-bottom:1px solid #e8e8e8;">'
                          +   '<span style="font-size:11px;font-weight:600;color:#666;text-transform:uppercase;letter-spacing:0.4px;">Session</span>'
                          +   '<div style="font-size:13px;font-weight:700;color:#333;margin-top:2px;line-height:1.3;">' + title + '</div>'
                          +   (sessionDate ? '<div style="font-size:11px;color:#888;margin-top:2px;">' + sessionDate + '</div>' : '')
                          + '</div>';

                    // All observations for this session
                    obs.forEach(function (rec, idx) {
                        html += '<div style="padding:12px 14px;display:flex;gap:12px;align-items:flex-start;'
                              + (idx > 0 ? 'border-top:1px solid #f0f0f0;' : '') + '">'

                              // Observation icon
                              + '<div style="flex-shrink:0;width:36px;height:36px;margin-top:2px;">'
                              + (rec.icon_url
                                  ? '<img src="' + rec.icon_url + '" style="width:36px;height:36px;object-fit:contain;display:block;" onerror="this.style.opacity=0.3;">'
                                  : '')
                              + '</div>'

                              // Text body: topic label (bold), date, note
                              + '<div style="flex:1;min-width:0;">'
                              +   '<div style="font-weight:700;font-size:13px;color:#222;line-height:1.3;">' + (rec.name || '') + '</div>'
                              +   '<div style="font-size:11px;color:#aaa;margin-top:3px;">' + (rec.created_at || '') + '</div>'
                              +   (rec.short_note
                                    ? '<div style="font-size:12px;color:#555;margin-top:6px;line-height:1.5;word-break:break-word;">' + rec.short_note + '</div>'
                                    : '')
                              + '</div>'

                              // Evidence photo (if present)
                              + (rec.image_url
                                  ? '<img src="' + rec.image_url + '" style="width:68px;height:68px;border-radius:8px;object-fit:cover;flex-shrink:0;margin-top:2px;">'
                                  : '')

                              + '</div>'; // end observation row
                    });

                    html += '</div>'; // end card
                });

                $('#obsRecordsContainer').html(html);
            },
            error: function () {
                $('#obsLoadingSpinner').hide();
                $('#obsRecordsContainer').html(
                    '<p style="text-align:center;color:#aaa;font-size:13px;padding:24px 0;">Could not load observations. Please try again.</p>'
                );
            }
        });
    }

    $('#studentObsTab').on('shown.bs.tab', function () {
        if (studentObsLoaded) return;
        studentObsLoaded = true;
        loadStudentObservations();
    });

    $('#primaryLevel').on('change', function () {
        if ($('#studentObsTab').hasClass('active')) {
            loadStudentObservations();
        } else {
            studentObsLoaded = false;
        }
    });

</script>
@endsection
