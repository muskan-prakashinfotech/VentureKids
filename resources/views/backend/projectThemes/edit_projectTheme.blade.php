@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
            <div class="mb-5 container-fluid ">
                <form action="{{ route('backend.projectThemeupdate.projectThemeUpdate',$theme->id) }}" method="POST" enctype="multipart/form-data" class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Project Theme</h4>
                        <a href="{{ route('backend.projectThemelist.projectThemeList') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                                <label for="student_grade_id">Select Grade</label>
                                <select class="form-control" name="student_grade_id" required>
                                    @foreach($grades as $grade)
                                    <option value="{{ $grade->id }}" {{ $theme->student_grade_id == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                                    @endforeach
                                </select>
                                @error('student_grade_id')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                        </div>
                        <div class="form-group">
                                <label for="project_theme_name">Project Theme Name</label>
                                <input type="text" class="form-control" id="project_theme_name" name="project_theme_name"  value="{{ old('name', $theme->project_theme_name) }}" required>
                                @error('project_theme_name')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                                <div>
                                    <label class="mr-3">
                                        <input type="radio" name="status" value="1" {{ $theme->status == 1 ? 'checked' : '' }}> Active
                                    </label>
                                    <label>
                                        <input type="radio" name="status" value="0" {{ $theme->status == 0 ? 'checked' : '' }}> Inactive
                                    </label>
                                </div>
                                @error('status')
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
