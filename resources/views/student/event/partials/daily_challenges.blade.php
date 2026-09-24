@if($dailyQuizEnabled && !empty($quizzes))  
<div class="CustomBody">
    <h6 class="border-bottom pb-2">Beat boredom with one quiz a day. A new one unlocks every 24 hours!</h6>
    <div class="row">
        @foreach ($quizzes as $challenge)
            <div class="col-md-4 {{ $challenge->is_unlocked ? '' : 'disable-weekly-quiz' }}">
                <div class="InnerContentCard h-auto">
                    <div class="ContentBody p-0">
                        <img src="{{ asset('image/challenge/' . $challenge['challenge_image']) }}" class="trainer-level-image card-img-top" alt="...">
                        <div class="p-3 text-center">
                            <h5 class="card-title text-center w-100 d-block daily-quiz-title">{{ $challenge['challenge_name'] }}</h5>
                            <div>    
                                <a href="{{ route('student.quiz', ['daily', $challenge['id']]) }}" class="btn btn-sm btn-primary take-quiz-btn go_to_quiz">
                                    @if(in_array($challenge['id'], $attemptedQuizIdList)) View Score @else Go to Quiz @endif
                                    <i class="fa fa-arrow-right ms-3 fs-11"></i> 
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@else
    <p>We're updating your daily dose of fun! Quizzes will be back shortly.</p>
@endif
