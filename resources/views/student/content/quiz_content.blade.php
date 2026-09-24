@extends('backend.layouts.app')
@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>{{ __('admin/content.quiz') }}</h2>
        <!-- <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">{{ __('admin/content.home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin/content.quiz') }}</li>
        </ol> -->
    </div>

    <!-- Main content -->
    <section>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    @if($content['content_type'] == 'session')
                        {{$content['title']}} ({{$content['level']}} - {{$content['session']}})
                    @else
                        {{$content['challenge_name']}} 
                    @endif
                </h3>
                <div class="card-tools ml-auto">
                    <div class="btns-group">
                        <a @if($content['content_type'] == 'session') href="{{ route('student.contentview.contentView', $content['stream_id']) }}"  @else href="{{ route('student.event_list') }}" @endif class="btn btn-sm btn-warning">
                            <i class="material-icons">west</i>
                            Back
                        </a>
                        
                    </div>
                </div>
            </div>
            <!-- /.card-header -->
            @if(empty($isQuizSubmit) && empty($answers))
            <div class="card-body box_padd">
                <div class="row">
                    <div class="col-12">
                        @if(empty($questions[0]))
                            <div class="card-title">
                                New challenge coming soon. Hang tight!
                            </div>
                        @else
                        <form method="POST" id="studQuizFrm">
                        <div class="question-div new_quiz_box">
                            <input type="hidden" name="queCnt" value="1" id="queCnt">
                            <input type="hidden" name="content_id" value="{{$content['id']}}" id="content_id">
                            
                            <div class="d-flex flex-wrap align-items-center justify-content-between progress_with_counter">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: 50%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                        <div class="d-flex align-items-center justify-content-center">
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="queCntDiv">
                                    Question <span class="currentCnt">1</span>/<span class="totalCnt">{{count($questions)}}</span>   
                                </div>
                            </div>
                            
                            @foreach($questions as $queKey => $question)
                            <div class="question-box w-100" id="question-box{{$queKey+1}}" data-id="{{$queKey+1}}" @if(isset($question->is_attempt)) data-is_attempt="{{$question->is_attempt}}" @else data-is_attempt="0" @endif>    
                            <h2 class="form-heading">Q. {{ $question->question }}</h2>    
                                <div class="mt-4 pop-slide" id="options">
                                    <label class="options">{{ $question->option1 }}
                                        <input class="radio" type="radio" name="answer[{{$question->id}}][]" id="{{$question->id}}|A" @if(isset($question->answer) && $question->answer == 'A') checked @endif>
                                        <span class="checkmark"></span>
                                    </label>
                                    <label class="options">{{ $question->option2 }}
                                        <input class="radio" type="radio" name="answer[{{$question->id}}][]" id="{{$question->id}}|B" @if(isset($question->answer) && $question->answer == 'B') checked @endif>
                                        <span class="checkmark"></span>
                                    </label>
                                    <label class="options">{{ $question->option3 }}
                                        <input class="radio" type="radio" name="answer[{{$question->id}}][]" id="{{$question->id}}|C" @if(isset($question->answer) && $question->answer == 'C') checked @endif>
                                        <span class="checkmark"></span>
                                    </label>
                                    <label class="options">{{ $question->option4 }}
                                        <input class="radio" type="radio" name="answer[{{$question->id}}][]" id="{{$question->id}}|D" @if(isset($question->answer) && $question->answer == 'D') checked @endif>
                                        <span class="checkmark"></span>
                                    </label>
                                    <input type="hidden" name="questionId[]" class="questionId" value="{{$question->id}}">
                                </div>
                            </div>
                            @endforeach
                            <div class="d-flex align-items-center pt-3 border-top mt-5">
                                <div id="prev">
                                    <span class="btn btn-primary" id="prevAction" onclick="changeQuestion('prev')">Previous</span>
                                </div>
                                <div class="ml-auto">
                                    <span class="btn btn-success" id="nextAction" onclick="changeQuestion('next')">Next</span>
                                    <span class="btn btn-success" id="submitAction" onclick="submitQuiz()">Submit</span>
                                </div>
                            </div>
                        </div>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            <!-- /.card-body -->
            <a type="button" class="" id="completeBtn" data-toggle="modal" data-target="#quizCompleteModal" hidden>Thank You</a>
            <!-- Thank You modal -->
            <div class="modal fade" id="quizCompleteModal" tabindex="-1" role="dialog" aria-labelledby="quizCompleteModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen" role="document">
                    <div class="modal-content h-100">
                        <div class="modal-body">
                            <div class="thankyou-page thankyou-circle steps-inner" id="thankyou">
                                <div class="row">
                                    <div class="col-md-12 mx-auto">
                                        <div class="thankyou-page-inner">
                                            <div class="wrapper">
                                                <!-- tick  -->
                                                <div class="tick">
                                                    <div class="done-tick"></div>
                                                    <i class="fas fa-check"></i>
                                                </div>
                                                <!-- thankyou page heading -->
                                                <h2>Thank you for your participation</h2>
                                                <!-- thankyou page text -->
                                                <p>Click View Score button to View Your Score and Correct Answers. </p>
                                                <!-- thankyou paeg button -->
                                                <div class="next-prev-btn">
                                                    <button class="next back" id="scoreBtn">View Score</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($isQuizSubmit))
            <div class="card-body table-responsive">
                <div class="row">
                    <div class="col-md-12">
                        <span class="stud-score">
                            <h2>Your Score : {{ $score .'/'. $totalQuestions}}</h2>
                        </span>
                        @if(!empty($answers))
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Questions</th>
                                    <th scope="col">Your Answer</th>
                                    <th scope="col">Correct Answer</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($answers as $ans)
                                <tr>
                                    <td>{{ $ans['question']}}</td>
                                    <td>{{ $ans['answer']}}</td>
                                    <td>{{ $ans['correct_option']}}</td>
                                </tr>                      
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>
            </div>
            @endif

        </div>
    </section>
