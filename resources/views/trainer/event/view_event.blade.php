@extends('backend.layouts.app')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Challenge Details</h2>
        <!-- <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('trainer.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">{{$event['event_name']}}</li>
        </ol> -->
    </div>

    <!-- Main content -->
    <section>
        <div class="card">
            <!-- <div class="card-header">
                <h3 class="card-title">{{$event['event_name']}}</h3>
                <a href="{{ URL::previous() }}" class="btn btn-sm btn-warning">
                    <i class="material-icons">west</i>
                    Back
                </a>
            </div> -->
            <!-- /.card-header -->
            <div class="card-body challenge_details">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="challenge_details_hero">
                            <img src="{{asset($event['event_image'])}}" class="img-fluid">
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center mt-4">
                    <div class="col-lg-10">
                        <div class="challenge_details_content bg-white">
                            <h3>{{$event['event_name']}}</h3>
                            <p>{!!$event['event_description']!!}</p>
                        </div>
                    </div>
                </div>
                
                <div class="row justify-content-center">
                    <div class="col-xl-6 col-lg-8 col-md-10 col-11">
                        <div class="overlap_section">
                            <div class="row">
                                <div class="col-lg-6 mb">
                                    <p>
                                        <b>Start Date :</b> 
                                        @php
                                        $startDate = DateTime::createFromFormat('Y-m-d', $event['event_date']);
                                        $formattedStartDate = $startDate->format('F d, Y');   
                                        @endphp
                                        {{ $formattedStartDate }}
                                    </p>
                                </div>
                                <div class="col-lg-6 mb">
                                    <p>
                                        <b>Last Date :</b> 
                                        @php
                                        $lastDate = DateTime::createFromFormat('Y-m-d', $event['event_last_date']);
                                        $formattedLastDate = $lastDate->format('F d, Y');   
                                        @endphp
                                        {{ $formattedLastDate }}
                                    </p>
                                </div>
                                <div class="col-lg-12 mb">
                                    <p><b>Registration Fee :</b> {{$event['event_fee']}}  {{strtoupper($event['currency'])}}</p>
                                </div>
                                <!-- <div class="col-lg-12 mb">
                                    <p><b>Challenge Address :</b> {{$event['event_address']}}</p>
                                </div>
                                <div class="col-lg-12 mb">
                                    <p><b>Country :</b> {{$event['country_id']}} </p>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>

                @if(!empty($event['reward_amount']) || !empty($event['reward_description']))
                <div class="row justify-content-center mt-4 mb-4">
                    <div class="col-lg-10">
                        <div class="challenge_details_content bg-white p-2 p-lg-3">  
                            <div class="reward_section">
                                <h3>Reward</h3>
                                <div class="disc">
                                    <p>{{$event['reward_description']}}</p>
                                </div>
                            </div>
                        </div>  
                    </div>
                </div>
                @endif
                @if(!empty($event['event_video']) || !empty($event['event_attachments']))
                <div class="row mt-4">
                    @if(!empty($event['event_video']))
                    <div class="col-lg-12">
                        <div class="video_link mb-4">
                            <iframe src="{{ str_replace('watch?v=', 'embed/', $event['event_video']) }}" title="YouTube video player" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" frameborder="0" width="100%" height="75%" allowfullscreen></iframe>
                        </div>
                    </div>
                    @endif
                    @if(!empty($event['event_attachments']))
                    @foreach($event['event_attachments'] as $poster)
                    <div class="col-lg-12">
                        <div class="poster_link text-center mb-2">
                            <a href="{{ url('/image/event/poster/' . $poster['attachment']) }}" download><i class="fa fa-file-pdf"></i> {{$poster['title']}}</a>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
                @endif
                
                @if(!empty($event['event_highlights']))
                <div class="row justify-content-center mt-4 mb-4">
                    <div class="col-lg-10">
                        <div class="challenge_details_content p-4 bg-white">
                            <div class="col-lg-12 text-center mb-4">
                                <h3 class="mb-0 text-center">Highlights</h3>
                            </div>
                            <div class="col-lg-12">
                                <div class="row">
                                    @foreach($event['event_highlights'] as $resource)
                                    <div class="col-xl-6">
                                        <div class="highlight_box mt-3 mb-3"> 
                                            <div class="icon">
                                                <img src="{{asset('/image/event/highlight/'.$resource['attachment'])}}">
                                            </div>
                                            <div class="content">
                                                <h5>{{$resource['title']}}</h5>
                                                <p>{{$resource['description']}}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            <!-- /.card-body -->
            </div>
        </div>
    </section>
</div>
<!-- /.modal -->
@endsection