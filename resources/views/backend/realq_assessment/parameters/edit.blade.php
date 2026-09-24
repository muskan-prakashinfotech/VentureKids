@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content">
            <div class="mb-5 container-fluid">
                <form action="{{ route('backend.realqassessment.parameters.update') }}" method="POST" class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Parameter</h4>
                        <a href="{{ route('backend.realqassessment.parameters.index') }}" class="btn btn-warning float-right">
                            <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                        </a>
                    </div>
                    <div class="card-body table-responsive">
                        @csrf
                        <div class="form-group">
                            <label for="name">Parameter Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" placeholder="Parameter Name" value="{{ old('name', $parameter->name) }}" required>
                            @error('name')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="parameter_description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="parameter_description" name="description"
                                rows="4" placeholder="Description" required>{{ old('description', $parameter->description) }}</textarea>
                            @error('description')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <input type="hidden" name="parameter_id" value="{{ $parameter->id }}">
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-stream-submit">Submit</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
