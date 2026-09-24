@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
            <div class="mb-5 container-fluid ">
                <form action="{{ route('backend.skill.update') }}" method="POST" enctype="multipart/form-data" class="card">
                    <div class="card-header">
                        <h4 class="card-title">Edit Skill</h4>
                        <a href="{{ route('backend.skill_list') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        @csrf
                        <div class="form-group">
                            <label for="skill_name">Skill Name</label>
                            <input type="text" class="form-control @error('skill_name') is-invalid @enderror" id="skill_name"
                                name="skill_name" placeholder="Skill Name" value="{{ old('skill_name', $skillData->skill_name) }}" required>
                            @error('skill_name')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>
                        <input type="hidden" name="skill_id" value="{{$skillData->id}}">
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
