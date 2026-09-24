@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content">
            <div class="mb-5 container-fluid">
                <form action="{{ route('backend.realqassessment.subjects.update') }}" method="POST" class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Subject</h4>
                        <a href="{{ route('backend.realqassessment.subjects.index') }}" class="btn btn-warning float-right">
                            <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                        </a>
                    </div>
                    <div class="card-body table-responsive">
                        @csrf
                        <div class="form-group">
                            <label for="name">Subject Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" placeholder="Subject Name" value="{{ old('name', $subject->name) }}" required>
                            @error('name')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-stream-submit">Submit</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

