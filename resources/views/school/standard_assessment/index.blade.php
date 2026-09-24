@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="pageTitle">
        <h2>Standard Assessment</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Standard Assessment</li>
        </ol>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Standard Assessment Settings</h3>
                </div>
                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success">{{ session('message') }}</div>
                    @endif

                    @if (session()->has('message1'))
                        <div class="alert alert-danger">{{ session('message1') }}</div>
                    @endif

                    <form method="POST" action="{{ route('school.standard-assessment.update') }}">
                        @csrf
                        <div class="form-group">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="mb-0" for="is_active_switch">Enable Standard Assessment</label>
                                <input type="hidden" name="is_active" value="0">
                                <div class="custom-control custom-switch m-0">
                                    <input type="checkbox" class="custom-control-input" id="is_active_switch" name="is_active" value="1"
                                           {{ (int) old('is_active', $school->standard_assessment_enabled ?? 0) === 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active_switch"></label>
                                </div>
                            </div>
                            @error('is_active')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        <div id="date_range_section">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control"
                                       value="{{ old('start_date', $school->standard_assessment_enabled_from ?? '') }}">
                                @error('start_date')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control"
                                       value="{{ old('end_date', $school->standard_assessment_enabled_to ?? '') }}">
                                @error('end_date')
                                    <strong class="text-danger">{{ $message }}</strong>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('is_active_switch');
        const dateSection = document.getElementById('date_range_section');

        function syncDateSection() {
            dateSection.style.display = toggle.checked ? 'block' : 'none';
        }

        toggle.addEventListener('change', syncDateSection);
        syncDateSection();
    });
</script>
@endsection

