@extends('backend.layouts.app')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>Challenge Details</h2>
        <!-- <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">{{$event['event_name']}}</li>
        </ol> -->
        <a href="{{ route('student.event_list') }}" class="btn btn-sm btn-warning">
            <i class="material-icons">west</i>
            Back
        </a>
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

                @if(!empty($event['event_video']) || !empty($posterData))
                <div class="row mt-4">
                    @if(!empty($event['event_video']))
                    <div class="col-lg-12">
                        <div class="video_link mb-4">
                            <iframe src="{{ str_replace('watch?v=', 'embed/', $event['event_video']) }}" title="YouTube video player" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" frameborder="0" width="100%" height="75%" allowfullscreen></iframe>
                        </div>
                    </div>
                    @endif
                    @if(!empty($posterData))
                    @foreach($posterData as $poster)
                    <div class="col-lg-12">
                        <div class="poster_link text-center mb-2">
                            <a href="{{ url('/image/event/poster/' . $poster['attachment']) }}" download><i class="fa fa-file-pdf"></i> {{$poster['title']}}</a>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
                @endif

                @if(!empty($resourceData))
                <div class="row justify-content-center mt-4 mb-4">
                    <div class="col-lg-10">
                        <div class="challenge_details_content p-4 bg-white">
                            <div class="col-lg-12 text-center mb-4">
                                <h3 class="mb-0 text-center">Highlights</h3>
                            </div>
                            <div class="col-lg-12">
                                <div class="row">
                                    @foreach($resourceData as $resource)
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
                <div class="row justify-content-center mt-4">
                    <div class="col-lg-10 text-center">
                        <a class="btn btn-orange btn-respond" id="<?= ($studentAllowToRespondChallenge) ? 'respondToChallenge' : 'viewResponse' ?>" href="javascript:void(0);"><?= ($studentAllowToRespondChallenge) ? 'Respond to the Challenge' : 'View Response' ?></a>
                    </div>
                </div> 
                @if($studentAllowToRespondChallenge)
                <div class="row justify-content-center" id="eventResponse">
                    <div class="col-lg-10">
                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title">Add Response</h2>
                            </div>
                            <form id="respondFrm" action="{{ route('student.event_challenge_response') }}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="event_id" value="{{$event['id']}}">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea class="form-control" id="description" name="description"></textarea>
                                        @error('description')
                                        <p class="text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-0">
                                        <div class="project_example">
                                            <label>Add Attachment file</label>
                                            <input type="file" class="form-control respondAttachment" name="attachment[]" >
                                            @error('attachment')
                                            <p class="text-danger">{{ $message }}</p>
                                            @enderror
                                            @error('attachment.*')
                                            <p class="text-danger">{{ $message }}</p>
                                            @enderror
                                            <div class="BtnMores">
                                                <span role="button" class="addBtn btn btn-sm iconBtn btn-warning">
                                                    <i class="material-icons">add</i>
                                                </span>
                                                <span role="button" class="btnRemove btn btn-sm iconBtn btn-danger">
                                                    <i class="material-icons">close</i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="button" class="btn btn-orange submitBtn btn-respond-action">Submit</button>
                                    <button type="button" class="btn btn-orange cancelBtn btn-respond-action">Cancel</button>
                                </div>
                            </form>
                        </div> 
                    </div>
                </div>
                @endif
                @if(!$studentAllowToRespondChallenge && $challengeRespondData->count())
                <div class="row justify-content-center" id="challenge-respond">
                    <div class="col-lg-10">
                        <div class="card"> 
                            <div class="card-header">
                                <h2 class="card-title">View Response</h2>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <div class="download border-none">
                                        {!!$challengeRespondData[0]->description!!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="description">Attachment</label>
                                    @foreach($challengeRespondData[0]->challengesFiles as $attachment)
                                    <div class="download d-flex flex-wrap justify-content-between align-items-center border-none mt-2"> 
                                        <span>{{ basename($attachment->attachment) }}</span> 
                                        <div class="action-btn">
                                            <a href="{{ url('tenants/'.$attachment->attachment) }}" class="btn btn-success btn-sm" download>
                                                <i class="fa fa-download"></i> 
                                            </a>  
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

@if (session()->has('confetti_visible'))
    <script>
        var confetti_sound_path = "{{asset('asset/dist/audio/great-job-speech.mp3')}}";
    </script>
    <script src="{{asset('asset/dist/js/confetti.js')}}"></script>
    <script>
        // Call Confetti Animation
        poof();
    </script>
@endif

<script>

    $(document).ready(function() {        
        $("#eventResponse").hide();
        $("#challenge-respond").hide();
        $("#respondToChallenge").on('click', function() {
            $("#eventResponse").show();
            $(this).hide();
        });
        
        $(".cancelBtn").on('click', function() {
            $("#respondToChallenge").show();
            $("#eventResponse").hide();
        });    

        $("#viewResponse").on('click', function() {
            $("#challenge-respond").show();
            $(this).hide();
        });

    });
    
    $(".submitBtn").click(function(){
        var flagValid = true;
        if(!$.trim($('#description').val()).length) {
            alert("Description field is required.");
            flagValid = false;
            return false;
        }
        $("input[name='attachment[]'").each(function(){
            if(!$.trim($(this).val()).length) { 
                alert("Attachment field is required.");
                flagValid = false;
                return false;
            }
        });
        if(flagValid) {
            $('#respondFrm').submit();
        }
    });

</script>

@endsection