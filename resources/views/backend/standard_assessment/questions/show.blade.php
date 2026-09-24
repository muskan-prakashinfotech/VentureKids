@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="mb-5 container-fluid">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">View Question</h4>
                    <a href="{{ route('backend.standard_assessment.questions.list') }}" class="btn btn-warning float-right">
                        <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select class="form-control" id="category_id" disabled>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $question->category_id == $category->id ? 'selected' : '' }}>
                                    {{ optional($category->grade)->grade }} - {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="question_text">Question</label>
                        <textarea class="form-control" id="question_text" rows="3" readonly>{{ $question->question_text }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Option A</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $question->option_a }}" readonly>
                            <div class="input-group-append">
                                <input type="text" class="form-control" value="{{ $question->option_a_score }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Option B</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $question->option_b }}" readonly>
                            <div class="input-group-append">
                                <input type="text" class="form-control" value="{{ $question->option_b_score }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Option C</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $question->option_c }}" readonly>
                            <div class="input-group-append">
                                <input type="text" class="form-control" value="{{ $question->option_c_score }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Option D</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $question->option_d }}" readonly>
                            <div class="input-group-append">
                                <input type="text" class="form-control" value="{{ $question->option_d_score }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <div>
                            <label class="mr-3">
                                <input type="radio" {{ (int) $question->active === 1 ? 'checked' : '' }} disabled> Active
                            </label>
                            <label>
                                <input type="radio" {{ (int) $question->active === 0 ? 'checked' : '' }} disabled> Inactive
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
