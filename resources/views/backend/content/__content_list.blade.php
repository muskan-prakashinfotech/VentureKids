@extends('backend.layouts.app')
@section('content')
    <!-- <style>
      select.form-control{
        display: inline;
        width: 200px;
        margin-left: 25px;
      }
    </style> -->


    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="mb-2 row">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ __('admin/content.content_list') }}</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('backend.home') }}">{{ __('admin.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ __('admin/content.list_content') }}</li>
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
                    <div class="card-tools">
                        <a href="{{ route('backend.addcontent.addContent') }}"
                            class="btn btn-primary">{{ __('admin/content.list_content') }}</a>
                    </div>
                </div>

                <div class="card-body table-responsive">

                    <div class="mb-3 col-md-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="group-filter">
                                    <label for="">Age Group</label>
                                    <select id="group" class="form-control">
                                        <option value="">Show All</option>
                                        @foreach ($contents as $content)
                                            <option value="{{ $content['get_age_group']['grade'] }}">
                                                {{ $content['get_age_group']['grade'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stream-filter">
                                    <label for="">Stream</label>
                                    <select id="stream" class="form-control">
                                        <option value="">Show All</option>
                                        @foreach ($contents as $content)
                                            <option value="{{ $content['get_stream']['title'] }}">
                                                {{ $content['get_stream']['title'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>


                    <table id="contentListDatatable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('admin/content.video') }}</th>
                                <th>{{ __('admin/content.title') }}</th>
                                <th>{{ __('admin/content.description') }}</th>
                                <th>{{ __('admin/content.age_group') }}</th>
                                <th>{{ __('admin/content.stream') }}</th>
                                <th>{{ __('admin/content.worksheets') }}</th>
                                <th>{{ __('admin/content.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contents as $content)
                                <tr>
                                    <td>
                                        <video width="320" height="240" controls>
                                            <source src="{{ url('/video/content/' . $content['video']) }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    </td>
                                    <td>{{ $content['title'] }} </td>
                                    <td>
                                        <!-- {!! strip_tags(substr($content['description'], 0, 100)) !!} -->
                                        {!! strip_tags(str_replace('&nbsp;', ' ', $content['description'])) !!}
                                    </td>
                                    <td>
                                        {{ $content['get_age_group']['grade'] }}
                                    </td>
                                    <td>
                                        {{ $content['get_stream']['title'] }}
                                    </td>
                                    <td>
                                        <a class="btn btn-warning"
                                            href="{{ url('/files/content/' . $content['worksheet']) }}" download>
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </td>

                                    <td>
                                        <a href="{{ route('backend.contentview.contentView', $content['id']) }}"
                                            class="btn btn-block btn-primary btn-sm"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('backend.contentedit.contentEdit', $content['id']) }}"
                                            class="btn btn-block btn-info btn-sm"><i class="fas fa-edit"></i></a>
                                        <a href="{{ route('backend.contentdelete.contentDelete', $content['id']) }}"
                                            class="btn btn-block btn-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
    </div>
    </section>
    </div>


    <script>
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
            // var streamIndex = 0;
            $("#contentListDatatable th").each(function(i) {
                if ($($(this)).html() == "Select Age Group") {
                    groupIndex = i;
                    return false;
                }
                // if ($($(this)).html() == "Stream") {
                //   streamIndex = i;
                //   return false;
                // }
            });

            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var groupItem = $('#group').val()
                    var group = data[groupIndex];
                    if (groupItem === "" || group.includes(groupItem)) {
                        return true;
                    }
                    // var streamItem = $('#stream').val()
                    // var stream = data[streamItem];
                    // if (streamItem === "" || stream.includes(streamItem)) {
                    //   return true;
                    // }
                    // return false;
                }
            );


            $("#group").change(function(e) {
                table.draw();
            });
            // $("#stream").change(function(e) {
            //   table.draw();
            // });

            table.draw();

        });
    </script>
@endsection
