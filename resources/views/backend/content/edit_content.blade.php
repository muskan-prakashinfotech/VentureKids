@extends('backend.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        {{-- <h1 class="m-0">{{ __('admin/content.edit_content') }}</h1> --}}
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('admin/content.edit_content') }}</li>
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
                                        <h3>{{ __('admin/content.edit_content') }}</h3>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ URL::previous() }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <form action="{{ route('backend.updatecontent.updateContent') }}" method="POST"
                    enctype="multipart/form-data" onsubmit="return validateForm()">
                    @csrf
                    <input type="hidden" name="id" value="{{ $content['id'] }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-primary">
                                {{-- <div class="card-header"></div> --}}
                                <div class="card-body">
                                    @livewire('admin.content.trainer-age-group-streams-selection', ['content' => $content])
                                    <div class="form-group">
                                        <label for="inchargename">{{ __('admin/content.content_title') }}</label>
                                        <input type="text" class="form-control" id="inchargename" placeholder=""
                                            name="title" value="{{ $content['title'] }}" required>
                                        @error('title')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="eventimages">{{ __('admin/content.event_video') }}</label>
                                        <input type="hidden" value="{{ $content['video'] }}" name="pre_video" id="pre_video">
                                        <input type="file" class="form-control" id="eventimage" placeholder=""
                                            name="video" accept=".mp4">
                                        @if(!empty($content['video']) && $content['video'] != 'no video')
                                            <div class="download mt-2 d-flex flex-wrap justify-content-between"> 
                                                {{ $content['video_name'] }}
                                                <div class="btn-group">
                                                    <a href="{{ url('/video/content/trainer/' . $content['video']) }}" class="btn btn-primary btn-sm ml-5" download><i class="fas fa-download"></i></a>
                                                    <a onclick="deleteTrainerContentVideo({{$content['id']}})" class="btn btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="">Or {{ __('admin/content.event_video_url') }}</label>
                                        <input type="text" class="form-control" id="video_url" placeholder="YouTube video url"
                                            name="video_url" value="{{ old('video_url', $content['video_url']) }}" onblur="validateYouTubeUrl()">
                                        @error('video_url')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="">{{ __('admin/content.drive_url') }}</label>
                                        <input type="url" class="form-control" id="drive_url" placeholder=""
                                            name="drive_url" value="{{ old('drive_url', $content['drive_url']) }}" onblur="validateURL()">
                                        @error('drive_url')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="Worksheets">{{ __('admin/content.session_images') }}</label>
                                        <input type="file" multiple accept="image/*" class="form-control" id="session_images" placeholder=""
                                            name="session_images[]">
                                        @foreach ($contentImages as $contImg)
                                            <div class="download mt-2 d-flex flex-wrap justify-content-between"> 
                                                {{ pathinfo($contImg->attachment, PATHINFO_FILENAME) }}
                                                <div class="btn-group">
                                                    <a href="{{ url('/files/content/trainer/' . $contImg->attachment) }}" class="btn btn-primary btn-sm ml-5" download><i class="fas fa-download"></i></a>
                                                    <a onclick="deleteTrainerSessionImage({{$contImg->id}})" class="btn btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="form-group">
                                        <label for="Worksheets">{{ __('admin/content.pdf') }}</label>
                                        <input type="file" accept=".pdf" class="form-control" id="session_pdf" placeholder=""
                                            name="session_pdf" onchange="validatePdf(this.id)">
                                        @if(!empty($content['pdf']))
                                            <div class="download mt-2 d-flex flex-wrap justify-content-between"> 
                                                {{ $content['pdf_name'] }}
                                                <div class="btn-group">
                                                    <a href="{{ url('/files/content/trainer/' . $content['pdf']) }}" class="btn btn-primary btn-sm ml-5" download><i class="fas fa-download"></i></a>
                                                    <a onclick="deleteTrainerSessionPdf({{$content['id']}})" class="btn btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="Worksheets">{{ __('admin/content.worksheets') }}</label>
                                        <input type="hidden" value="{{ $content['worksheet'] }}" name="pre_worksheet">
                                        <input type="file" class="form-control" id="Worksheets" placeholder=""
                                            name="worksheets" onchange="validateWorksheet(this.id)">
                                        @if(!empty($content['worksheet']))
                                            <div class="download mt-2 d-flex flex-wrap justify-content-between"> 
                                                {{ $content['worksheet_name'] }}
                                                <div class="btn-group">
                                                    <a href="{{ url('/files/content/trainer/' . $content['worksheet']) }}" class="btn btn-primary btn-sm ml-5" download><i class="fas fa-download"></i></a>
                                                    <a onclick="deleteTrainerWorksheet({{$content['id']}})" class="btn btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="session_presentation">{{ __('admin/content.session_presentation') }}</label>
                                        <input type="file" class="form-control" id="session_presentation" placeholder=""
                                            name="session_presentation" onchange="validateSessionPresentation(this.id)" accept=".ppt, .pptx">
                                        @if(!empty($content['session_presentation']))
                                            <div class="download mt-2 d-flex flex-wrap justify-content-between"> 
                                                {{ $content['session_presentation_name'] }}
                                                <div class="btn-group">
                                                    <a href="{{ url('/files/content/trainer/' . $content['session_presentation']) }}" class="btn btn-primary btn-sm ml-5" download><i class="fas fa-download"></i></a>
                                                    <a onclick="deleteTrainerSessionPresentation({{$content['id']}})" class="btn btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                                </div>
                                            </div>
                                        @endif
                                        <div  style="display: none" class="progress mt-3 scorm-progress">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%; height: 100%">75%</div>
                                        </div>
                                        <input type="hidden" id="uploadedFileName" name="uploadedFileName">
                                        <input type="hidden" id="displayFileName" name="displayFileName">
                                    </div>
                                    <div class="form-group">
                                        <label for="Worksheets">Save as Draft OR Publish</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="contentPublish" id="isDraft" value="0" @if($content['is_publish'] == 0) checked @endif>
                                            <label class="form-check-label" for="isDraft">Draft</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="contentPublish" id="isPublish" value="1" @if($content['is_publish'] == 1) checked @endif>
                                            <label class="form-check-label" for="isPublish">Publish</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-warning">
                                {{-- <div class="card-header"></div> --}}
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="eventstart">Learning Objective</label>
                                        <textarea id="contentdescription" class="form-control" name="learning_object">{{ $content['learning_object'] }}</textarea>
                                    </div>
                                    @error('learning_object')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="eventstart">Outcome of session</label>
                                        <textarea id="contentdescription_1" class="form-control" name="outcome_session">{{ $content['outcome_session'] }}</textarea>
                                    </div>
                                    @error('outcome_session')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="eventstart">Questions to assess prior knowledge</label>
                                        <textarea id="contentdescription_2" class="form-control" name="question_access_knowledge">{{ $content['question_access_knowledge'] }}</textarea>
                                    </div>
                                    @error('question_access_knowledge')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="eventstart">How to introduce the topic to the students</label>
                                        <textarea id="contentdescription_3" class="form-control" name="introduce_topic_student">{{ $content['introduce_topic_student'] }}</textarea>
                                    </div>
                                    @error('introduce_topic_student')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="eventstart">Related Activity 1</label>
                                        <textarea id="contentdescription_4" class="form-control" name="related_activity_one">{{ $content['related_activity_one'] }}</textarea>
                                    </div>
                                    @error('related_activity_one')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="eventstart">Related Activity 2</label>
                                        <textarea id="contentdescription_5" class="form-control" name="related_activity_two">{{ $content['related_activity_two'] }}</textarea>
                                    </div>
                                    @error('related_activity_two')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="eventstart">Vocabulary</label>
                                        <textarea id="contentdescription_6" class="form-control" name="vocabulary">{{ $content['vocabulary'] }}</textarea>
                                    </div>
                                    @error('vocabulary')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="eventstart">Tips for parents/home assignments</label>
                                        <textarea id="contentdescription_7" class="form-control" name="tips_of_parents">{{ $content['tips_of_parents'] }}</textarea>
                                    </div>
                                    @error('tips_of_parents')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                </div>

                                <div class="card-footer">
                                    <button type="submit"
                                        class="mt-4 btn btn-primary" id="btnSubmit">{{ __('admin/content.submit') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>





            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>

    <script src="{{asset('asset/dist/js/resumable.min.js')}}"></script>

    <script>

        var uploadElement = "session_presentation";
        var target = "{{ route('backend.trainerContent.uploadFile') }}";
        var fileType = ['ppt', 'pptx'];
        var chunkSize = 10*1024*1024;
        var submitBtnName = "btnSubmit";
        var moduleName = "trainer-content";
        var token = "{{ csrf_token() }}";

        $(document).ready(function() {
            //  $('#eventdescription').summernote({
            //   placeholder: 'Event description',
            //   tabsize: 2,
            //   height: 385,
            // })

            $("#eventimage").on("change", function() {
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

            $('select[name="agegroup_id"]').on('change', function() {
                var agegroupId = $(this).val();
                $.ajax({
                    type: "POST",
                    url: "{{ route('backend.changetrainerstream.changeTrainerStream') }}",
                    data: {agegroupId : agegroupId },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $('select[name="stream_id"]').prop("disabled", true);
                    },
                    success: function (data) {
                        streamList = "<option value='' selected>---Select---</option>";
                        $.each(data, function (index, stream) {
                            streamList += "<option value='" + stream.id + "'>" + stream.title + "</option>";
                        });
                        $('select[name="stream_id"]').prop("disabled", false);
                        $('select[name="stream_id"]').html(streamList);
                    }
                });
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

        function validatePdf(id) {
            var fileExtension = ['pdf'];
            if ($("#"+id).val() && $.inArray($("#"+id).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                alert("Only PDF file is allowed.");
                $("#"+id).val(''); 
            }
        }

        function validateWorksheet(id) {
            var fileExtension = ['xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'pdf'];
            if ($("#"+id).val() && $.inArray($("#"+id).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                alert("Only xls, xlsx, doc, docx, ppt, pptx, pdf files are allowed.");
                $("#"+id).val(''); 
            }
        }

        function validateSessionPresentation(id) {
            var fileExtension = ['ppt', 'pptx'];
            if ($("#"+id).val() && $.inArray($("#"+id).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                alert("Only ppt or pptx file is allowed.");
                $("#"+id).val(''); 
            }
        }

        function validateForm() {
            var flagValid = true;
            
            // if(!$.trim($('#pre_video').val()).length && !$.trim($('#video_url').val()).length) {
            //     alert("Session Video Or Session Video URL field is required");
            //     $('#video_url').focus();
            //     flagValid = false;
            //     return false;
            // }

            // $("input[name='session_images[]'").each(function(index, value){
            //     if(!$.trim($(this).val()).length) { 
            //         $('#session_images').val('');
            //         alert("Only JPG, JPEG or PNG files are allowed.");
            //         flagValid = false;
            //         return false;
            //     }
            // });
            
            if(flagValid) {
                return true;
            }
        }

        function deleteTrainerContentVideo(contentId) {
            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "{{ route('backend.content.deleteTrainerContentVideo') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        contentId: contentId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Content Video Deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        }

        function deleteTrainerSessionImage(contentId) {
            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "{{ route('backend.content.deleteTrainerSessionImage') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        contentId: contentId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Session Image Deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        }

        function deleteTrainerSessionPdf(contentId) {
            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "{{ route('backend.content.deleteTrainerSessionPdf') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        contentId: contentId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Session PDF Deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        }

        function deleteTrainerWorksheet(contentId) {
            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "{{ route('backend.content.deleteTrainerWorksheet') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        contentId: contentId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Worksheet Deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        } 
        
        function deleteTrainerSessionPresentation(contentId) {
            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "{{ route('backend.content.deleteTrainerSessionPresentation') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        contentId: contentId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Session Presentation Deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        }     

    </script>

    <script src="{{asset('asset/dist/js/chunk-functions.js')}}"></script>
    
@endsection
