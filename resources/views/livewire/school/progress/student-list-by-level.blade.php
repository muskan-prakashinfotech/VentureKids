<div class="row">

    <div class="col-md-3">
        <div class="mb-2 sticky-top">
            <div class="card card-default">
                <div class="card-header">
                    You have {{ $totalstudents }} {{ Str::plural('student', $totalstudents) }} from your
                    school pursuing Entrepreneurship Course</div>
                <select class="form-control" id="grades" wire:model='grade'>
                    @forelse($grades as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->grade }}</option>
                    @empty
                    @endforelse
                </select>
                <br>

                @if (isset($students) && sizeof($students) > 0)
                    <div class="p-0 card-body table-responsive" style="max-height:535px;overflow-y: scroll;">
                        <table class="table table-striped table-valign-middle">
                            <tbody id="student_list">
                                @forelse($students as $student)
                                    <tr>
                                        <td>
                                            <button type="button" class="text-left w-100 btn"
                                                wire:click='studentInfo({{ $student->id }})'>
                                                <span class="student_info">
                                                    @if (isset($student->image) && $student->image != 'no_image')
                                                        <img src="{{ asset($student->image) }}" alt="Product 1"
                                                            class="mr-2 img-circle img-size-32">
                                                        {{ $student->user->name }} <br>
                                                    @else
                                                        <img src="{{ asset('img/default_image.png') }}" alt="Product 1"
                                                            class="mr-2 img-circle img-size-32">
                                                        {{ $student->user->name }} <br>
                                                    @endif
                                                </span>
                                                {{-- <p style="font-size: 11px; margin-left: 45px">
                                                    <span class="info-box-text">Level: </span>
                                                    <span class="info-box-number">{{ $student->grade }}</span>
                                                </p> --}}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @isset($selectstudent)
        <div class="col-md-9 student_information">
            <div class="card card-info">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3 info-box bg-success">
                                <div class="info-box-content"><span class="info-box-text">Name of the Student</span><span
                                        class="info-box-number">{{ $selectstudent->name }}</span></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3 info-box bg-primary">
                                <div class="info-box-content"><span class="info-box-text">Classes Held</span><span
                                        class="info-box-number">null</span></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3 info-box bg-danger">
                                <div class="info-box-content"><span class="info-box-text">Classes
                                        Attended</span><span class="info-box-number">null</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-info">
                                <div class="card-header">Projects involved in</div>
                                <div class="card-body" style="max-height: 250px;overflow-y: auto;">
                                    <ul class="nav flex-column">
                                        <li class="nav-item"><a
                                                href="http://localhost/9thDecembergit/public/image/project/project_CvW5zNWvo8.pdf"
                                                target="_blank" class="nav-link"><span class="btn btn-sm btn-danger"><i
                                                        class="fas fa-file-image"></i></span> Project 1</a></li>
                                        <li class="nav-item"><a
                                                href="http://localhost/9thDecembergit/public/image/project/project_K4VFt8jXME.pdf"
                                                target="_blank" class="nav-link"><span class="btn btn-sm btn-danger"><i
                                                        class="fas fa-file-image"></i></span> Project 2</a></li>
                                        <li class="nav-item"><a
                                                href="http://localhost/9thDecembergit/public/image/project/project_Fa4oJwPJhc.pdf"
                                                target="_blank" class="nav-link"><span class="btn btn-sm btn-danger"><i
                                                        class="fas fa-file-image"></i></span> Project 3</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card card-primary">
                                <div class="card-header">Assignment Submitted</div>
                                <div class="card-body" style="max-height: 250px;overflow-y: auto;">
                                    <ul class="nav flex-column">
                                        @forelse ($assignments as $assignment)
                                            <li>{{ $assignment->assignment->title }}</li>
                                        @empty
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box"><span class="info-box-icon bg-warning"><i class="far fa-star"></i></span>
                                <div class="info-box-content"><span class="info-box-text">Overall Grade</span><span
                                        class="info-box-number">null<sup>+</sup></span></div>
                            </div>
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Comments/Feedback </h3>
                                </div>
                                <div class="card-body">
                                    <ul>
                                        <li>Good Need to more improve (Alex Broad)</li>
                                        <li>More improvement needed (Donald Tramp)</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">Assessment Parameters</h3>
                                </div>
                                <div class="card-body">
                                    <ul>
                                        <li><a href="#">Emotional Quotient (EQ)-1-10</a></li>
                                        <li><a href="#">Intelligence Quotient (IQ)-1-10</a></li>
                                        <li><a href="#">Creative &amp; Critical Thinking Quotient (CQ)-1-10</a></li>
                                        <li><a href="#">Adversity Quotient (AQ)-1-10</a></li>
                                        <li><a href="#">Social Quotient (SQ) -1-10</a></li>
                                        <li><a href="#">Entrepreneurship Quotient (EnQ)– 1-10</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card card-primary">
                                <div class="card-header">Assignment Submitted</div>
                                <div class="card-body" style="max-height: 250px;overflow-y: auto;">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">Title</th>
                                                <th scope="col">Feedback</th>
                                                <th scope="col">Comment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($assignments as $assignment)
                                                <tr>
                                                    <td>{{ $assignment->assignment->title }}</td>
                                                    <td>{{ $assignment->feedback }}</td>
                                                    <td>{{ $assignment->comment }}</td>
                                                </tr>
                                            @empty
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card card-warning">
                                <div class="card-header">Note By Entrepreneurship Coach</div>
                                <div class="card-body">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam
                                    fermentum enim neque.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endisset


</div>
