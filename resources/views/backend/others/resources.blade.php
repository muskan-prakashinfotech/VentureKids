@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Other Resources</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('admin/event.other') }}</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <input type="radio" name="other" class="other_resource" id="" value="1" checked> Trainer
                        <input type="radio" name="other" class="other_resource" id="" value="2"> Student
                        <input type="radio" name="other" class="other_resource" id="" value="3"> School
                    </div>
                </div>
            </div>
        </section>
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <form action="{{ route('backend.updateresources.updateResources') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-primary">
                                <div class="alert alert-success" id="teacher_success" style="text-align: center;display:none">Trainer Info Updated</div>
                                <div class="alert alert-success" id="student_success" style="text-align: center;display:none">Student Info Updated</div>
                                <div class="alert alert-success" id="school_success" style="text-align: center;display:none">School Info Updated</div>

                                <div class="card-header">
                                    <div class="card-tool float-right">
                                        <a href="{{ URL::previous() }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="eventstart">{{ __('admin/event.description') }}</label>
                                        <textarea id="compose-textarea" class="form-control setting_value" name="resource">{{ $resource['setting_value'] }}</textarea>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="button" id="resource_submit"
                                        class="btn btn-primary">{{ __('admin/event.submit') }}</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </section>
    </div>


    <script>
        $(document).ready(function() {
            $('#resource_submit').click(function() {
                var other_resource = $("input[name='other']:checked").val();
                var textareaValue = $('#compose-textarea').summernote('code');

                $("#Trainer_success").css("display", "none");
                $("#student_success").css("display", "none");
                $("#school_success").css("display", "none");

                $.ajax({
                    url: "{{ route('backend.save_resource_value') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    data: {
                        other_resource: other_resource,
                        textareaValue: textareaValue
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data == 1) {
                            $("#teacher_success").css("display", "block");
                        } else if (data == 2) {
                            $("#student_success").css("display", "block");
                        } else if (data == 3) {
                            $("#school_success").css("display", "block");
                        }
                    }
                });
            });

        });

        $('.other_resource').on("change", function() {
            var resource = $(this).val();

            $.ajax({
                url: "{{ route('backend.get_resource_value') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                data: {
                    resource: resource
                },
                dataType: 'json',
                success: function(data) {
                    console.log(data);
                    if (data.teacher != '') {
                        $('#compose-textarea').summernote('code', data.teacher.setting_value);
                    } else if (data.student != '') {
                        $('#compose-textarea').summernote('code', data.student.setting_value);
                    } else if (data.school != '') {
                        $('#compose-textarea').summernote('code', data.school.setting_value);
                    }
                }
            });

            //   if(resource == 1){

            //     $.ajax({
            //       url: "{{ route('backend.trainerterms.trainerTerms') }}",
            //       headers: {
            //           'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //       },
            //       data: {
            //           "resource": resource,
            //       },
            //       dataType: 'JSON',
            //       success: function(data) {
            //           $('#compose-textarea').summernote('destroy');
            //           $('#compose-textarea').summernote('pasteHTML', data.setting_value);

            //           // $('#compose-textarea').css('display', 'block');
            //           // $('#compose-textarea1').css('display', 'none');
            //           // $('#compose-textarea2').css('display', 'none');
            //           // window.location.reload();
            //       },
            //     });
            //     // console.log('Teacher');
            //   }
            // if(resource == 2){
            //   $.ajax({
            //     url: "{{ route('backend.studentterms.studentTerms') }}",
            //     headers: {
            //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //     },
            //     data: {
            //         "resource": resource,
            //     },
            //     dataType: 'JSON',
            //     success: function(data) {
            //         $('#compose-textarea').summernote('destroy');
            //         $('#compose-textarea').summernote('pasteHTML', data.setting_value);

            //         // $('#compose-textarea').css('display','none');
            //         // $('#compose-textarea1').css('display', 'block');
            //         // $('#compose-textarea2').css('display', 'none');
            //         // window.location.reload();
            //     },
            //   });
            //   // console.log('Student');
            // }
            // if(resource == 3){

            //   $.ajax({
            //     url: "{{ route('backend.schoolterms.schoolTerms') }}",
            //     headers: {
            //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //     },
            //     data: {
            //         "resource": resource,
            //     },
            //     dataType: 'JSON',
            //     success: function(data) {
            //       $('#compose-textarea').summernote('destroy');
            //       $('#compose-textarea').summernote('pasteHTML', data.setting_value);

            //       // $('#compose-textarea').css('display','none');
            //       // $('#compose-textarea1').css('display', 'none');
            //       // $('#compose-textarea2').css('display', 'block');
            //         // window.location.reload();
            //     },
            //   });

            //   // console.log('School');
            // }
        });
    </script>
@endsection
