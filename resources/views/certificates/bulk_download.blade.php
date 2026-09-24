@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
    <div class="pageTitle">
        <h2>Bulk Certificate Download</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Bulk Certificate Download</li>
        </ol>
    </div>

    <section>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Download Certificates</h3>
                <div class="card-tools">
                    <span class="badge badge-info">{{ count($certificates) }} certificates available</span>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> Click on individual certificate links below to download each certificate separately.
                    @if(!auth()->check())
                        This bulk download link will expire in 7 days.
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Grade</th>
                                <th>Unique ID</th>
                                <th>Issue Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($certificates as $certificate)
                            <tr>
                                <td>{{ $certificate->student->name }}</td>
                                <td>{{ $certificate->grade->grade }}</td>
                                <td>{{ $certificate->unique_id }}</td>
                                <td>{{ $certificate->issue_date }}</td>
                                <td>
                                    <a href="{{ route('certificate.download', ['token' => $certificate->download_token]) }}"
                                       class="btn btn-sm btn-primary"
                                       target="_blank">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection