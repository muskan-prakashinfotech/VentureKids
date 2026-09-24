<div class="feedback-modal pb-4">
    <div class="light-orange-gradiant p-4">
        <div class="feedback-header mb-4">
            <h2 class="w-100">View Feedback</h2>
            <p>Review the feedback shared for this project</p>
        </div>

        <div class="teacher-info">
            <div class="teacher-name">
                <div class="avatar">{{ strtoupper(substr(optional($feedback->trainer)->trainer_name ?: 'T', 0, 1)) }}</div>
                <div>
                    <p class="name">{{ optional($feedback->trainer)->trainer_name ?: 'Teacher' }}</p>
                    <p class="date">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg> Evaluated on {{ \Carbon\Carbon::parse($feedback->updated_at)->format('F d, Y') }}
                    </p>
                </div>
            </div>
            <div class="trainer-feedback-smartscore-box">
                <span class="trainer-feedback-smartscore-label">SmartScore</span>
                <span class="trainer-feedback-smartscore-value">{{ $feedback->smart_score !== null ? $feedback->smart_score . '/12' : 'N/A' }}</span>
            </div>
        </div>
    </div>

    <div class="px-4 pt-4">
        <div class="feedback-section mb-4">
            <div class="section-header mb-2">
                <h3>Smart Score</h3>
            </div>
            <p class="comment mb-0">{{ $feedback->smart_score !== null ? $feedback->smart_score . '/12' : 'No score provided.' }}</p>
        </div>

        <div class="feedback-section mb-4">
            <div class="section-header mb-2">
                <h3>Teacher Feedback Note <small class="text-muted">(Public)</small></h3>
            </div>
            <p class="comment mb-0">{{ $feedback->public_note ?: 'No feedback provided.' }}</p>
        </div>

        <div class="feedback-section">
            <div class="section-header mb-2">
                <h3>Suggestions for Improvement <small class="text-muted">(Private)</small></h3>
            </div>
            <p class="comment mb-0">{{ $feedback->private_suggestions ?: 'No suggestions provided.' }}</p>
        </div>
    </div>
</div>
