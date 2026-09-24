@extends('backend.layouts.app')

@section('content')
<style>
.card-body>.table>thead>tr>td,
.card-body>.table>thead>tr>th {
    border-top-width: 2px;
}
</style>
<div class="content-wrapper">
    <div class="pageTitle">
        <h2>Assignment</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Assignment</li>
        </ol>
    </div>

    <!-- Main content -->
    <section>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{ $student_communications->title }}</h5>
                <a href="{{ route('trainer.assigment.index', request()->query()) }}" class="btn btn-sm btn-warning">
                    <i class="material-icons">west</i>
                    Back
                </a>
            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Submitted Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissions as $submission)
                        @php
                        $isReviewed = !empty($submission->feedback) || !empty($submission->comment);
                        @endphp
                        <tr @if(empty($submission->feedback) && empty($submission->comment)) class="assignment-review-pending" @endif>
                            <td>{{ $submission->student->name }}</td>
                            <td>{{ $submission->created_at->format('l jS \\of F Y h:i:s A') }}</td>
                            <td>
                                <a href="{{ route('trainer.assigment.submission', array_merge(['student_communications' => $student_communications, 'submission' => $submission], request()->query())) }}"
                                    class="btn {{ $isReviewed ? 'btn-success' : 'btn-primary' }}">{{ $isReviewed ? 'Reviewed' : 'Review' }}</a>
                            </td>
                            {{-- <td>{{ $assigment->title }}</td>
                            <td>{{ $assigment->school->school_name }}</td>
                            <td>{{ $assigment->level->grade }}</td>
                            <td><a href="{{ route('trainer.assigment.show', $assigment->id) }}"
                                    class="btn btn-primary">Review</a></td> --}}
                        </tr>
                        @empty
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($submissions->count()){{ $submissions->links() }}@endif
            <!-- /.card-body -->
        </div>
    </section>
    <!-- /.content -->
</div>
@endsection