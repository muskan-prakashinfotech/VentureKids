@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="student-baseline-mode-page">
        <div class="assessment-tab">
            <div class="pageTitle">
                <h2>Assessment</h2>
            </div>
        </div>

        <div class="card baseline-mode-wrap">
            <div class="card-body">
                <div class="baseline-mode-hero">
                    <div class="baseline-mode-hero__eyebrow">Welcome to RealQ</div>
                    <h3 class="baseline-mode-hero__title">Show how you think in real-life situations</h3>
                    <p class="baseline-mode-hero__text">
                        This is not a test with one correct answer. You will read realistic situations, think through them carefully,
                        and explain your ideas in your own words.
                    </p>
                    <div class="baseline-mode-pill-list">
                        <span class="baseline-mode-pill">No right or wrong answer</span>
                        <span class="baseline-mode-pill">Your thinking matters most</span>
                        <span class="baseline-mode-pill">Simple, honest responses</span>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(!empty($completedMode))
                    <div class="alert alert-success">
                        You have already completed the RealQ Assessment.
                        <a href="{{ ($completedMode ?? '') === 'mcq' ? route('student.assessment.report.download', ['mode' => 'mcq']) : route('student.assessment.report.download') }}" class="ml-2">Download Report</a>
                    </div>
                @endif

                <form id="baselineModeForm" action="{{ route('student.assessment.mode.save') }}" method="POST">
                    @csrf
                    <input type="hidden" name="assessment_mode" id="assessment_mode" value="subjective">

                    <div class="baseline-mode-cards">
                        {{--
                        <button type="button" class="baseline-mode-card baseline-mode-card--mcq" data-mode="mcq">
                            <h3>MCQ Based</h3>
                            <p>Answer multiple-choice questions based on the topics you select</p>
                            <span class="baseline-card-cta">TAKE THIS MODE</span>
                        </button>
                        --}}
                        <button type="button" class="baseline-mode-card baseline-mode-card--subjective is-selected" data-mode="subjective" {{ ($completedMode ?? '') === 'subjective' ? 'disabled' : '' }}>
                            <div class="baseline-mode-card__badge">RealQ Experience</div>
                            <h3>How you think matters here</h3>
                            <p>Read each situation, understand the challenge, and explain what you would do and why.</p>
                            <ul class="baseline-mode-card__points">
                                <li>Think carefully</li>
                                <li>Share your ideas clearly</li>
                                <li>Explain your reasoning</li>
                            </ul>
                        </button>
                    </div>

                    <div class="baseline-mode-guidance">
                        <div class="baseline-mode-guidance__item">
                            <strong>Take your time</strong>
                            <span>Read each situation properly before answering.</span>
                        </div>
                        <div class="baseline-mode-guidance__item">
                            <strong>Be yourself</strong>
                            <span>There is no need to guess a perfect answer.</span>
                        </div>
                        <div class="baseline-mode-guidance__item">
                            <strong>Explain your thinking</strong>
                            <span>Your reasoning is more important than just the final response.</span>
                        </div>
                    </div>

                    <div class="baseline-mode-footer">
                        <button type="submit" id="baselineNextBtn" class="baseline-tips-btn baseline-mode-next-btn">Start Assessment</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
    (function() {
        const form = document.getElementById('baselineModeForm');
        const nextBtn = document.getElementById('baselineNextBtn');
        const completedMode = "{{ $completedMode ?? '' }}";

        if (completedMode) {
            nextBtn.setAttribute('disabled', 'disabled');
        }
    })();
</script>
@endsection
