<div id="student-leaderboard-section" class="row mb-4 stud-dashboard-leader-board">
    @if(!empty($topFiveStudents))
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header custom_padding bg-skyblue">
                <h5 class="m-0 text-white">Leaderboard</h5>
            </div>
            <div class="card-body bg-white p-0">
                @foreach($topFiveStudents as $stud)
                    <div id="heading" class="card-header border-bottom">
                        <div class="leader-board-avtar mr-2">
                            <img class="h-100 img-fluid img-circle w-100" src="@if(!empty($stud['image'])) {{ asset('tenants/'.$stud['image']) }} @else {{ asset('img/default_image.png') }} @endif" />
                        </div>
                        <span class="text text-bold stud-dashboard-name-text">{{ $stud['user']['name'] }}</span>
                        <span class="ml-auto text-bold">{{ $stud['tot_reward_points'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    <div class="@if(!empty($topFiveStudents)) col-lg-6 @else col-lg-12 @endif mb-4">
        <div class="button-wrao mb-3 text-center">
            <a href="https://calendly.com/tanyasarin/venderkids-mentoring-call" target="_blank"> <span>Mentor Call</span> <i class="fa fa-angle-right"></i></a>
        </div>
        <div class="speak-up-wrap w-100">
            <label for="" class="w-100">Speak Up...</label>
            <textarea class="form-control w-100 speak-up" placeholder="speak up ..." disabled resize="false"></textarea>
        </div>
    </div>
</div>
