@extends('backend.layouts.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Change Password</h2>
        <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-warning float-right"> <i class="material-icons">west</i> Back</a>
    </div>

    <!-- Main content -->
    <section>
        <div class="card">
            <div class="card-body py-4 px-4">
                <p class="mb-3">
                    Your school will receive an email requesting approval for the password change. Once the school approves the request, you will receive an email with your new password.
                </p>
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <p class="mb-0">Are you sure you want to send this request?</p>
                    <form action="{{ route('student.change-password-request') }}" method="POST" class="mb-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">Send Request</button>
                    </form>
                </div>
            </div>
        </div>
        
    </section>
</div>
@endsection
