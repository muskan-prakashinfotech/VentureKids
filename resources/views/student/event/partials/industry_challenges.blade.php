<div class="eventsList-container overlap_section challenges_box mt-4 mb-4">
    <h5 class="border-bottom pb-2">Current Industry Challenges</h5>
    @if(count($currentEventList))
        <div class="row">
            @foreach($currentEventList as $event)
                <div class="col-xl-6">
                    <div class="eventsList">
                        <div class="image_title">
                            <div class="eventsList-thumbnail">
                                <img src="{{ asset($event['event_image']) }}" alt="Event">
                            </div>
                            <div class="titleInfo">
                                <h2>{{ $event['event_name'] }}</h2>
                            </div>
                        </div>
                        <div class="eventsList-details">
                            <div class="title_date">
                                <div class="titleInfo">
                                    <h2>{{ $event['event_name'] }}</h2>
                                    <p>{{ $event['event_description'] }}</p>
                                </div>
                                <div class="dateInfo">
                                    <label class="mb-0">Start Date</label>
                                    <p>{{ \Carbon\Carbon::parse($event['event_date'])->format('F d, Y') }}</p>
                                </div>
                                <div class="dateInfo">
                                    <label class="mb-0">Last Date</label>
                                    <p>{{ \Carbon\Carbon::parse($event['event_last_date'])->format('F d, Y') }}</p>
                                </div>
                            </div>
                            <div class="bottomInfo">
                                <a href="{{ route('student.event_view', $event['id']) }}" class="btn btn-sm btn-primary ml-auto">
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
        <h6 class="mb-4 mt-4 w-100 text-center">New challenge coming soon. Keep coming back for more!</h6>
    @endif
</div>

<div class="eventsList-container overlap_section challenges_box">
    <h5 class="border-bottom pb-2">Past Industry Challenges</h5>
    @if(count($pastEventList))
        <div class="row">
            @foreach($pastEventList as $event)
                <div class="col-xl-6">
                    <div class="eventsList past-event {{ $event->is_locked ? 'stream-list-lock' : '' }}">
                        <div class="image_title">
                            <div class="eventsList-thumbnail">
                                <img src="{{ asset($event['event_image']) }}" alt="Event">
                            </div>
                            <div class="titleInfo">
                                <h2>{{ $event['event_name'] }}</h2>
                            </div>
                        </div>
                        <div class="eventsList-details">
                            <div class="title_date">
                                <div class="titleInfo">
                                    <h2>{{ $event['event_name'] }}</h2>
                                    <p>{{ $event['event_description'] }}</p>
                                </div>
                                <div class="dateInfo">
                                    <label class="mb-0">Start Date</label>
                                    <p>{{ \Carbon\Carbon::parse($event['event_date'])->format('F d, Y') }}</p>
                                </div>
                                <div class="dateInfo">
                                    <label class="mb-0">Last Date</label>
                                    <p>{{ \Carbon\Carbon::parse($event['event_last_date'])->format('F d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <h6 class="mb-4 mt-4 w-100 text-center">No challenges found!</h6>
    @endif
</div>
