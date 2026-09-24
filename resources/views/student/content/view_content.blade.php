@extends('backend.layouts.app')
@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Main content -->
    <section>
        <div class="card bg_light_yellow">
            <div class="card-header">
                <h3 class="card-title">{{ $streamData->title }}</h3>
                <a href="{{ route('student.contentlist.streamlist', $streamData->agegroup_id) }}" class="btn btn-sm btn-warning">
                    <i class="material-icons">west</i>
                    Back
                </a>
            </div>

             <!-- /.card-body -->
            <div class="card-body">

                @if($sessionData->count())
                    <div class="topics-scroll-tab">
                        <div class="row">
                            <div class="col-12">
                                <h5 class="pl-2 mb-3 topics-header">Topics Covered</h5>
                            </div>
                            <div class="col-12 scroll-container-wrapper">
                                <div class="scroll-tab-container">
                                    <button class="scroll-arrow left" id="scrollLeft">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <div class="tabs-wrapper" id="tabsWrapper">
                                        <div class="nav nav-tabs scroll-tab pb-0" id="trainer" role="tablist">
                                            @forelse ($sessionData as $session)
                                                <button @class(['nav-item nav-link btn text-left', 'active'=> $loop->first])
                                                    id="trainer-tab-{{ $session->id }}-tab"
                                                    data-toggle="pill" data-target="#trainer-tab-{{ $session->id }}" @if(array_key_exists('youtube_id',$youtubeCompletionData[$session->id])) data-youtube-id="{{$youtubeCompletionData[$session->id]['youtube_id']}}" data-id="{{$session->id}}" @endif type="button"
                                                    role="tab" aria-controls="trainer-tab-{{ $session->id }}"
                                                    aria-selected="true">{{ $session->title }}
                                                </button>
                                            @empty
                                            @endforelse
                                        </div>
                                    </div>
                                    <button class="scroll-arrow right" id="scrollRight">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="tab-content" id="trainerContent" role="tablist">
                                    @forelse ($sessionData as $session)
                                        <div @class(['tab-pane fade', 'show active'=> $loop->first]) id="trainer-tab-{{ $session->id }}"  role="tabpanel" aria-labelledby="trainer-tab-{{ $session->id }}-tab">
                                            @if(!empty($assignmentData[$session->id]))
                                                <div class="col-12 watch-video-assi">
                                                    <div class="nav nav-tabs BeginnerTab scroll-tab pb-0" aria-orientation="vertical">
                                                        <img class="rocket" src="{{asset('asset/dist/img/Rocket.png')}}" />
                                                        <div class="eual-div">
                                                            <button class="nav-item nav-link btn text-left active watch-video-btn">
                                                                <span class="light-yellow">SESSION 1</span> 
                                                                <span class="text-white">{{ $session->title }}</span> 
                                                                <a class="play-icon text-white"> Watch Video</a>
                                                            </button>
                                                        </div>
                                                        @foreach($assignmentData[$session->id] as $assignment)
                                                            <div class="light-orange eual-div @if(!$assignment['is_active']) assignment-worksheet-lock @endif">
                                                                <a href="{{ route('student.assigment.show', $assignment['id']) }}"> 
                                                                    <span class="satisfy-regular inner-text">Assignment</span>
                                                                    <span class="text-black">{{$assignment['title']}}</span>
                                                                </a> 
                                                            </div>
                                                        @endforeach
                                                        <div class="light-bulb-img-wrap">
                                                            <img class="light-bulb" src="{{asset('asset/dist/img/Lightbulb.png')}}" />
                                                        </div>
                                                    </div>
    
                                                    <div class="tab-content">
                                                        <div class="tab-pane fade active show" id="watch-video-tab-{{ $session->id }}" role="tabpanel" aria-labelledby="watch-video-tab-click--{{ $session->id }}">
                                                            @if(!empty($session->video) || !empty($session->video_url))
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        @if(!empty($session->video))
                                                                            <video width="100%" controls>
                                                                                <source src="{{ url('/video/content/' . $session->video) }}" type="video/mp4">
                                                                                    Your browser does not support the video tag.
                                                                            </video>
                                                                        @elseif(!empty($session->video_url))
                                                                            <div id="player{{$youtubeCompletionData[$session->id]['youtube_id']}}"></div>  
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endif
                        
                                                            @if(!empty($session->worksheet))
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <hr></hr>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="description-info pdf-wrap mt-0 student-workbook">
                                                                        <h5>Workbook</h5>
                                                                        @php 
                                                                        $worksheet_name = $session->worksheet_name; 
                                                                        if(empty($worksheet_name)) 
                                                                            $worksheet_name = pathinfo($session->worksheet, PATHINFO_FILENAME)
                                                                        @endphp 
                                                                        <h6><i class="fa fa-file-pdf"></i> <a href="{{ url('/files/content/' . $session->worksheet) }}" download> {{$worksheet_name}} </a></h6>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
                        
                                                            @if(!empty($quizData) && in_array($session->id, $quizData))
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <hr></hr>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <div class="pdf-wrap mt-0">
                                                                            <h5>Quiz</h5>
                                                                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                                                <h6 class="mt-2 mb-2 title_minus_btn">{{$session->title}}</h6>
                                                                                <a href="{{ route('student.quiz', ['content', $session->id]) }}" class="btn btn-sm btn-primary take-quiz-btn go_to_quiz"> @if(in_array($session->id, $attemptedQuizIdList)) View Score @else Go to Quiz @endif 
                                                                                    <i class="fa fa-arrow-right ms-3 fs-11"></i> 
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
    
                                                            @if(empty($session->video) && empty($session->video_url) && empty($session->worksheet) && empty($quizData))
                                                                <h3 class="card-title">No data found</h3>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                @if(!empty($session->video) || !empty($session->video_url))
                                                    <div class="row">
                                                        <div class="col-12">
                                                            @if(!empty($session->video))
                                                                <video width="100%" controls>
                                                                    <source src="{{ url('/video/content/' . $session->video) }}" type="video/mp4">
                                                                        Your browser does not support the video tag.
                                                                </video>
                                                            @elseif(!empty($session->video_url))
                                                                <div id="player{{$youtubeCompletionData[$session->id]['youtube_id']}}"></div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
            
                                                @if(!empty($session->worksheet))
                                                <div class="row">
                                                    <div class="col-12">
                                                        <hr></hr>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="description-info pdf-wrap mt-0 student-workbook">
                                                            <h5>Workbook</h5>
                                                            @php 
                                                            $worksheet_name = $session->worksheet_name; 
                                                            if(empty($worksheet_name)) 
                                                                $worksheet_name = pathinfo($session->worksheet, PATHINFO_FILENAME)
                                                            @endphp 
                                                            <h6><i class="fa fa-file-pdf"></i> <a href="{{ url('/files/content/' . $session->worksheet) }}" download> {{$worksheet_name}} </a></h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
            
                                                @if(!empty($quizData) && in_array($session->id, $quizData))
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <hr></hr>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="pdf-wrap mt-0">
                                                                <h5>Quiz</h5>
                                                                <div class="d-flex flex-wrap align-items-center justify-content-between">
                                                                    <h6 class="mt-2 mb-2 title_minus_btn">{{$session->title}}</h6>
                                                                    <a href="{{ route('student.quiz', ['content', $session->id]) }}" class="btn btn-sm btn-primary take-quiz-btn go_to_quiz"> @if(in_array($session->id, $attemptedQuizIdList)) View Score @else Go to Quiz @endif 
                                                                        <i class="fa fa-arrow-right ms-3 fs-11"></i> 
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
    
                                                @if(empty($session->video) && empty($session->video_url) && empty($session->worksheet) && empty($quizData))
                                                    <h3 class="card-title">No data found</h3>
                                                @endif
    
                                            @endif
    
                                        </div>
                                    @empty
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if(!$sessionData->count())

                    @if(!empty($assignmentData))
                        <div class="col-12 watch-video-assi">
                            <div class="nav nav-tabs BeginnerTab scroll-tab pb-0" aria-orientation="vertical">
                                <img class="rocket" src="{{asset('asset/dist/img/Rocket.png')}}" />

                                <div class="eual-div">
                                    <button class="nav-item nav-link btn text-left active watch-video-btn">
                                        <span class="light-yellow">SESSION 1</span>
                                        <span class="text-white">{{ $streamData->title }}</span> 
                                        <a class="play-icon text-white"> Watch Video</a>
                                    </button>
                                </div>
                                @foreach($assignmentData as $assignment)
                                    <div class="light-orange eual-div @if(!$assignment['is_active']) assignment-worksheet-lock @endif">
                                        <a href="{{ route('student.assigment.show', $assignment['id']) }}"> 
                                            <span class="satisfy-regular inner-text">Assignment</span>
                                            <span class="text-black">{{$assignment['title']}}</span>
                                        </a> 
                                    </div>
                                @endforeach
                                <div class="light-bulb-img-wrap">
                                    <img class="light-bulb" src="{{asset('asset/dist/img/Lightbulb.png')}}" />
                                </div>
                            </div>

                            <div class="tab-content">
                                <div class="tab-pane fade active show" id="watch-video-tab" role="tabpanel" aria-labelledby="watch-video-tab-click">                                    
                                    @if(!empty($streamData->videoUrl))
                                    <div class="row">
                                        <div class="col-12">
                                            <div id="player{{$streamData->youtube_id}}"></div>
                                        </div>
                                    </div>
                                    @endif
                
                                    @if(!empty($streamData->scormFile))
                                        @if(!empty($streamData->videoUrl))
                                            <div class="row">
                                                <div class="col-12">
                                                    <hr></hr>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="row">
                                            <div class="col-12">
                                                <iframe  width="100%" height="600" class="col border-0 m-0 p-0 scorm-iframe" id="scormSrc"></iframe>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                        </div>
                    @else
                        @if(!empty($streamData->videoUrl))
                            <div class="row">
                                <div class="col-12">
                                    <div id="player{{$streamData->youtube_id}}"></div>
                                </div>
                            </div>
                        @endif
                        @if(!empty($streamData->scormFile))
                            @if(!empty($streamData->videoUrl))
                                <div class="row">
                                    <div class="col-12">
                                        <hr></hr>
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-12">
                                    <iframe  width="100%" height="600" class="col border-0 m-0 p-0 scorm-iframe" id="scormSrc"></iframe>
                                </div>
                            </div>
                        @endif
                    @endif
                @endif

                @if(!$sessionData->count() && empty($streamData->videoUrl) && empty($streamData->scormFile))
                    <h3 class="card-title">No data found</h3>
                @endif

            </div>
            <!-- /.card-body -->
        </div>
    </section>
