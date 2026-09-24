@extends('backend.layouts.app')
@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" style="padding: 0px 20px;">
    <!-- Content Header (Page header) -->
    <!-- <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        {{-- <h1 class="m-0">Lesson Plans</h1> --}}
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">{{ __('admin/content.home') }}</a></li>
                            <li class="breadcrumb-item active">Lesson Plans</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div> -->
    <!-- /.content-header -->

    <!-- Main content -->
    <section>
        <div class="MainContent">
            @if (session()->has('success'))
            <div class="alert alert-success" style="text-align: center;">
                {{ session()->get('success') }}
            </div>
            @endif

            @if (session()->has('update_success'))
            <div class="alert alert-success" style="text-align: center;">
                {{ session()->get('update_success') }}
            </div>
            @endif

            @if (session()->has('delete_success'))
            <div class="alert alert-success" style="text-align: center;">
                {{ session()->get('delete_success') }}
            </div>
            @endif
           
            <div class="CustomBody levels-add-ons">
                @if(isset($filtered_levels['primary']))
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="mb-2">Levels</div>
                        </div>
                        <div class="row for-loop-btn fig-card"> 
                            @forelse ($filtered_levels['primary'] as $k => $level)
                                <div class="col-md-6 bg-b">
                                    <div class="InnerContentCard levels bg-white two-box">
                                        <div class="ContentBody d-flex flex-wrap align-items-center">
                                            <div class="IConHere text-center">
                                                <img class="level_icon" src="{{asset('/image/level/icon/'.$level['level_icon'])}}">
                                            </div>
                                            <div class="content mb-4">
                                                <h5 class="card-title"><span>{{ $level['grade'] }}</span>
                                                    @if(in_array($level['id'], $studentGradeIds))
                                                    <span class="content-ninja-verified fas fa-circle"></span>
                                                    @endif
                                                </h5>
                                                <p class="card-text">{{ $level['description'] }}</p>
                                            </div>
                                            <div class="view-button d-flex align-items-center justify-content-end w-100">
                                                <a href="{{ route('student.contentlist.streamlist', $level['id']) }}" class="btn ThemeBtnContent btn-primary">View Content <i class="fa fa-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                            @endforelse
                        
                        </div>
                        </div>
                    </div>
                @endif
                @if(isset($filtered_levels['add-ons']))
                    <hr class="mt-2 mb-2 opacity-50"></hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="mb-2">Content Add-Ons</div>
                        </div>
                        <div class="row for-loop-color"> 
                            @forelse ($filtered_levels['add-ons'] as $k => $level)
                                <div class="col-lg-4 col-md-6 bg-r">
                                    <div class="InnerContentCard add-ons-box fig-img-card">
                                        <div class="ContentBody p-0">
                                            <div class="IConHere text-center ml-auto mr-auto mb-3"> <img class="level_icon" src="@if(!empty($level['image']) && file_exists(public_path().'/image/level/'.$level['image'])) {{asset('/image/level/'.$level['image'])}} @else {{asset_v('/image/level/default.jpg')}} @endif"> </div>
                                            <div class="content mb-auto">
                                                <h2 class="card-title text-start w-100 d-block text-black">{{ $level['grade'] }}</h2>
                                                <p class="card-text text-start text-black">{{ $level['description'] }}</p>
                                            </div>
                                            <div class="view-button d-flex align-items-center justify-content-start mt-2">
                                                <a href="{{ route('student.contentlist.streamlist', $level['id']) }}" class="btn ThemeBtnContent btn-orange">View Content <i class="fa fa-arrow-right"></i> </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection