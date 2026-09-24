@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
            <div class="mb-5 container-fluid ">
                <form action="{{ route('backend.observation.store') }}" method="POST" enctype="multipart/form-data" class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Observation</h4>
                        <a href="{{ route('backend.observation_list') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        @csrf
                        <div class="form-group">
                            <label for="observation_name">Observation Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="observation_name"
                                name="name" placeholder="Observation Name" value="{{ old('name') }}" required>
                            @error('name')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select class="form-control @error('category') is-invalid @enderror" id="category" name="category" required>
                                <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select Category</option>
                                <option value="skill" {{ old('category') === 'skill' ? 'selected' : '' }}>Skill</option>
                                <option value="mindset" {{ old('category') === 'mindset' ? 'selected' : '' }}>Mindset</option>
                            </select>
                            @error('category')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="icon">Observation Icon</label>
                            <input type="file" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" accept="image/*" required>
                            @error('icon')
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
