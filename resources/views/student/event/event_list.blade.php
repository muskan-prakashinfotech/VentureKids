@extends('backend.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="pageTitle">
        <h2>Challenges</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active">Challenges</li>
        </ol>
    </div>

    <section>
        <div class="card">
            <div class="card-body">
                <div class="col-12">
                    <div class="nav nav-tabs BeginnerTab scroll-tab pb-0" id="trainer" role="tablist" aria-orientation="vertical">
                        <button class="nav-item nav-link btn text-left active"
                                id="weekly-challenges"
                                data-url="{{ route('student.daily_challenges') }}"
                                data-toggle="pill"
                                data-target="#weekly-challenges-tab"
                                type="button"
                                role="tab"
                                aria-controls="weekly-challenges-tab"
                                aria-selected="true">
                            Daily Quizzes
                        </button>
                        <button class="nav-item nav-link btn text-left"
                                id="industry-challenges"
                                data-url="{{ route('student.industry_challenges') }}"
                                data-toggle="pill"
                                data-target="#industry-challenges-tab"
                                type="button"
                                role="tab"
                                aria-controls="industry-challenges-tab"
                                aria-selected="false">
                            Industry Challenges
                        </button>
                    </div>
                </div>

                <div class="col-12">
                    <div class="tab-content" id="trainer" role="tablist">
                        <div class="tab-pane fade show active" id="weekly-challenges-tab" role="tabpanel">
                            <!-- Loader for initial load -->
                            <div class="text-center my-5 weekly-loader" id="weekly-loader">
                                <div class="spinner-border" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="industry-challenges-tab" role="tabpanel">
                            <!-- Loader for industry tab -->
                            <div class="text-center my-5 industry-loader" id="industry-loader">
                                <div class="spinner-border" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function applyRandomColors() {
    // Get all child elements of the parent
    const childElements = document.querySelectorAll('.CustomBody .InnerContentCard');

    function getRandomColor() {
        const colorsArray = getComputedStyle(document.documentElement)
                                .getPropertyValue('--colors')
                                .trim();
        const colors = colorsArray.split(', ');
        const randomIndex = Math.floor(Math.random() * colors.length);
        return colors[randomIndex];
    }

    childElements.forEach((child) => {
        child.style.setProperty('--random-color', getRandomColor());
    });
}

$(document).ready(function() {

    function loadTabContent(tab) {
        const target = $(tab).data('target');
        const url = $(tab).data('url');
        const loaderId = target === '#weekly-challenges-tab' ? '#weekly-loader' : '#industry-loader';

        if (!$(target).data('loaded')) {
            $(loaderId).show(); // Show loader

            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    $(target).html(data);
                    $(target).data('loaded', true);

                    // Apply random colors to new content
                    applyRandomColors();
                },
                error: function() {
                    $(target).html('<p class="text-danger text-center">Failed to load data. Please try again.</p>');
                },
                complete: function() {
                    $(loaderId).hide(); // Hide loader after load
                }
            });
        }
    }

    // Explicitly select the active tab button
    const activeTab = $('#trainer .nav-link.active');
    loadTabContent(activeTab); // Load Daily Quizzes by default

    // Load content on tab click
    $('.nav-link').on('shown.bs.tab', function (e) {
        loadTabContent(e.target);
    });
});
</script>
@endsection
