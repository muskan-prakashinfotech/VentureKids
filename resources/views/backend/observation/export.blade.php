@extends('backend.layouts.app')

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="pageTitle">
        <h2>Export Observation Data</h2>
        <!-- <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">Home</a></li>
            <li class="breadcrumb-item active">Students</li>
        </ol> -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section>
        <div class="container-fluid p-0">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('backend.observation.downloadData') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="school">Select School</label>
                            <select class="form-control @error('school') is-invalid @enderror" id="school" name="school" required>
                                <option value="">---Select School---</option>
                                @foreach ($school_list as $school)
                                <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                                @endforeach
                            </select>
                            @error('school')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-observation-export">Export</button>
                        </div>
                    </form>
                </div>
                <!-- /.card-body -->
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>



<script>
    $("#school").change(function () {
        if(parseInt(this.value)) {
            $(this).removeClass('is-invalid');
            $('.text-danger').remove();
        }
    });
</script>
@endsection