@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="pageTitle">
        <h2>Students</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">{{ __('admin.home') }}</a></li>
            <li class="breadcrumb-item active">Add Student</li>
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
                    <h4 class="card-title">Add Student</h4>
                    <a href="{{ route('school.student-list') }}" class="btn btn-sm btn-warning float-right"> <i
                            class="material-icons">west</i> Back</a>
                </div>
            </div>
            <form action="{{ route('school.student-store') }}" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary InnerForm">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Student Full Name<span class="required-asterisks">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="name">Username<span class="required-asterisks">*</span></label>
                                <small class="text-muted">(Allowed: letters, numbers, dot (.), underscore (_))</small>
                                <input type="text" class="form-control @error('username') is-invalid @enderror"
                                    id="username" name="username" value="{{ old('username') }}" required>
                                @error('username')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">Student Email ID</label>
                                <input type="text" class="form-control @error('email') is-invalid @enderror" id="email"
                                    name="email" placeholder="" value="{{ old('email') }}">

                                @error('email')
                                <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="date_of_birth">Date Of Birth</label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                    id="date_of_birth" name="date_of_birth" placeholder=""
                                    value="{{ old('date_of_birth') }}">

                                @error('date_of_birth')
                                <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="gender">Gender<span class="required-asterisks">*</span></label>
                                <select class="form-control @error('gender') is-invalid @enderror" id="gender"
                                    name="gender" required>
                                    <option value="Male" @if (old('gender')=='Male' ) selected @endif>Male
                                    </option>
                                    <option value="Female" @if (old('gender')=='Female' ) selected @endif>Female
                                    </option>
                                    <option value="Other" @if (old('gender')=='Other' ) selected @endif>Other
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
                                    <option value="{{$studGrade->id}}">{{$studGrade->name}}</option>
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
                                    <option value="{{$batch->id}}" @if (old('school_batch') == '{{$batch->id}}') selected @endif>{{$batch->batch_name}}
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
                                    id="address" name="address" placeholder="" value="{{ old('address') }}">

                                @error('address')
                                <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">

                    <div class="card InnerForm">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="parent_name">Parent Full Name</label>
                                <input type="text" class="form-control @error('parent_name') is-invalid @enderror"
                                    id="parent_name" name="parent_name" placeholder="" value="{{ old('parent_name') }}">

                                @error('parent_name')
                                <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="parent_email">Parent Email</label>
                                <input type="email" class="form-control @error('parent_email') is-invalid @enderror"
                                    id="parent_email" name="parent_email" placeholder="" value="{{ old('parent_email') }}">

                                @error('parent_email')
                                <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="mobile">Parent Contact Number</label>
                                <input type="text" class="form-control @error('mobile') is-invalid @enderror"
                                    id="mobile" name="mobile" placeholder="" value="{{ old('mobile') }}">

                                @error('mobile')
                                <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <div class="card InnerForm">
                        <div class="card-body">

                            <div class="form-group">
                                <label for="profile_image">Upload a image</label>
                                <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/jpg, image/png, image/jpeg">
                                @error('profile_image')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary w-100">Save</button>
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
    });
</script>
@endsection