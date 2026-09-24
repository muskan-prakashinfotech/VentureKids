@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
    

        <div class="pageTitle">
        <h2>{{ __('admin/event.event_list') }}</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">{{ __('admin/event.home') }}</a></li>
            <li class="breadcrumb-item active">{{ __('admin/event.event_list') }}</li>
        </ol>
    </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section>
            <div class="container-fluid p-0">
                @if (session()->has('message'))
                    <div class="alert alert-success">
                        {{ session()->get('message') }}
                    </div>
                @endif
                <div class="card">
                    
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">

                    <div class="eventsList-container">

                    <?php $i=1;?>
                    @foreach($event as $events)

                    <div class="eventsList">
                        <div class="eventsList-thumbnail">
                            <img src="{{ asset($events->event_image) }}" alt="Product 1">
                        </div>

                        <div class="eventsList-details">
                            <div class="titleInfo">
                                <h2>{{ $events->event_name }}</h2>
                            </div>

                            <div class="dateInfo">
                                <label>Last Date To Submit</label>
                                <p>{{ $events->event_last_date }}</p>
                            </div>

                            <div class="bottomInfo">
                                <div class="dateInfo">
                                    <label>Start Date</label>
                                    <p>{{ $events->event_date }}</p>
                                </div>
                                <a href="{{ route('school.event-view', $events->id) }}" class="btn btn-sm btn-primary">
                                    <span>View Details</span>
                                    <i class="material-icons">east</i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php $i++;?>
                    @endforeach
                </div>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </section>
    </div>
@endsection
