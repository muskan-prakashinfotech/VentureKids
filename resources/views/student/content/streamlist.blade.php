@extends('backend.layouts.app')
@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <!-- Page Title  -->
    <div class="pageTitle">
        <h2>My Learning Space</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">{{ __('admin/content.home') }}</a></li>
            <li class="breadcrumb-item active">My Learning Space</li>
        </ol>
    </div>

    <!-- Main content -->
    <section>
        <div class="card bg_light_grey">
            <div class="card-header">
                <h3 class="card-title">{{ $grade->grade }}
                    <!-- {{ $grade->grade . ' ' . $grade->description }} -->
                </h3>
                <div class="card-tool">
                    <a href="{{ route('student.contentlist.contentList') }}" class="btn btn-sm btn-warning">
                        <i class="material-icons">west</i>
                        Back
                    </a>
                </div>
            </div>

            <div class="card-body">
                @if($streams->count())
                <div class="row">
                    <div class="col-12">
                        <div class="nav nav-pills" id="v-pills-tab" role="tablist">
                            <ul class="beginner-cardList stream-sessionList new_design_three_col lesson_plan_fig row w-100">
                                @forelse ($streams as $stream)
                                <li class="col-xl-4 col-lg-6 col-md-6 col_wrap">
                                    <div class="detailsInfo @if($hasAccess && !$stream->hasAccess) stream-list-lock @endif">
                                        <div class="icons"> 
                                            @if($stream->image)
                                            <img class="h-100" src="{{ asset('image/stream/'.$stream->image) }}">
                                            @else
                                            <i class="fa fa-lightbulb"></i> 
                                            @endif
                                        </div>
                                        <div class="content">
                                            <h2>{{ $stream->title }}</h2>
                                            <p>{{ $stream->description }}</p>
                                        </div>
                                        
                                        <div class="btns-group d-flex flex-wrap justify-content-end" aria-label="Basic example">
                                            @if ($hasAccess)
                                            <a href="{{ route('student.contentview.contentView', $stream->id) }}"
                                                class="btn btn-sm btn-light">
                                                <span>View Content</span>
                                                <i class="material-icons">east</i>
                                            </a>
                                            @else
                                            <button class="btn btn-sm btn-light get_access_btn">Get Access Now</button>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                                @empty
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
                @else
                No Data Found
                @endif
            </div>
        </div>
    </section>
</div>
<script>
    $(function() {
        $(document).on('click', '.get_access_btn', function() {
            swal("You don't have access to this content yet. Please contact system administrator.", {
                button: "Ok",
            });
        });
    });
</script>
@endsection