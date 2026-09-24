@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Edit Allocated Trainer</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Edit Allocated Trainer</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->
        
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid ">
                <form action="{{ route('backend.trainer_allocation.update') }}" method="POST" enctype="multipart/form-data" class="card">
                    @if (session()->has('error'))
                        <div class="alert alert-danger" style="text-align: center;">
                            {{ session()->get('error') }}
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Edit Allocated Trainer</h4>
                                    <div class="card-tools">
                                        <a href="{{ route('backend.trainerallocation.trainerallocation') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                                    </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body table-responsive">
                                    @csrf
                                    <div class="form-group">
                                        <label>Trainer</label>
                                        <select class="form-control" name="trainer" id="trainer_id" required>
                                            <option value="">Select Trainer</option>
                                            @foreach ($trainer_list as $trainer)
                                                <option value="{{ $trainer->id }}" @if($trainer_allocation_data->trainer_id == $trainer->id) selected @endif>{{ $trainer->trainer_name }}</option>
                                            @endforeach
                                        </select>    
                                        @error('trainer')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror                        
                                    </div>
                                    <div class="form-group">
                                        <label>School</label>
                                        <select class="form-control" name="school" id="school_id" required disabled>
                                            <option value="">Select School</option>
                                        </select>
                                        @error('school')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror                        
                                    </div>
                                    <div class="form-group">
                                        <label>School Batch</label>
                                        <select class="form-control" name="school_batch" id="school_batch_id" required disabled>
                                            <option value="">Select School Batch</option>
                                        </select>
                                        @error('school_batch')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror                        
                                    </div>
                                    <input type="hidden" name="trainer_allocation_id" value="{{$trainer_allocation_data->id}}">
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary btn-stream-submit">Submit</button>
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
            const $trainer = $("#trainer_id");
            const $school = $("#school_id");
            const $batch = $("#school_batch_id");
            const selectedSchoolId = "{{ $trainer_allocation_data->school_id }}";
            const selectedBatchId = "{{ $trainer_allocation_data->school_batch_id }}";

            function resetSchoolOptions() {
                $school.prop('disabled', true).empty().append('<option value="">Select School</option>');
            }

            function resetBatchOptions() {
                $batch.prop('disabled', true).empty().append('<option value="">Select School Batch</option>');
            }

            function loadBatches(schoolId, batchId) {
                if (!schoolId) {
                    resetBatchOptions();
                    return;
                }

                $.ajax({
                    url: "{{ route('backend.getAllSchoolBatch') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        resetBatchOptions();
                    },  
                    data: {
                        school_id: schoolId,
                    },
                    method: "POST",
                    success: function(res) {
                        if (typeof res === 'string') {
                            res = $.parseJSON(res);
                        }
                        if(res.status == 'success' && !$.isEmptyObject(res.data)) {
                            let batch_list = '<option value="">Select School Batch</option>';
                            $.each(res.data, function(index, value) {
                                const selected = String(value.id) === String(batchId) ? 'selected' : '';
                                batch_list += `<option value="${value.id}" ${selected}>${value.batch_name}</option>`;
                            });
                            $batch.html(batch_list).prop('disabled', false);
                        }
                    }
                });
            }

            function loadSchools(trainerId, schoolId, batchId) {
                if (!trainerId) {
                    resetSchoolOptions();
                    resetBatchOptions();
                    return;
                }

                $.ajax({
                    url: "{{ route('backend.getSchoolsByTrainer') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        trainer_id: trainerId,
                    },
                    method: "POST",
                    success: function(res) {
                        if (res.status === 'success' && !$.isEmptyObject(res.data)) {
                            let schoolOptions = '<option value="">Select School</option>';
                            $.each(res.data, function(index, value) {
                                const selected = String(value.id) === String(schoolId) ? 'selected' : '';
                                schoolOptions += `<option value="${value.id}" ${selected}>${value.school_name}</option>`;
                            });
                            $school.html(schoolOptions).prop('disabled', false);

                            if (schoolId) {
                                loadBatches(schoolId, batchId);
                            }
                        }
                    }
                });
            }

            $trainer.on("change", function () {
                resetSchoolOptions();
                resetBatchOptions();
                loadSchools($(this).val(), null, null);
            });

            $("#school_id").change(function () {
                const schoolId = $(this).val();
                loadBatches(schoolId, null);
            });

            const initialTrainerId = $trainer.val();
            if (initialTrainerId) {
                loadSchools(initialTrainerId, selectedSchoolId, selectedBatchId);
            } else {
                resetSchoolOptions();
                resetBatchOptions();
            }
        });

        
    </script>
@endsection
