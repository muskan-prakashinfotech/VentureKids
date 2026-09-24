@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="pageTitle">
        <h2>Student</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">{{ __('admin.home') }}</a></li>
            <li class="breadcrumb-item active">Edit Student</li>
        </ol>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section>
        <div class="container-fluid p-0">
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
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Student</h4>
                    <a href="{{ route('school.student-list') }}" class="btn btn-sm btn-warning float-right"> <i
                            class="material-icons">west</i> Back</a>
                </div>
            </div>
            <form action="{{ route('school.student-update') }}" method="POST" class="validatedForm" enctype="multipart/form-data">
            @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary InnerForm">


                            <input type="hidden" name="student_id" value="{{ $student['id'] }}">
                            <input type="hidden" name="user_id" value="{{ $student['std_user']['id'] }}">

                            <div class="card-body">
                                <div class="form-group">
                                    <label for="name">Student Full Name<span class="required-asterisks">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ $student['std_user']['name'] }}" required>
                                    @error('name')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="name">Username<span class="required-asterisks">*</span></label>
                                    <small class="text-muted">(Allowed: letters, numbers, dot (.), underscore (_))</small>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                                        id="username" name="username" value="{{ $student['std_user']['username'] }}" required>
                                    @error('username')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Student Email ID</label>
                                    <input type="text" class="form-control @error('email') is-invalid @enderror" id="email"
                                        name="email" placeholder="" value="{{ $student['std_user']['email'] }}">

                                    @error('email')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="date_of_birth">Date Of Birth</label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                        id="date_of_birth" name="date_of_birth" placeholder=""
                                        value="{{ !empty($student['std_user']['date_of_birth']) ? date('Y-m-d', strtotime($student['std_user']['date_of_birth'])) : '' }}">

                                    @error('date_of_birth')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="gender">Gender<span class="required-asterisks">*</span></label>
                                    <select class="form-control @error('gender') is-invalid @enderror" id="gender"
                                        name="gender" required>
                                        <option value="Male" @if ('Male'==$student['std_user']['gender']) selected @endif>
                                            Male
                                        </option>
                                        <option value="Female" @if ('Female'==$student['std_user']['gender']) selected
                                            @endif>Female
                                        </option>
                                        <option value="Other" @if ('Other'==$student['std_user']['gender']) selected @endif>
                                            Other
                                        </option>
                                    </select>
                                    @error('gender')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="gender">Student Grade<span class="required-asterisks">*</span></label>
                                    <select class="form-control @error('student_grade_id') is-invalid @enderror" id="student_grade_id"
                                        name="student_grade_id" required>
                                        <option value="">Select</option>
                                        @foreach($student_grade as $studGrade)
                                        <option value="{{$studGrade->id}}" @if ($student['student_grade_id'] == $studGrade->id) selected @endif>{{$studGrade->name}}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('student_grade_id')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="school_batch">School Batch<span class="required-asterisks">*</span></label>
                                    <select class="form-control @error('school_batch') is-invalid @enderror" id="school_batch" name="school_batch" required>
                                        <option value="">Select</option>
                                        @foreach($school_batch_list as $batch)
                                        <option value="{{$batch->id}}" @if ($student['school_batch_id'] == $batch->id) selected @endif>{{$batch->batch_name}}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('school_batch')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror"
                                        id="address" name="address" placeholder="" value="{{ $student['address'] }}">

                                    @error('address')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="card">
                            <div class="card-body InnerForm">
                                <div class="form-group">
                                    <label for="parent_name">Parent Full Name</label>
                                    <input type="text" class="form-control @error('parent_name') is-invalid @enderror"
                                        id="parent_name" name="parent_name" placeholder=""
                                        value="{{ $student['parent_name'] }}">

                                    @error('parent_name')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="parent_email">Parent Email</label>
                                    <input type="email" class="form-control @error('parent_email') is-invalid @enderror"
                                        id="parent_email" name="parent_email" placeholder=""
                                        value="{{ $student['parent_email'] }}">

                                    @error('parent_email')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="mobile">Parent Contact Number</label>
                                    <input type="text" class="form-control @error('mobile') is-invalid @enderror" id="phone"
                                        name="mobile" placeholder="" value="{{ $student['std_user']['mobile'] }}">
                                    {{-- <input type="text" class="form-control @error('mobile') is-invalid @enderror"
                                                id="phone" name="mobile" placeholder="" value="{{ old('mobile') }}"> --}}

                                    @error('mobile')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
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
                                    @error('confirm_password')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body InnerForm">

                                <div class="form-group">
                                    <label for="profile_image">Upload a image</label>
                                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/jpg, image/png, image/jpeg">
                                    @error('profile_image')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                    @if(!empty($student['image']) && $student['image'] != 'no_image' && $student['image_path'])
                                    <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3"> 
                                        <span>{{ $student['image'] }}</span> 
                                        <div class="action-btn">
                                            <a href="{{ url($student['image_path']) }}" class="btn btn-success btn-sm" download>
                                                <i class="fa fa-download"></i> 
                                            </a>  
                                            <a onclick="deleteStudentProfileAvatar({{$student['id']}})" class="btn btn-danger btn-sm" id="attachId{{$student['id']}}">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary w-100">Update</button>
                            </div>           
                        </div>

                    </div>
                        
                </div>
            </form>

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
        $('.reset-password').click(function(){ 
            if(confirm("Are you sure want to Reset Student Password?")) {
                $('.btn').attr('disabled', true); 
                $.ajax({
                    url: "{{ route('school.student-reset-password') }}",
                    
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        studentId: "{{$student['id']}}"
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Password has been reset successfully!");
                            $('.btn').attr('disabled', false); 
                        }
                        else {
                            alert("Error in sending mail. Please try again.");
                            window.location.href = "{{ route('school.student-edit', $student['id']) }}";
                        }
                    }
                });
            }
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
        $('.validatedForm').on('submit', function() {
            var pwd = $.trim($("#newpassword").val()).length;
            var cpwd = $.trim($("#confirmpassword").val()).length;
            if(pwd != cpwd) {
                alert("Your new password and confirm password didn't match.");
                return false;
            } else if (pwd > 0 && (pwd < 6 || pwd > 10)) {
                alert("Password length must be atleast 6 characters. Password length must not exceed 10 characters.");
                return false;       
            } 
            return true;
        });
    });

    function deleteStudentProfileAvatar(studentId) {
        if(confirm("Are you sure want to Delete Student Profile Picture?")) {
            $(`#attachId${studentId}`).addClass('disabled'); 
            $.ajax({
                url: "{{ route('school.delete-student-profile-avatar') }}",
                
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
                        window.location.href = "{{ route('school.student-edit', $student['id']) }}";
                    }
                }
            });
        }
    }

</script>
@endsection
