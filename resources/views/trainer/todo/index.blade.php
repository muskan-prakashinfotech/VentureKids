@extends('backend.layouts.app')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="pageTitle">
        <h2>To Do List</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">To Do List</li>
        </ol>
    </div>

    <!-- Main content -->
    <section>
        <div class="alert alert-success" id="todo_success"></div>
        @if (session()->has('success'))
        <div class="alert alert-danger" style="text-align: center;">
            {{ session()->get('message') }}
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">To Do List Details</h3>
                <a href="{{ route('trainer.dashboard') }}" class="btn btn-sm btn-warning">
                    <i class="material-icons">west</i>
                    Back
                </a>
            </div>

            <div class="card-body">
                <ul class="todo-list ui-sortable" data-widget="todo-list">
                    @foreach ($all_todo as $all_todos)
                    <li>
                        <!-- drag handle -->
                        <span class="handle ui-sortable-handle">
                            <i class="fas fa-ellipsis-v"></i>
                            <i class="fas fa-ellipsis-v"></i>
                        </span>
                        <!-- checkbox -->
                        <div class="ml-2 icheck-primary d-inline">
                            <input type="checkbox" class="todo_checked" value="{{ $all_todos->id }}" name="todo1"
                                id="todoCheck{{ $all_todos->id }}" @if ($all_todos->todo_done == 1) checked @endif>
                            <label for="todoCheck{{ $all_todos->id }}"></label>
                        </div>
                        <!-- todo text -->
                        <span class="text">{{ $all_todos->todo_name }}</span>
                        <!-- Emphasis label -->
                        <!-- <small class="badge badge-danger"><i class="far fa-clock"></i> 2 mins</small> -->
                        <!-- General tools such as edit or delete-->
                        <div class="tools">
                            <a href="javascript:void(0)" type="button" class="btn btn-sm iconBtn btn-warning todo_edit"
                            data-id="{{ $all_todos->id }}">
                                <i class="material-icons">edit</i>
                            </a>

                            <a href="{{ route('trainer.todo_delete', $all_todos->id) }}" type="button" class="btn btn-sm iconBtn btn-danger">
                                <i class="material-icons">delete</i>
                            </a>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            <!-- /.card-body -->
            <div class="clearfix card-footer">
                <button type="button" class="float-right btn btn-primary" data-toggle="modal"
                    data-target="#exampleModal"><i class="material-icons">add</i> Add item</button>
            </div>
        </div>
    </section>
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