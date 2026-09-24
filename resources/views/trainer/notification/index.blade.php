@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
    <div class="pageTitle">
        <h2>All Notifications</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Notification</li>
        </ol>
    </div>

    <section>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Notification List</h3>
                <a href="{{ URL::previous() }}" class="btn btn-sm btn-warning">
                    <i class="material-icons">west</i>
                    Back
                </a>
            </div>
            @livewire('trainer.notification-trainer')
        </div>
    </section>
</div>
@endsection