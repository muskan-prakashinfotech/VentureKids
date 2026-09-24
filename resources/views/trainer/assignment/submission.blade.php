@extends('backend.layouts.app')

@section('content')
    <style>
        .card-body>.table>thead>tr>td,
        .card-body>.table>thead>tr>th {
            border-top-width: 2px;
        }
    </style>
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Assignment</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Assignment</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            {{ session()->get('success') }}
                        </div>
                    @endif
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>{{ 'Assignment Name:- ' . $student_communications->title }}</h5>
                                <h5>{{ 'Student Name:- ' . $submission->student->name }}</h5>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('trainer.assigment.show', array_merge(['student_communications' => $student_communications->id], request()->query())) }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                            </div>
                        </div>


                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        <div class="row">
                            <div class="col-md-6">
                                @if (!empty($submission->file))
                                    <iframe src="{{ asset($submission->file) }}" class="w-100"
                                        style="height: 500px"></iframe>
                                @elseif(!empty($submission->link))
                                    <iframe src="{{ $submission->link }}" class="w-100" style="height: 500px"></iframe>
                                @else
                                <button class="btn btn-primary">Assignment submitted manually!</button>
                                @endif
                                    
                            </div>
                            <div class="col-md-6">
                                <form
                                    action="{{ route('trainer.assigment.review', ['student_communications' => $student_communications, 'submission' => $submission]) }}"
                                    method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Trainer Feedback</label>
                                        <textarea name="feedback" id="" class="form-control" rows="3" required>{{ $submission->feedback }}</textarea>
                                        @error('feedback')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleInputPassword1">Possible Improvement</label>
                                        <textarea name="comment" class="form-control" id="" rows="3" required>{{ $submission->comment }}</textarea>
                                        @error('comment')
                                            <strong class="text-danger">{{ $message }}</strong>
                                        @enderror
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <button type="submit" class="btn btn-primary">Submit Review</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                    <!-- /.card-body -->
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
@endsection
