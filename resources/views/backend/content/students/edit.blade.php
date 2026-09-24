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
                <form class="form-wizard" action="{{ route('backend.addcontent.editContentStudents', $studentscontents->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('patch')
                    <div class="row">
                        <div class="col-md-12 mb-4 wizard-fieldset @if(!session()->has('success')) show @endif">
                            <div class="card card-primary">
                                <div class="card-header bg-color-skyblue">
                                    <div class="w-100 d-flex align-items-center justify-content-between">
                                        <h5 class="m-0 text-dark">{{ __('admin/content.edit_content') }}</h5>
                                        <a href="{{ URL::previous() }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @livewire('admin.content.age-group-streams-selection', ['content' => ['agegroup_id' => old('agegroup_id', $studentscontents->ageGroup_id), 'stream_id' => old('stream_id', $studentscontents->stream_id)]])
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="inchargename">{{ __('admin/content.content_title') }}</label>
                                                <input type="text" class="form-control" id="inchargename" placeholder=""
                                                    name="title" value="{{ old('title', $studentscontents->title) }}">
                                                @error('title')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="eventimages">{{ __('admin/content.event_video') }}</label>
                                                <input type="file" class="form-control" id="eventimage" placeholder=""
                                                    name="video">
                                                <input type="hidden" value="{{ $studentscontents->video }}" name="pre_video">
                                                @error('video')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                                @if(!empty($studentscontents->video) && $studentscontents->video != 'no video')
                                                <div class="w-100 h6 mt-2 videoInfo d-flex">
                                                    <span>{{ $studentscontents->video }}</span>
                                                    <div class="btn-group">
                                                        <a href="{{ url('/video/content/' . $studentscontents->video) }}" class="btn btn-primary btn-sm ml-5" download><i class="fas fa-download"></i></a>
                                                        <a onclick="deleteContentVideo({{$studentscontents->id}})" class="btn btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="">Or {{ __('admin/content.event_video_url') }}</label>
                                                <input type="text" class="form-control" id="video_url" placeholder=""
                                                    name="video_url" value="{{ old('video_url', $studentscontents->video_url) }}" onblur="validateYouTubeUrl()">
                                                @error('video_url')
                                                    <strong class="text-danger">{{ $message }}</strong>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="Worksheet">{{ __('admin/content.worksheets') }}</label>
                                                <input type="file" class="form-control" id="Worksheets" placeholder=""
                                                    name="worksheets" onchange="validateAttachment(this.id)">
                                                <input type="hidden" value="{{ $studentscontents->worksheet }}" name="pre_worksheet">
                                                @if(!empty($studentscontents->worksheet) && $studentscontents->worksheet != 'no worksheet')
                                                <div class="w-100 h6 mt-2 worksheetInfo d-flex">
                                                    <span>{{ $studentscontents->worksheet }}</span>
                                                    <div class="btn-group">
                                                        <a href="{{ url('/files/content/' . $studentscontents->worksheet) }}" class="btn btn-primary btn-sm ml-5" download><i class="fas fa-download"></i></a>
                                                        <a onclick="deleteWorkSheet({{$studentscontents->id}})" class="btn btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                            <label for="Worksheets">Save as Draft OR Publish</label>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="contentPublish" id="isDraft" value="0" @if($studentscontents->is_publish == 0) checked @endif>
                                                <label class="form-check-label" for="isDraft">Draft</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="contentPublish" id="isPublish" value="1" @if($studentscontents->is_publish == 1) checked @endif>
                                                <label class="form-check-label" for="isPublish">Publish</label>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group clearfix">
								<a href="javascript:;" class="btn btn-primary form-wizard-next-btn float-right">Next</a>
							</div>
                        </div>
                        <div class="col-md-12 mb-4 wizard-fieldset @if(session()->has('success')) show @endif">
                            <div class="card card-primary">
                                <div class="card-header bg-color-skyblue">
                                    <div class="w-100 d-flex align-items-center justify-content-between">
                                        <h5 class="mb-0 text-dark">{{ __('admin/content.quiz') }}</h5>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="que-div" id="que-div">
                                        @foreach($quizQuestions as $queKey => $question)
                                        <div class="que-box mb-4" id="que-box{{$queKey}}">
                                            <div class="col-12 mb-4">
                                                <div class="row">    
                                                    <div class="col-12 d-flex aling-item-start justify-content-between">
                                                        <label for="Worksheet" class="fs-22 mb-2  mr-auto">Question</label>
                                                        @if($question->isDisabled)
                                                        <span class="text-danger mr-3">Disabled</span>
                                                        <a href="javascript:void(0);" class="text-primary" onclick="enableQuestion({{$question->id}})">Enable</a>
                                                        @elseif(in_array($question->id, $quizAttemptIds))
                                                        <a href="javascript:void(0);" class="text-primary" onclick="disableQuestion({{$question->id}})">Disable</a>
                                                        @else    
                                                        <a href="javascript:void(0);" class="text-danger" onclick="deleteQuestion({{$question->id}})"><i class="fas fa-trash"></i></a>
                                                        @endif
                                                        <input type="hidden" name="questionIds[]" value="{{$question->id}}">
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control" id="que{{$queKey}}" placeholder=""
                                                    name="questions[]" value="{{ $question->question }}" required>
                                            </div>
                                            <div class="col-12">
                                                <div class="row">
                                                    <div class="col-md-6 col-sm-12 col-12">
                                                        <div class="form-group">
                                                            <label for="Worksheet" class="fw-300">Option A</label>
                                                            <input type="text" class="form-control" id="que{{$queKey}}Opt1" placeholder="" name="options[{{$queKey}}][]" value="{{ $question->option1 }}" required>
                                                            <div class="currect_ans"><input type="radio" class="radioGroup" id="answer1[{{$queKey}}]" value="A" name="answers[{{$queKey}}]" @if($question->correct_option == 'A') checked @endif><label for="answer1[{{$queKey}}]"></label></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-sm-12 col-12">
                                                        <div class="form-group">
                                                            <label for="Worksheet" class="fw-300">Option B</label>
                                                            <input type="text" class="form-control" id="que{{$queKey}}Opt2" placeholder=""name="options[{{$queKey}}][]" value="{{ $question->option2 }}" required>
                                                            <div class="currect_ans"><input type="radio" class="radioGroup" id="answer2[{{$queKey}}]" value="B" name="answers[{{$queKey}}]" @if($question->correct_option == 'B') checked @endif><label for="answer2[{{$queKey}}]"></label></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-sm-12 col-12">
                                                        <div class="form-group">
                                                            <label for="Worksheet" class="fw-300">Option C</label>
                                                            <input type="text" class="form-control" id="que{{$queKey}}Opt3" placeholder="" name="options[{{$queKey}}][]" value="{{ $question->option3 }}" required>
                                                            <div class="currect_ans"><input type="radio" class="radioGroup" id="answer3[{{$queKey}}]" value="C" name="answers[{{$queKey}}]" @if($question->correct_option == 'C') checked @endif><label for="answer3[{{$queKey}}]"></label></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-sm-12 col-12">
                                                        <div class="form-group">
                                                            <label for="Worksheet" class="fw-300">Option D</label>
                                                            <input type="text" class="form-control" id="que{{$queKey}}Opt4" placeholder="" name="options[{{$queKey}}][]" value="{{ $question->option4 }}" required>
                                                            <div class="currect_ans"><input type="radio" class="radioGroup" id="answer4[{{$queKey}}]" value="D" name="answers[{{$queKey}}]" @if($question->correct_option == 'D') checked @endif><label for="answer4[{{$queKey}}]"></label></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    <div class="addMore">
                                        <a href="javscript:void(0);" class="btn btn-sm btn-success add_que_btn" id="addMoreLink" onclick="addMore()">+ Add Questions</a>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex aling-items-center justify-content-between">
                                <a href="javascript:;" id="btn-previous" class="mt-4 btn btn-primary form-wizard-previous-btn">Previous</a>
                                <button type="submit" id="btn-submit" class="mt-4 btn btn-primary">{{ __('admin/content.submit') }}</button>
                            </div>
                        </div>        
                    </div>
                </form>
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <div class="modal fade" id="addStream">
        <div class="modal-dialog">
            <form action="{{ route('backend.addstream.addStream') }}" class="modal-content" id="addstreamform"
                method="POST">
                <div class="modal-header">
                    <h4 class="modal-title">{{ __('admin/content.add_stream') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="addstreamform-errors"></div>

                    <div class="form-group">
                        <label>{{ __('admin/content.stream_name') }}</label>
                        <input type="text" class="form-control" name="stream_name" id="gettitle">
                        <span id="stream_error" class="text-danger"></span>
                    </div>
                    <div class="form-group">
                        <label>{{ __('admin/content.select_level') }}</label>
                        <select class="form-control" name="level" id="getlevel">
                            @foreach ($AgeGroups as $ag)
                                <option value="{{ $ag['id'] }}">{{ $ag['grade'] . ' ' . $ag['description'] }}
                                </option>
                            @endforeach
                        </select>
                        <span id="stream_error" class="text-danger"></span>
                    </div>

                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default">{{ __('admin/content.cancel') }}</button>
                    <button type="submit" class="btn btn-primary"
                        id="streamAdd">{{ __('admin/content.add_stream') }}</button>
                </div>
            </form>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
    <div class="modal fade" id="addAgeGroup">
        <div class="modal-dialog">
            <form method="POST" id="addlevelform" class="modal-content"
                action="{{ route('backend.addagegroup.addAgeGroup') }}">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">Add Level</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="addlevelform-errors"></div>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" class="form-control" name="title">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" class="form-control" name="description">
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="submit" class="btn btn-primary" id="ageGroupAdd">Add Level</button>
                </div>
            </form>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <script>
        
        jQuery(document).ready(function() {

            $( ".form-wizard" ).on( "submit", function( event ) {
                if($('select[name="agegroup_id').find(":selected").val() == '' || !$.isNumeric($('select[name="agegroup_id').find(":selected").val())) {
                    alert('The level field is required.');
                    return false;
                }
                if($('select[name="stream_id').find(":selected").val() == '' || !$.isNumeric($('select[name="stream_id').find(":selected").val())) {
                    alert('The stream field is required.');
                    return false;
                }
                if($('#inchargename').val() == '') {
                    alert('The title field is required.');
                    return false;
                }
                // if($('#pre_video').val() == '' && $('#video_url').val() == '') {
                //     alert('The Session Video or Session Video URL field is required.');
                //     return false;
                // }
                var queCount = $('.que-box').length;
                if(queCount) {
                    var checkedOptionCount = $("input[class='radioGroup']").filter(':checked').length;
                    if(checkedOptionCount != queCount) {
                        alert('Please select one of the option for every questions!');
                        return false;     
                    }
                }
                $("#btn-submit").attr('disabled', true); 
                $("#btn-previous").attr('disabled', true); 
                return true;
            });

            // click on next button
            jQuery('.form-wizard-next-btn').click(function() {
                var parentFieldset = jQuery(this).parents('.wizard-fieldset');
                var currentActiveStep = jQuery(this).parents('.form-wizard').find('.form-wizard-steps .active');
                var next = jQuery(this);
                var nextWizardStep = true;
                parentFieldset.find('.wizard-required').each(function(){
                    var thisValue = jQuery(this).val();

                    if( thisValue == "") {
                        jQuery(this).siblings(".wizard-form-error").slideDown();
                        nextWizardStep = false;
                    }
                    else {
                        jQuery(this).siblings(".wizard-form-error").slideUp();
                    }
                });
                if( nextWizardStep) {
                    next.parents('.wizard-fieldset').removeClass("show","400");
                    currentActiveStep.removeClass('active').addClass('activated').next().addClass('active',"400");
                    next.parents('.wizard-fieldset').next('.wizard-fieldset').addClass("show","400");
                    jQuery(document).find('.wizard-fieldset').each(function(){
                        if(jQuery(this).hasClass('show')){
                            var formAtrr = jQuery(this).attr('data-tab-content');
                            jQuery(document).find('.form-wizard-steps .form-wizard-step-item').each(function(){
                                if(jQuery(this).attr('data-attr') == formAtrr){
                                    jQuery(this).addClass('active');
                                    var innerWidth = jQuery(this).innerWidth();
                                    var position = jQuery(this).position();
                                    jQuery(document).find('.form-wizard-step-move').css({"left": position.left, "width": innerWidth});
                                }else{
                                    jQuery(this).removeClass('active');
                                }
                            });
                        }
                    });
                }
            }); 

            //click on previous button
            jQuery('.form-wizard-previous-btn').click(function() {
                var counter = parseInt(jQuery(".wizard-counter").text());;
                var prev =jQuery(this);
                var currentActiveStep = jQuery(this).parents('.form-wizard').find('.form-wizard-steps .active');
                prev.parents('.wizard-fieldset').removeClass("show","400");
                prev.parents('.wizard-fieldset').prev('.wizard-fieldset').addClass("show","400");
                currentActiveStep.removeClass('active').prev().removeClass('activated').addClass('active',"400");
                jQuery(document).find('.wizard-fieldset').each(function(){
                    if(jQuery(this).hasClass('show')){
                        var formAtrr = jQuery(this).attr('data-tab-content');
                        jQuery(document).find('.form-wizard-steps .form-wizard-step-item').each(function(){
                            if(jQuery(this).attr('data-attr') == formAtrr){
                                jQuery(this).addClass('active');
                                var innerWidth = jQuery(this).innerWidth();
                                var position = jQuery(this).position();
                                jQuery(document).find('.form-wizard-step-move').css({"left": position.left, "width": innerWidth});
                            }else{
                                jQuery(this).removeClass('active');
                            }
                        });
                    }
                });
            });    
        });

        $('#addstreamform').submit(function(event) {
            event.preventDefault();
            var frm = $("#addstreamform");
            var data = new FormData(this);
            $.ajax({
                type: frm.attr('method'),
                url: frm.attr('action'),
                data: data,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    window.location.reload();
                },
                error: function(data) {
                    var errors = data.responseJSON;
                    console.log(errors);
                    errorsHtml = '<div class="alert alert-danger"><ul>';
                    $.each(errors.errors, function(k, v) {
                        errorsHtml += '<li>' + v + '</li>';
                    });
                    errorsHtml += '</ul></div>';
                    $('#addstreamform-errors').html(errorsHtml);
                }
            });
        });


        function deleteStream() {
            let stream = $('#stream_id').val();
            $.ajax({
                url: "{{ route('backend.addstream.destoryStream') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    stream: stream
                },
                method: "POST",
                success: function(data) {
                    window.location.reload();
                }
            });
        }

        function deleteWorkSheet(contentId) {
            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "{{ route('backend.content.deleteWorkSheet') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        contentId: contentId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Worksheet deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        }

        function deleteContentVideo(contentId) {
            if(confirm("Are you sure?")) {
                $.ajax({
                    url: "{{ route('backend.content.deleteContentVideo') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        contentId: contentId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Content Video deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        }
        
        function addMore() {
            var queCnt = $('.que-box').length;
            
            var addQue = `<div class="que-box mb-4" id="que-box${queCnt}">
                            <div class="col-12 mb-4">
                                <div class="row">    
                                    <div class="col-12 d-flex aling-item-start justify-content-between">
                                        <label for="Worksheet" class="fs-22 mb-2">Question</label>
                                        <a href="javascript:void(0)" class="text-danger" onclick="removeQuestion(${queCnt})"><i class="fas fa-trash"></i></a>
                                    </div>
                                </div>
                                <input type="text" class="form-control" id="que${queCnt}" placeholder="" name="questions[]" required>
                                </div>
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-md-6 col-sm-12 col-12">
                                        <div class="form-group">
                                            <label for="Worksheet" class="fw-300">Option A</label>
                                            <input type="text" class="form-control" id="que${queCnt}Opt1" placeholder="" name="options[${queCnt}][]" required>
                                            <div class="currect_ans"><input type="radio" class="radioGroup" id="answer1[${queCnt}]" value="A" name="answers[${queCnt}]"><label for="answer1[${queCnt}]"></label></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12 col-12">
                                        <div class="form-group">
                                            <label for="Worksheet" class="fw-300">Option B</label>
                                            <input type="text" class="form-control" id="que${queCnt}Opt2" placeholder=""name="options[${queCnt}][]" required>
                                            <div class="currect_ans"><input type="radio" class="radioGroup" id="answer2[${queCnt}]" value="B" name="answers[${queCnt}]"><label for="answer2[${queCnt}]"></label></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12 col-12">
                                        <div class="form-group">
                                            <label for="Worksheet" class="fw-300">Option C</label>
                                            <input type="text" class="form-control" id="que${queCnt}Opt3" placeholder="" name="options[${queCnt}][]" required>
                                            <div class="currect_ans"><input type="radio" class="radioGroup" id="answer3[${queCnt}]" value="C" name="answers[${queCnt}]"><label for="answer3[${queCnt}]"></label></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12 col-12">
                                        <div class="form-group">
                                            <label for="Worksheet" class="fw-300">Option D</label>
                                            <input type="text" class="form-control" id="que${queCnt}Opt4" placeholder="" name="options[${queCnt}][]" required>
                                            <div class="currect_ans"><input type="radio" class="radioGroup" id="answer4[${queCnt}]" value="D" name="answers[${queCnt}]"><label for="answer4[${queCnt}]"></label></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
            if(!queCnt) {
                $(`#que-div`).append(addQue);
            } else {
                $(`#que-box${queCnt-1}`).after(addQue);
            }
            
            $("#addMoreLink").html(' <span>+ Add Question</span> ');
            
            $('.que-div').animate({
                scrollTop: $('.que-div').prop('scrollHeight')
            }, 1000);
        }

        function removeQuestion(queId) {
            $(`#que-box${queId}`).remove();
            var queCnt = $('.que-box').length;
            if(!queCnt) {
                $("#addMoreLink").text('Add Questions');
            }
        }

        function deleteQuestion(queId) {    
            if(confirm("Are you sure want to Delete this Question?")) {
                $.ajax({
                    url: "{{ route('backend.content.deleteQuestion') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        questionId: queId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Question deleted.");
                            window.location.reload();
                        }
                    }
                });
            }
        }

        function disableQuestion(queId) {
            if(confirm("Are you sure want to Disable this Question?")) {
                $.ajax({
                    url: "{{ route('backend.content.disableQuestion') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        questionId: queId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Question Disabled.");
                            window.location.reload();
                        }
                    }
                });
            }
        }

        function enableQuestion(queId) {
            if(confirm("Are you sure want to Enable this Question?")) {
                $.ajax({
                    url: "{{ route('backend.content.enableQuestion') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        questionId: queId
                    },
                    method: "POST",
                    success: function(res) {
                        if(res) {
                            alert("Question Enabled.");
                            window.location.reload();
                        }
                    }
                });
            }
        }

        $('#addlevelform').submit(function(event) {
            event.preventDefault();
            var frm = $("#addlevelform");
            var data = new FormData(this);
            $.ajax({
                type: frm.attr('method'),
                url: frm.attr('action'),
                data: data,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    window.location.reload();
                },
                error: function(data) {
                    var errors = data.responseJSON;
                    console.log(errors);
                    errorsHtml = '<div class="alert alert-danger"><ul>';
                    $.each(errors.errors, function(k, v) {
                        errorsHtml += '<li>' + v + '</li>';
                    });
                    errorsHtml += '</ul></div>';
                    $('#addlevelform-errors').html(errorsHtml);
                }
            });
        });

        $('select[name="agegroup_id"]').on('change', function() {
            var agegroupId = $(this).val();
            $.ajax({
                type: "POST",
                url: "{{ route('backend.changestudentstream.changeStudentStream') }}",
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

        function validateAttachment(id){
            var fileExtension = ['pdf'];
            if ($("#"+id).val() && $.inArray($("#"+id).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                alert("Only PDF file is allowed.");
                $("#"+id).val(''); 
            }
        }
        
    </script>
@endsection
