@extends('backend.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ __('admin/event.event_list') }}</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('admin/event.event_list') }}</li>
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
                        @if (session()->has('message'))
                            <div class="alert alert-success">
                                {{ session()->get('message') }}
                            </div>
                        @endif
                        <a href="{{ route('backend.createevent.createevent') }}" class="btn btn-primary">{{ __('admin/event.add_event') }}</a>
                        <div class="card-tools">
                            <a href="{{ route('backend.dashboard') }}" class="btn btn-warning"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('admin/event.event_id') }}</th>
                                    <th>{{ __('admin/event.event_image') }}</th>
                                    <th>{{ __('admin/event.name') }}</th>
                                    <th>Last Date To Submit</th>
                                    <th>Start Date</th>
                                    <th>{{ __('admin/event.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($event as $key => $events)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            @if ($events->event_image)
                                                <img src="{{ asset($events->event_image) }}" alt="Product 1"
                                                    class="img-rounded img-size-80" height="150" width="150">
                                            @else
                                                <img src="{{ asset('image/event/images.png') }}" alt="Product 1"
                                                    class="img-rounded img-size-80" hieght="200px" width="200px">
                                            @endif
                                        </td>
                                        <td>{{ $events->event_name }}</td>
                                        <td>{{ date('d F Y', strtotime($events->event_last_date)) }}</td>
                                        <td>{{ date('d F Y', strtotime($events->event_date)) }}</td>
                                        <td>
                                            <a href="{{ route('backend.viewevent.viewevent', $events->id) }}"
                                                class="btn btn-block btn-primary btn-sm"><i class="fas fa-eye"></i></a>
                                            <a href="{{ route('backend.viewchallenge.viewchallenge', $events->id) }}"
                                                class="btn btn-block btn-warning btn-sm" title="Student Challenge files"><i class="fas fa-file"></i></a>
                                            <a href="{{ route('backend.editevent.editevent', $events->id) }}"
                                                class="btn btn-block btn-info btn-sm"><i class="fas fa-edit"></i></a>
                                            @if (!isPartnerUser())
                                                <button type="button"
                                                    data-event-id="{{ route('backend.eventdelete.eventdelete', $events->id) }}"
                                                    class="btn btn-block btn-danger deleteEvent btn-sm">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
        </section>
    </div>

    <script>
        $(document).ready(function() {
            //delete event
            $(document).on('click', '.deleteEvent', function(e) {
                e.preventDefault();
                var id = $(this).data('event-id');
                swal({
                        title: "Are you sure?",
                        text: "You want to delete this file!",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (willDelete) {
                            swal("Success! Your file has been deleted!", {
                                icon: "success",
                            });
                            window.location.href = id;
                        } else {
                            swal("Great! Event records are safe.");
                        }

                    });
            });
        });
    </script>
@endsection
