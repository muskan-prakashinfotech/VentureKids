@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Pending School Approvals</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Pending School Approvals</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @if (session()->has('message'))
                    <div class="alert alert-success">{{ session()->get('message') }}</div>
                @endif
                @if (session()->has('error'))
                    <div class="alert alert-danger">{{ session()->get('error') }}</div>
                @endif

                <div class="card">
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>School Name</th>
                                    <th>Principal</th>
                                    <th>Email</th>
                                    <th>Country</th>
                                    <th>City</th>
                                    <th>Submitted By</th>
                                    <th>Submitted At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pendingSchools as $pendingSchool)
                                    @php $data = $pendingSchool->form_data ?? []; @endphp
                                    <tr>
                                        <td>{{ $pendingSchool->id }}</td>
                                        <td>{{ $data['school_name'] ?? '-' }}</td>
                                        <td>{{ $data['principle_name'] ?? '-' }}</td>
                                        <td>{{ $data['email'] ?? '-' }}</td>
                                        <td>
                                            {{ optional(\App\Models\Country::find($data['country'] ?? null))->name ?? '-' }}
                                        </td>
                                        <td>{{ $data['city'] ?? '-' }}</td>
                                        <td>{{ optional(\App\Models\User::find($pendingSchool->submitted_by))->name ?? 'Partner' }}</td>
                                        <td>{{ optional($pendingSchool->created_at)->format('d M Y, h:i A') }}</td>
                                        <td>
                                            <form action="{{ route('backend.pending-schools.approve', $pendingSchool->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this school request?')">Approve</button>
                                            </form>
                                            <form action="{{ route('backend.pending-schools.reject', $pendingSchool->id) }}" method="POST" class="d-inline pending-reject-form">
                                                @csrf
                                                <input type="hidden" name="rejection_reason" value="">
                                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No pending school requests found.</td>
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
        $(document).on('submit', '.pending-reject-form', function (e) {
            e.preventDefault();
            var form = this;
            var reason = prompt('Optional rejection reason:');
            $(form).find('input[name="rejection_reason"]').val(reason || '');
            if (confirm('Reject this school request?')) {
                form.submit();
            }
        });
    </script>
@endsection
