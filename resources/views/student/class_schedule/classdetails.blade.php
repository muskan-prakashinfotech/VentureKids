<div>
    @forelse ($class_schedule as $class)
        @if (isset($class->trainer))
            <div class="container mt-5 d-flex justify-content-center">
                <div class="card p-3">
                    <div class="d-flex align-items-center">
                        <div class="image">
                            <img
                                src="{{ asset('asset/images/zoomicon.webp')  }}"
                                class="rounded" width="120">
                        </div>
                        <div class="ml-3 w-100">
                            <h4 class="mb-0 mt-0">{{  $class->trainer->trainer_name }}</h4>
                            <span>{{ date('g:i a', strtotime($class->class_start)) . ' - ' . date('g:i a', strtotime($class->class_end)) }}</span>
                            <br>
                            <span>Level {{$class->grade}}</span>
                            @php
                                $from = \Carbon\Carbon::createFromFormat('m/d/Y H:i', request()->get('date') . $class->class_start)->setTimezone('UTC')->addMinute(+30);
                                $meetingfilter = $allmeetings->filter(function ($item) use($from) {
                                    if($item->meeting_time == $from->format('Y-m-d H:i:s')){
                                        return $item;
                                    }
                                });
                                $meeting = $meetingfilter->first();
                            @endphp
                            <div class="button mt-2 d-flex flex-row align-items-center">
                                @if(isset($meeting))
                                    <a href="{{$meeting->join_url}}" class="btn btn-sm btn-outline-primary w-100">Zoom
                                        Link</a>
                                @else
                                    <span>Sorry, Zoom link is expired</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @empty
        <div class="d-flex justify-content-center align-items-center">
            <div>No Class Schedule on this day.</div>
        </div>
    @endforelse
</div>
