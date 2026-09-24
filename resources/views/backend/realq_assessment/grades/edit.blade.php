@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content">
            <div class="mb-5 container-fluid">
                <form action="{{ route('backend.realqassessment.grades.update') }}" method="POST" class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Grade</h4>
                        <a href="{{ route('backend.realqassessment.grades.index') }}" class="btn btn-warning float-right">
                            <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                        </a>
                    </div>
                    <div class="card-body table-responsive">
                        @csrf
                        <div class="form-group">
                            <label for="name">Grade Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" placeholder="Grade Name" value="{{ old('name', $grade->name) }}" required>
                            @error('name')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <input type="hidden" name="grade_id" value="{{ $grade->id }}">
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-stream-submit">Submit</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

