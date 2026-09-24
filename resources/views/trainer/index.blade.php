@extends('backend.layouts.app')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->

    <!-- /.content-header -->

    <!-- Main content -->
    <section>
        <div class="container-fluid p-0">
            <div class="row h-100">
                <!-- <div class="col-md-6 mb-3">
                    <div class="card h-100 CardStyle">
                        <div class="card-header">
                            <h3 class="card-title">Content</h3>
                            <a href="{{ route('trainer.content/list.contentList') }}" class="btn btn-sm btn-primary btn-dark">View All Content</a>
                        </div>

                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <th>Video</th>
                                    <th>Title</th>
                                </thead>
                                <tbody>
                                    @foreach ($data['content'] as $contens)
                                    <tr>
                                        <td>
                                            <video width="80" height="50" controls>
                                                <source src="{{ url('/video/content/' . $contens['video']) }}"
                                                    type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        </td>
                                        <td>{{ $contens['title'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> -->

                {{--
                <!-- <div class="col-md-6 mb-3">
                    <div class="card h-100 CardStyle">
                        <div class="card-header">
                            <h3 class="card-title">Class Timetable</h3>
                            <a href="{{ route('trainer.class/schedule.classSchedule') }}" class="btn btn-sm btn-primary btn-dark">View All Class
                                schedule</a>
                        </div>
                        <div class="card-body table-responsive">


                            <div class="HeadRow">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <h5 class="HeadTitle">Day</h5>
                                    </div>
                                    <div class="col-lg-4">
                                        <h5 class="HeadTitle">Time</h5>
                                    </div>
                                    <div class="col-lg-4">
                                        <h5 class="HeadTitle">Level</h5>
                                    </div>
                                </div>
                            </div>
                            @foreach ($data['trainer_schedule'] as $schedules)
                            <div class="row mt-2">
                                <div class="col-lg-4">
                                    {{ \Carbon\Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Toronto')->addDays($schedules['day'])->format('l') }}
                                </div>
                                <div class="col-lg-4">
                                    {{ $schedules['class_start'] . '-' . $schedules['class_end'] }}
                                </div>
                                <div class="col-lg-4">
                                    {{ $schedules['grade'] }}
                                </div>
                            </div>
                            @endforeach

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Level</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['trainer_schedule'] as $schedules)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::create(2012, 1, 1, 0, 0, 0, 'America/Toronto')->addDays($schedules['day'])->format('l') }}
                                        </td>
                                        <td>{{ $schedules['class_start'] . '-' . $schedules['class_end'] }}</td>
                                        <td>{{ $schedules['grade'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center card-footer">
                           
                        </div>
                    </div>
                </div> -->
                --}}
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Students</h3>
                            <a href="{{ route('trainer.student_list') }}" class="btn btn-sm btn-primary btn-dark">View All Students</a>
                        </div>
                        <!-- /.card-header -->
                        <div class="p-0 card-body">
                            <div class="ListView">
                            <ul class="list-group">
                                @forelse ($data['students'] as $students)
                                <li class="list-group-item ">
                                    <div class="row">
                                        <div class="col-md-2">
                                            @if ($students['image'] != '' && $students['image'] != 'no_image')
                                                @php
                                                    $stud_profie_pic = asset($students['image']);
                                                    if ($students['school']['tenant_id'] && !str_contains($students['image'], 'tenants/')) {
                                                        $stud_profie_pic = asset('tenants/'.$students['image']);
                                                    }
                                                @endphp
                                                <img class="ml-0 mr-2 profile-user-img img-fluid img-circle"
                                                    style="height: 64px;width: 64px;object-fit: cover;"
                                                    src="{{ $stud_profie_pic }}" />
                                            @else
                                                <img src="{{ asset('img/default_image.png') }}"
                                                    class="mr-2 img-circle img-size-64" />
                                            @endif
                                        </div>
                                        <div class="col-md-8">
                                            <a class="users-list-name" href="{{ route('trainer.student_view', $students['id']) }}">{{ $students['name'] }}</a>
                                        </div>
                                    </div>
                                </li>
                                @empty
                                @endforelse
                            </ul>
                            </div>
                            
                            <!-- /.users-list -->
                        </div>
                        <!-- /.card-body -->
                        <!-- <div class="text-center card-footer">
                            
                        </div> -->
                        <!-- /.card-footer -->
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="h-100 d-flex flex-column">
                        <!-- <div class="card h-50">
                            <div class="card-header">
                                <h3 class="card-title">Training Hours (Start-To-Date)</h3>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li>{{ $totalHour }} hours</li>
                                </ul>
                            </div>
                        </div> -->
    
                        <div class="card flex-fill">
                            <div class="card-header">
                                <h3 class="card-title">
                                    To Do List
                                </h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <ul class="todo-list ui-sortable" data-widget="todo-list">
                                    @foreach ($data['all_todo'] as $all_todos)
                                    <li>
                                        <!-- drag handle -->
                                        <span class="handle ui-sortable-handle">
                                            <i class="fas fa-ellipsis-v"></i>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </span>
                                        <!-- checkbox -->
                                        <div class="ml-2 icheck-primary d-inline">
                                            <input type="checkbox" class="todo_checked" value="{{ $all_todos->id }}"
                                                name="todo1" id="todoCheck{{ $all_todos->id }}" @if ($all_todos->todo_done
                                            == 1) checked @endif>
                                            <label for="todoCheck{{ $all_todos->id }}"></label>
                                        </div>
                                        <!-- todo text -->
                                        <span class="text">{{ $all_todos->todo_name }}</span>
                                        <!-- Emphasis label -->
                                        <!-- <small class="badge badge-danger"><i class="far fa-clock"></i> 2 mins</small> -->
                                        <!-- General tools such as edit or delete-->
                                        <div class="tools">
                                            <a href="javascript:void(0)" type="button" class="btn btn-sm iconBtn btn-warning todo_edit" data-id="{{ $all_todos->id }}">
                                                <i class="material-icons">edit</i>
                                            </a>

                                            <a href="{{ route('trainer.todo_delete', $all_todos->id) }}" type="button" class="btn btn-sm iconBtn btn-danger" >
                                                <i class="material-icons">delete</i>
                                            </a>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            <!-- /.card-body -->
                            <div class="clearfix card-footer">
                                <a href="{{ route('trainer.todo_index') }}">View All Todo</a>
                                <button type="button" class="float-right btn btn-sm btn-primary" data-toggle="modal"
                                    data-target="#exampleModal"><i class="material-icons">add</i> Add item
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- <div class="col-md-12 mt-3">
                    
                </div> -->
                
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add To Do</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('trainer.todo_insert') }}" method="post" id="todo_submit">
                @csrf
                <div class="modal-body">
                    <input type="text" class="form-control" name="todo_name" placeholder="Name">
                    <span id="todo_error" class="text-danger"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Todo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('trainer.todo_update') }}" method="post" id="todo_update">
                @csrf
                <div class="modal-body">
                    <input type="hidden" class="form-control" name="todo_id" id="todo_id_show">
                    <input type="text" class="form-control" name="todo_name" placeholder="Name" id="todo_name_show">
                    <span id="todo_error_new" class="text-danger"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$('#todo_success').hide();
//Trainer assign-------------------------
$('#todo_submit').submit(function(e) {
    e.preventDefault();
    var url = $(this).attr('action');
    var request = $(this).serialize();
    var custom_data = request;
    $.ajax({
        url: url,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'post',
        async: false,
        data: custom_data,
        dataType: 'JSON',
        success: function(data) {
            if (data.error) {
                $('#todo_error').html(data.error.todo_name[0]);
            } else {
                $('#exampleModal').modal('hide');
                $('#todd_success').addClass('alert alert-success');
                $('#todo_success').html(data.success);
                $('#todo_success').show();

                window.location.reload()
            }
        }
    });
});

$('.todo_edit').click(function(e) {
    var id = $(this).attr('data-id');
    $('#editModal').modal('show');

    $.ajax({
        type: 'get',
        url: "{{ route('trainer.todo_edit') }}",
        data: {
            id: id,
        },
        success: function(data) {
            var data = JSON.parse(data);
            $('#todo_name_show').val(data.todo_name);
            $('#todo_id_show').val(data.id);
        }
    });
});

//Today Update code---------------
$('#todo_update').submit(function(e) {
    e.preventDefault();
    var url = $(this).attr('action');
    var request = $(this).serialize();
    var custom_data = request;
    $.ajax({
        url: url,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        type: 'post',
        async: false,
        data: custom_data,
        dataType: 'JSON',
        success: function(data) {
            if (data.error) {
                $('#todo_error_new').html(data.error.todo_name[0]);
            } else {
                $('#editModal').modal('hide');
                $('#todo_success').html(data.success);
                $('#todo_success').show();

                window.location.reload()
            }
        }
    });
});

$('.todo_checked').click(function() {
    var id = $(this).val();
    if ($(this).is(':checked')) {
        var action = 'checked';
    } else {
        var action = 'unchecked';
    }

    $.ajax({
        type: "POST",
        url: "{{ route('trainer.todo_check') }}",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            "id": id,
            "action": action,
        },
        dataType: 'JSON',
        success: function(data) {
            window.location.reload();
        },
    });

});
</script>
@endsection