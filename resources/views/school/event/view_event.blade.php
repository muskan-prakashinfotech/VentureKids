@extends('backend.layouts.app')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="pageTitle">
        <h2>Challenge Details</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('school.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">{{$event['event_name']}}</li>
        </ol>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section>
        <div class="container-fluid p-0">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{$event['event_name']}}</h3>
                    <a href="{{ URL::previous() }}" class="btn btn-sm btn-warning">
                        <i class="material-icons">west</i>
                        Back
                    </a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="eventDetails-box">
                        <div class="thumbImg">
                            <img src="{{ asset($event->event_image) }}" class="img-fluid">
                        </div>

                        <div class="detailsBox">
                            <div class="statusInfo">
                                <i class="mr-1 fas fa-map-marker-alt"></i>
                                <p>{{ $event->event_address }}</p>
                            </div>

                            <p>{{ strip_tags($event->event_description) }}</p>
                            @foreach (explode(',', $event->event_poster) as $file)
                            <a class="btn btn-warning" href="{{ asset($file) }}" download="">
                                <i class="fas fa-file"></i>
                            </a>
                            @endforeach
                            <div class="feeInfo">
                                <h5>Fee : <span>{{ $event->event_fee }}</span></h5>

                            </div>

                            @if(!empty($event['reward_amount']))
                            <div class="feeInfo">
                                <h5>Reward Amount : <span>{{ $event['reward_amount'] }}</span></h5>
                            </div>
                            @endif

                            @if(!empty($event['reward_description']))
                            <div class="rewardInfo">
                                <h5>Reward Description : <span>{{ $event['reward_description'] }}</span></h5>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- <img src="{{ asset($event->event_image) }}" class="img-fluid" height="100">
                    <div class="pt-4 pb-2">
                        <p><strong><i class="mr-1 fas fa-map-marker-alt"> </i>{{ $event->event_address }}</strong></p>
                    </div>
                    <p>{{ strip_tags($event->event_description) }}</p>

                    <div class="d-flex justify-content-start align-items-center">
                        @foreach (explode(',', $event->event_poster) as $file)
                        <a class="btn btn-warning" href="{{ asset($file) }}" download="">
                            <i class="fas fa-file"></i>
                        </a>
                        @endforeach
                        <div class="mt-4 ml-3">
                            <label for="">Fee :</label>
                            <span>{{ $event->event_fee }}</span>
                            {{-- <span class="text-uppercase">{{ '(' . $event->currency . ')' }}</span> --}}
                        </div>
                    </div>
                    <br>-->
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </section>
</div>
@endsection