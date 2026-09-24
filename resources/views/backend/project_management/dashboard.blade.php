@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">Project Management</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">Project Management</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                @php
                    $projectManagementMenuItems = [
                        [
                            'title' => 'Project Section',
                            'description' => 'Manage project sections and their questions.',
                            'icon' => 'fas fa-list',
                            'url' => route('backend.projectSectionlist.projectSectionList'),
                        ],
                    ];
                @endphp

                <div class="realq-dashboard-grid">
                    @foreach($projectManagementMenuItems as $item)
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
