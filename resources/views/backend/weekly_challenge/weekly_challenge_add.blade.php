@extends('backend.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        {{-- <h1 class="m-0">Add Weekly Challenge</h1> --}}
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Add Daily Quiz</h4>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('backend.weeklyChallengeList') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                            </div>
                        </div>
                    </div>
                </div>
                <form action="{{ route('backend.weeklyChallengeStore') }}" method="POST" enctype="multipart/form-data" name="challengeFrm" id="challengeFrm">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-primary">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="inchargename">Quiz Name</label>
                                        <input type="text" class="form-control @error('challenge_name') is-invalid @enderror"
                                            id="challenge_name" placeholder="Challenge Name" name="challenge_name" required>
                                        @error('challenge_name')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="challenge_image">Quiz Image</label>
                                        <input type="file"
                                            class="form-control @error('challenge_image') is-invalid @enderror" id="challenge_image"
                                            name="challenge_image" accept="image/*" required>
                                        @error('challenge_image')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="que-div" id="que-div"></div>
                                    <div class="addMore">
                                        <a href="javscript:void(0);" class="btn btn-sm btn-success add_que_btn" id="addMoreLink" onclick="addMore()"><span>+ Add Question</span></a>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex aling-items-center justify-content-between">
                                        <button type="submit" id="btn-submit" class="mt-4 btn btn-primary">Submit</button>
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
            $( "#challengeFrm" ).on( "submit", function( event ) {
                var valid = true;
                 $('.que-box').each(function() {
                if($(this).find("input.checkGroup:checked").length == 0) {
                    valid = false;
                   }
                });
                if(!valid) {
                     alert('Please select at least one correct option for every question!');
                    return false;
                           }
                return true;
            });
            $("#challenge_image").change(function () {
                var fileExtension = ['jpeg', 'jpg', 'png'];
                if ($(this).val() && $.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
                    alert("Only JPG, JPEG or PNG files are allowed.");
                    $(this).val(''); 
                }
            });
        });

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
                                            <div class="currect_ans"><input type="checkbox" class="checkGroup" id="answer1[${queCnt}]" value="A" name="answers[${queCnt}][]"><label for="answer1[${queCnt}]"></label></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12 col-12">
                                        <div class="form-group">
                                            <label for="Worksheet" class="fw-300">Option B</label>
                                            <input type="text" class="form-control" id="que${queCnt}Opt2" placeholder=""name="options[${queCnt}][]" required>
                                            <div class="currect_ans"><input type="checkbox" class="checkGroup" id="answer2[${queCnt}]" value="B" name="answers[${queCnt}][]"><label for="answer2[${queCnt}]"></label></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12 col-12">
                                        <div class="form-group">
                                            <label for="Worksheet" class="fw-300">Option C</label>
                                            <input type="text" class="form-control" id="que${queCnt}Opt3" placeholder="" name="options[${queCnt}][]" required>
                                            <div class="currect_ans"><input type="checkbox" class="checkGroup" id="answer3[${queCnt}]" value="C" name="answers[${queCnt}][]"><label for="answer3[${queCnt}]"></label></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12 col-12">
                                        <div class="form-group">
                                            <label for="Worksheet" class="fw-300">Option D</label>
                                            <input type="text" class="form-control" id="que${queCnt}Opt4" placeholder="" name="options[${queCnt}][]" required>
                                            <div class="currect_ans"><input type="checkbox" class="checkGroup" id="answer4[${queCnt}]" value="D" name="answers[${queCnt}][]"><label for="answer4[${queCnt}]"></label></div>
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
            
            $('.que-div').animate({
                scrollTop: $('.que-div').prop('scrollHeight')
            }, 1000);
        }

        function removeQuestion(queId) {
            $(`#que-box${queId}`).remove();
        }
        
    </script>
@endsection
