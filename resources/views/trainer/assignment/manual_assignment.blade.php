@extends('backend.layouts.app')

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="pageTitle">
        <h2>Mark Assignment - {{ $assigment_title }}</h2>
        <a href="{{ route('trainer.assigment.index') }}" class="btn btn-sm btn-warning float-right">
            <i class="material-icons">west</i>
            Back
        </a>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section>
        <div class="container-fluid p-0">
            <div class="card">
                <form action="{{ route('trainer.mark.assignment') }}" method="POST" id="manual-submission-frm">
                @csrf
                <div class="card-header">
                    <div class="row col-md-12">
                        <div class="col-md-4">
                            <select class="form-control" id="select_school">
                                <option value="">---Select School---</option>
                                @foreach ($school_list as $school)
                                <option value="{{ $school['get_school']['id'] }}">{{ $school['get_school']['school_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <button type="submit" class="text-center btn btn-primary float-right disable-manual-submission" id="submit">Mark Assignment</button>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive">
                    <table id="example_new" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Select</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <input type="hidden" name="assignmentId" value="{{ $assignmentId }}">
            </form>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

<script>
$(document).ready(function() {
    $('#select_school').change(function() {
        $(this).attr('disabled','disabled');
        $("#submit").addClass('disable-manual-submission');
        // $('#example_new tbody').empty();
        $('#example_new').DataTable().clear();
        $('#example_new').DataTable().destroy();
        if($(this).val()) {
            var table = $('#example_new').DataTable({
                searching: false,
                bDestroy: true,  
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('trainer.manual_student_list_datatable')}}",
                    type: "get",
                    dataType: 'JSON',
                    data: function(d) {
                        d.school_id = $('#select_school').val(),
                        d.assignId = {{$assignmentId}}
                    }
                },
                columns: [{
                        data: 'std_user.name',
                        name: 'std_user.name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                ]
            });
        }
        setTimeout(function(){
            $("#select_school").removeAttr('disabled');
        }, 2000);
    });

    
        
});
$(document).on('click', '.stud-checkBox' , function() {
    var countCheckedCheckboxes = $('.stud-checkBox').filter(':checked').length;
    if(countCheckedCheckboxes) {
        $("#submit").removeClass('disable-manual-submission');
    } else {
        $("#submit").addClass('disable-manual-submission');
    }
});
</script>
@endsection