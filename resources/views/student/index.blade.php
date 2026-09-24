@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper old_dashboard">
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
        <section>
            
                <div class="row mb-3">
                    <div class="col-lg-2">
                        <div class="InnerBox">
                            <div class="IconCounter">
                                <div class="IconRaps">
                                    <span><i class="fa fa-lightbulb"></i></span>
                                </div>
                                <div class="CounterRaps">
                                    <span>{{ $student->getproject_count }}</span>
                                </div>
                            </div>
                            <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Projects</p></div>
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="InnerBox">
                            <div class="IconCounter">
                                <div class="IconRaps">
                                    <span><i class="fa fa-clipboard-list"></i></span>
                                </div>
                                <div class="CounterRaps">
                                    <span>{{ $student->submission_count }} / {{ $assigment_count }}</span>
                                </div>
                            </div>
                            <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Assignment</p></div>
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="InnerBox GreenBox">
                            <div class="IconCounter">
                                <div class="IconRaps">
                                    <span><i class="fa fa-calendar"></i></span>
                                </div>
                                <div class="CounterRaps">
                                    <span>{{ $held }}</span>
                                </div>
                            </div>
                            <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Sessions Held</p></div>
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="InnerBox PinkBox">
                            <div class="IconCounter">
                                <div class="IconRaps">
                                    <span><i class="fa fa-user-graduate"></i></span>
                                </div>
                                <div class="CounterRaps">
                                    <span>{{ $attended }}</span>
                                </div>
                            </div>
                            <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Sessions Attended</p></div>
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="InnerBox">
                            <div class="IconCounter">
                                <div class="IconRaps">
                                    <span><i class="fa fa-user-check"></i></span>
                                </div>
                                <div class="CounterRaps">
                                    <span>{{ (int) (($attended / ($held > 0 ? $held : 1)) * 100) }}%</span>
                                </div>
                            </div>
                            <div class="BoxTitle"><p class="m-0 stud-dash-cnt-box">Attendance</p></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- <div class="col-md-3">
                        <div class="card card-primary card-outline">
                            <div class="card-body box-profile">
                                <div class="text-center">
                                    @if ($student->image != 'no_image')
                                        <img class="profile-user-img img-fluid img-circle"
                                            src="{{ asset($student->image) }}" alt="User profile picture">
                                    @else
                                        <img src="{{ asset('img/default-150x150.png') }}" alt="Product 1"
                                            class="mr-2 img-circle">
                                    @endif
                                </div>
                                <h3 class="text-center profile-username">{{ $student->user->name }}</h3>
                                @isset($student->school)
                                    <p class="text-center text-muted">{{ $student->school->school_name }}</p>
                                @endisset
                            </div>
                        </div>

                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">About Me</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">
                                    {{-- <strong>Blood Group:</strong> {{ $student->blood_group }} --}}
                                    {{-- <br> --}}
                                    <strong>Date of Birth:</strong> {{ $student->user->date_of_birth->format('d M, Y') }}
                                    <br>
                                    <strong>Parent Name:</strong> {{ $student->parent_name }}
                                    <br>
                                    {{-- <strong>Parent Email:</strong> {{ $student->parent_email }}
                                    <br> --}}
                                    <strong>Email:</strong> {{ $student->user->email }}
                                    <br>
                                    <strong>Phone:</strong> {{ $student->user->mobile }}
                                    <br>
                                    <strong>Address:</strong> {{ $student->address }}
                                    <br>
                                    {{-- <strong>Activity Incharge:</strong> {{ $student->activity_incharge }} --}}
                                </p>
                                <hr>
                                @isset($student->grade)
                                    <strong><i class="mr-1 fas fa-map-marker-alt"></i> Level:</strong>
                                    <p class="text-muted">{{ $student->level->grade }}</p>
                                @endisset
                            </div>
                        </div>
                    </div> -->

                    <div class="col-md-6">
                        <div class="card direct-chat direct-chat-primary">
                            <div class="card-header p-0">
                                <h3 class="m-0">Comments</h3>
                                <span><i class="fa fa-comments"></i></span>
                            </div>
                            <div class="card-body">
                            <ul class="comments_list">
                                    @foreach ($comments as $comment)
                                        <li>{{ $comment->feedback }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <!-- /.card-footer-->
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-primary">
                                    <div class="card-header">
                                            Quiz History
                                            @if($quizDetail)
                                                <div class="btn-group">
                                                    <a href="{{ route('student.downloadQuizScore', 'all') }}" class="btn btn-sm btn-warning">
                                                        <i class="fa fa-download"></i>
                                                    </a>
                                                </div>
                                            @endif
                                    </div>
                                    <div class="card-body table-responsive">
                                        @if($quizDetail)
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" style="width: 25%;">Level Name</th>    
                                                        <th scope="col" style="width: 25%;">Session Name</th>
                                                        <th scope="col" style="width: 40%;">Quiz Name</th>
                                                        <th scope="col" class="text-right" style="width: 10%;">Score</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($quizDetail as $quiz)
                                                        <tr>
                                                            <td><span title="{{ $quiz['level'] }}">{{ substr($quiz['level'], 0, 50) }}</span></td>
                                                            <td><span title="{{ $quiz['session'] }}">{{ substr($quiz['session'], 0, 50) }}</span></td>
                                                            <td><span title="{{ $quiz['title'] }}">{{ substr($quiz['title'], 0, 150) }}</span></td>
                                                            <td class="text-right">{{ $quiz['score'] }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            No data found
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="gradeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog" style="max-width : 70%" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Overall Grade</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="modal-body overallgrade">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Feedback</th>
                                            <th>Grade</th>
                                            <th>Date</th>
                                        </tr>
                                        @forelse($students->studentcomminucate as $row)
                                            <tr>
                                                <td>{{ $row->feedback }}</td>
                                                <td>{{ $row->grade }}</td>
                                                <td>
                                                    @php
                                                        $date = date('d-m-Y', strtotime($row->created_at));
                                                    @endphp
                                                    {{ $date }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" align="center">Grade data not found</td>
                                            </tr>
                                        @endforelse
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header p-0">
                                <h3 class="m-0">Overall Grade</h3>
                                <span><a href="javascript:;" data-toggle="modal" data-target="#gradeModal">All Grade</a></span>
                            </div>
                            <div class="card-body">
                                <div class="OverallGrade">
                                    <div class="GradeBox">
                                        <img src="{{ asset('asset/dist/img/student.png') }}" alt="">
                                    </div>

                                    <div class="GradeBox">
                                        <img src="{{ asset('asset/dist/img/student.png') }}" alt="">
                                    </div>

                                    <div class="GradeBox">
                                        <img src="{{ asset('asset/dist/img/student.png') }}" alt="">
                                    </div>
                                </div>

                                <div class="gradeInfo">
                                    <i class="far fa-star"></i>
                                    @if (isset($overallgrade))
                                    <span class="info-box-number">{{ $overallgrade->grade }}</span>
                                    @else
                                        <span class="info-box-number">--</span>
                                    @endif 
                                </div>
                            </div>
                        </div>  
                        <!-- <div class="info-box">
                            <span class="info-box-icon bg-warning"><i
                                    class="far fa-star"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Overall Grade</span>
                                @if (isset($overallgrade))
                                    <span class="info-box-number">{{ $overallgrade->grade }}</span>
                                @else
                                    <span class="info-box-number">--</span>
                                @endif
                                <span class="info-box-number">
                                                        <a href="javascript:;" data-toggle="modal" data-target="#gradeModal">All Grade</a>
                                </span>
                            </div>
                        </div> -->
                        @isset($feedback)
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Assessment Parameters</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool"
                                            data-card-widget="remove"><i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul>
                                        <li>Emotional Quotient (EQ)- {{ $feedback->eq }}</li>
                                        <li>Intelligence Quotient (IQ)- {{ $feedback->iq }}</li>
                                        <li>Creative &amp; Critical Thinking Quotient (CQ)-
                                            {{ $feedback->cq }}
                                        </li>
                                        <li>Adversity Quotient (AQ)- {{ $feedback->aq }}</li>
                                        <li>Social Quotient (SQ)- {{ $feedback->sq }}</li>
                                        <li>Entrepreneurship Quotient (EnQ)- {{ $feedback->enq }}</li>
                                    </ul>
                                </div>
                            </div>
                        @endisset
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
@endsection
