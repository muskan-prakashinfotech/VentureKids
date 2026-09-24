@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content">
            <div class="mb-5 container-fluid ">
                <form action="{{ route('backend.projectQuestionstore.projectQuestionStore', $section->id) }}" method="POST" class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add Question — {{ $section->section_title }}</h4>
                        <a href="{{ route('backend.projectQuestionlist.projectQuestionList', $section->id) }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                    </div>
                    <div class="card-body">
                        @csrf
                        <div class="form-group">
                            <label for="field_text">Field Text</label>
                            <textarea class="form-control" id="field_text" name="field_text" rows="2" placeholder="Enter question text" required>{{ old('field_text') }}</textarea>
                            @error('field_text')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="help_text">Help Text</label>
                            <textarea class="form-control" id="help_text" name="help_text" rows="2" placeholder="Enter help text (optional)">{{ old('help_text') }}</textarea>
                            @error('help_text')
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
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Is Required</label>
                                <div>
                                    <label class="mr-3">
                                        <input type="radio" name="is_required" value="1" checked> Yes
                                    </label>
                                    <label>
                                        <input type="radio" name="is_required" value="0"> No
                                    </label>
                                </div>
                                @error('is_required')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                            <div class="form-group col-md-6">
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
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-stream-submit">Submit</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