</div>

<script>
    var confetti_sound_path = "{{asset('asset/dist/audio/great-job-speech.mp3')}}";

    var youtube_id;
    var item_id;
    var item_type;

    let player;
    let lastAllowedTime = 0;
    let videoDuration = 0;
    let rewardGiven = false;
    let isPlayerReady = false;
    let progressInterval = null;

    // Load YouTube IFrame API
    function loadYouTubeAPI() {
        let tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        let firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }

    // This function is called by the API
    function onYouTubeIframeAPIReady() {
        resetPlayer();
        player = new YT.Player(`player${youtube_id}`, {
            height: '600',
            width: '100%',
            videoId: youtube_id, 
            playerVars: {
                controls: 1,
                disablekb: 1,
                modestbranding: 1,
                rel: 0
            },
            events: {
                onReady: onPlayerReady,
                onStateChange: onPlayerStateChange
            }
        });
    }

    function onPlayerReady(event) {
        isPlayerReady = true;
        videoDuration = player.getDuration();

        // Start a setInterval to track progress every second
        progressInterval = setInterval(trackTime, 1000);
    }

    function resetPlayer() {
        // Clear the interval
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }

        if (player && typeof player.destroy === 'function') {
            player.destroy();
            player = null;
            isPlayerReady = false;
        }
    }

    function trackTime() {
        const currentTime = player.getCurrentTime();
        
        // Detect skipping forward
        if (currentTime - lastAllowedTime > 2) {
            player.seekTo(lastAllowedTime); // Rewind to last watched
        } else {
            if (currentTime > lastAllowedTime) {
                lastAllowedTime = currentTime;
            }
        }

        // Reward if 98% or more watched
        // if (!rewardGiven && currentTime / videoDuration > 0.98) {
        //     rewardGiven = true;
        // }
    }

    // Track video state changes
    function onPlayerStateChange(event) {
        // Optional: pause when tab is not active
        if (event.data === YT.PlayerState.PLAYING) {
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) player.pauseVideo();
            });
        }

        if (event.data === YT.PlayerState.ENDED) {    
            // Call Confetti Animation 
            poof();

            $.ajax({
                url: "{{ route('student.storeYoutubeCompletionPoints') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    item_id: item_id,
                    item_type: item_type
                },
                method: "POST",
                success: function(response) {
                    
                }
            });
        } 
    }
