@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="pageTitle">
        <h2>RealQ Assessment</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">RealQ Assessment</li>
        </ol>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">RealQ Assessment Settings</h3>
                </div>
                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success">{{ session('message') }}</div>
                    @endif

                    @if (session()->has('message1'))
                        <div class="alert alert-danger">{{ session('message1') }}</div>
                    @endif

                    <div class="alert alert-light border">
                        <strong>Assigned Scope:</strong>
                        <div>
                            <span>Grades:</span>
                            <span>
                                @if (!empty($assignedGrades) && $assignedGrades->count())
                                    {{ $assignedGrades->pluck('name')->implode(', ') }}
                                @else
                                    Not set
                                @endif
                            </span>
                        </div>
                        <div>
                            <span>Board:</span>
                            <span>{{ $assignedBoard ? $assignedBoard->name : 'Not set' }}</span>
                        </div>
                    </div>
                    <div id="realq_date_range_section" class="alert alert-light border">
                            <strong>Assessment Duration:</strong>
                            <div>
                                <span>Start Date:</span>
                                <span>{{ $school->realq_assessment_enabled_from ?: 'Not set' }}</span>
                            </div>
                            <div>
                                <span>End Date:</span>
                                <span>{{ $school->realq_assessment_enabled_to ?: 'Not set' }}</span>
                            </div>
                        </div>

                    @if(!empty($latestReport))
                        <div class="alert alert-light border d-flex align-items-center justify-content-between flex-wrap">
                            <div>
                                <strong>Latest RealQ Report:</strong>
                                <div>Generated report is available for download.</div>
                            </div>
                            <a href="{{ route('school.realq-assessment.report.download') }}" class="btn btn-primary mt-2 mt-md-0">
                                Download Report
                            </a>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('school.realq-assessment.update') }}" id="realqSettingsForm">
                        @csrf
                        <div class="form-group">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="mb-0" for="realq_is_active_switch">Enable RealQ Assessment</label>
                                <input type="hidden" name="is_active" value="0">
                                <div class="custom-control custom-switch m-0">
                                    <input type="checkbox" class="custom-control-input" id="realq_is_active_switch" name="is_active" value="1"
                                           {{ (int) old('is_active', $school->realq_assessment_enabled ?? 0) === 1 ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="realq_is_active_switch"></label>
                                </div>
                            </div>
                            @error('is_active')
                                <strong class="text-danger">{{ $message }}</strong>
                            @enderror
                        </div>

                        

                        <button type="submit" class="btn btn-primary mt-3">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('realq_is_active_switch');
        const form = document.getElementById('realqSettingsForm');
        const dateSection = document.getElementById('realq_date_range_section');
        const wasEnabled = {{ (int) ($school->realq_assessment_enabled ?? 0) === 1 ? 'true' : 'false' }};

        function syncDateSection() {
            // dateSection.style.display = toggle.checked ? 'block' : 'none';
        }

        toggle.addEventListener('change', syncDateSection);
        syncDateSection();

        form.addEventListener('submit', function (event) {
            if (!wasEnabled && toggle.checked) {
                event.preventDefault();
                swal({
                    title: "Enable RealQ Assessment?",
                    text: "Students will now be able to access the RealQ assessment upon logging in.",
                    icon: "warning",
                    buttons: true,
                }).then((confirmed) => {
                    if (confirmed) {
                        form.submit();
                    }
                });
            }
        });
    });
</script>
@endsection
