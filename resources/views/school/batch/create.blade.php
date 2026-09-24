@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <div class="pageTitle">
            <h2>Add Batch</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('school.batch-list') }}">School Batch Management</a></li>
                <li class="breadcrumb-item active">Add Batch</li>
            </ol>
        </div>
        <!-- Main content -->
        <section class="content">
            <div class="mb-5 container-fluid ">
                <form action="{{ route('school.batch.store') }}" method="POST" enctype="multipart/form-data">
                    <div class="card">
                        <div class="card-header">
                            <h4>Add Batch</h4>
                            <div class="card-tools">
                                <a href="{{ route('school.batch-list') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                            </div>
                        </div>
                        <div class="card-body table-responsive">
                            @csrf
                            <div class="form-group">
                                <label for="title">Name</label>
                                <input type="text" class="form-control @error('batch_name') is-invalid @enderror" id="batch_name"
                                    name="batch_name" placeholder="Batch Name" value="{{ old('batch_name') }}" required>
                                @error('batch_name')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-stream-submit">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
