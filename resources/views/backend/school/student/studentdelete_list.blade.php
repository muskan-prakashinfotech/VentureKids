@extends('backend.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h3 class="m-0">Students Delete Request - {{ $schools[0]['school_name'] }} </h3>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ $schools[0]['school_name'] }} Students Delete Request</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        @if (Session::has('message'))
            <div class="alert alert-success">
                {{ Session::get('message') }}
            </div>
        @endif
        @if (Session::has('message1'))
            <div class="alert alert-danger">
                {{ Session::get('message1') }}
            </div>
        @endif
        @if (Session::has('message3'))
            <div class="alert alert-success">
                {{ Session::get('message3') }}
            </div>
        @endif
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <div class="card-tool">
                            <a href="{{ route('backend.schoollist.schoolList') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        <table id="example_new" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Level</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <script>
        $(document).ready(function() {
            var table = $('#example_new').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('backend.student_delete_list_datatable') }}",
                    type: "get",
                    dataType: 'JSON',
                    data: function(d) {
                        d.grade_id = $('#select_grade').val(),
                            d.school_id = <?php echo $data['id']; ?>
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: '',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'std_user.name',
                        name: 'std_user.name'
                    },
                    {
                        defaultContent: "-",
                        data: 'level.grade',
                        name: 'level.grade'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [0, 'asc']
                ],
            });

            $('#select_grade').change(function() {
                table.draw();
            });

            //delete student sweetalert
            $(document).on('click', '#deleteStudent', function(e) {
                e.preventDefault();
                var Id = $(this).attr('href');

                swal({
                        title: "Are you sure?",
                        text: "You want to delete this student!",

                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            swal("Success! Student has been deleted!", {
                                icon: "success",
                            });

                            window.location.href = Id;

                        } else {
                            swal("Your file is safe!");
                        }

                    });
            });
        });
    </script>
@endsection
