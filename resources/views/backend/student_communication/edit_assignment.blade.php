@extends('backend.layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('asset/plugins/dropzone/min/dropzone.min.css') }}">

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        {{-- <h1 class="m-0">{{ __('admin/student_communication.edit_assignment') }}</h1> --}}
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ __('admin/student_communication.edit_assignment') }}
                            </li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        @if (Session::has('message'))
            <div class="alert alert-success">
                {{ Session::get('message') }}
            </div>
        @endif

          {{-- Error message --}}
        @if (Session::has('error'))
            <div class="alert alert-danger">
                {{ Session::get('error') }}
            </div>
        @endif

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <form id="multiAssignment" method="POST" action="{{ route('backend.update-assignment') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>{{ __('admin/student_communication.edit_assignment') }}</h4>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ URL::previous() }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Assignment Title</label>
                                <input type="text" name="assignment_title" id="" class="form-control" value="{{$assignments->title}}" required>
                                @error('assignment_title')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="schoolname">{{ __('admin/student_communication.select_school') }}</label>
                                        <select class="form-control" name="school_id" id="school_id" required>
                                            <option value="">---Select---</option>
                                            <option value="0" @if($assignments->school_id == 0) selected @endif>All</option>
                                            @foreach ($schools as $school)
                                                <option value="{{ $school->id }}" @if($assignments->school_id == $school->id) selected @endif>{{ $school->school_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('school_id')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="schoolname">Select Trainer</label>
                                        <select class="form-control" name="trainer_id" id="trainer_id">
                                            <option value="">---Select---</option>
                                            @foreach ($trainers as $trainer)
                                                <option value="{{ $trainer['get_trainer']['id'] }}" @if($assignments->trainer_id ==  $trainer['get_trainer']['id']) selected @endif>{{ $trainer['get_trainer']['trainer_name'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('trainer_id')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="schoolname">{{ __('admin/student_communication.select_level') }}</label>
                                <select class="form-control" name="grade_id" id="grade_id" required>
                                    <option value="">---Select---</option>
                                    @if(isset($filteredLevels['primary']))
                                        <optgroup label="Levels">
                                        @foreach ($filteredLevels['primary'] as $k => $level)
                                            <option value="{{ $level['id'] }}" @if($assignments->grade_id ==  $level['id']) selected @endif>{{ $level['grade'] }}</option>
                                        @endforeach
                                        </optgroup>
                                    @endif
                                    @if(isset($filteredLevels['add-ons']))
                                        <optgroup label="Content Add-Ons">
                                        @foreach ($filteredLevels['add-ons'] as $k => $level)
                                            <option value="{{ $level['id'] }}" @if($assignments->grade_id ==  $level['id']) selected @endif>{{ $level['grade'] }}</option>
                                        @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                                @error('grade_id')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Select Stream</label>
                                <select class="form-control" name="stream_id" id="stream_id">
                                    <option value="">---Select---</option>
                                    @foreach ($streamList as $stream)
                                        <option value="{{ $stream['id'] }}" @if($assignments->stream_id ==  $stream['id']) selected @endif>{{ $stream['title'] }}</option>
                                    @endforeach
                                </select>
                                @error('stream_id')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Select Session</label>
                                <select class="form-control" name="session_id" id="session_id">
                                    <option value="">---Select---</option>
                                    @foreach ($sessionList as $session)
                                        <option value="{{ $session['id'] }}" @if($assignments->session_id ==  $session['id']) selected @endif>{{ $session['title'] }}</option>
                                    @endforeach
                                </select>
                                @error('session_id')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="form-group d-flex align-items-center">
                                <label class="mb-0 mr-3">Assignment Category</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="category" id="category_facilitated"
                                    value="facilitated" {{ $assignments->category == 'facilitated' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="category_facilitated">Facilitated</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="category" id="category_selflearning"
                                    value="self_learning" {{ $assignments->category == 'self_learning' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="category_selflearning">Self-Learning</label>
                                </div>
                                @error('category')
                                <strong class="text-danger ml-2">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="form-group {{ $assignments->category == 'facilitated' ? '' : 'd-none' }}" id="facilitated_block">
                                <label>{{ __('admin/student_communication.create_assignment') }}</label>

                                <div id="image_upload" class="dropzone">
                                    <div class="dz-message needsclick">
                                        <div class="mb-3">
                                            <i class="display-4 text-muted mdi mdi-cloud-upload-outline"></i>
                                        </div>
                                        <h4>{{ __('admin/student_communication.drop_file') }}</h4>
                                    </div>
                                </div>

                                @if($assingmentFiles->count())
                                <div class="form-group mb-0">
                                @foreach($assingmentFiles as $key=>$attachment)

                                <div class="w-100 h6 mt-2 worksheetInfo d-flex">
                                    <span>{{ str_replace("image/assignment/","",$attachment->attachment) }}</span>
                                    <div class="btn-group">
                                        <a href="{{ asset($attachment->attachment) }}" class="btn btn-primary btn-sm ml-5" download><i class="fas fa-download"></i></a>
                                        <a onclick="deleteAssignmentFile({{$attachment->id}})" class="btn btn btn-danger btn-sm" id="attachId{{$attachment['id']}}"><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </div>
                                @endforeach
                                </div>
                                @endif

                            </div>

                            {{-- Self-Learning Block --}}
                            <div class="form-group {{ $assignments->category == 'self_learning' ? '' : 'd-none' }}" id="self_learning_block">
                                <label for="scorm_file">Upload SCORM File</label>
                                    <div class="input-group">
                                       <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="scorm_file" name="scorm_file"  accept=".zip,.rar,.7zip,.xml">
                                            <label class="custom-file-label" for="scorm_file">Upload SCORM File</label>
                                       </div>
                                   </div>
                                   <div  style="display: none" class="progress mt-3 scorm-progress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 75%; height: 100%">75%</div>
                                   </div>
                                    @error('scorm_file')
                                        <strong class="text-danger">{{ $message }}</strong>
                                    @enderror
                                @if($assignments->scorm_file)
                                    <div class="download mt-2 d-flex flex-wrap justify-content-between align-items-center scorm-view-download">  
                                       <span>{{ basename($assignments->scorm_file) }}</span> 
                                            <div class="action-btn">
                                                <a href="{{ url('/scorm-files/assignment/' . $assignments->scorm_file) }}" class="text-primary" download>
                                                    <i class="fa fa-download"></i> 
                                                </a>  
                                                <a href="javascript:void(0)" class="text-primary scorm-delete ml-2" data-id="{{$assignments->id}}">
                                                    <i class="fas fa-trash"></i> 
                                                </a>  
                                           </div>  
                                    </div>
                                @endif
                            </div>
                              <input type="hidden" id="uploadedFileName" name="uploadedFileName">
                              
                            <div class="form-group">
                                <label>{{ __('admin/student_communication.add_comments') }}</label>
                                <textarea class="form-control" name="comment">{{$assignments->comment}}</textarea>
                                @error('comment')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="upload_icon">Upload Icon</label>
                                <input type="file" class="form-control" id="upload_icon" name="upload_icon" accept="image/*" @if(empty($assignments->icon_name)) required @endif>
                                @error('upload_icon')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                                @if($assignments->icon_name)
                                <div class="download mt-2 d-flex flex-wrap justify-content-between"> 
                                    <span>{{$assignments->icon_name}}</span> 
                                    <div class="action-btn">
                                        <a href="{{ url('/image/assignment/' . $assignments->icon_name) }}" class="text-primary" download>
                                            <i class="fa fa-download"></i> 
                                        </a>  
                                        <a href="javascript:void(0)" class="text-primary icon-delete ml-2" data-id="{{$assignments->id}}">
                                            <i class="fas fa-trash"></i> 
                                        </a>  
                                    </div>  
                                </div>
                                @endif

                            </div>
                            <div class="form-group">
                                <label for="display_order">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order"  min="1" value="{{$assignments->display_order_id}}">
                            </div>
                            <div class="form-group d-flex align-items-center">
                                <label class="mb-0 mr-3">Status</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_active" id="active_status" value="1" {{ $assignments->is_active == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active_status">Active</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_active" id="inactive_status" value="0" {{ $assignments->is_active == 0 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="inactive_status">Inactive</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="is_active" id="hide_status" value="2"  {{ $assignments->is_active == 2 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="hide_status">Hide</label>
                                </div>
                                @error('is_active')
                                    <strong class="text-danger ml-2">{{ $message }}</strong>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer">
                            <input type="hidden" name="assignmentId" id="assignmentId" class="form-control" value="{{$assignments->id}}">
                            <button type="submit"
                                class="btn btn-primary" id="btnSubmit">{{ __('admin/student_communication.update') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <!-- dropzonejs -->
    <script src="{{ asset('asset/plugins/dropzone/min/dropzone.min.js') }}"></script>
    <script src="{{ asset('asset/dist/js/resumable.min.js') }}"></script>

    <script>
        var uploadElement = "scorm_file";
        var target = "{{ route('backend.assignment.uploadAssignmentSCORMFile') }}";
        var fileType = ['zip'];
        var chunkSize = 10*1024*1024;
        var submitBtnName = "btnSubmit";
        var moduleName = "scormAssignment";
        var token = "{{ csrf_token() }}";

        $(document).ready(function() {
            $("#upload_icon").change(function () {
                var fileExtension = ['jpeg', 'jpg', 'png'];
                if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only JPG, JPEG or PNG files are allowed.");
                    $(this).val(''); 
                }
            });

            $("#scorm_file").change(function () {
                var fileExtension = ['xml', 'zip'];
                if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only ZIP or XML files are allowed.");
                    $(this).val('');
                }
            });
        });

        $(".icon-delete").click(function() {
            if(confirm("Are you sure want to Delete this Icon?")) {
                var assignmentId = $(this).data('id');
                $('.action-btn').css({'opacity': '0.5', 'pointer-events': 'none'});
                $.ajax({
                    url: "{{ route('backend.delete-assignment-icon') }}",
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        assignmentId: assignmentId,                            
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Icon deleted.");
                            window.location.href = "{{ route('backend.edit-assignment', $assignments->id) }}";
                        }
                    }
                });
            }
        });

        $(".scorm-delete").click(function() {
            if(confirm("Are you sure want to Delete this SCORM File?")) {
                var assignmentId = $(this).data('id');
                $('.action-btn').css({'opacity': '0.5', 'pointer-events': 'none'});
                $.ajax({
                    url: "{{ route('backend.assignment.deleteAssignmentScormFile') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: { assignmentId: assignmentId },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("SCORM File deleted.");
                            window.location.href = "{{ route('backend.edit-assignment', $assignments->id) }}";
                        } else {
                            alert("Failed to delete SCORM file.");
                        }
                    }
                });
            }
        });
        
        $(document).on('change', '#school_id', function() {  
            var school_id = parseInt($(this).val());
            $('#trainer_id option:not(:first)').remove();
            if(school_id) {
                $('#trainer_id').attr("required", true);
                $.ajax({
                    type: "POST",
                    url: "{{ route('backend.getSchoolTrainers') }}",
                    data: {school_id : school_id },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $('#school_id').prop("disabled", true);
                        $('#trainer_id').prop("disabled", true);
                    },
                    success: function (data) {
                        data = $.parseJSON(data);
                        if(data.status == 'success' && !$.isEmptyObject(data.trainers)) {
                            var trainer_list = '';
                            $.each(data.trainers, function(index, value) {
                                trainer_list += `<option value="${value.get_trainer.id}">${value.get_trainer.trainer_name}</option>`;
                            });
                            $('#trainer_id').append(trainer_list);
                        } 
                        $('#school_id').prop("disabled", false);
                        $('#trainer_id').prop("disabled", false);
                    }
                });
            } else {
                $('#trainer_id').removeAttr('required');
                $('#trainer_id').prop("disabled", false);
            }
        }); 

        $(document).on('change', '#grade_id', function(){  
            $('#stream_id option:not(:first)').remove();
            $.ajax({
                type: "POST",
                url: "{{ route('backend.changestudentstream.changeStudentStream') }}",
                data: {agegroupId : $(this).find(":selected").val() },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('select[name="grade_id"]').prop("disabled", true);
                    $('select[name="stream_id"]').prop("disabled", true);
                    $('select[name="session_id"]').prop("disabled", true);
                },
                success: function (data) {
                    if(!$.isEmptyObject(data)) {
                        var streamList = '';
                        $.each(data, function (index, stream) {
                            streamList += "<option value='" + stream.id + "'>" + stream.title + "</option>";
                        });
                        $('#stream_id').append(streamList);
                    } 
                    $('#session_id option:not(:first)').remove();
                    $('select[name="grade_id"]').prop("disabled", false);
                    $('select[name="stream_id"]').prop("disabled", false);
                    $('select[name="session_id"]').prop("disabled", false);
                }
            });
        }); 

        $(document).on('change', '#stream_id', function(){  
            $('#session_id option:not(:first)').remove();
            $.ajax({
                type: "POST",
                url: "{{ route('backend.getStreamSession.getStreamSession') }}",
                data: {
                    agegroupId : $('#grade_id').find(":selected").val(),
                    sessionId : $(this).find(":selected").val() 
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('select[name="stream_id"]').prop("disabled", true);
                    $('select[name="session_id"]').prop("disabled", true);
                },
                success: function (data) {
                    if(!$.isEmptyObject(data)) {
                        var sessionList = '';
                        $.each(data, function (index, stream) {
                            sessionList += "<option value='" + stream.id + "'>" + stream.title + "</option>";
                        });
                        $('#session_id').append(sessionList);
                    } 
                    $('select[name="stream_id"]').prop("disabled", false);
                    $('select[name="session_id"]').prop("disabled", false);
                }
            });
        }); 

        // Dropzone.options.imageUpload= {
        //   maxFilesize  : 1,
        //   acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf"
        // }

        var myDropzone = new Dropzone("#image_upload", {
            url: "{{ route('backend.multi-assignment') }}",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            parallelUploads: 1,
            uploadMultiple: true,
            acceptedFiles: '.png,.jpg,.jpeg,.doc,.docx,.pdf',
            autoProcessQueue: true
        });

        myDropzone.on("success", (file, response) => {
            // console.log(file);
            // console.log(response);
            $('#multiAssignment').append('<input type="hidden" name="multi_assignment[]" value="' + response +
                '">');

            // for(var i; i< response.length; i++){
            //   $('#multiAssignment').append('<input type="hidden" name="multi_assignment[]" value="'+response[i]+'">');
            // }
        });

        function deleteAssignmentFile(attachmentId) {
            if(confirm("Are you sure want to Delete this Assignment File?")) {
                $(`#attachId${attachmentId}`).addClass('disabled'); 
                $.ajax({
                    url: "{{ route('backend.delete-assignment-file') }}",
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        attachmentId: attachmentId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Assignment File deleted.");
                            window.location.href = "{{ route('backend.edit-assignment', $assignments->id) }}";
                        }
                    }
                });
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            const facilitatedRadio = document.getElementById("category_facilitated");
            const selfLearningRadio = document.getElementById("category_selflearning");

            const facilitatedBlock = document.getElementById("facilitated_block");
            const selfLearningBlock = document.getElementById("self_learning_block");

            facilitatedRadio.addEventListener("click", function () {
                facilitatedBlock.classList.remove("d-none");
                selfLearningBlock.classList.add("d-none");
            });

            selfLearningRadio.addEventListener("click", function () {
                selfLearningBlock.classList.remove("d-none");
                facilitatedBlock.classList.add("d-none");
            });
       });

    </script>
    <script src="{{ asset('asset/dist/js/chunk-functions.js') }}"></script>
@endsection