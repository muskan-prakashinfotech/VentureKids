@extends('backend.layouts.app')

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1 class="m-0"> Session Listing</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                        <li class="breadcrumb-item active">Session Listing</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            @if (session()->has('message'))
                <div class="alert alert-success" style="text-align: center;">
                    {{ session()->get('message') }}
                </div>
            @endif
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <a href="{{ route('backend.external_session.create') }}" class="btn btn-primary">Add Session</a>
                        <a href="{{ route('backend.external_session.calendar') }}" class="btn btn-info ml-2">View Calendar</a>
                    </div>
                    <div class="card-tools">
                        <a href="{{ route('backend.home') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date & Time</th>
                                <th>Trainer</th>
                                <!-- <th>Attendance</th> -->
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($external_session as $session)
                                <tr>
                                    <td>{{ $session->title }}</td>
                                    <td>{{ \Carbon\Carbon::parse($session->date_time, 'UTC')->timezone('Asia/Singapore')->format('d-m-Y h:i A') }}</td>
                                    <td>{{ $session->speaker }}</td>
                                    <!-- <td>{{ $session->attendees }}</td> -->
                                    <td class="d-flex" style="gap: 12px;">
                                        <a href="{{ route('backend.external_session.duplicate', $session->id) }}"
                                            class="btn btn-info" title="Duplicate Session">
                                            <i class="fas fa-copy"></i>
                                        </a>
                                        @if (!$session->is_cancelled)
                                            <a href="{{ route('backend.external_session.edit', $session->id) }}"
                                                class="btn btn-success"><i class="fas fa-edit"></i></a>
                                        @else
                                            <button type="button" class="btn btn-secondary" disabled title="Cancelled sessions cannot be edited">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                        <form action="{{ route('backend.external_session.delete', $session->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="deleteExternalSession(event, this)" type="button"
                                                class="btn btn-danger">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5">No session found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </section>
</div>
<script>
    function deleteExternalSession(e, target) {
        e.preventDefault();
        var form = $(target).parents('form');
        swal({
                title: "Are you sure?",
                text: "Are you sure want to delete this External Session!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    swal("Success! Your External Session has been deleted!", {
                        icon: "success",
                    });
                    form.submit();
                } else {
                    swal("Great! External Session records are safe.");
                }
            });
    }
</script>
@endsection
