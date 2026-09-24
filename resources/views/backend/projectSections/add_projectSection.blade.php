@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content">
            <div class="mb-5 container-fluid ">
                <form action="{{ route('backend.projectSectionstore.projectSectionStore') }}" method="POST" class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Project Section</h4>
                        <a href="{{ route('backend.projectSectionlist.projectSectionList') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                    </div>
                    <div class="card-body">
                        @csrf
                        <div class="form-group">
                            <label for="section_title">Section Title</label>
                            <input type="text" class="form-control" id="section_title" name="section_title" placeholder="Enter Section Title" required>
                            @error('section_title')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" min="1" value="{{ old('display_order', $nextDisplayOrder) }}">
                            @error('display_order')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <div>
                                <label class="mr-3">
                                    <input type="radio" name="status" value="1" checked> Active
                                </label>
                                <label>
                                    <input type="radio" name="status" value="0"> Inactive
                                </label>
                            </div>
                            @error('status')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-stream-submit">Submit</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
