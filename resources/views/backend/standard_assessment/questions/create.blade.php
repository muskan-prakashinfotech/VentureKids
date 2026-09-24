@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="mb-5 container-fluid">
            <form action="{{ route('backend.standard_assessment.questions.store') }}" method="POST" class="card">
                <div class="card-header">
                    <h4 class="card-title">Add Question</h4>
                    <a href="{{ route('backend.standard_assessment.questions.list') }}" class="btn btn-warning float-right">
                        <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    @csrf
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ optional($category->grade)->grade }} - {{ $category->category_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="question_text">Question</label>
                        <textarea class="form-control" id="question_text" name="question_text" rows="3" required>{{ old('question_text') }}</textarea>
                        @error('question_text')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="option_a">Option A</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="option_a" name="option_a" value="{{ old('option_a') }}" required>
                            <div class="input-group-append">
                                <select class="form-control" name="option_a_score" required>
                                    <option value="">Weightage</option>
                                    <option value="0" {{ old('option_a_score') == '0' ? 'selected' : '' }}>0</option>
                                    <option value="1" {{ old('option_a_score') == '1' ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ old('option_a_score') == '2' ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ old('option_a_score') == '3' ? 'selected' : '' }}>3</option>
                                    <option value="4" {{ old('option_a_score') == '4' ? 'selected' : '' }}>4</option>
                                </select>
                            </div>
                            <!-- <div class="input-group-append">
                                <span class="input-group-text">
                                    <input type="radio" name="correct_option" value="a" {{ old('correct_option') == 'a' ? 'checked' : '' }}>
                                    <span class="ml-1">Correct</span>
                                </span>
                            </div> -->
                        </div>
                        @error('option_a')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                        @error('option_a_score')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="option_b">Option B</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="option_b" name="option_b" value="{{ old('option_b') }}" required>
                            <div class="input-group-append">
                                <select class="form-control" name="option_b_score" required>
                                    <option value="">Weightage</option>
                                    <option value="0" {{ old('option_b_score') == '0' ? 'selected' : '' }}>0</option>
                                    <option value="1" {{ old('option_b_score') == '1' ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ old('option_b_score') == '2' ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ old('option_b_score') == '3' ? 'selected' : '' }}>3</option>
                                    <option value="4" {{ old('option_b_score') == '4' ? 'selected' : '' }}>4</option>
                                </select>
                            </div>
                            <!-- <div class="input-group-append">
                                <span class="input-group-text">
                                    <input type="radio" name="correct_option" value="b" {{ old('correct_option') == 'b' ? 'checked' : '' }}>
                                    <span class="ml-1">Correct</span>
                                </span>
                            </div> -->
                        </div>
                        @error('option_b')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                        @error('option_b_score')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="option_c">Option C</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="option_c" name="option_c" value="{{ old('option_c') }}" required>
                            <div class="input-group-append">
                                <select class="form-control" name="option_c_score" required>
                                    <option value="">Weightage</option>
                                    <option value="0" {{ old('option_c_score') == '0' ? 'selected' : '' }}>0</option>
                                    <option value="1" {{ old('option_c_score') == '1' ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ old('option_c_score') == '2' ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ old('option_c_score') == '3' ? 'selected' : '' }}>3</option>
                                    <option value="4" {{ old('option_c_score') == '4' ? 'selected' : '' }}>4</option>
                                </select>
                            </div>
                            <!-- <div class="input-group-append">
                                <span class="input-group-text">
                                    <input type="radio" name="correct_option" value="c" {{ old('correct_option') == 'c' ? 'checked' : '' }}>
                                    <span class="ml-1">Correct</span>
                                </span>
                            </div> -->
                        </div>
                        @error('option_c')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                        @error('option_c_score')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="option_d">Option D</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="option_d" name="option_d" value="{{ old('option_d') }}" required>
                            <div class="input-group-append">
                                <select class="form-control" name="option_d_score" required>
                                    <option value="">Weightage</option>
                                    <option value="0" {{ old('option_d_score') == '0' ? 'selected' : '' }}>0</option>
                                    <option value="1" {{ old('option_d_score') == '1' ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ old('option_d_score') == '2' ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ old('option_d_score') == '3' ? 'selected' : '' }}>3</option>
                                    <option value="4" {{ old('option_d_score') == '4' ? 'selected' : '' }}>4</option>
                                </select>
                            </div>
                            <!-- <div class="input-group-append">
                                <span class="input-group-text">
                                    <input type="radio" name="correct_option" value="d" {{ old('correct_option') == 'd' ? 'checked' : '' }}>
                                    <span class="ml-1">Correct</span>
                                </span>
                            </div> -->
                        </div>
                        @error('option_d')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                        @error('option_d_score')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                    </div>
                    @error('correct_option')
                        <strong class="text-danger">{{ $message }}</strong>
                    @enderror

                    <div class="form-group">
                        <label>Status</label>
                        <div>
                            <label class="mr-3">
                                <input type="radio" name="active" value="1" {{ old('active', 1) == 1 ? 'checked' : '' }}> Active
                            </label>
                            <label>
                                <input type="radio" name="active" value="0" {{ old('active') === '0' ? 'checked' : '' }}> Inactive
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

