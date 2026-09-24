@extends('backend.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Edit Student</h3>
                                <div class="card-tools">
                                    <a href="{{ route('backend.studentList.studentList', $student->school_id) }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                                </div>
                            </div>
                       
                            @if (session()->has('email_faild'))
                                <div class="alert alert-danger" style="text-align: center;">
                                    {{ session()->get('email_faild') }}
                                </div>
                            @endif

                            @if (session()->has('confirm_password_faild'))
                                <div class="alert alert-danger" style="text-align: center;">
                                    {{ session()->get('confirm_password_faild') }}
                                </div>
                            @endif

                            @if (session()->has('old_password_faild'))
                                <div class="alert alert-danger" style="text-align: center;">
                                    {{ session()->get('old_password_faild') }}
                                </div>
                            @endif

                            @if (session()->has('capacity_failed'))
                                <div class="alert alert-danger" style="text-align: center;">
                                    {{ session()->get('capacity_failed') }}
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card card-primary">
                                        <form action="{{ route('backend.student-update', [$student->school_id, $student->id]) }}"
                                            method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                                            <input type="hidden" name="user_id" value="{{ $student->stdUser->id }}">

                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="name">Student Full Name</label>
                                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                        id="name" name="name" value="{{ $student->stdUser->name }}" required>
                                                    @error('name')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="name">Username</label>
                                                    <small class="text-muted">(Allowed: letters, numbers, dot (.), underscore (_))</small>
                                                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                                                        id="username" name="username" value="{{ $student->stdUser->username }}" required>
                                                    @error('username')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="email">Student Email</label>
                                                    <input type="text" class="form-control @error('email') is-invalid @enderror"
                                                        id="email" name="email" placeholder=""
                                                        value="{{ $student->stdUser->email }}">

                                                    @error('email')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                @if(!isPartnerUser())
                                                    <div class="form-group">
                                                        <label for="name">Student level</label>
                                                        @foreach ($grades as $grade)
                                                            <div class="form-check">
                                                                <input name="grade_id[]" class="form-check-input" type="checkbox" value="{{ $grade->id }}" id="grade_id" @if (in_array($grade->id, explode(',',$student->grade_id))) checked @endif >
                                                                <label class="form-check-label" for="flexCheckChecked">
                                                                    {{ $grade->grade . ' (' . $grade->description . ')' }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                        @error('grade_id')
                                                            <strong class="text-danger">{{ $message }}</strong>
                                                        @enderror
                                                    </div>
                                                @endif

                                                <div class="form-group">
                                                    <label for="student_grade_id">Student Grade</label>
                                                    <select class="form-control @error('student_grade_id') is-invalid @enderror" id="student_grade_id"
                                                        name="student_grade_id" required>
                                                        <option value="">Select</option>
                                                        @foreach($student_grade as $studGrade)
                                                        <option value="{{$studGrade->id}}" @if ($student->student_grade_id == $studGrade->id) selected @endif>{{$studGrade->name}}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    @error('student_grade_id')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="country">Country</label>
                                                    @if(isPartnerUser())
                                                        <input type="text" class="form-control" value="{{ optional($country->firstWhere('id', partnerCountryId()))->name }}" readonly>
                                                        <input type="hidden" name="country" value="{{ partnerCountryId() }}">
                                                    @else
                                                        <select class="form-control @error('country') is-invalid @enderror" id="country" name="country" required>
                                                            <option value="">Select Country</option>
                                                            @foreach($country as $row)
                                                                <option value="{{$row->id}}" @if ($row->id == $student->stdUser->country_id) selected @endif>{{$row->name}}</option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                    @error('country')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="address">Address</label>
                                                    <input type="text" class="form-control @error('address') is-invalid @enderror"
                                                        id="address" name="address" placeholder="" value="{{ $student->address }}">

                                                    @error('address')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="date_of_birth">Date Of Birth</label>
                                                    <input type="date" id="date_of_birth" name="date_of_birth" placeholder=""
                                                        class="form-control @error('date_of_birth') is-invalid @enderror"
                                                        value="{{ !empty($student->stdUser->date_of_birth) ? date('Y-m-d', strtotime($student->stdUser->date_of_birth)) : '' }}">

                                                    @error('date_of_birth')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="gender">Gender</label>
                                                    <select class="form-control @error('gender') is-invalid @enderror" id="gender"
                                                        name="gender" required>
                                                        <option value="Male" @if ('Male' == $student->stdUser->gender) selected @endif>Male
                                                        </option>
                                                        <option value="Female" @if ('Female' == $student->stdUser->gender) selected @endif>Female
                                                        </option>
                                                        <option value="Other" @if ('Other' == $student->stdUser->gender) selected @endif>Other
                                                        </option>
                                                    </select>
                                                    @error('gender')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="school_batch">School Batch</label>
                                                    <select class="form-control @error('school_batch') is-invalid @enderror" id="school_batch"
                                                        name="school_batch" required>
                                                        <option value="">Select</option>
                                                        @foreach($school_batch_list as $batch)
                                                        <option value="{{$batch->id}}" @if ($student->school_batch_id == $batch->id) selected @endif>{{$batch->batch_name}}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    @error('school_batch')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>

                                            </div>
                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="card">
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="parent_name">Parent Name</label>
                                                <input type="text" class="form-control @error('parent_name') is-invalid @enderror"
                                                    id="parent_name" name="parent_name" placeholder=""
                                                    value="{{ $student->parent_name }}">

                                                @error('parent_name')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="parent_email">Parent Email</label>
                                                <input type="email"
                                                    class="form-control @error('parent_email') is-invalid @enderror"
                                                    id="parent_email" name="parent_email" placeholder=""
                                                    value="{{ $student->parent_email }}">

                                                @error('parent_email')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="parent_mobile">Phone</label>
                                                <input type="text" class="form-control @error('parent_mobile') is-invalid @enderror"
                                                    id="parent_mobile" name="parent_mobile" placeholder=""
                                                    value="{{ $student->stdUser->mobile }}">
                                                @error('parent_mobile')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="profile_image">Upload a image</label>
                                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/jpg, image/png, image/jpeg">
                                                @error('profile_image')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                                @if(!empty($student->image) && $student['image'] != 'no_image')
                                                <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3"> 
                                                    <span>{{basename($student->image)}}</span> 
                                                    <div class="action-btn">
                                                        <a href="{{ url('tenants/'.$student->image) }}" class="btn btn-success btn-sm" download>
                                                            <i class="fa fa-download"></i> 
                                                        </a>  
                                                        <a onclick="deleteStudentProfileAvatar({{$student->id}})" class="btn btn-danger btn-sm" id="attachId{{$student->id}}">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>

                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title font-weight-bold">Reset Password</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="newpassword">New Password</label>
                                                <div class="input-group mb-3">
                                                    <input type="password" class="form-control" id="newpassword" name="new_password">
                                                    <div class="input-group-append viewpassword" data-id="newpassword">
                                                        <span class="input-group-text"><i class="fa fa-eye"></i></span>
                                                    </div>
                                                </div>
                                                {{-- <input type="password" class="form-control" id="newpassword" name="new_password"> --}}
                                                @error('new_password')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="confirmpassword">Confirm Password</label>
                                                <div class="input-group mb-3">
                                                    <input type="password" class="form-control" id="confirmpassword" name="confirm_password">
                                                    <div class="input-group-append viewpassword" data-id="confirmpassword">
                                                        <span class="input-group-text"><i class="fa fa-eye"></i></span>
                                                    </div>
                                                </div>
                                                {{-- <input type="password" class="form-control" id="confirmpassword" name="confirm_password"> --}}
                                                @error('confirm_password')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                            </div>

                                        </div>

                                        <div class="card-footer">
                                            <button type="submit" class="btn btn-primary">Update</button>
                                            <a href="{{ route('backend.studentList.studentList', $student->school_id) }}" class="btn btn-secondary">Cancel & Back</a>
                                        </div>
                                        </form>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <script>
        $(document).ready(function() {
            $("#profile_image").change(function () {
                var fileExtension = ['jpeg', 'jpg', 'png'];
                if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only JPG, JPEG or PNG files are allowed.");
                    $(this).val(''); 
                }
            });
        });

        $('.viewpassword').click(function(){
            var id = $(this).data('id');
            var action = $(this).data('action');
            if(action == 'text'){
                $(this).data('action','password')
                $('#'+id).attr('type','password');
            }else{
                $(this).data('action','text')
                $('#'+id).attr('type','text');
            }

        });

        function deleteStudentProfileAvatar(studentId) {
            if(confirm("Are you sure want to Delete Student Profile Picture?")) {
                $(`#attachId${studentId}`).addClass('disabled'); 
                $.ajax({
                    url: "{{ route('backend.delete-student-profile-avatar') }}",
                    
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        studentId: studentId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Student Profile Picture Deleted!");
                            window.location.href = "{{ route('backend.student-edit', ['school' => $student->school_id, 'student' => $student->id]) }}";
                        }
                    }
                });
            }
        }
    </script>
@endsection
