@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">How It Works</h1> 
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">How It Works</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
          <div class="card">
        <div class="card-body">
            @if(session('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif
            <form method="POST" action="{{ route('backend.storevideo.storeVideo') }}" enctype="multipart/form-data">
                @csrf
                <div class="card shadow-sm">
                    <div class="card-body">
                            <div class="form-group">
                                <label for="video_url">Video Link</label>
                                <input type="url" class="form-control" id="video_url" name="video_url" placeholder="Enter YouTube Video Link" value="{{ $howItWorks->video_path ?? '' }}" required>
                                @error('video_url')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                        <button type="submit" class="btn btn-info mt-3 text-white" id="btnSubmit">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection