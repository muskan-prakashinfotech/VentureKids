@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        {{-- <h1 class="m-0">Edit Trainer Stream</h1> --}}
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Edit Trainer Stream</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="mb-5 container-fluid ">
                <form action="{{ route('backend.trainerstream.update', $streamData->id) }}" method="POST" enctype="multipart/form-data" class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Edit Trainer Stream</h4>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ URL::previous() }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card-body table-responsive">
                                @csrf
                                @method('patch')
                            
                                <div class="form-group">
                                    <label>Select Level</label>
                                    <select class="form-control" name="level" id="level" required>
                                        <option value="">Select Level</option>
                                        @foreach ($levels as $level)
                                            <option value="{{ $level['id'] }}" @if($level['id'] == $streamData->agegroup_id) Selected @endif>{{ $level['grade'] }}</option>
                                        @endforeach
                                    </select>    
                                    @error('agegroup_id')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror                        
                                </div>

                                <div class="form-group">
                                    <label for="title">Name</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                                        name="title" placeholder="Title" value="{{ old('title', $streamData->title) }}" required>
                                    @error('title')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="video">Video</label>
                                    <input type="file" class="form-control" id="video" placeholder=""
                                        name="video"  accept=".mp4">
                                    @error('video')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                    @if(!empty($streamData->video) && $streamData->video != 'no video')
                                        <div class="download mt-2 d-flex flex-wrap justify-content-between"> 
                                            {{ $streamData->video_name }}
                                            <div class="btn-group">
                                                <a href="{{ url('/video/stream/trainer/' . $streamData->video) }}" class="btn btn-primary btn-sm ml-5" download><i class="fas fa-download"></i></a>
                                                <a onclick="deleteTrainerStreamVideo({{$streamData->id}})" class="btn btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="video_url">Or Video URL</label>
                                    <input type="url" class="form-control" id="video_url" placeholder="YouTube video url"
                                        name="video_url" value="{{ old('video_url', $streamData->video_url) }}" onblur="validateYouTubeUrl()">
                                    @error('video_url')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="drive_url">G-drive URL</label>
                                    <input type="url" class="form-control" id="drive_url" placeholder=""
                                        name="drive_url" value="{{ old('drive_url', $streamData->drive_url) }}" onblur="validateURL()">
                                    @error('drive_url')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="images">Images</label>
                                    <input type="file" multiple accept="image/*" class="form-control" id="images" placeholder=""
                                        name="images[]" onchange="validateImages(this.id)">
                                    @if(!empty($streamData->trainerStreamImages))
                                        @foreach($streamData->trainerStreamImages as $attachment)
                                            <div class="download mt-2 d-flex flex-wrap justify-content-between"> 
                                                {{ pathinfo($attachment->attachment, PATHINFO_FILENAME) }}
                                                <div class="btn-group">
                                                    <a href="{{ url('/image/stream/trainer/' . $attachment->attachment) }}" class="btn btn-primary btn-sm ml-5" download><i class="fas fa-download"></i></a>
                                                    <a onclick="deleteTrainerStreamImage({{$attachment->id}})" class="btn btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="pdf">Pdf</label>
                                    <input type="file" accept=".pdf" class="form-control" id="pdf" placeholder=""
                                        name="pdf" onchange="validatePdf(this.id)">
                                    @if(!empty($streamData->pdf))
                                        <div class="download mt-2 d-flex flex-wrap justify-content-between"> 
                                            {{ $streamData->pdf_name }}
                                            <div class="btn-group">
                                                <a href="{{ url('/files/stream/trainer/' . $streamData->pdf) }}" class="btn btn-primary btn-sm ml-5" download><i class="fas fa-download"></i></a>
                                                <a onclick="deleteTrainerStreamPdf({{$streamData->id}})" class="btn btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="worksheet">Worksheet</label>
                                    <input type="file" class="form-control" id="worksheet" placeholder=""
                                        name="worksheet" onchange="validateWorksheet(this.id)">
                                    @if(!empty($streamData->worksheet))
                                        <div class="download mt-2 d-flex flex-wrap justify-content-between"> 
                                            {{ $streamData->worksheet_name }}
                                            <div class="btn-group">
                                                <a href="{{ url('/files/stream/trainer/' . $streamData->worksheet) }}" class="btn btn-primary btn-sm ml-5" download><i class="fas fa-download"></i></a>
                                                <a onclick="deleteTrainerStreamWorksheet({{$streamData->id}})" class="btn btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <input type="hidden" name="streamId" value="{{$streamData->id}}">

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card-body table-responsive">
                                <div class="form-group">
                                    <label for="learning_object">Learning Objective</label>
                                    <textarea id="contentdescription" class="form-control" name="learning_object">{{ $streamData->learning_object }}</textarea>
                                    @error('learning_object')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="outcome_session">Outcome of session</label>
                                    <textarea id="contentdescription_1" class="form-control" name="outcome_session">{{ $streamData->outcome_session }}</textarea>
                                    @error('outcome_session')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="question_prior_knowledge">Questions to assess prior knowledge</label>
                                    <textarea id="contentdescription_2" class="form-control" name="question_prior_knowledge">{{ $streamData->question_prior_knowledge }}</textarea>
                                    @error('question_prior_knowledge')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="introduce_topic">How to introduce the topic to the students</label>
                                    <textarea id="contentdescription_3" class="form-control" name="introduce_topic">{{ $streamData->introduce_topic }}</textarea>
                                    @error('introduce_topic')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="related_activity_one">Related Activity 1</label>
                                    <textarea id="contentdescription_4" class="form-control" name="related_activity_one">{{ $streamData->related_activity_one }}</textarea>
                                    @error('related_activity_one')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="related_activity_two">Related Activity 2</label>
                                    <textarea id="contentdescription_5" class="form-control" name="related_activity_two">{{ $streamData->related_activity_two }}</textarea>
                                    @error('related_activity_two')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="vocabulary">Vocabulary</label>
                                    <textarea id="contentdescription_6" class="form-control" name="vocabulary">{{ $streamData->vocabulary }}</textarea>
                                    @error('vocabulary')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="home_assignments">Tips for parents/home assignments</label>
                                    <textarea id="contentdescription_7" class="form-control" name="home_assignments">{{ $streamData->home_assignments }}</textarea>
                                    @error('home_assignments')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
    <script>
        $(document).ready(function() {
            $("#video").on("change", function() {
                var file = this.files[0];
                if(file) {
                    var mbSize = file.size/1024/1024;   
                    var fileIsMp4 = (file.type === "video/mp4");
                    if(!fileIsMp4)  // mbSize > 1
                    {
                        alert("Only Mp4 Video is allowed.");
                        $(this).val('');
                        return false;
                    }
                }
            });
        });

        function validateYouTubeUrl() {
            var url = $('#video_url').val();
            if (url != '') {
                var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
                var match = url.match(regExp);
                if (match && match[2].length == 11) {
                    // $('#video_url').attr('src', 'https://www.youtube.com/embed/' + match[2] + '?autoplay=0');
                } else {
                    alert("Please enter valid YouTube Url");
                    $('#video_url').val('');
                    return false;
                }
            }
        }

        function validateURL() {
            var url = $('#drive_url').val();
            if (url != '') {
                var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
                    '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
                    '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
                    '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
                    '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
                    '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
                if(!pattern.test(url)) {
                    alert("Please enter valid URL");
                    $('#drive_url').val('');
                    return false;
                }
            }
        }

        var MAX_FILE_SIZE = 30 * 1024 * 1024; // 30MB
        var MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5MB per image

        function validateImages(id) {
            var imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            var input = $("#"+id)[0];
            var files = input.files;
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var ext = file.name.split('.').pop().toLowerCase();
                if ($.inArray(ext, imageExtensions) == -1) {
                    alert("Only jpg, jpeg, png, webp images are allowed.");
                    $(input).val('');
                    return;
                }
                if (file.size > MAX_IMAGE_SIZE) {
                    alert("Each image should not exceed 5MB.");
                    $(input).val('');
                    return;
                }
            }
        }

        function validatePdf(id) {
            var fileExtension = ['pdf'];
            var file = $("#"+id)[0].files[0];
            if (!file) return;
            if ($.inArray($("#"+id).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                alert("Only PDF file is allowed.");
                $("#"+id).val('');
                return;
            }
            if (file.size > MAX_FILE_SIZE) {
                alert("File size should not exceed 30MB.");
                $("#"+id).val('');
            }
        }

        function validateWorksheet(id) {
            var fileExtension = ['xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'pdf'];
            var file = $("#"+id)[0].files[0];
            if (!file) return;
            if ($.inArray($("#"+id).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                alert("Only xls, xlsx, doc, docx, ppt, pptx, pdf files are allowed.");
                $("#"+id).val('');
                return;
            }
            if (file.size > MAX_FILE_SIZE) {
                alert("File size should not exceed 30MB.");
                $("#"+id).val('');
            }
        }

        function deleteTrainerStreamVideo(streamId) {
            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "{{ route('backend.trainerstream.deleteTrainerStreamVideo') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        streamId: streamId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Trainer Stream Video Deleted!");
                            window.location.reload();
                        }
                    }
                });
            }
        }

        function deleteTrainerStreamImage(attachmentId) {
            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "{{ route('backend.trainerstream.deleteTrainerStreamImage') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        attachmentId: attachmentId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Trainer Stream Image Deleted!");
                            window.location.reload();
                        }
                    }
                });
            }
        }

        function deleteTrainerStreamPdf(streamId) {
            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "{{ route('backend.trainerstream.deleteTrainerStreamPdf') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        streamId: streamId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Trainer Stream PDF Deleted!");
                            window.location.reload();
                        }
                    }
                });
            }
        }

        function deleteTrainerStreamWorksheet(streamId) {
            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "{{ route('backend.trainerstream.deleteTrainerStreamWorksheet') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        streamId: streamId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Trainer Stream Worksheet Deleted!");
                            window.location.reload();
                        }
                    }
                });
            }
        }
        
    </script>
@endsection
