@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="mb-5 container-fluid">
            <form action="{{ route('backend.standard_assessment.categories.update', $category->id) }}" method="POST" class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Category</h4>
                    <a href="{{ route('backend.standard_assessment.categories.list') }}" class="btn btn-warning float-right">
                        <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="grade_id">Level</label>
                        <select class="form-control" id="grade_id" name="grade_id" required>
                            <option value="">Select Level</option>
                            @foreach($grades as $grade)
                                <option value="{{ $grade->id }}" {{ old('grade_id', $category->grade_id) == $grade->id ? 'selected' : '' }}>
                                    {{ $grade->grade }}
                                </option>
                            @endforeach
                        </select>
                        @error('grade_id')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="category_name">Category Name</label>
                        <input type="text" class="form-control" id="category_name" name="category_name"
                            value="{{ old('category_name', $category->category_name) }}" required>
                        @error('category_name')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <div>
                            <label class="mr-3">
                                <input type="radio" name="active" value="1" {{ old('active', $category->active) == 1 ? 'checked' : '' }}> Active
                            </label>
                            <label>
                                <input type="radio" name="active" value="0" {{ old('active', $category->active) == 0 ? 'checked' : '' }}> Inactive
                            </label>
                        </div>
                        @error('active')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

