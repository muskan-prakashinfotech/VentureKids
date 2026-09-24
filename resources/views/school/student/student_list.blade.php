@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="pageTitle">
        <h2>Students</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">{{ __('admin.home') }}</a></li>
            <li class="breadcrumb-item active">Student</li>
        </ol>
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

    @if (Session::has('capacity_failed'))
    <div class="alert alert-danger">
        {{ Session::get('capacity_failed') }}
    </div>
    @endif
    @php
        $canImportStudent = empty($capacityState['school_at_capacity']) && empty($capacityState['partner_at_capacity']);
        $isPartnerSchool = !empty($school) && (($school->created_type ?? 'admin') === 'partner');
    @endphp
    
    <!-- Main content -->
    <section>
        <div class="container-fluid p-0">
            <div class="card">
                <div class="card-header">
                    <div class="row w-100 justify-content-between align-items-center">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    @if(!$isPartnerSchool)
                                        <a @if($canImportStudent) href="{{ route('school.student-add') }}" @else onclick="validationMsg();" @endif class="btn btn-sm btn-primary">
                                            <i class="mr-1 fa fa-plus"></i><span>Add Student</span>
                                        </a>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <a @if($canImportStudent) href="{{ route('school.student-import') }}" @else onclick="validationMsg();" @endif class="btn btn-sm btn-primary">
                                        <i class="mr-1 fa fa-plus"></i><span>Import Students</span>
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control customselect" id="select_grade">
                                        <option value="">---Select Level---</option>
                                        @foreach ($grades as $key => $all_grade)
                                        <option value="{{ $all_grade->id }}">{{ $all_grade->grade }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{route('school.dashboard')}}" class="btn btn-sm btn-warning float-right"> <i
                                            class="material-icons">west</i> Back</a>
                                </div>
                            </div>
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
                                <th>Level</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
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
            url: "{{ route('school.student_list_datatable') }}",
            type: "get",
            dataType: 'JSON',
            data: function(d) {
                d.grade_id = $('#select_grade').val()
            }
        },
        columns: [{
                data: 'DT_RowIndex',
                name: '',
                orderable: false,
                searchable: false
            }, {
                data: 'std_user.name',
                name: 'std_user.name'
            },
            {
                data: 'grade_id',
                name: 'grade_id'
            },
            {
                data: 'action',
                name: 'action',
                orderable: true,
                searchable: true
            },
        ],

    });


    $('#select_grade').change(function() {
        table.draw();
    });
});

function validationMsg() {
    alert('Import exceeds allowed student/license limit');
}
</script>
@endsection
