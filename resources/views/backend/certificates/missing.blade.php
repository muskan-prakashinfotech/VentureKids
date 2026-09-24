@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Missing Certificates</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Missing Certificates</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        @if (session()->has('success'))
                            <div class="alert alert-success" style="text-align: center;">
                                {{ session()->get('success') }}
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger" style="text-align: center;">
                                {{ session()->get('error') }}
                            </div>
                        @endif
                    </div>

                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>School</th>
                                    <th>Grade</th>
                                    <th>Certificate ID</th>
                                    <th>PDF Path</th>
                                    <th>Missing Reason</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($missingCertificates as $cert)
                                    @php
                                        $pdfPath = $cert->pdf_path ?: ('certificates/' . $cert->unique_id . '.pdf');
                                        $exists = $pdfPath ? File::exists(public_path($pdfPath)) : false;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $cert->student->name ?? 'N/A' }}</td>
                                        <td>{{ $cert->student->school->school_name ?? 'N/A' }}</td>
                                        <td>{{ $cert->grade->grade ?? 'N/A' }}</td>
                                        <td>{{ $cert->unique_id }}</td>
                                        <td>{{ $pdfPath }}</td>
                                        <td>
                                            @if (!$exists)
                                                File not found
                                            @else
                                                Unknown
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('backend.certificates.regenerate', $cert->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    Regenerate
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No missing certificates found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
