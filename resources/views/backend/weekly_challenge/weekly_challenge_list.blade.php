@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Daily Quiz List</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        @if (session()->has('message'))
            <div class="alert alert-success">
                {{ session()->get('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger">
                {{ session()->get('error') }}
            </div>
        @endif

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daily Quiz Settings</h5>
                        <form id="dailyQuizStatusFrm" action="{{ route('backend.settings.updateDailyQuizStatus') }}" method="POST">
                            @csrf
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    id="dailyQuizToggle"
                                    name="daily_quiz_enabled"
                                    value="1"
                                    {{ $dailyQuizEnabled ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="dailyQuizToggle">
                                    Enable Daily Quiz
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <a href="{{ route('backend.weeklyChallengeAdd') }}" class="btn btn-primary">+ Add Daily Quiz</a>
                        <div class="card-tools">
                            <a href="{{ route('backend.dashboard') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>created Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($challengeList as $key => $challenge)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $challenge->challenge_name }}</td>
                                        <td>{{ $challenge->challenge_type }}</td>
                                        <td>{{ $challenge->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <a href="{{ route('backend.weeklyChallengeEdit', $challenge->id) }}"
                                                class="btn btn-block btn-info btn-sm"><i class="fas fa-edit"></i></a>
                                            <button type="button"
                                                data-event-id="{{ route('backend.weeklyChallengeDelete', [$challenge->id, $challenge->challenge_type]) }}"
                                                class="btn btn-block btn-danger deleteEvent btn-sm">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </section>
    </div>

    <script>
        $(document).ready(function() {
            //delete event
            $(document).on('click', '.deleteEvent', function(e) {
                e.preventDefault();
                var id = $(this).data('event-id');
                swal({
                        title: "Are you sure?",
                        text: "If daily quiz is created and any student already attempted this daily quiz then it will get also deleted. Are you sure?",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            swal("Daily Quiz Deleted!", {
                                icon: "success",
                            });
                            window.location.href = id;
                        } else {
                            swal("Great! Daily Quiz record are safe.");
                        }

                    });
            });
        });

        const toggle = document.getElementById('dailyQuizToggle');
        const form = document.getElementById('dailyQuizStatusFrm');

        toggle.addEventListener('change', function () {
            if (!this.checked) {
                if (confirm('Are you sure you want to disable the Daily Quiz feature?')) {
                    form.submit();
                } else {
                    this.checked = true;
                }
            } else {
                form.submit();
            }
        });
    </script>
@endsection
