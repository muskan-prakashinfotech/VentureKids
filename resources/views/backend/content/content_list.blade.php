@extends('backend.layouts.app')
@section('content')
    <style>
        .social-link {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            border-radius: 50%;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .social-link:hover,
        .social-link:focus {
            background: #ddd;
            text-decoration: none;
            color: #555;
        }
    </style>


    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ __('admin/content.content_list_trainer') }}</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('admin/content.content_list_trainer') }}</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">

            </div>

            <div class="card">
                <div class="card-header">
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
                    <a href="{{ route('backend.addcontent.addContent') }}" class="btn btn-primary">{{ __('admin/content.list_content') }}</a>
                    <div class="card-tools">
                        <a href="{{ route('backend.dashboard') }}" class="btn btn-warning float-right"> <i class="fa fa-chevron-left" aria-hidden="true"></i> Back</a>
                    </div>
                </div>

                <div class="card-body table-responsive">
                    <div class="row">
                        @forelse ($allGrade as $grade)
                            <div class="col-md-4">
                                <div class="card">
                                    <img src="{{ asset('image/level/trainer/' . $grade->image) }}" class="card-img-top" style="height: 250px !important;" alt="...">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $grade->grade }}</h5>
                                        <p class="card-text">{{ $grade->description }}</p>
                                        <a href="{{ route('backend.contentlist.streamlist', $grade->id) }}"
                                            class="btn btn-primary">View Content</a>
                                    </div>
                                </div>
                            </div>
                        @empty
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>


    {{-- <script>
        $(function() {

            $("#contentListDatatable").dataTable({
                "searching": true
            });

            //Get a reference to the new datatable
            var table = $('#contentListDatatable').DataTable();

            $("#contentListDatatable_filter.group-filter").append($("#group"));
            // $("#contentListDatatable_filter.stream-filter").append($("#stream"));

            // $("#contentListDatatable_filter.dataTables_filter").append($("#testCategory"));

            var groupIndex = 0;
            var streamIndex = 0;
            $("#contentListDatatable th").each(function(i) {
                if ($($(this)).html() == "Select Age Group") {
                    groupIndex = i;
                    return true;
                } else if ($($(this)).html() == "Stream") {
                    streamIndex = i;
                    return true;
                }
            });

            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var groupItem = $('#group').val();
                    var streamItem = $('#stream').val();
                    if (groupItem != '') {
                        var group = data[groupIndex];
                        if (groupItem === "" || group.includes(groupItem)) {
                            if (streamItem != '') {
                                var stream = data[streamIndex];
                                if (streamItem === "" || stream.includes(streamItem)) {
                                    return true;
                                }
                            } else {
                                return true;
                            }
                        }
                    } else {
                        return true;
                    }
                }
            );


            $("#group").change(function(e) {
                table.draw();
            });
            $("#stream").change(function(e) {
                table.draw();
            });

            table.draw();

        });
    </script> --}}
@endsection
