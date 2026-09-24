@extends('backend.layouts.app')

@section('content')
<link rel="stylesheet" href="public/asset/dist/css/style.css">
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">Assignment Review</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Assignment</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="row">
        <div class="col-sm-6">
    <div class="col-md-12">
        <div class="form-group">
            <label for="schoolname">{{ __('admin/student_communication.select_school') }}</label>
            <select class="form-control" name="school_id" wire:model='school' id="schoolName">
                <option value="">---Select---</option>
                @foreach ($school_name as $school)
                    <option value="{{ $school->id }}"> {{ $school->school_name }} </option>
                @endforeach
            </select>
            @error('school_id')
                <strong class="text-danger">{{ $message }}</strong>
            @enderror
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group" id="grade_id">
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group" id="student_id">
        </div>
    </div>
    </div>
    <div class="col-md-6" style="height:400px">
        <div class="form-group" id="assignment_id">
        </div>
        <div></div>
    </div>

</div>

    <!-- /.content -->
</div>

<script>
    $(document).ready(function() {

        $('#schoolName').on('change',function(e) {
            var school_id = $(this).val();
            e.preventDefault();

            $.ajax({
                url: "{{ route('trainer.getLevelList') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                data: {
                    school_id: school_id
                },
                dataType: 'json',
                success: function(data) {
                    if(data){
                        $('#grade_id').empty().html(data);
                    }
                    console.log(data)
                }
            });
        });



        $("body").delegate(".assignment_comment", "change", function() {

            if ($(this).is(":checked")) {
                var comment = 1;
            } else {
                var comment = 2;
            }
            var student_id = $(this).data('studentid');
            var assignment_id = $(this).data("assignmentid");

            $.ajax({
                url: "{{ route('trainer.update_complete_assignment') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                data: {
                    comment: comment,
                    student_id: student_id,
                    assignment_id: assignment_id
                },
                dataType: 'json',
                success: function(data) {
                    window.location.reload();
                }
            });

        });


        $('.reply_comment').click(function(e) {
            e.preventDefault();
            let form = $(this).closest('form');
            var student_id = $.trim(form.find("select[name='student']").val());
            var message = $.trim(form.find("input[name='message']").val());
            var assignment_id = $.trim(form.find("input[name='assignment_id']").val());
            var class_id = $(this).attr('data-index');

            $.ajax({
                url: "{{ route('trainer.createComment') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                data: {
                    student_id: student_id,
                    message: message,
                    assignment_id: assignment_id
                },
                dataType: 'json',
                success: function(data) {
                    console.log(data);

                    var image = "{{ url('/image/trainer') }}/" + data.get_trainer.image;

                    var html =
                        '<div class="direct-chat-msg right"><div class="clearfix direct-chat-infos"><span class="float-right direct-chat-name">' +
                        data.get_trainer.trainer_name +
                        '</span><span class="float-left direct-chat-timestamp">23 Jan 6:10 pm</span></div><img class="direct-chat-img" src="' +
                        image + '" alt="user image"><div class="direct-chat-text">' + data
                        .message + '</div></div>';

                    $(".comments" + class_id).append(html);
                    $(".message").val("");

                }
            });

        });

        $('.student_comment').change(function(e) {
            e.preventDefault();
            var student_id = $(this).val();
            var assignment_id = $(this).attr('data-id');
            var class_id = $(this).attr('data-index');

            var attendent = $(this).attr('data-attendent');

            $.ajax({
                url: "{{ route('trainer.getStudentComment') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                data: {
                    student_id: student_id,
                    assignment_id: assignment_id
                },
                dataType: 'json',
                success: function(data) {
                    console.log(data);

                    var html = '';
                    var assignment = data.getComment;
                    var student = data.student;
                    var trainer = data.tariner;
                    console.log(assignment);

                    if (data.message != '') {
                        $('.check_attendent_' + attendent).html(
                            '<span style="margin-left: 16px;">' + data.message +
                            '</span>');
                    }
                    if (data.assignmentCommnet.comment_status == 1) {
                        $('.check_attendent_' + attendent).html(
                            '<span class="bs-stepper-circle bg-success"><i class="fas fa-check"></i></span>'
                        );
                    } else {
                        var attendentAppend =
                            '<span style="font-size: 15px; margin-left: 16px;">Mark as complete</span> <input type="checkbox" name="markascomplete1" class="assignment_comment" data-assignmentid="' +
                            assignment_id + '" data-studentid="' + student_id + '" >';
                        $('.check_attendent_' + attendent).html(attendentAppend);
                    }

                    for (var i = 0; i < assignment.length; i++) {
                        $("#comment").val(assignment[i].comment);

                        if (assignment[i].reciever_id == student.id) {
                            var css =
                                'background-color: #d2d6de;border: 1px solid #d2d6de;color: #444';
                            var name = trainer.trainer_name;
                            var image = "{{ url('/image/trainer') }}/" + trainer.image;

                        } else {
                            var css =
                                'background-color: #007bff;border-color: #007bff;color: #fff;';
                            var name = student.name;
                            var image = "{{ url('') }}/" + student.image;
                        }

                        html +=
                            '<div class="direct-chat-msg right"><div class="clearfix direct-chat-infos"><span class="float-right direct-chat-name">' +
                            name +
                            '</span><span class="float-left direct-chat-timestamp">23 Jan 6:10 pm</span></div><img class="direct-chat-img" src="' +
                            image +
                            '" alt="user image"><div class="direct-chat-text" style="' +
                            css + '">' + assignment[i].message + '</div></div>';

                    }

                    $(".comments" + class_id).html(html);

                }
            });

        });
    });

</script>
<script>
function gradeId(id) {
    var school_id = $('#schoolName').val();
    var grade = id;

    $.ajax({
        url: "{{ route('trainer.getStudentList') }}",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: "POST",
        data: {
            school_id : school_id,
            grade : grade
        },
        dataType: 'json',
        success: function(data) {
            if(data){
                $('#student_id').empty().html(data);
            }
            console.log(data)
        }
    });
}
</script>

<script>
function studentAssignment(id) {
    var school_id = $('#schoolName').val();
    var grade = $('#grade').val();
    var student = id;

    $.ajax({
        url: "{{ route('trainer.studentAssignment') }}",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: "POST",
        data: {
            school_id : school_id,
            grade : grade,
            student : id

        },
        dataType: 'json',
        success: function(data) {
            if(data){
                $('#assignment_id').empty().html(data);
            }
            console.log(data)
        }
    });
}
</script>
@endsection
