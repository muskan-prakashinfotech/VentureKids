@extends('backend.layouts.app')
@section('content')
<div class="content-wrapper">
    <section>
        @if (session()->has('message'))
            <div class="alert alert-success m-3">{{ session('message') }}</div>
        @endif
        @if (session()->has('message1'))
            <div class="alert alert-danger m-3">{{ session('message1') }}</div>
        @endif
            <div class="assessment-tab">
                <div class="pageTitle">
                    <h2>Assessment</h2>
                    <!-- <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle grade-dropdown-btn" type="button" id="gradeDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Select Grade
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="gradeDropdown">
                            @foreach($grades as $grade)
                                <a class="dropdown-item" href="#">{{ $grade->name }}</a>
                            @endforeach
                        </div>
                    </div> -->
                </div>
            </div>   
        <div class="card">
           
            <div class="card-body">
                <h2 class="assessment-title">
                    Holistic Assessment of <span class="highlight">Entrepreneurial Mindset Program</span>
                </h2>
                <div class="assessment-card">
                    <div class="assessment-info">
                        <div class="assessment-title">Standard Assessment</div>
                        @php
                            $standardActionClass = !empty($standardActionUrl) && in_array($standardActionLabel ?? '', ['Start now', 'Resume'], true)
                                ? 'btn-start student-assessment-btn-start--active'
                                : 'btn btn-start';
                        @endphp
                        @if(!empty($standardActionUrl))
                            <a href="{{ $standardActionUrl }}" class="{{ $standardActionClass }}">{{ $standardActionLabel }}</a>
                        @else
                            <button class="btn btn-start" type="button">{{ $standardActionLabel }}</button>
                        @endif
                    </div>
                    <img src="{{asset('asset/dist/img/assessment1.png')}}" alt="Assessment Image">
                </div>

                <div class="assessment-card">
                    <div class="assessment-info">
                        <div class="assessment-title">RealQ Assessment</div>
                        @php
                            // Previously: btn-start student-assessment-btn-start--active for Start now/Resume, btn btn-start otherwise
                            if ($realqActionLabel === 'Download Report') {
                                $realqActionClass = 'btn btn-success';
                            } elseif ($realqActionLabel === 'Report Under Review') {
                                $realqActionClass = 'btn realq-report-review-btn';
                            } else {
                                $realqActionClass = 'btn btn-start';
                            }
                            if(!empty($realqActionUrl) && in_array($realqActionLabel, ['Start now', 'Resume'])) {
                                $realqActionClass = 'btn-start student-assessment-btn-start--active';
                            }
                        @endphp
                        @if(!empty($realqActionUrl))
                            <a href="{{ $realqActionUrl }}" class="{{ $realqActionClass }}">{{ $realqActionLabel }}</a>
                        @else
                            <button class="{{ $realqActionClass }}" type="button">{{ $realqActionLabel }}</button>
                        @endif
                    </div>
                    <img src="{{asset('asset/dist/img/assessment2.png')}}" alt="RealQ Assessment Image">
                </div>

                <div class="assessment-card">
                    <div class="assessment-info">
                        <div class="assessment-title">Post Assessment</div>
                            <button class="btn btn-start">Start now</button>
                        </div>
                    <img src="{{asset('asset/dist/img/assessment3.png')}}" alt="Post Assessment Image">
                </div>

                <div class="assessment-card">
                    <div class="assessment-info">
                        <div class="assessment-title">Student Reflections (Self-Insights)</div>
                            <button class="btn btn-start">Start now</button>
                        </div>
                    <img src="{{asset('asset/dist/img/assessment4.png')}}" alt="Reflection Image">
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
