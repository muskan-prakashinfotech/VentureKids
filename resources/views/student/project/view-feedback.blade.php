<div class="feedback-modal pb-4">
    <div class="light-orange-gradiant p-4">
        <div class="feedback-header mb-4">
            <h2 class="w-100">Teacher Feedback</h2>
            <p>Review your teacher's evaluation and suggestions</p>
        </div>

        <div class="teacher-info">
            <div class="teacher-name">
                <div class="avatar">{{ strtoupper(substr($feedback->trainer->trainer_name ?? 'T', 0, 1)) }}</div>
                <div>
                    <p class="name">{{ $feedback->trainer->trainer_name ?? 'Teacher' }}</p>
                    <p class="date">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg> Evaluated on {{ \Carbon\Carbon::parse($feedback->created_at)->format('F d, Y') }}
                    </p>
                </div>
            </div>
            <div class="score-box">
                <span>{{ number_format(($feedback->knowledge_score ?? 0) + ($feedback->skills_score ?? 0) + ($feedback->mindset_score ?? 0), 0) }}/12</span>
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
                <span class="level-tag">Level {{ $feedback->knowledge_score ?? 0 }}</span>
            </div>
            <div class="rating">
                @for($i=1;$i<=4;$i++)
                    <button class="{{ ($feedback->knowledge_score == $i) ? 'active' : '' }}">{{ $i }}: {{ ['Emerging','Developing','Proficient','Advanced'][$i-1] }}</button>
                @endfor
            </div>
            <p class="comment mb-0">{{ $feedback->knowledge_feedback ?? 'No feedback provided.' }}</p>
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
                <span class="level-tag">Level {{ $feedback->skills_score ?? 0 }}</span>
            </div>
            <div class="rating">
                @for($i=1;$i<=4;$i++)
                    <button class="{{ ($feedback->skills_score == $i) ? 'active' : '' }}">{{ $i }}: {{ ['Emerging','Developing','Proficient','Advanced'][$i-1] }}</button>
                @endfor
            </div>
            <p class="comment mb-0">{{ $feedback->skills_feedback ?? 'No feedback provided.' }}</p>
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
                <span class="level-tag">Level {{ $feedback->mindset_score ?? 0 }}</span>
            </div>
            <div class="rating">
                @for($i=1;$i<=4;$i++)
                    <button class="{{ ($feedback->mindset_score == $i) ? 'active' : '' }}">{{ $i }}: {{ ['Emerging','Developing','Proficient','Advanced'][$i-1] }}</button>
                @endfor
            </div>

            <div class="row mt-3">
                <div class="col-lg-5 mb-3">
                    <p class="comment h-100 mb-0">{{ $feedback->mindset_feedback ?? 'No feedback provided.' }}</p>
                </div>
                <div class="col-lg-7 mb-3">
                    <div class="chart-box">
                        <div class="graph-title mb-4">
                            <img src="{{ asset('asset/dist/img/graph-icon.svg') }}">
                            <h5> Mindset Analysis</h5>
                        </div>
                        <div class="spider-chart-container">
                            <canvas id="viewSpiderChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teacher's Note -->
        @if($feedback->teacher_notes)
        <div class="teacher-note mt-4">
            <div class="feedback-section">
                <div class="section-header">
                    <div class="feedback-header">
                        <div class="icon yellow">
                            <img src="{{ asset('asset/dist/img/Teacher-Note.svg') }}"/>
                        </div>
                        <div class="feedback-title">
                            <h3>Teacher's Note</h3>
                            <p>Personal message from your teacher</p>
                        </div>
                    </div>
                </div>
                <p class="comment mb-0">{{ $feedback->teacher_notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
(function() {
    setTimeout(function() {
        var chartCanvas = document.getElementById('viewSpiderChart');
        if (chartCanvas) {
            if (window.viewSpiderChartInstance) {
                window.viewSpiderChartInstance.destroy();
            }
            
            var ctx = chartCanvas.getContext('2d');
            window.viewSpiderChartInstance = new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: {!! json_encode($feedback->spider_graph_data['labels']) !!},
                    datasets: [{
                        label: 'Mindset Assessment',
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
                                display: false
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