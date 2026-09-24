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
        <a href="{{ route('trainer.dashboard') }}" class="btn btn-sm btn-warning float-right">
            <i class="material-icons">west</i>
            Back
        </a>
    </div>

    <!-- Main content -->
    <section>
        @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session()->get('message') }}
        </div>
        @endif
        @if (Session::has('suspend_success'))
        <div class="alert alert-success">
            {{ Session::get('suspend_success') }}
        </div>
        @endif
        @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
        @endif

        <div class="card">
            <div class="card-header" style="border: none;">
                <div class="col-md-3">
                    <label for="school_id">School</label>
                    <select class="form-control" id="school_id">
                        @forelse ($schools as $school)
                        <option value="{{ $school->getSchool->id }}" @if (request()->school_id == $school->getSchool->id) selected
                            @endif>
                            {{ $school->getSchool->school_name }}
                        </option>
                        @empty
                        @endforelse
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="grade_id">Level</label>
                    <select class="form-control" id="grade_id">
                        <option value="">All</option>
                        @if(isset($filteredLevels['primary']))
                            <optgroup label="Levels">
                            @foreach ($filteredLevels['primary'] as $k => $level)
                                <option value="{{ $level['id'] }}" @if (request()->grade_id == $level['id']) selected @endif>{{ $level['grade'] }}</option>
                            @endforeach
                            </optgroup>
                        @endif
                        @if(isset($filteredLevels['add-ons']))
                            <optgroup label="Content Add-Ons">
                            @foreach ($filteredLevels['add-ons'] as $k => $level)
                                <option value="{{ $level['id'] }}" @if (request()->grade_id == $level['id']) selected @endif>{{ $level['grade'] }}</option>
                            @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('trainer.createAssignment') }}" class="btn btn-primary btn-sm mt-4">
                        <i class="material-icons">add</i>
                        Add Assignment
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Level</th>
                                <th colspan="2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($assigments as $assigment)
                            @php
                                $pending_review_count = 0;
                                if(!empty($assigment->submissions)) {
                                    $pending_review_count = $assigment->submissions->whereNull('feedback')->whereNull('comment')->count();
                                }
                            @endphp
                            <tr>
                                <td>{{ $assigment->title }}</td>
                                <td>{{ $assigment->level ? $assigment->level->grade : '' }}</td>
                                <td>
                                    <span class="position-relative">
                                    <a href="{{ route('trainer.assigment.show', array_merge(['student_communications' => $assigment->id], request()->query())) }}"
                                        class="btn btn-sm {{ (count($assigment->submissions) && !$pending_review_count) ? 'btn-success' : 'btn-primary' }}">
                                        {{ (count($assigment->submissions) && !$pending_review_count) ? 'Reviewed' : 'Review' }}
                                    </a>
                                    @if($pending_review_count) <span class="assignment-review-pending-badge">{{$pending_review_count}}</span> @endif
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('trainer.manualSubmission', $assigment->id) }}" class="btn btn-primary btn-sm">
                                        Manual Submit
                                    </a>
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($assigments->count())
                {{ $assigments->appends(request()->query())->links() }}
            @endif
            <!-- /.card-body -->
        </div>
    </section>
    <!-- /.content -->
</div>
<script>
$(document).ready(function() {
    $('#school_id').change(() => appendParams());
    $('#grade_id').change(() => appendParams());
    
    function appendParams() {
        let url = new URL(window.location.href);
        let params = new URLSearchParams(url.search);
        let school = $('#school_id').val();
        params.delete('school_id');
        if (school.length > 0) {
            params.append('school_id', school);
        }

        let grade_id = $('#grade_id').val();
        params.delete('grade_id');
        if (grade_id.length > 0) {
            params.append('grade_id', grade_id);
        }

        window.location.href = location.origin + location.pathname + "?" + params.toString();
    }
});
</script>
@endsection