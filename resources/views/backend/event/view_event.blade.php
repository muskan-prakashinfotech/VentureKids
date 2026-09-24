@extends('backend.layouts.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ $event->event_name }}</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ $event->event_name }}</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <a href="{{ route('backend.createevent.createevent') }}" class="btn btn-primary">{{ __('admin/event.add_event') }}</a>
                        <div class="card-tools">
                            <a href="{{ URL::previous() }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <img src="{{ asset($event->event_image) }}" class="img-fluid w-100">
                            </div>
                            <div class="col-md-6">
                                <div class="pt-4 pb-2">
                                    <p><strong><i class="mr-1 fas fa-map-marker-alt">
                                            </i>{{ $event->event_address }}</strong>
                                    </p>
                                </div>
                                <p>{!! $event->event_description !!}</p>
                                <?php $file = explode(',', $event->event_poster); ?>
                                <div class="">
                                    @foreach ($file as $files)
                                        <a class="btn btn-warning" href="{{ asset($files) }}" target="_blank">
                                            <i class="fas fa-file"></i>
                                        </a>
                                    @endforeach
                                </div><br>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
        </section>
    </div>
@endsection
