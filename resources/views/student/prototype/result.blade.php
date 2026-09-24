@extends('backend.layouts.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Prototype Result</h2>
        <a href="{{ route('student.prototype') }}" class="btn btn-sm btn-warning float-right"> <i class="material-icons">west</i> Back</a>
    </div>

    <!-- Main content -->
    <section>
        @if(Session::has('message'))
            <div class="alert alert-success">
                {{ Session::get('message') }}
            </div>
        @endif
        <div class="card">
            <div class="card-body">
                @if($aiImageUrl)
                    <img src="{{ $aiImageUrl }}" width="400">
                @else   
                    Prototype not available
                @endif
            </div>
        </div>
        
    </section>
</div>
@endsection