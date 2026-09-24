@extends('backend.layouts.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Industry Challenges</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Industry Challenges</li>
        </ol>
    </div>

    <!-- Main content -->
    <section>
        <div class="card">
            <div class="card-body">
                <div class="eventsList-container overlap_section challenges_box">
                    <h4 class="border-bottom pb-2">Current Challenges</h4>
                    @if(count($currentEvents))
                    <div class="row">
                    @foreach($currentEvents as $event)
                    <div class="col-xl-6">
                    <div class="eventsList">
                        <div class="image_title">
                            <div class="eventsList-thumbnail">
                                <img src="{{asset($event['event_image'])}}" alt="Product 1">
                            </div>
                            <div class="titleInfo">
                                <h2>{{$event['event_name']}}</h2>
                            </div>
                        </div>

                        <div class="eventsList-details">
                            <div class="title_date">
                                <div class="titleInfo">
                                    <h2>{{$event['event_name']}}</h2>
                                    <p>{{$event['event_description']}}</p>
                                </div>
                                <div class="dateInfo">
                                    <label class="mb-0">Start Date</label>
                                    @php
                                    $startDate = DateTime::createFromFormat('Y-m-d', $event['event_date']);
                                    $formattedStartDate = $startDate->format('F d, Y');   
                                    @endphp
                                    <p>{{ $formattedStartDate }}</p>
                                </div>
                                <div class="dateInfo">
                                    <label class="mb-0">Last Date</label>
                                    @php
                                        $lastDate = DateTime::createFromFormat('Y-m-d', $event['event_last_date']);
                                        $formattedLastDate = $lastDate->format('F d, Y');   
                                    @endphp
                                    <p>{{$formattedLastDate}}</p>
                                </div>
                            </div>

                            <div class="bottomInfo">
                                <a href="{{ route('trainer.event/view.eventView',$event['id'])}}" class="btn btn-sm btn-primary ml-auto">
                                    <span>View Details</span>
                                    <i class="material-icons">east</i>
                                </a>
                            </div>
                        </div>
                    </div>
                    </div>
                    @endforeach
                    </div>
                    @else
                    <h6 class="mb-4 mt-4 w-100 text-center">No challenges found</h6>
                    @endif
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="eventsList-container overlap_section challenges_box">
                    <h4 class="border-bottom pb-2">Past Challenges</h4>
                    @if(count($pastEvents))
                    <div class="row">
                    @foreach($pastEvents as $event)
                    <div class="col-xl-6">
                    <div class="eventsList">
                        <div class="image_title">
                            <div class="eventsList-thumbnail">
                                <img src="{{asset($event['event_image'])}}" alt="Product 1">
                            </div>
                            <div class="titleInfo">
                                <h2>{{$event['event_name']}}</h2>
                            </div>
                        </div>

                        <div class="eventsList-details">
                            <div class="title_date">
                                <div class="titleInfo">
                                    <h2>{{$event['event_name']}}</h2>
                                    <p>{{$event['event_description']}}</p>
                                </div>
                                <div class="dateInfo">
                                    <label class="mb-0">Start Date</label>
                                    @php
                                    $startDate = DateTime::createFromFormat('Y-m-d', $event['event_date']);
                                    $formattedStartDate = $startDate->format('F d, Y');   
                                    @endphp
                                    <p>{{ $formattedStartDate }}</p>
                                </div>
                                <div class="dateInfo">
                                    <label class="mb-0">Last Date</label>
                                    @php
                                        $lastDate = DateTime::createFromFormat('Y-m-d', $event['event_last_date']);
                                        $formattedLastDate = $lastDate->format('F d, Y');   
                                    @endphp
                                    <p>{{$formattedLastDate}}</p>
                                </div>
                            </div>

                            <div class="bottomInfo">
                                <a href="{{ route('trainer.event/view.eventView',$event['id'])}}" class="btn btn-sm btn-primary ml-auto">
                                    <span>View Details</span>
                                    <i class="material-icons">east</i>
                                </a>
                            </div>
                        </div>
                    </div>
                    </div>
                    @endforeach
                    </div>
                    @else
                    <h6 class="mb-4 mt-4 w-100 text-center">No challenges found</h6>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection