@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0">Standard Assessment Export</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item active">Export</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-tools">
                <a href="{{ route('backend.dashboard') }}" class="btn btn-warning">
                    <i class="fa fa-chevron-left" aria-hidden="true"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <form id="standard-assessment-export-form">
                <div class="row">
                    <div class="col-md-4">
                        <label for="school_id">Filter by School</label>
                        <select class="form-control" id="school_id" name="school_id">
                            <option value="">All Schools</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" id="standard-assessment-export-btn" class="btn btn-primary">Export</button>
                    </div>
                </div>
            </form>
            <small class="text-muted d-block mt-2">
                Export includes student name, grade, category, question, selected option, response, and option weightage.
            </small>
        </div>
    </div>
</div>
<script>
    (function () {
        const exportUrl = "{{ route('backend.standard_assessment.export.download') }}";
        const button = document.getElementById('standard-assessment-export-btn');
        const schoolSelect = document.getElementById('school_id');

        function buildUrl() {
            const url = new URL(exportUrl, window.location.origin);
            if (schoolSelect && schoolSelect.value) {
                url.searchParams.set('school_id', schoolSelect.value);
            }
            return url.toString();
        }

        button.addEventListener('click', function () {
            button.disabled = true;
            button.textContent = 'Exporting...';

            fetch(buildUrl(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Export failed.');
                }
                return response.blob();
            })
            .then(function (blob) {
                const link = document.createElement('a');
                const blobUrl = window.URL.createObjectURL(blob);
                link.href = blobUrl;
                link.download = 'standard_assessment_export.xlsx';
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(blobUrl);
            })
            .catch(function () {
                alert('Failed to export. Please try again.');
            })
            .finally(function () {
                button.disabled = false;
                button.textContent = 'Export';
            });
        });
    })();
</script>
@endsection

