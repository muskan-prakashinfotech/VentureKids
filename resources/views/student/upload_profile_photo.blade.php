@extends('backend.layouts.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Upload Profile Photo</h2>
        <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-warning float-right"> <i class="material-icons">west</i> Back</a>
    </div>

    <!-- Main content -->
    <section>
        @if(Session::has('message'))
            <div class="alert alert-success">
                {{ Session::get('message') }}
            </div>
        @endif
        <div class="card">
            <div class="card-body">
                <form action="{{ route('student.upload-profile-photo') }}" name="uploadFrm" id="uploadFrm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="profile_photo">Upload Profile Photo</label>
                    <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/jpg" required>
                    @error('profile_photo')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    @if(!empty($student->image))
                        <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center mb-3"> 
                            <span>{{basename($student->image)}}</span> 
                            <div class="action-btn">
                                <a href="{{ url('tenants/'.$student->image) }}" class="btn btn-success btn-sm" download>
                                    <i class="fa fa-download"></i> 
                                </a>  
                                <a onclick="deleteProfilePhoto()" class="btn btn-danger btn-sm" id="attachId">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
                <button type="submit" class="btn btn-sm btn-primary float-right">Upload</button>
                </form>
            </div>
        </div>
        
    </section>
</div>
<script>
    document.getElementById('uploadFrm').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('profile_photo');
        const file = fileInput.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

            if (!allowedTypes.includes(file.type)) {
                alert('Only JPG, PNG and JPEG images are allowed.');
                e.preventDefault();
                return;
            }

            if (file.size > maxSize) {
                alert('Max file size is 2MB.');
                e.preventDefault();
                return;
            }
        }
    });
    function deleteProfilePhoto() {
            if(confirm("Delete profile photo?")) {
                $(`#attachId`).addClass('disabled'); 
                $.ajax({
                    url: "{{ route('student.delete-profile-photo') }}",
                    
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Profile photo deleted!");
                            window.location.href = "{{ route('student.profile-photo') }}";
                        }
                    }
                });
            }
        }
</script>
@endsection