</script>

@if($sessionData->count())
<script>
    youtube_id = $(".topics-scroll-tab .nav-link").first().attr('data-youtube-id');
    if(youtube_id !== undefined && youtube_id != '') {
        item_id = $(".topics-scroll-tab .nav-link").first().attr('data-id');
        item_type = 'session';
    }
    loadYouTubeAPI();
    $('.nav-link').on('shown.bs.tab', function (e) {
        youtube_id = $(e.target).attr("data-youtube-id");
        if(youtube_id !== undefined && youtube_id != '') {
            item_id = $(e.target).attr("data-id");
            item_type = 'session';
            onYouTubeIframeAPIReady();
        } else {
            resetPlayer()
        }
    });
</script>
@endif

@if(!$sessionData->count() && !empty($streamData->videoUrl))
<script>
    youtube_id = '{{$streamData->youtube_id}}';
    item_id = '{{$streamData->id}}';
    loadYouTubeAPI();
</script>
@endif

@if(!$sessionData->count() && !empty($streamData->scormFile))

<!-- Prettify -->
<script src="{{asset('asset/dist/js/scorm/run_prettify.js')}}"></script>

<script src="{{asset('asset/dist/js/scorm/scorm-again.js')}}"></script>

<script>
    
    var scormFile = '';
    var scormUrl = '';
    var scormCompleted = {{$streamData->scormCompleted}};
    var is_allowed = true;

    window.API = new Scorm12API();  

    // window.API.on("LMSInitialize", function() {
        // alert('LMS Initialized!');
        // console.log(window.API);
    // });

    /*
    window.API.on("LMSFinish", function() {
        if(!scormCompleted) {
            $.ajax({
                url: "{{ route('student.storeScormCompletionPoints') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    stream_id: '{{$streamData->id}}',
                },
                method: "POST",
                success: function(response) {
                    
                }
            });
        }
    });
    */

    window.API.on("LMSSetValue.cmi.core.lesson_status", function(CMIElement, value) {
        var lesson_status = window.API.cmi.core.lesson_status;
        if(typeof lesson_status !== "undefined" && (lesson_status == 'completed' || lesson_status == 'passed')) {
            
            // Call Confetti Animation 
            poof();

            // Store SCORM Completion Reward Points - Motivated Learner 
            if(!scormCompleted) {
                $.ajax({
                    url: "{{ route('student.storeScormCompletionPoints') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        stream_id: '{{$streamData->id}}',
                    },
                    method: "POST",
                    success: function(response) {
                        
                    }
                });
            }

            // Store Learning Reward Points - Knowledge Mastery
            var scorm_percentage = window.API.cmi.core.score.raw;
            if(typeof scorm_percentage !== "undefined" && scorm_percentage != '' && is_allowed) {
                $.ajax({
                    url: "{{ route('student.storeScormLearningPoints') }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        stream_id: '{{$streamData->id}}',
                        percentage : scorm_percentage,
                        
                    },
                    method: "POST",
                    success: function(response) {
                        is_allowed = false;
                    }
                });
            }
        }
    });

    $(document).ready(function() {
        var scormFile = "{{$streamData->scormFile}}";
        if(scormFile) {
            var scormUrl = `{{ asset('scorm-files/stream/') }}/${scormFile}/index_lms.html`;

            // window.open(scormUrl,"_blank","toolbar=yes, location=yes, directories=no, status=no, menubar=yes, scrollbars=yes, resizable=no, copyhistory=yes, width=400, height=400");
            $("#scormSrc").attr("src", scormUrl);
        }
    });
    
