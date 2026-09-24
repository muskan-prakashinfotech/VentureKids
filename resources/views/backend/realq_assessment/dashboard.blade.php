@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">RealQ Assessment</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">RealQ Assessment</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @php
                    $realqMenuItems = isPartnerUser()
                        ? [
                            [
                                'title' => 'School Reports',
                                'description' => 'Generate and download school-wide RealQ reports for your country.',
                                'icon' => 'fas fa-chart-line',
                                'url' => route('backend.realqassessment.reports.index'),
                            ],
                            [
                                'title' => 'Student Reports',
                                'description' => 'Generate, edit, and approve individual student RealQ reports.',
                                'icon' => 'fas fa-user-check',
                                'url' => route('backend.realqassessment.student-reports.index'),
                            ],
                        ]
                        : [
                            [
                                'title' => 'Grade Management',
                                'description' => 'Manage grade levels used across RealQ setup.',
                                'icon' => 'fas fa-layer-group',
                                'url' => route('backend.realqassessment.grades.index'),
                            ],
                            [
                                'title' => 'Subject Management',
                                'description' => 'Create and update RealQ academic subjects.',
                                'icon' => 'fas fa-book-open',
                                'url' => route('backend.realqassessment.subjects.index'),
                            ],
                            [
                                'title' => 'Parameters Management',
                                'description' => 'Define thinking parameters for question and report evaluation.',
                                'icon' => 'fas fa-sliders-h',
                                'url' => route('backend.realqassessment.parameters.index'),
                            ],
                            [
                                'title' => 'Scale Management',
                                'description' => 'Manage assessment scales used by rubric levels.',
                                'icon' => 'fas fa-ruler-combined',
                                'url' => route('backend.realqassessment.scales.index'),
                            ],
                            [
                                'title' => 'Rubric Management',
                                'description' => 'Maintain rubric levels, descriptions, and scores.',
                                'icon' => 'fas fa-clipboard-check',
                                'url' => route('backend.realqassessment.rubrics.index'),
                            ],
                            [
                                'title' => 'Board Management',
                                'description' => 'Manage boards available for RealQ assignment.',
                                'icon' => 'fas fa-university',
                                'url' => route('backend.realqassessment.boards.index'),
                            ],
                            [
                                'title' => 'Topic Generator',
                                'description' => 'Generate, moderate, and approve RealQ topics.',
                                'icon' => 'fas fa-lightbulb',
                                'url' => route('backend.realqassessment.topics.index'),
                            ],
                            [
                                'title' => 'Question Bank',
                                'description' => 'Review approved and rejected RealQ questions.',
                                'icon' => 'fas fa-question-circle',
                                'url' => route('backend.realqassessment.questions.bank'),
                            ],
                            [
                                'title' => 'School Reports',
                                'description' => 'Generate and download school-wide RealQ reports.',
                                'icon' => 'fas fa-chart-line',
                                'url' => route('backend.realqassessment.reports.index'),
                            ],
                            [
                                'title' => 'Student Reports',
                                'description' => 'Generate, edit, and approve individual student RealQ reports.',
                                'icon' => 'fas fa-user-check',
                                'url' => route('backend.realqassessment.student-reports.index'),
                            ],
                            [
                                'title' => 'Accuracy Dashboard',
                                'description' => 'Track how closely AI-generated reports match admin-approved reports.',
                                'icon' => 'fas fa-chart-pie',
                                'url' => route('backend.realqassessment.accuracy-dashboard.index'),
                            ],
                        ];
                @endphp

                <div class="realq-dashboard-grid">
                    @foreach($realqMenuItems as $item)
                        <a href="{{ $item['url'] }}" class="realq-dashboard-card">
                            <span class="realq-dashboard-card__icon">
                                <i class="{{ $item['icon'] }}"></i>
                            </span>
                            <span class="realq-dashboard-card__content">
                                <strong>{{ $item['title'] }}</strong>
                                <small>{{ $item['description'] }}</small>
                            </span>
                            <span class="realq-dashboard-card__arrow">
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
