@extends('backend.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        {{-- <h1 class="m-0">Add Student</h1> --}}
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Add Student</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h3>Add Student</h3>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ URL::previous() }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                                    </div>
                                </div>
                            </div>
                        </div>
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

                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <form action="{{ route('backend.student-new') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="name">Select School</label>
                                        <select required class="form-control @error('school_id') is-invalid @enderror" id="school_id" name="school_id">
                                            <option value="">---Select---</option>
                                            @foreach ($school as $row)
                                                <option value="{{ $row->id }}" @if (old('school_id') == $row->id) selected @endif>
                                                    {{ $row->school_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('school_id')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Student Full Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name') }}">
                                        @error('name')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="name">Student Level</label>
                                        @foreach ($grades as $grade)
                                            <div class="form-check">
                                                <input name="grade_id[]" class="form-check-input" type="checkbox" value="{{ $grade->id }}" id="grade_id" @if (old('grade_id') == $grade->id) checked @endif >
                                                <label class="form-check-label" for="flexCheckChecked">
                                                    {{ $grade->grade . ' (' . $grade->description . ')' }}
                                                </label>
                                            </div>
                                        @endforeach
                                        {{-- <select class="form-control @error('grade_id') is-invalid @enderror" id="grade_id" name="grade_id">
                                            @foreach ($grades as $grade)
                                                <option value="{{ $grade->id }}"
                                                    @if (old('grade_id') == $grade->id) selected @endif>
                                                    {{ $grade->grade . ' (' . $grade->description . ')' }}</option>
                                            @endforeach
                                        </select> --}}
                                        @error('grade_id')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Student Email</label>
                                        <input type="text" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" placeholder="" value="{{ old('email') }}">

                                        @error('email')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="mobile">Parent’s phone number</label>
                                        <input type="text" class="form-control @error('mobile') is-invalid @enderror"
                                            id="mobile" name="mobile" placeholder="" value="{{ old('mobile') }}">

                                        @error('mobile')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <select class="form-control" name="country">
                                            <option value="">-- Select Country --</option>
                                            @foreach ($country as $country)
                                                <option value="{{$country->id}}">{{$country->name}}</option>
                                            @endforeach
                                        </select>
                                        @error('country')
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

                                    <div class="form-group">
                                        <label for="date_of_birth">Date Of Birth</label>
                                        <input type="date"
                                            class="form-control @error('date_of_birth') is-invalid @enderror"
                                            id="date_of_birth" name="date_of_birth" placeholder=""
                                            value="{{ old('date_of_birth') }}">

                                        @error('date_of_birth')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="gender">Gender</label>
                                        <select class="form-control @error('gender') is-invalid @enderror" id="gender"
                                            name="gender">
                                            <option value="Male" @if (old('gender') == 'Male') selected @endif>Male
                                            </option>
                                            <option value="Female" @if (old('gender') == 'Female') selected @endif>Female
                                            </option>
                                            <option value="Other" @if (old('gender') == 'Other') selected @endif>Other
                                            </option>
                                        </select>
                                        @error('gender')
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
                                        value="{{ old('parent_name') }}">

                                    @error('parent_name')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="parent_email">Parent Email</label>
                                    <input type="email"
                                        class="form-control @error('parent_email') is-invalid @enderror"
                                        id="parent_email" name="parent_email" placeholder=""
                                        value="{{ old('parent_email') }}">

                                    @error('parent_email')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputFile">Upload a image</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="exampleInputFile"
                                                onchange="fileUploadName(event, this)" name="profile_image"
                                                data-img-preview="#profileshow" accept="image/*">
                                            <label class="custom-file-label"
                                                for="exampleInputFile">{{ __('admin/trainer.proof_file') }}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <span class="input-group-text">{{ __('admin/trainer.proof_upload') }}</span>
                                        </div>
                                    </div>
                                    <img src="#" alt="newupload" id="profileshow" class="mt-2 img-thumbnail"
                                        style="display: none;height: 160px; width: 160px; object-fit: contain;">
                                    @error('profile_image')
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
                                        <input type="password" class="form-control" id="password" name="password">
                                        <div class="input-group-append viewpassword" data-id="password">
                                            <span class="input-group-text"><i class="fa fa-eye"></i></span>
                                        </div>
                                    </div>
                                    {{-- <input type="password" class="form-control" id="password" name="password"> --}}
                                    @error('password')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="confirmpassword">Confirm Password</label>
                                    <div class="input-group mb-3">
                                        <input type="password" class="form-control" id="confirmpassword" name="password_confirmation">
                                        <div class="input-group-append viewpassword" data-id="confirmpassword">
                                            <span class="input-group-text"><i class="fa fa-eye"></i></span>
                                        </div>
                                    </div>
                                    {{-- <input type="password" class="form-control" id="confirmpassword" name="password_confirmation"> --}}
                                    @error('password_confirmation')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                            </form>
                        </div>

                    </div>

                </div>

            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <script>
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
    </script>
@endsection
