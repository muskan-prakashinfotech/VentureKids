@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        {{-- <h1 class="m-0">Add Stream</h1> --}}
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Add Stream</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="mb-5 container-fluid ">
                <form action="{{ route('backend.stream.store') }}" method="POST" enctype="multipart/form-data" class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Add Stream</h4>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ URL::previous() }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        @csrf
                        <div class="form-group">
                            <label>Select Level</label>
                            <select class="form-control" name="level" id="level" required>
                                <option value="">Select Level</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level['id'] }}" @if(old('level') == $level['id']) selected @endif>{{ $level['grade'] }}</option>
                                @endforeach
                            </select>    
                            @error('level')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror                        
                        </div>
                        
                        <div class="form-group">
                            <label for="title">Name</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                                name="title" placeholder="Title" value="{{ old('title') }}" required>
                            @error('title')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                id="streamDescription" name="description" placeholder="Description" required>{{ old('description') }}</textarea>
                            @error('description')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="videoUrl">Video URL</label>
                            <input type="text" class="form-control @error('videoUrl') is-invalid @enderror"
                                id="videoUrl" placeholder="YouTube Video URL" name="videoUrl"
                                value="{{ old('videoUrl') }}" onblur="validateYouTubeUrl()">
                            @error('videoUrl')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="image">Image</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="image" name="image" accept="image/jpg, image/png, image/jpeg" required>
                                    <label class="custom-file-label" for="image">Image</label>
                                </div>
                            </div>
                            @error('image')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="scormFile">Upload SCORM File</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="scormFile" name="scormFile" accept=".zip,.rar,.7zip,.xml">
                                    <label class="custom-file-label" for="scormFile">Upload SCORM File</label>
                                </div>
                            </div>
                            <div  style="display: none" class="progress mt-3 scorm-progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%; height: 100%">75%</div>
                            </div>
                            @error('scormFile')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <input type="hidden" id="uploadedFileName" name="uploadedFileName">

                        <div class="form-group">
                            <label for="no_of_questions">No. of SCORM Questions</label>
                            <input type="number" class="form-control @error('no_of_questions') is-invalid @enderror"
                                id="no_of_questions" placeholder="No. of SCORM Questions" name="no_of_questions"
                                value="{{ old('no_of_questions') }}" min="0">
                            @error('no_of_questions')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-stream-submit" id="btnSubmit">Submit</button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <script src="{{asset('asset/dist/js/resumable.min.js')}}"></script>

    <script>

        var uploadElement = "scormFile";
        var target = "{{ route('backend.stream.uploadSCORMFile') }}";
        var fileType = ['zip'];
        var chunkSize = 10*1024*1024;
        var submitBtnName = "btnSubmit";
        var moduleName = "scorm";
        var token = "{{ csrf_token() }}";

        $(document).ready(function() {
            $("#image").change(function () {
                var fileExtension = ['jpeg', 'jpg', 'png'];
                if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only JPG, JPEG or PNG files are allowed.");
                    $(this).val(''); 
                }
            });
            $("#scormFile").change(function () {
                var fileExtension = ['xml', 'zip'];
                if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only ZIP or XML files are allowed.");
                    $(this).val(''); 
                }
            });
        });

        function validateYouTubeUrl() {
            var url = $('#videoUrl').val();
            if (url != '') {
                var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
                var match = url.match(regExp);
                if (match && match[2].length == 11) {
                    // $('#videoUrl').attr('src', 'https://www.youtube.com/embed/' + match[2] + '?autoplay=0');
                } else {
                    alert("Please enter valid YouTube Url");
                    $('#videoUrl').val('');
                    return false;
                }
            }
        }
    </script>

    <script src="{{asset('asset/dist/js/chunk-functions.js')}}"></script>

@endsection