</script>

@elseif($sessionData->count())

<script>
    
    $(document).ready(function() {
        const tabsContainer = $('#trainer');
        const tabsWrapper = $('#tabsWrapper');
        const scrollLeftBtn = $('#scrollLeft');
        const scrollRightBtn = $('#scrollRight');
        
        function updateScrollButtons() {
            const scrollLeft = tabsContainer.scrollLeft();
            const scrollWidth = tabsContainer[0].scrollWidth;
            const clientWidth = tabsContainer[0].clientWidth;
            
            // Update button states
            scrollLeftBtn.prop('disabled', scrollLeft <= 0);
            scrollRightBtn.prop('disabled', scrollLeft >= scrollWidth - clientWidth - 1);
            
            // Update fade indicators
            tabsWrapper.toggleClass('can-scroll-left', scrollLeft > 0);
            tabsWrapper.toggleClass('can-scroll-right', scrollLeft < scrollWidth - clientWidth - 1);
        }
        
        function scrollTabs(direction) {
            const scrollAmount = 200;
            const currentScroll = tabsContainer.scrollLeft();
            const newScroll = direction === 'left' 
                ? currentScroll - scrollAmount 
                : currentScroll + scrollAmount;
            
            tabsContainer.animate({
                scrollLeft: newScroll
            }, 300, updateScrollButtons);
        }
        
        // Scroll button events
        scrollLeftBtn.click(function() {
            scrollTabs('left');
        });
        
        scrollRightBtn.click(function() {
            scrollTabs('right');
        });
        
        // Update buttons on scroll
        tabsContainer.on('scroll', updateScrollButtons);
        
        // Update buttons on window resize
        $(window).resize(updateScrollButtons);
        
        // Initial update
        setTimeout(updateScrollButtons, 100);
        
        // Auto-scroll to active tab when clicked
        $('.nav-link').click(function() {
            setTimeout(() => {
                const activeTab = $(this);
                const tabPosition = activeTab.position().left;
                const containerWidth = tabsContainer.width();
                const tabWidth = activeTab.outerWidth();
                
                if (tabPosition < 0 || tabPosition + tabWidth > containerWidth) {
                    const scrollTo = tabsContainer.scrollLeft() + tabPosition - (containerWidth / 2) + (tabWidth / 2);
                    tabsContainer.animate({
                        scrollLeft: scrollTo
                    }, 300, updateScrollButtons);
                }
            }, 50);
        });
    });

</script>

@endif

<script src="{{asset('asset/dist/js/confetti.js')}}"></script>

@endsection