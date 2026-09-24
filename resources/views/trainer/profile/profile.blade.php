@extends('backend.layouts.app')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="pageTitle">
        <h2>Profile Edit</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Profile Edit</li>
        </ol>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section>
        <div class="container-fluid p-0">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Details</h3>
                    <a href="{{ route('trainer.dashboard')  }}" class="btn btn-sm btn-warning">
                        <i class="material-icons">west</i>
                        Back
                    </a>
                </div>
            </div>
            @if(session()->has('update_success'))
            <div class="alert alert-success" style="text-align: center;">
                {{ session()->get('update_success') }}
            </div>
            @endif
            <form action="{{route('trainer.profile_update')}}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{$datas['trainer']->id}}">
                <input type="hidden" name="user_id" value="{{$datas['trainer']->user_id}}">

                <div class="card InnerForm ">
                    <!-- <div class="card-header">
                <h3 class="card-title">Profile</h3>
                <a href="{{ URL::previous() }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
            </div> -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Trainer Full Name</label>
                                    <input type="text" class="form-control" name="trainer_name"
                                        value="{{$datas['trainer']->trainer_name}}" required>
                                    @error('trainer_name')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Trainer Address</label>
                                    <input type="text" class="form-control" name="address"
                                        value="{{$datas['trainer']->address}}" required>
                                    @error('address')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Trainer City</label>
                                    <input type="text" class="form-control" name="city"
                                        value="{{$datas['trainer']->city}}" required>
                                    @error('city')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="eventend">{{ __('admin/event.event_country') }}</label>
                                    <select class="form-control" name="country" disabled>
                                        <option value="">-- Select Country --</option>
                                        @foreach ($countries as $country)
                                            <option @if ($datas['trainer']->country_id == $country->id) selected @endif value="{{$country->id}}">{{$country->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('country')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Joining Date</label>
                                    <input type="date" class="form-control" name="join_date"
                                        value="{{$datas['trainer']->join_date}}" required>
                                    @error('join_date')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth"
                                        value="{{$datas['trainer']->date_of_birth}}" required>
                                    @error('date_of_birth')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Official Email Id</label>
                                    <input type="text" class="form-control" name="official_email_id"
                                        value="{{$datas['trainer']->official_email_id}}" required>
                                    @error('official_email_id')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contact Number</label>
                                    <input type="text" class="form-control" id="phone" name="contact_no"
                                        value="{{$datas['trainer']->contact_no}}" required>
                                    @error('contact_no')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Upload Profile Image</label><br>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    @error('image')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                    @if($datas['trainer']->image)
                                        <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3"> 
                                            <span>{{$datas['trainer']->image}}</span> 
                                            <div class="action-btn">
                                                <a href="{{ url('/image/trainer/' .$datas['trainer']->image) }}" class="btn btn-success btn-sm" download>
                                                    <i class="fa fa-download"></i> 
                                                </a>  
                                                <a onclick="deleteTrainerProfileImage({{$datas['trainer']->id}})" class="btn btn-danger btn-sm" id="attachId{{$datas['trainer']->id}}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mode</label>
                                    <select class="form-control" name="mode" required>
                                        <option value="1" <?php if ($datas['trainer']->mode == 1) {
                                                echo "selected";
                                             } ?>>Online</option>
                                        <option value="2" <?php if ($datas['trainer']->mode == 2) {
                                                echo "selected";
                                             } ?>>Offline</option>
                                        <option value="3" <?php if ($datas['trainer']->mode == 3) {
                                                echo "selected";
                                             } ?>>Hybrid</option>
                                    </select>
                                    @error('mode')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select class="form-control" name="type" required>
                                        <option value="1" <?php if ($datas['trainer']->type == 1) {
                                                echo "selected";
                                             } ?>>Full Time</option>
                                        <option value="2" <?php if ($datas['trainer']->type == 2) {
                                                echo "selected";
                                             } ?>>Per Time</option>
                                        <option value="3" <?php if ($datas['trainer']->type == 3) {
                                                echo "selected";
                                             } ?>>Industry</option>
                                        <option value="3" <?php if ($datas['trainer']->type == 4) {
                                                echo "selected";
                                             } ?>>Expert</option>
                                        <option value="3" <?php if ($datas['trainer']->type == 5) {
                                                echo "selected";
                                             } ?>>Other</option>
                                    </select>
                                    @error('type')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No. of hours per week</label>
                                    <input type="text" class="form-control" name="no_of_hour_per_week"
                                        value="{{$datas['trainer']->no_of_hour_per_week}}" required>
                                    @error('no_of_hour_per_week')
                                    <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Upload Attachment</label><br>
                                    <input type="file" class="form-control" id="attachment" name="attachment" accept="image/*">
                                    @error('attachment')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                    @if($datas['trainer']->attachment)
                                        <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3"> 
                                            <span>{{$datas['trainer']->attachment}}</span> 
                                            <div class="action-btn">
                                                <a href="{{ url('/image/trainer/attachment/' .$datas['trainer']->attachment) }}" class="btn btn-success btn-sm" download>
                                                    <i class="fa fa-download"></i> 
                                                </a>  
                                                <a onclick="deleteTrainerAttachment({{$datas['trainer']->id}})" class="btn btn-danger btn-sm" id="attachmentId{{$datas['trainer']->id}}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Upload CV</label><br>
                                    <input type="file" class="form-control" id="cv" name="cv" accept=".doc,.docx,.pdf" />
                                    @error('cv')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                    @if($datas['trainer']->cv)
                                        <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3"> 
                                            <span>{{$datas['trainer']->cv}}</span> 
                                            <div class="action-btn">
                                                <a href="{{ url('/image/trainer/cv/' .$datas['trainer']->cv) }}" class="btn btn-success btn-sm" download>
                                                    <i class="fa fa-download"></i> 
                                                </a>  
                                                <a onclick="deleteTrainerCV({{$datas['trainer']->id}})" class="btn btn-danger btn-sm" id="cvId{{$datas['trainer']->id}}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mt-3 text-left">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <br>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<script>
    $(document).ready(function() {
        $('#achievements').summernote({
            placeholder: 'Past Achievements',
            tabsize: 2,
            height: 100,
        });

        $('#expertise').summernote({
            placeholder: 'Expertise',
            tabsize: 2,
            height: 180,
        });

        $("#image, #attachment").change(function () {
            var fileExtension = ['jpeg', 'jpg', 'png'];
            if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                alert("Only JPG, JPEG or PNG files are allowed.");
                $(this).val(''); 
            }
        });

        $("#cv").change(function () {
            var fileExtension = ['doc', 'docx', 'pdf'];
            if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                alert("Only DOC, DOCX or PDF files are allowed.");
                $(this).val(''); 
            }
        });
    });
    function deleteTrainerProfileImage(trainerId) {
        if(confirm("Are you sure want to Delete this File?")) {
            $(`#attachId${trainerId}`).addClass('disabled'); 
            $.ajax({
                url: "{{ route('trainer.deleteTrainerProfileImage') }}",
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    trainerId: trainerId,
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
    function deleteTrainerAttachment(trainerId) {
        if(confirm("Are you sure want to Delete this File?")) {
            $(`#attachmentId${trainerId}`).addClass('disabled'); 
            $.ajax({
                url: "{{ route('trainer.deleteTrainerAttachment') }}",
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    trainerId: trainerId,
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
    function deleteTrainerCV(trainerId) {
        if(confirm("Are you sure want to Delete this File?")) {
            $(`#cvId${trainerId}`).addClass('disabled'); 
            $.ajax({
                url: "{{ route('trainer.deleteTrainerCV') }}",
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    trainerId: trainerId,
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