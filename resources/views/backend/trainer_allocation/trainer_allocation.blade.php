@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Trainer Allocation List</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <!-- <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Student Stream List</li>
                        </ol> -->
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->


        
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        @if (session()->has('success'))
                            <div class="alert alert-success" style="text-align: center;">
                                {{ session()->get('success') }}
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger" style="text-align: center;">
                                {{ session()->get('error') }}
                            </div>
                        @endif
                        <div class="card">
                            <div class="card-header">
                            <a href="{{ route('backend.trainer_allocation.create') }}" class="btn btn-primary">Allocate Trainer</a>
                            <div class="card-tools">
                                <a href="{{  route('backend.dashboard') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                            </div>
                            </div>
                            <div class="card-body table-responsive">
                                <table id="trainer_allocation" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>School</th>
                                            <th>Tainer </th>
                                            <th>Batch Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                            
                        </div>
                    </div>
                </div>
            
            </div>
        </section>
    </div>

    <script>
        $(document).ready(function() {
            var table = $('#trainer_allocation').DataTable({
                //order: [[ 0, 'DESC']],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('backend.trainer_allocation_list_datatable')}}",
                    type: "get",
                    dataType: 'JSON',
                    data: function(d) {
                        // d.school_id = $('#select_school').val(),
                        // d.grade_id = $('#select_grade').val()
                    }
                },
                columns: [{
                        data: 'get_school.school_name',
                        name: 'get_school.school_name'
                    },
                    {
                        data: 'get_trainer.trainer_name',
                        name: 'get_trainer.trainer_name'
                    },
                    {
                        data: 'get_batch.batch_name',
                        name: 'get_batch.batch_name'
                    },
                    {
                        "data": null,
                        "render": function ( data, type, row, meta ) {
                            var edit_link = `{{ route('backend.trainer_allocation.edit', 'random-id') }}`;
                            edit_link = edit_link.replace("random-id", row.id);
                            var del_link = `{{ route('backend.trainer_allocation.delete', 'random-id') }}`;
                            del_link = del_link.replace("random-id", row.id);
                            return `<a href="${edit_link}" class="btn btn-success"><i class="fas fa-edit"></i></a>
                            <form action="${del_link}">
                                @csrf
                                @method('DELETE')
                                <button onclick="deleteTrainerAllocation(event, this)" type="button"
                                    class="btn btn-danger">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>`;
                        }
                    },
                ]
            });
        });
        function deleteTrainerAllocation(e, target) {
            e.preventDefault();
            var form = $(target).parents('form');
            swal({
                    title: "Are you sure?",
                    text: "Trainer will not able to access students's either assignments or projects. Are you suere want to delete this trainer allocation!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        swal("Success! Trainer allocation has been deleted!", {
                            icon: "success",
                        });
                        form.submit();
                    } else {
                        swal("Great! Trainer allocation records are safe.");
                    }
                });
        }
    </script>
@endsection
