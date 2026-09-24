@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
            <div class="mb-5 container-fluid ">
                <form action="{{ route('backend.mindset.store') }}" method="POST" enctype="multipart/form-data" class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Mindset</h4>
                        <a href="{{ route('backend.mindset_list') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        @csrf
                        <div class="form-group">
                            <label for="mindset_name">Mindset Name</label>
                            <input type="text" class="form-control @error('mindset_name') is-invalid @enderror" id="mindset_name"
                                name="mindset_name" placeholder="Mindset Name" value="{{ old('mindset_name') }}" required>
                            @error('mindset_name')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-stream-submit">Submit</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
