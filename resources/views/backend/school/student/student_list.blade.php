@extends('backend.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">All Students - {{ $school->school_name }} </h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ $school->school_name }} All Students</li>
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
        @if (Session::has('capacity_failed'))
        <div class="alert alert-danger">
            {{ Session::get('capacity_failed') }}
        </div>
        @endif
        @php
            $canAddOrImportStudent = empty($capacityState['school_at_capacity']) && empty($capacityState['partner_at_capacity']);
        @endphp
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center flex-nowrap">
                            <div class="col-auto">
                                @if(!isPartnerUser())
                                    @if($canAddOrImportStudent)
                                        <a href="{{ route('backend.student-add', $data['id']) }}" class="btn btn-primary">
                                            <i class="mr-1 fa fa-plus"></i>
                                            <span>Add Student</span>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-primary" disabled>
                                            <i class="mr-1 fa fa-plus"></i>
                                            <span>Add Student</span>
                                        </button>
                                    @endif
                                @endif
                            </div>
                            <div class="col-auto">
                                @if($canAddOrImportStudent)
                                    <a href="{{ route('backend.student-import', $data['id']) }}" class="btn btn-primary">
                                        <i class="mr-1 fa fa-plus"></i>
                                        <span>Import Student</span>
                                    </a>
                                @else
                                    <button type="button" class="btn btn-primary" disabled>
                                        <i class="mr-1 fa fa-plus"></i>
                                        <span>Import Student</span>
                                    </button>
                                @endif
                            </div>
                            <div class="col-auto">
                                <select class="form-control" id="select_grade">
                                    <option value="">---Select Level---</option>
                                    @foreach ($grades as $key => $all_grade)
                                        <option value="{{ $all_grade['id'] }}">{{ $all_grade['grade'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2"></div>
                            <div class="col-auto d-flex ml-auto">
                                <a href="{{ route('backend.student-export', $data['id']) }}"
                                   class="btn btn-primary btn-sm mr-2"
                                   id="export-students-btn"
                                   title="Export Students"
                                   aria-label="Export Students">
                                    <i class="fa fa-download" aria-hidden="true"></i>
                                </a>
                                <a href="{{ route('backend.schoollist.schoolList') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        <table id="example_new" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
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
            $('#export-students-btn').on('click', function(e) {
                const $button = $(this);

                if ($button.hasClass('disabled')) {
                    e.preventDefault();
                    return false;
                }

                $button.addClass('disabled');
                $button.attr('aria-disabled', 'true');
                $button.css({
                    'pointer-events': 'none',
                    'opacity': '0.65'
                });
            });

            var table = $('#example_new').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('backend.student_list_datatable') }}",
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
                        data: 'email',
                        name: 'std_user.email' 
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
            $(document).on('click', '#deleteStudentFromAdmin', function(e) {
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
        function validationMsg() {
            alert('Import exceeds allowed student/license limit');
        }
    </script>
@endsection
