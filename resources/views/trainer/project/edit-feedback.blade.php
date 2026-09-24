<div class="feedback-modal pb-4">
    <form action="{{ route('trainer.update-feedback', $feedback->id) }}" method="POST" id="editFeedbackForm">
        @csrf
        @method('PUT')

        <div class="light-orange-gradiant p-4">
            <div class="feedback-header mb-4">
                <h2 class="w-100">Edit Feedback</h2>
                <p>Adjust your evaluation and suggestions for this project</p>
            </div>

            <div class="teacher-info">
                <div class="teacher-name">
                    <div class="avatar">{{ strtoupper(substr($feedback->student->name ?? 'S', 0, 1)) }}</div>
                    <div>
                        <p class="name">{{ $feedback->student->name ?? 'Student' }}</p>
                        <p class="date">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg> Last edited on {{ \Carbon\Carbon::parse($feedback->updated_at)->format('F d, Y') }}
                        </p>
                    </div>
                </div>
                <div class="score-box">
                    <span id="modalTotalScore">{{ number_format(($feedback->knowledge_score ?? 0) + ($feedback->skills_score ?? 0) + ($feedback->mindset_score ?? 0)) }}/12</span>
                </div>
            </div>
        </div>

        <div class="px-4">
            <!-- Knowledge -->
            <div class="feedback-section blue-box mb-4">
                <div class="section-header">
                    <div class="feedback-header">
                        <div class="icon blue">
                            <img src="{{ asset('asset/dist/img/Project_Details.svg') }}"/>
                        </div>
                        <div class="feedback-title">
                            <h3>Knowledge</h3>
                            <p>Understanding and research</p>
                        </div>
                    </div>
                    <span class="level-tag">Level <span id="modalKnowledgeDisplay">{{ $feedback->knowledge_score ?? 0 }}</span></span>
                </div>
                <div class="rating-bar rating" data-target="knowledge_score">
                    @for($i=1;$i<=4;$i++)
                        <button type="button" class="rating-option {{ ($feedback->knowledge_score == $i) ? 'active' : '' }}" data-value="{{ $i }}">{{ $i }}: {{ ['Emerging','Developing','Proficient','Advanced'][$i-1] }}</button>
                    @endfor
                    <input type="hidden" name="knowledge_score" id="modal_knowledge_score" value="{{ $feedback->knowledge_score ?? 0 }}">
                </div>
                <textarea name="knowledge_feedback" class="form-control mt-3" rows="5" placeholder="Enter Knowledge feedback...">{{ $feedback->knowledge_feedback }}</textarea>
            </div>

            <!-- Skills -->
            <div class="feedback-section sky-blue-box mb-4">
                <div class="section-header">
                    <div class="feedback-header">
                        <div class="icon sky-blue">
                            <img src="{{ asset('asset/dist/img/Skills.svg') }}"/>
                        </div>
                        <div class="feedback-title">
                            <h3>Skills</h3>
                            <p>Planning and execution</p>
                        </div>
                    </div>
                    <span class="level-tag">Level <span id="modalSkillsDisplay">{{ $feedback->skills_score ?? 0 }}</span></span>
                </div>
                <div class="rating-bar rating" data-target="skills_score">
                    @for($i=1;$i<=4;$i++)
                        <button type="button" class="rating-option {{ ($feedback->skills_score == $i) ? 'active' : '' }}" data-value="{{ $i }}">{{ $i }}: {{ ['Emerging','Developing','Proficient','Advanced'][$i-1] }}</button>
                    @endfor
                    <input type="hidden" name="skills_score" id="modal_skills_score" value="{{ $feedback->skills_score ?? 0 }}">
                </div>
                <textarea name="skills_feedback" class="form-control mt-3" rows="5" placeholder="Enter Skills feedback...">{{ $feedback->skills_feedback }}</textarea>
            </div>

            <!-- Mindset -->
            <div class="feedback-section orange-box">
                <div class="section-header">
                    <div class="feedback-header">
                        <div class="icon red">
                            <img src="{{ asset('asset/dist/img/Mindset.svg') }}"/>
                        </div>
                        <div class="feedback-title">
                            <h3>Mindset</h3>
                            <p>Attitude and approach</p>
                        </div>
                    </div>
                    <span class="level-tag">Level <span id="modalMindsetDisplay">{{ $feedback->mindset_score ?? 0 }}</span></span>
                </div>
                <div class="rating-bar rating" data-target="mindset_score">
                    @for($i=1;$i<=4;$i++)
                        <button type="button" class="rating-option {{ ($feedback->mindset_score == $i) ? 'active' : '' }}" data-value="{{ $i }}">{{ $i }}: {{ ['Emerging','Developing','Proficient','Advanced'][$i-1] }}</button>
                    @endfor
                    <input type="hidden" name="mindset_score" id="modal_mindset_score" value="{{ $feedback->mindset_score ?? 0 }}">
                </div>

                <div class="row mt-3">
                    <div class="col-lg-5 mb-3">
                        <textarea name="mindset_feedback" class="form-control" rows="8" placeholder="Enter Mindset feedback...">{{ $feedback->mindset_feedback }}</textarea>
                    </div>
                    <div class="col-lg-7 mb-3">
                        <div class="chart-box">
                            <div class="graph-title mb-4">
                                <img src="{{ asset('asset/dist/img/graph-icon.svg') }}">
                                <h5> Mindset Analysis</h5>
                            </div>
                            <div class="spider-chart-container">
                                <canvas id="modalSpiderChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teacher Note -->
            <div class="teacher-note mt-4">
                <div class="feedback-section">
                    <div class="section-header">
                        <div class="feedback-header">
                            <div class="icon yellow">
                                <img src="{{ asset('asset/dist/img/Teacher-Note.svg') }}"/>
                            </div>
                            <div class="feedback-title">
                                <h3>Teacher's Note</h3>
                                <p>Personal message for the student</p>
                            </div>
                        </div>
                    </div>
                    <textarea name="teacher_notes" class="form-control mt-3" rows="4" placeholder="Write a note...">{{ $feedback->teacher_notes }}</textarea>
                </div>
            </div>

             <input type="hidden" name="is_publish" id="modal_is_publish" value="{{ (int) $feedback->is_publish }}">

            <div class="feedback-section mt-4">
                <div class="section-header mb-2">
                    <h3>Feedback Actions</h3>
                    <p>Choose how to save this feedback for the student</p>
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
// This script runs immediately when the HTML is injected
(function() {
    console.log('Modal feedback form loaded');

    var $form = $('#editFeedbackForm');
    var $activeButton = null;
    var currentPublish = String($('#modal_is_publish').val() || '0');

    $form.on('click', '.feedback-action-btn', function() {
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
    
    // Handle rating button clicks using event delegation
    $('.feedback-modal').on('click', '.rating-option', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $btn = $(this);
        const value = $btn.data('value');
        const $bar = $btn.closest('.rating-bar');
        const $hidden = $bar.find('input[type="hidden"]');

        // Update visual state
        $bar.find('.rating-option').removeClass('active');
        $btn.addClass('active');

        // Update hidden input value
        $hidden.val(value);

        console.log('Rating clicked:', value, 'Hidden input:', $hidden.attr('id'), 'New value:', $hidden.val());

        // Update displays immediately
        updateModalScoreDisplay();
    });

    // Function: update total in the header + sections
    function updateModalScoreDisplay() {
        const k = parseInt($('#modal_knowledge_score').val()) || 0;
        const s = parseInt($('#modal_skills_score').val()) || 0;
        const m = parseInt($('#modal_mindset_score').val()) || 0;
        const total = k + s + m;

        console.log('Updating scores - K:', k, 'S:', s, 'M:', m, 'Total:', total);

        // Update the visible total in header
        $('#modalTotalScore').text(total + '/12');

        // Update individual displays
        $('#modalKnowledgeDisplay').text(k);
        $('#modalSkillsDisplay').text(s);
        $('#modalMindsetDisplay').text(m);
    }

    // Run once on load
    updateModalScoreDisplay();

    // Loading spinner on submit
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
                $btn.addClass('is-loading-action');
                $btn.html('<span class="spinner-border spinner-border-sm mr-2 feedback-action-spinner" role="status"></span><span class="feedback-action-label">' + loadingLabel + '</span>');
            } else {
                $btn.addClass('is-inactive-action');
            }
        });
    });

    // Initialize spider chart
    setTimeout(function() {
        var chartCanvas = document.getElementById('modalSpiderChart');
        if (chartCanvas) {
            // Destroy existing chart if any
            if (window.modalSpiderChartInstance) {
                window.modalSpiderChartInstance.destroy();
            }
            
            var ctx = chartCanvas.getContext('2d');
            window.modalSpiderChartInstance = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: {!! json_encode($feedback->spider_graph_data['labels']) !!},
                    datasets: [{
                        data: {!! json_encode($feedback->spider_graph_data['values']) !!},
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgb(54, 162, 235)',
                        pointBackgroundColor: 'rgb(54, 162, 235)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(54, 162, 235)',
                        pointRadius: 0,
                        pointHoverRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            min: 0,
                            max: 4,
                            ticks: {
                                stepSize: 1,
                                callback: function(value) {
                                    return value;
                                },
                                backdropColor: 'transparent',
                                font: {
                                    size: 12
                                }
                            },
                            pointLabels: {
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            angleLines: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': Level ' + context.parsed.r;
                                }
                            }
                        }
                    }
                }
            });
        }
    }, 150);
})();
</script>
