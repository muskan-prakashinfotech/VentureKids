@extends('backend.layouts.app')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Profile Edit</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Profile</li>
        </ol>
    </div>

    <!-- Main content -->
    <section>
        <div class="container-fluid p-0">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Details</h3>
                    <a href="{{ route('school.dashboard') }}" class="btn btn-sm btn-warning">
                        <i class="material-icons">west</i>
                        Back
                    </a>
                </div>
            </div>
            @if (session()->has('email_faild'))
            <div class="alert alert-danger" style="text-align: center;">
                {{ session()->get('email_faild') }}
            </div>
            @endif

            @if (Session::has('message'))
            <div class="alert alert-success">
                {{ Session::get('message') }}
            </div>
            @endif

            @if (session()->has('csv_email_faild'))
            <div class="alert alert-danger" style="text-align: center;">
                {{ session()->get('csv_email_faild') }}
            </div>
            @endif

            @if (session()->has('csv_invalid_email'))
            <div class="alert alert-danger" style="text-align: center;">
                {{ session()->get('csv_invalid_email') }}
            </div>
            @endif

            @if (Session::has('message1'))
            <div class="alert alert-danger">
                {{ Session::get('message1') }}
            </div>
            @endif

            <form action="{{ route('school.update-school') }}" method="POST" enctype="multipart/form-data" id="schoolprofile">
                @csrf
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card card-primary">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="InnerForm">

                                            <?php
                                    $number_of_student = $school['number_of_student'] / 120;
                                    $number_student = round($number_of_student);
                                    ?>
                                            <input type="hidden" value="{{ $school['user_id'] }}" name="user_id">
                                            <input type="hidden" value="{{ $school['id'] }}" name="school_id"
                                                id="school_id">
                                            <div>
                                                <div class="form-group">
                                                    <label for="schoolname">{{ __('admin.school_name') }}</label>
                                                    <input type="text"
                                                        class="form-control @error('school_name') is-invalid @enderror"
                                                        id="schoolname" name="school_name"
                                                        value="{{ $school['school_name'] }}" required disabled>
                                                    @error('school_name')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="yearestablished">School Contact Person Full Name</label>
                                                    <input type="text"
                                                        class="form-control @error('principle_name') is-invalid @enderror"
                                                        id="principle_name" placeholder="School Contact Person Full Name" name="principle_name" value="{{ $school['principle_name'] }}" required disabled>
                                                    @error('principle_name')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="yearestablished">School Official Email ID</label>
                                                    <input type="email"
                                                        class="form-control @error('official_email_id') is-invalid @enderror"
                                                        id="yearestablished" placeholder="School Official Email ID" name="official_email_id" value="{{ $school['official_email_id'] }}" required disabled>
                                                    @error('official_email_id')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label
                                                        for="inchargecontact">Offical Contact Number</label>
                                                    <input type="tel"
                                                        class="form-control @error('contact_number') is-invalid @enderror"
                                                        id="phone" placeholder="Offical Contact Number" name="contact_number" value="{{ $school['contact_number'] }}"
                                                        default="bangladesh" required disabled>
                                                    @error('contact_number')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="schoolname">{{ __('admin.number_of_student') }}</label>
                                                    <div class="mb-3 input-group">
                                                        <input type="number" class="form-control" placeholder=""
                                                            name="number_of_student"
                                                            value="{{ $school['number_of_student'] }}" required disabled>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="schoolname">{{ __('admin.course_start_date') }}</label>
                                                    <input type="date" class="form-control" id="startdate"
                                                        name="course_start_date" placeholder=""
                                                        value="{{ $school['course_start_date'] }}" required disabled>
                                                </div>
                                                <div class="form-group">
                                                    <label for="schoolname">Course Expiration Date</label>
                                                    <input type="date" class="form-control" id="course_end_date"
                                                        name="course_end_date" placeholder=""
                                                        value="{{ $school['course_end_date'] }}" required disabled>
                                                </div>
                                                <div class="form-group">
                                                    <label for="schoolname">{{ __('admin.full_address') }}</label>
                                                    <textarea type="text"
                                                        class="form-control @error('address') is-invalid @enderror"
                                                        id="address" placeholder=""
                                                        name="address" disabled>{{ $school['school_address'] }}</textarea>
                                                    @error('address')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="schoolname">City</label>
                                                    <input type="text"
                                                        class="form-control @error('city') is-invalid @enderror"
                                                        id="schoolname" name="city" placeholder="City"
                                                        value="{{ $school['city'] }}" required disabled>
                                                    @error('city')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="eventend">{{ __('admin/event.event_country') }}</label>
                                                    <select class="form-control" name="country" required disabled>
                                                        <option value="">-- Select Country --</option>
                                                        @foreach ($countries as $country)
                                                            <option @if ($school['country_id'] == $country->id) selected @endif value="{{$country->id}}">{{$country->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('country')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="yearestablished">{{ __('admin.year_established') }}</label>
                                                    <input type="number"
                                                        class="form-control @error('year_establish') is-invalid @enderror"
                                                        id="yearestablished" placeholder="" name="year_establish"
                                                        value="{{ $school['year_establish'] }}" disabled>
                                                    @error('year_establish')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-md-6">
                                        <div class="InnerForm">
                                            {{-- <div class="card-header"></div> --}}
                                            <div>
                                            <div class="form-group">
                                                    <label
                                                        for="inchargename">{{ __('admin.activity_in_charge_first_name') }}</label>
                                                    <input type="text"
                                                        class="form-control @error('incharge_name') is-invalid @enderror"
                                                        id="inchargename" name="incharge_name" placeholder=""
                                                        value="{{ $school['incharge_name'] }}" required>
                                                    @error('incharge_name')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>
                                                <div class="form-group">
                                                    <label for="inchargeemail">{{ __('admin.in_charge_email') }}</label>
                                                    <input type="text"
                                                        class="form-control @error('incharge_email') is-invalid @enderror"
                                                        id="inchargeemail" name="incharge_email" placeholder=""
                                                        value="{{ $school['incharge_email'] }}" required>
                                                    @error('incharge_email')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                </div>
                                                {{-- <div class="form-group">
                                                    <label for="partnername">VentureKids Coach</label>
                                                    <input type="text" class="form-control" id="partnername"
                                                        name="partner_name" placeholder=""
                                                        value="{{ $school['venturekids_representative'] }}">
                                                </div> --}}
                                                <div class="form-group">
                                                    <label for="school_logo" class="d-inline-flex">{{ __('admin.school_logo') }}
                                                        <span class="ml-1 form-text text-muted font-weight-light" style="font-size: 10px; margin-top: 7px;">Ration 1x1.</span>
                                                    </label>
                                                    <input type="file" class="form-control" id="school_logo" name="school_logo" accept="image/jpg, image/png, image/jpeg">
                                                    @error('school_logo')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                    @if($school['school_logo'] && $school['school_logo_path'])
                                                    <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3"> 
                                                        <span>{{$school['school_logo']}}</span> 
                                                        <div class="action-btn">
                                                            <a href="{{ url($school['school_logo_path']) }}" class="btn btn-success btn-sm" download>
                                                                <i class="fa fa-download"></i> 
                                                            </a>  
                                                            <a onclick="deleteSchoolLogo({{$school['id']}})" class="btn btn-danger btn-sm" id="attachId{{$school['id']}}">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>

                                                <div class="form-group">
                                                    <label for="exampleInputFile" class="d-inline-flex">Upload School Cover Picture
                                                        <span class="ml-1 form-text text-muted font-weight-light" style="font-size: 10px; margin-top: 7px;">Ration 2x1.</span>
                                                    </label>
                                                    <input type="file" class="form-control" id="school_cover_image" name="school_cover_image" accept="image/jpg, image/png, image/jpeg">
                                                    @error('school_cover_image')
                                                        <strong class="text-danger">{{ $message }}</strong>
                                                    @enderror
                                                    @if($school['school_cover_image'] && $school['school_cover_image_path'])
                                                    <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3"> 
                                                        <span>{{$school['school_cover_image']}}</span> 
                                                        <div class="action-btn">
                                                            <a href="{{ url($school['school_cover_image_path']) }}" class="btn btn-success btn-sm" download>
                                                                <i class="fa fa-download"></i> 
                                                            </a>  
                                                            <a onclick="deleteSchoolCoverLogo({{$school['id']}})" class="btn btn-danger btn-sm" id="attachId{{$school['id']}}">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="UpdateFoem">
                                                <button type="submit" class="btn btn-primary submitbtn">Update</button>
                                            </div>
                                        </div>

                                    </div>

                                </div>
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
        $("#school_logo, #school_cover_image").change(function () {
            var fileExtension = ['jpeg', 'jpg', 'png'];
            if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                alert("Only JPG, JPEG or PNG files are allowed.");
                $(this).val(''); 
            }
        });
    });
    function deleteSchoolLogo(schoolId) {
        if(confirm("Are you sure want to Delete this File?")) {
            $(`#attachId${schoolId}`).addClass('disabled'); 
            $.ajax({
                url: "{{ route('school.deleteSchoolLogo') }}",
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    schoolId: schoolId,
                },
                method: "POST",
                success: function(res) {
                    if(res) {
                        alert("File deleted.");
                        window.location.reload();
                    }
                }
            });
        }
    }
    function deleteSchoolCoverLogo(schoolId) {
        if(confirm("Are you sure want to Delete this File?")) {
            $(`#attachId${schoolId}`).addClass('disabled'); 
            $.ajax({
                url: "{{ route('school.deleteSchoolCoverLogo') }}",
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    schoolId: schoolId,
                },
                method: "POST",
                success: function(res) {
                    if(res) {
                        alert("File deleted.");
                        window.location.reload();
                    }
                }
            });
        }
    }

</script>
@endsection