@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Assignment</h1>
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
        <section class="content">
            <div class="container-fluid">

                <div class="row">
                    <?php $i = 0;
                    $j = 1; ?>
                    @foreach ($assignment as $assignments)
                        <div class="mb-4 col-md-6">
                            <form class="teacher_comment" method="post">
                                <div class="card direct-chat direct-chat-primary">
                                    <div class="card-header d-flex align-items-center">
                                        <input type="hidden" name="assignment_id" value="{{ $assignments['id'] }}">
                                        <div class="mr-2 d-flex align-items-start">
                                            <select class="form-control student_comment" name="student" id="student_comment"
                                                data-id="{{ $assignments['id'] }}" data-index="<?= $i ?>"
                                                data-attendent="<?= $j ?>">
                                                <option value="">--- Select Student ---</option>
                                                @foreach ($students as $student)
                                                    <option value="{{ $student['id'] }}">{{ $student['name'] }}</option>
                                                @endforeach
                                            </select>
                                            <span class="badge badge-danger"><?= count($students) ?></span>
                                        </div>
                                        <h6>{{ $assignments['title'] }}</h6>

                                        <div class="check_attendent_<?= $j ?>"></div>

                                    </div>
                                    <div class="single-item">
                                        <div>
                                            <!-- /.card-header -->
                                            <div class="card-body">
                                                <textarea class="form-control"><?= strip_tags('Hello <b>world!</b>') ?></textarea>

                                                <!-- Conversations are loaded here -->
                                                <div class="direct-chat-messages comments<?= $i ?>">


                                                </div>
                                                <!--/.direct-chat-messages-->
                                            </div>
                                            <!-- /.card-body -->
                                            <div class="card-footer">

                                                <div class="input-group">
                                                    <input type="text" name="message" placeholder="Type Message ..."
                                                        class="form-control message">
                                                    <span class="input-group-append">
                                                        <button type="button" class="btn btn-primary reply_comment"
                                                            data-id="{{ $assignments['id'] }}"
                                                            data-index="<?= $i ?>">Send</button>
                                                    </span>
                                                </div>
                                            </div>
                                            <!-- /.card-footer-->
                                        </div>


                                    </div>

                                </div>
                            </form>
                        </div>
                        <?php $i++;
                        $j++; ?>
                    @endforeach

                </div>
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>

    <script>
        $(document).ready(function() {

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
                        // var a= data.created_at;
                        // var sDate = a.ToString("MM/dd/yyyy hh:mm:ss tt")
                        // console.log(sDate);
                        //var a = '/image/trainer/thumbnail/'+data.get_trainer.image;
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
                        // console.log(student);
                        // console.log(assignment);
                        // alert(assignment_id);
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
@endsection