</div>

<script>
    var confetti_sound_path = "{{asset('asset/dist/audio/great-job-speech.mp3')}}";
     $(function() {
        var totQueCnt = "{{count($questions)}}";
        
        $('.question-box').hide();
        var question_box_div = $("div").find(`[data-is_attempt="0"]`).first();
        var question_box_id = $(question_box_div).data('id');

        // As per request, quiz should be start from question 1 so overwrited below value
        question_box_id = 1;
        
        $(`#question-box${question_box_id}`).show();
        
        $('.progress-bar').css('width', (question_box_id/totQueCnt) * 100+'%');
        $(".currentCnt").text(`${question_box_id}`)
        $("#queCnt").val(question_box_id)

        if(question_box_id == 1) {
            $("#prevAction").hide();
        }
        if(totQueCnt > 1 && question_box_id != totQueCnt) {
            $("#submitAction").hide();
        } else {
            $("#nextAction").hide();
        }

        $('#scoreBtn').click(function() {
            window.location.href = "{{ route('student.quiz', [$content['type'], $content['id']]) }}";
            return false;
        });
    });
    
    function changeQuestion(action) {
        var queCnt = $("#queCnt").val();
        var totalCnt = $(".totalCnt").text();
        $(`#question-box${queCnt}`).hide();
        
        if(action == 'next') {
            saveQuizAnswer(queCnt);
            queCnt++;
            if(queCnt == totalCnt) {
                $("#nextAction").hide();
                $("#submitAction").show();
            }
            $("#prevAction").show();
            $(`#question-box${queCnt}`).show();
        } else {
            queCnt--;
            if(queCnt == 1) {
                $("#prevAction").hide();
            }
            $("#submitAction").hide();
            $("#nextAction").show();
            $(`#question-box${queCnt}`).show();
        }
        $('.progress-bar').css('width', (queCnt/totalCnt) * 100+'%');
        $("#queCnt").val(queCnt)
        $(".currentCnt").text(`${queCnt}`)
    }

    function submitQuiz() { 
        if(confirm("Click OK to Submit Quiz!")) {
            $("#prevAction").hide();
            $("#submitAction").addClass("disabled");
            var frmData = new FormData();
            var data = {};
            var queIds = {};
            var optSelected = '';
            $(".radio:checked").each(function() {
                optSelected = $(this).attr('id').split('|');
                data[optSelected[0]] = optSelected[1];
            });
            $(".questionId").each(function() {
                queID = $(this).val();
                queIds[queID] = '';
            });
            frmData.append('content_id', $("#content_id").val());
            frmData.append('answers', JSON.stringify(data));
            frmData.append('queIds', JSON.stringify(queIds));
            frmData.append("content_type", "{{$content['content_type']}}");
            $.ajax({
                url: "{{ route('student.submitquiz.submitQuiz') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: frmData,
                contentType: false,
                processData: false,
                method: "POST",
                success: function(res) {
                    if(res) {
                        $( "#completeBtn" ).trigger( "click" );
                        // Call Confetti Animation 
                        poof();
                    }
                }
            });
        }
    }

    function saveQuizAnswer(queCnt) {
        
        var frmData = new FormData();
        var queId = {};
        var answer = {};
        
        $(`#question-box${queCnt}`).find('.radio').each(function() {
            optSelected = $(this).attr('id').split('|');
            queId = optSelected[0];
            if($(this).prop("checked")) {
                answer = optSelected[1];
            }
        });
        
        frmData.append('content_id', $("#content_id").val());
        frmData.append('queId', JSON.stringify(queId));
        frmData.append('answer', JSON.stringify(answer));
        frmData.append("content_type", "{{$content['content_type']}}");
        $.ajax({
            url: "{{ route('student.savequiz.saveQuiz') }}",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: frmData,
            contentType: false,
            processData: false,
            method: "POST",
            success: function(res) {
                
            }
        });    
    }

</script>
<script src="{{asset('asset/dist/js/confetti.js')}}"></script>
@endsection