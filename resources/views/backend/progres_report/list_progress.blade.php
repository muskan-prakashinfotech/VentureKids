@extends('backend.layouts.app')
@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="pageTitle">
        <h2>{{ $school_data->school_name }} Progress Report</h2>
        <a href="{{ route('backend.schoollist.schoolList') }}" class="btn btn-sm btn-warning float-right"> <i class="material-icons">west</i> Back</a>
    </div>

    <!-- School-level summary stats (backend only) -->
    <section>
        <div class="container-fluid p-0">
            <div class="row">
                <div class=" mb-2 col-md-12">
                    <div class="mb-2 sticky-top">
                        <div class="card card-default">
                            <div class="card-body">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-xl-4 pl-xl-3 pr-xl-3 mb-4">
                                            <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 orange_box school-progress-summary-card school-progress-summary-card--students">
                                                <div class="IconCounter">
                                                    <div class="IconRaps">
                                                        <span><i class="fa fa-users"></i></span>
                                                    </div>
                                                    <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Total Students</p></div>
                                                    <div class="CounterRaps">
                                                        <span>{{$school_data->total_students}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 pl-xl-3 pr-xl-3 mb-4">
                                            <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 blue_box school-progress-summary-card school-progress-summary-card--sessions">
                                                <div class="IconCounter">
                                                    <div class="IconRaps">
                                                        <span><i class="fa fa-boxes"></i></span>
                                                    </div>
                                                    <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Session Completion</p></div>
                                                    <div class="CounterRaps">
                                                        <span class="school-module-percentage">{{ $school_data->session_completed }}%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 pl-xl-3 pr-xl-3 mb-4">
                                            <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 yellow_box school-progress-summary-card school-progress-summary-card--assignments">
                                                <div class="IconCounter">
                                                    <div class="IconRaps">
                                                        <span><i class="fa fa-book"></i></span>
                                                    </div>
                                                    <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Assignments Uploaded</p></div>
                                                    <div class="CounterRaps">
                                                        <span>{{ $school_data->assignments_uploaded }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 pl-xl-3 pr-xl-3 mb-4">
                                            <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 orange_box school-progress-summary-card school-progress-summary-card--industry">
                                                <div class="IconCounter">
                                                    <div class="IconRaps">
                                                        <span><i class="fa fa-industry"></i></span>
                                                    </div>
                                                    <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Industry Challenges</p></div>
                                                    <div class="CounterRaps">
                                                        <span>{{ $school_data->industry_challenges_responded }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 pl-xl-3 pr-xl-3 mb-4">
                                            <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 blue_box school-progress-summary-card school-progress-summary-card--projects">
                                                <div class="IconCounter">
                                                    <div class="IconRaps">
                                                        <span><i class="fa fa-list"></i></span>
                                                    </div>
                                                    <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Projects Uploaded</p></div>
                                                    <div class="CounterRaps">
                                                        <span>{{ $school_data->projects_uploaded }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-4 pl-xl-3 pr-xl-3 mb-4">
                                            <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 yellow_box school-progress-summary-card school-progress-summary-card--daily">
                                                <div class="IconCounter">
                                                    <div class="IconRaps">
                                                        <span><i class="fa fa-industry"></i></span>
                                                    </div>
                                                    <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Daily Challenges</p></div>
                                                    <div class="CounterRaps">
                                                        <span>{{ $school_data->weekly_challenges_submitted }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if((int) $school_data->standard_assessment_assigned === 1)
                                            <div class="col-xl-4 pl-xl-3 pr-xl-3 mb-4">
                                                <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 orange_box">
                                                    <div class="IconCounter">
                                                        <div class="IconRaps">
                                                            <span><i class="fa fa-clipboard-check"></i></span>
                                                        </div>
                                                        <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Baseline Assessment</p></div>
                                                        <div class="CounterRaps">
                                                            <span>{{ $school_data->baseline_assessment_submitted }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 pl-xl-3 pr-xl-3 mb-4">
                                                <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 blue_box">
                                                    <div class="IconCounter">
                                                        <div class="IconRaps">
                                                            <span><i class="fa fa-tasks"></i></span>
                                                        </div>
                                                        <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Post Assessment</p></div>
                                                        <div class="CounterRaps">
                                                            <span>{{ $school_data->post_assessment_submitted }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-4 pl-xl-3 pr-xl-3 mb-4">
                                                <div class="InnerBox h-100 pt-4 pb-4 pl-3 pe-3 yellow_box">
                                                    <div class="IconCounter">
                                                        <div class="IconRaps">
                                                            <span><i class="fa fa-comment-dots"></i></span>
                                                        </div>
                                                        <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Student Reflections</p></div>
                                                        <div class="CounterRaps">
                                                            <span>{{ $school_data->student_reflections_submitted }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Shared tabs: Student Progress Report + Leader Board -->
    <section>
        <div class="container-fluid p-0">
            <div class="row">
                @include('partials.progress_report_tabs', [
                    'schoolId'               => $school_data->id,
                    'routeGetProgressByGrade' => route('backend.getProgressByGrade'),
                    'routeGenerateLeaderBoard' => route('backend.generateLeaderBoard'),
                    'studentInfoUrl'          => route('backend.student-info'),
                    'routeGetRewardPointDetails' => route('backend.getRewardPointDetails'),
                    'routeGetStudentObservations' => route('backend.getStudentObservations'),
                ])
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
@endsection
