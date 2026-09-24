@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Generate Report</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Generate Report</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @if (session()->has('success'))
                    <div class="alert alert-success" style="text-align: center;">
                        {{ session()->get('success') }}
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">RealQ Assigned Schools</h3>
                        <div class="card-tools">
                            <a href="{{ route('backend.realqassessment.index') }}" class="btn btn-warning">
                                <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>School Name</th>
                                    <th>Assigned Grades</th>
                                    <th>Students Attempted / Total</th>
                                    <th>Enabled From</th>
                                    <th>Enabled To</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($schools as $school)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $school->school_name }}</td>
                                        <td>{{ $school->grade_names ?: '—' }}</td>
                                        <td>{{ (int) ($attemptedCountMap[$school->school_id] ?? 0) }} / {{ (int) ($school->total_students ?? 0) }}</td>
                                        <td>{{ $school->enabled_from ?: '—' }}</td>
                                        <td>{{ $school->enabled_to ?: '—' }}</td>
                                        <td>
                                            @php
                                                $reportRow = $reportMap[$school->school_id] ?? null;
                                            @endphp
                                            @if($reportRow)
                                                <a class="btn btn-success"
                                                   href="{{ route('backend.realqassessment.reports.download', $reportRow->id) }}">
                                                    Download Report
                                                </a>
                                            @else
                                                <button type="button"
                                                        class="btn btn-primary realq-generate-report"
                                                        data-school-id="{{ $school->school_id }}">
                                                    Generate &amp; Publish
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No RealQ-assigned schools found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buttons = document.querySelectorAll('.realq-generate-report');
            buttons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const schoolId = this.dataset.schoolId;
                    const originalText = this.textContent;
                    this.disabled = true;
                    this.textContent = 'Generating...';

                    fetch("{{ url('admin/realq-assessment/reports') }}/" + schoolId + "/generate", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Unable to generate report.');
                        }
                        if (data.download_url) {
                            const downloadBtn = document.createElement('a');
                            downloadBtn.className = 'btn btn-success';
                            downloadBtn.href = data.download_url;
                            downloadBtn.textContent = 'Download Report';
                            this.parentNode.replaceChild(downloadBtn, this);
                        }
                    })
                    .catch(err => {
                        alert(err.message || 'Unable to generate report.');
                    })
                    .finally(() => {
                        this.disabled = false;
                        this.textContent = originalText;
                    });
                });
            });

        });
    </script>
@endsection
