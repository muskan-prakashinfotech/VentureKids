<div class="feedback-modal pb-4">
    <form action="{{ route('trainer.update-submission-feedback', $feedback->id) }}" method="POST" id="editSubmissionFeedbackForm">
        @csrf
        @method('PUT')

        <div class="light-orange-gradiant p-4">
            <div class="feedback-header mb-4">
                <h2 class="w-100">Edit Feedback</h2>
                <p>Review and adjust the AI-generated feedback for this project</p>
            </div>

            <div class="teacher-info">
                <div class="teacher-name">
                    <div class="avatar">{{ strtoupper(substr(optional($feedback->studentProject->student)->name ?: 'S', 0, 1)) }}</div>
                    <div>
                        <p class="name">{{ optional($feedback->studentProject->student)->name ?: 'Student' }}</p>
                        <p class="date">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg> Last edited on {{ \Carbon\Carbon::parse($feedback->updated_at)->format('F d, Y') }}
                        </p>
                    </div>
                </div>
                <div class="score-box">
                    <span class="smart-score-label">Smart Score</span>
                    <span id="modalSmartScore">{{ $feedback->smart_score !== null ? $feedback->smart_score . '/12' : 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="px-4 pt-4">
            <div class="feedback-section mb-4">
                <div class="section-header mb-2">
                    <h3>Smart Score</h3>
                    <p class="mb-0">Rate the overall project quality from 1 to 12</p>
                </div>
                <input
                    type="number"
                    name="smart_score"
                    class="form-control"
                    min="1"
                    max="12"
                    step="1"
                    value="{{ old('smart_score', $feedback->smart_score) }}"
                    placeholder="Enter SmartScore"
                >
            </div>

            <div class="feedback-section mb-4">
                <div class="section-header mb-2">
                    <h3>Teacher Feedback Note <small class="text-muted">(Public)</small></h3>
                </div>
                <textarea name="public_note" class="form-control" rows="6" placeholder="Enter public feedback note...">{{ $feedback->public_note }}</textarea>
            </div>

            <div class="feedback-section">
                <div class="section-header mb-2">
                    <h3>Suggestions for Improvement <small class="text-muted">(Private)</small></h3>
                </div>
                <textarea name="private_suggestions" class="form-control" rows="6" placeholder="Enter private suggestions...">{{ $feedback->private_suggestions }}</textarea>
            </div>

            <input type="hidden" name="is_publish" id="modal_is_publish" value="{{ (int) $feedback->is_publish }}">

            <div class="feedback-section mt-4">
                <div class="section-header mb-2">
                    <h3>Feedback Actions</h3>
                    <p class="mb-0">Choose how to save this feedback for the student</p>
                </div>
                @php
                    $currentPublishLabel = match ((int) $feedback->is_publish) {
                        0 => 'Draft',
                        1 => 'Published',
                        2 => 'Approved',
                        default => 'Draft',
                    };
                @endphp
                <div class="feedback-current-status mb-3">
                    Current Status: <span>{{ $currentPublishLabel }}</span>
                </div>
                <div class="feedback-action-group d-flex flex-wrap justify-content-end">
                    <button type="submit" class="btn btn-outline-secondary feedback-action-btn mr-2 mb-2" data-publish="0" data-loading-label="Saving..." id="modalSaveBtn">
                        <span class="feedback-action-label">Save Feedback</span>
                        <span class="spinner-border spinner-border-sm d-none feedback-action-spinner" role="status"></span>
                    </button>
                    <button type="submit" class="btn btn-primary feedback-action-btn mr-2 mb-2" data-publish="1" data-loading-label="Publishing..." id="modalPublishBtn">
                        <span class="feedback-action-label">Publish Feedback</span>
                        <span class="spinner-border spinner-border-sm d-none feedback-action-spinner" role="status"></span>
                    </button>
                    <button type="submit" class="btn btn-success feedback-action-btn mb-2" data-publish="2" data-loading-label="Publishing & Approving..." id="modalApproveBtn">
                        <span class="feedback-action-label">Publish Feedback &amp; Approve Project</span>
                        <span class="spinner-border spinner-border-sm d-none feedback-action-spinner" role="status"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function() {
    var $form = $('#editSubmissionFeedbackForm');
    var $activeButton = null;
    var currentPublish = String($('#modal_is_publish').val() || '0');

    $('#editSubmissionFeedbackForm').on('click', '.feedback-action-btn', function() {
        $activeButton = $(this);
        $('#modal_is_publish').val($activeButton.data('publish'));

        $form.find('.feedback-action-btn').removeClass('is-active-action');
        $activeButton.addClass('is-active-action');
    });

    var presetButton = $form.find('.feedback-action-btn[data-publish="' + currentPublish + '"]');
    if (presetButton.length) {
        $activeButton = presetButton.first();
        $form.find('.feedback-action-btn').removeClass('is-active-action');
        $activeButton.addClass('is-active-action');
    }

    $form.on('submit', function() {
        var btn = $form.find('.feedback-action-btn');
        if (!$activeButton || !$activeButton.length) {
            $activeButton = btn.first();
        }

        btn.prop('disabled', true);
        btn.each(function() {
            var $btn = $(this);
            var loadingLabel = $btn.data('loading-label') || 'Saving...';

            if ($activeButton && $btn.is($activeButton)) {
                $btn.html('<span class="spinner-border spinner-border-sm mr-2 feedback-action-spinner" role="status"></span><span class="feedback-action-label">' + loadingLabel + '</span>');
                $btn.addClass('is-loading-action');
            } else {
                $btn.addClass('is-inactive-action');
            }
        });
    });
})();
</script>
